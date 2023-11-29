<?php

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\CloudPrnt\StarCloudPrntJob;
use Appflix\DewaShop\Core\CloudPrnt\StarCloudPrntStarLineModeJob;
use Appflix\DewaShop\Core\CloudPrnt\StarCloudPrntStarPrntJob;
use Appflix\DewaShop\Core\CloudPrnt\StarCloudPrntTextPlainJob;
use Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover\PrintTurnoverEntity;
use Appflix\DewaShop\Core\Content\Printer\PrinterEntity;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Appflix\DewaShop\Core\Event\PrintJobFromPrinterClassEvent;
use Appflix\DewaShop\Core\Service\PrintService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\LoggerFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class PrinterController extends StorefrontController
{
    private EntityRepository $printerRepository;
    private EntityRepository $printJobRepository;
    private EntityRepository $printTurnoverRepository;
    private PrintService $printService;
    private Environment $twig;
    private TranslatorInterface $translator;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;

    public function __construct(
        EntityRepository $printerRepository,
        EntityRepository $printJobRepository,
        EntityRepository $printTurnoverRepository,
        PrintService $printService,
        Environment $twig,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        LoggerFactory $loggerFactory
    )
    {
        $this->printerRepository = $printerRepository;
        $this->printJobRepository = $printJobRepository;
        $this->printTurnoverRepository = $printTurnoverRepository;
        $this->printService = $printService;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;

        $this->logger = $loggerFactory->createRotating(
            'dewa_printer',
            7,
            Logger::DEBUG
        );
    }

    /**
     * @Route("/printer", name="dewa-shop.printer_post", methods={"POST"}, defaults={"csrf_protected"=false})
     */
    public function printerPoll(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $this->logger->info('POLL', $request->request->all());

        $requestData = json_decode($request->getContent(), true);
        $responseData = ['jobReady' => false];

        $printer = $this->printService->getPrinterFromMAC($requestData['printerMAC']);

        if ($printer && $printer->isActive()) {
            $printerReady = true;

            $payload = [
                'id' => $printer->getId(),
                'lastPolled' => date('Y-m-d H:i:s'),
                'status' => urldecode($requestData['statusCode'])
            ];

            // Save client action data if sent by printer
            if (isset($requestData['clientAction'])) {
                foreach ($requestData['clientAction'] as $clientAction) {
                    switch ($clientAction['request']) {
                        case 'PageInfo':
                            $payload['dotWidth'] = intval($clientAction['result']['printWidth']) * intval($clientAction['result']['horizontalResolution']);
                            break;
                        case 'ClientType':
                            $payload['printerType'] = strval($clientAction['result']);
                            break;
                        case 'ClientVersion':
                            $payload['printerVersion'] = strval($clientAction['result']);
                            break;
                    }
                }
            }

            // Request client action if necessary (no dotWidth saved, yet)
            if ($printer->getDotWidth() === 0 && empty($payload['dotWidth'])) {
                $printerReady = false;
                $responseData['clientAction'] = [
                    ['request' => 'PageInfo', 'options' => ''],
                    ['request' => 'ClientType', 'options' => ''],
                    ['request' => 'ClientVersion', 'options' => ''],
                ];
            }

            $this->printerRepository->upsert([$payload], $salesChannelContext->getContext());

            if ($printerReady && ($printer->getPrintJobs()->count() > 0 || $printer->getPrintTurnovers()->count() > 0)) {
                $responseData['jobReady'] = true;

                if ($this->cputilAvailable()) {
                    $responseData['mediaTypes'] = ['application/vnd.star.line', 'application/vnd.star.linematrix', 'application/vnd.star.starprnt', 'application/vnd.star.starprntcore', 'text/vnd.star.markup'];
                } else {
                    $responseData['mediaTypes'] = ['application/vnd.star.line', 'application/vnd.star.linematrix', 'application/vnd.star.starprnt', 'text/plain'];
                }
            }
        }

        $this->logger->info('RESPONSE', $responseData);

        return new JsonResponse($responseData);
    }

    /**
     * @Route("/printer", name="dewa-shop.printer_get", methods={"GET"})
     */
    public function printerGet(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $this->logger->info('GET', $request->query->all());

        if ($request->query->has('delete')) {
            return $this->deletePrintJob($request, $salesChannelContext);
        }

        return $this->getPrintJob($request, $salesChannelContext);
    }

    /**
     * @Route("/printer", name="dewa-shop.printer_delete", methods={"DELETE"})
     */
    public function printerDelete(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $this->logger->info('DELETE', $request->query->all());

        return $this->deletePrintJob($request, $salesChannelContext);
    }

    public function deletePrintJob(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $code = $request->query->get('code');
        $headercode = substr($code, 0, 1);
        if ($headercode != "2") {
            $fullcode = substr($code, 0, 3);
            if ($fullcode === "520") {
                return new JsonResponse([]);
            }
        }

        $printer = $this->printService->getPrinterFromMAC($request->query->get('mac'), true);

        if ($printer && $printer->isActive()) {
            $printJob = $printer->getPrintJobs()->first();
            $printTurnover = $printer->getPrintTurnovers()->first();

            if ($printTurnover) {
                $this->printTurnoverRepository->delete([['id' => $printTurnover->getId()]], $salesChannelContext->getContext());
            } else if ($printJob) {
                $this->printJobRepository->delete([['id' => $printJob->getId()]], $salesChannelContext->getContext());
            }
        }

        return new JsonResponse([]);
    }

    public function getPrintJob(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $mac = $request->query->get('mac');
        if (!$mac) {
            $this->logger->critical("Parameter mac missing", []);

            return new Response("Parameter mac missing");
        }

        $printer = $this->printService->getPrinterFromMAC($mac);
        if (!$printer || !$printer->isActive()) {
            $this->logger->critical(sprintf("Printer %s not found or not active.", $mac), []);

            return new Response(sprintf("Printer %s not found or not active.", $mac));
        }

        $contentType = $request->query->get('type');
        if (!$contentType) {
            $this->logger->critical("Parameter type missing", []);

            return new Response("Parameter type missing");
        }

        $printJob = $printer->getPrintJobs()->first();
        $printTurnover = $printer->getPrintTurnovers()->first();

        if ($printJob) {
            $order = $printJob->getShopOrder();

            $this->printJobRepository->upsert([[
                'id' => $printJob->getId(),
                'isPrinting' => true
            ]], $salesChannelContext->getContext());

            if ($this->cputilAvailable()) {
                return $this->getPrintJobFromMarkupTemplate($printer, $order, $contentType);
            } else {
                return $this->getPrintJobFromPrinterClass($printer, $order, $contentType);
            }
        } else if ($printTurnover) {
            $this->printTurnoverRepository->upsert([[
                'id' => $printTurnover->getId(),
                'isPrinting' => true
            ]], $salesChannelContext->getContext());

            return $this->getPrintTurnover($printer, $printTurnover, $contentType);
        } else {
            return new Response("");
        }
    }

    public function getPrintJobFromMarkupTemplate(PrinterEntity $printer, ShopOrderEntity $order, ?string $contentType = null): Response
    {
        $template = $this->twig->createTemplate($printer->getTemplate());
        $output = $template->render(['shopOrder' => $order]);

        $basefile = tempnam(sys_get_temp_dir(), "markup");
        $markupfile = $basefile.".stm";
        $outputfile = tempnam(sys_get_temp_dir(), "output");

        file_put_contents($markupfile, $output);

        $options = "";

        if ($printer->getDotWidth() <= (58 * 8)) {
            $options = $options."thermal2";
        } elseif ($printer->getDotWidth() <= (72 * 8)) {
            $options = $options."thermal3";
        } elseif ($printer->getDotWidth() <= (82 * 8)) {
            $options = $options."thermal82";
        } elseif ($printer->getDotWidth() <= (112 * 8)) {
            $options = $options."thermal4";
        }

        $options = $options." scale-to-fit dither ";
        $cputilpath = dirname(__FILE__) . "/../../Resources/bin/cputil";
        system($cputilpath." ".$options." decode \"".$contentType."\" \"".$markupfile."\" \"".$outputfile."\"", $retval);

        return new Response(file_get_contents($outputfile), 200, [
            'Content-Type' => $contentType,
            'Content-Length' => filesize($outputfile)
        ]);
    }

    public function getPrintTurnover(PrinterEntity $printer, PrintTurnoverEntity $printTurnover, ?string $contentType = null): Response
    {
        $printJob = $this->getStarCloudPrntJob($printer, $contentType);
        $columns = $printJob->columns;

        $shop = $printTurnover->getShop();
        $shopOrders = $this->printService->getPrintTurnoverShopOrders($printTurnover);
        $timezone = new \DateTimeZone($shop->getTimeZone());

        // Build slip content
        $printJob->set_text_center_align();
        $printJob->add_new_line(5);
        $printJob->set_text_emphasized();
        $printJob->print_wrapped_line($this->translator->trans('dewa-shop.slip.taxAdviser'));
        $printJob->print_wrapped_line("{$shopOrders->first()->getOrder()->getOrderDateTime()->setTimezone($timezone)->format("d.m.Y")} - {$shopOrders->last()->getOrder()->getOrderDateTime()->setTimezone($timezone)->format("d.m.Y")}");
        $printJob->cancel_text_emphasized();
        $printJob->add_new_line(1);
        $printJob->print_wrapped_line($shop->getName());
        $printJob->print_wrapped_line("{$shop->getStreet()} {$shop->getStreetNumber()}");
        $printJob->print_wrapped_line("{$shop->getZipcode()} {$shop->getCity()}");
        if ($shop->getPhoneNumber()) {
            $printJob->print_wrapped_line($shop->getPhoneNumber());
        }
        $printJob->add_new_line(1);
        $printJob->set_text_left_align();

        $totalPrice = 0.00;
        $taxes = [];

        foreach ($shopOrders as $shopOrder) {
            $order = $shopOrder->getOrder();
            $price = $order->getPrice();
            $shippingCosts = $order->getShippingCosts();

            // Order number and date
            $printJob->set_text_emphasized();
            $printJob->add_column_separated_line([
                "{$order->getOrderNumber()}",
                "{$order->getOrderDateTime()->setTimezone($timezone)->format("d.m.Y H:i")}"
            ]);
            $printJob->cancel_text_emphasized();

            // Payment and shipping method
            $printJob->add_column_separated_line([
                "{$order->getTransactions()->first()->getPaymentMethod()->getName()}",
                "{$order->getDeliveries()->first()->getShippingMethod()->getName()}"
            ]);

            // Price and taxes
            $printJob->add_column_separated_line([
                "{$this->translator->trans('dewa-shop.slip.totalCosts')}",
                $this->twig->createTemplate("{{ price.totalPrice | currency }}")->render(['price' => $price])
            ]);
            foreach ($price->getCalculatedTaxes() as $percentage => $tax) {
                if ($tax->getPrice() == 0) {
                    continue;
                }

                if (!isset($taxes[$percentage])) {
                    $taxes[$percentage] = $tax->getPrice();
                } else {
                    $taxes[$percentage] = $taxes[$percentage] + $tax->getPrice();
                }

                $printJob->add_column_separated_line([
                    "{$percentage}% {$this->translator->trans('dewa-shop.slip.vatAbbr')}",
                    $this->twig->createTemplate("{{ tax.tax | currency }}")->render(['tax' => $tax])
                ]);
            }

            // Shipping costs and taxes
            if ($shippingCosts->getTotalPrice() > 0) {
                $printJob->add_column_separated_line([
                    "{$this->translator->trans('dewa-shop.slip.deliveryCosts')}",
                    $this->twig->createTemplate("{{ price.totalPrice | currency }}")->render(['price' => $shippingCosts])
                ]);
                foreach ($shippingCosts->getCalculatedTaxes() as $percentage => $tax) {
                    if ($tax->getPrice() == 0) {
                        continue;
                    }

                    if (!isset($taxes[$percentage])) {
                        $taxes[$percentage] = $tax->getPrice();
                    } else {
                        $taxes[$percentage] = $taxes[$percentage] + $tax->getPrice();
                    }

                    $printJob->add_column_separated_line([
                        "{$percentage}% {$this->translator->trans('dewa-shop.slip.vatAbbr')}",
                        $this->twig->createTemplate("{{ tax.tax | currency }}")->render(['tax' => $tax])
                    ]);
                }
            }
        }

        $printJob->add_new_line(3);

        foreach ($taxes as $percentage => $tax) {
            $printJob->add_column_separated_line([
                "{$this->translator->trans('dewa-shop.slip.totalGross')} {$percentage}% {$this->translator->trans('dewa-shop.slip.vatAbbr')}",
                $this->twig->createTemplate("{{ tax | currency }}")->render(['tax' => $tax])
            ]);
        }

        $printJob->add_new_line(3);

        $printJob->cut();

        $output = $printJob->getPrintContent();

        return new Response($output, 200, [
            'Content-Type' => $contentType,
            'Content-Length' => strlen($output)
        ]);
    }

    public function getPrintJobFromPrinterClass(PrinterEntity $printer, ShopOrderEntity $shopOrder, $contentType): Response
    {
        $printJob = $this->getStarCloudPrntJob($printer, $contentType);
        $columns = $printJob->columns;

        $event = new PrintJobFromPrinterClassEvent(
            $printJob,
            $shopOrder,
            $this->twig,
            $this->translator
        );

        $this->eventDispatcher->dispatch($event);

        if ($event->isCustom()) {
            $output = $printJob->getPrintContent();
            return new Response($output, 200, [
                'Content-Type' => $contentType,
                'Content-Length' => strlen($output)
            ]);
        }

        $shop = $shopOrder->getShop();
        $order = $shopOrder->getOrder();
        $delivery = $order->getDeliveries()->first();
        $address = $delivery->getShippingOrderAddress();
        $timezone = new \DateTimeZone($shop->getTimeZone());

        // Build slip content
        $printJob->set_text_center_align();
        $printJob->add_new_line(5);
        $printJob->print_wrapped_line($shop->getName());
        $printJob->print_wrapped_line("{$shop->getStreet()} {$shop->getStreetNumber()}");
        $printJob->print_wrapped_line("{$shop->getZipcode()} {$shop->getCity()}");
        if ($shop->getPhoneNumber()) {
            $printJob->print_wrapped_line($shop->getPhoneNumber());
        }
        $printJob->add_new_line(1);
        $printJob->print_wrapped_line("{$this->translator->trans('dewa-shop.slip.orderNumber')}: {$order->getOrderNumber()}");
        $printJob->print_wrapped_line($order->getOrderDateTime()->setTimezone($timezone)->format("d.m.Y H:i"));
        $printJob->add_new_line(1);
        $printJob->set_text_emphasized();
        $printJob->print_wrapped_line($delivery->getShippingMethod()->getName());
        if ($shopOrder->getDesiredTime()) {
            $printJob->print_wrapped_line($shopOrder->getDesiredTime()->setTimezone($timezone)->format("d.m.Y H:i"));
        }
        $printJob->cancel_text_emphasized();
        $printJob->print_wrapped_line("{$address->getFirstName()} {$address->getLastName()}");
        $printJob->print_wrapped_line($address->getStreet());
        $printJob->print_wrapped_line("{$address->getZipcode()} {$address->getCity()}");
        if ($address->getPhoneNumber()) {
            $printJob->add_new_line(1);
            $printJob->print_wrapped_line($address->getPhoneNumber());
        }
        if ($shopOrder->getComment()) {
            $printJob->add_new_line(1);
            $printJob->print_wrapped_line($shopOrder->getComment());
        }
        $printJob->add_new_line(1);
        $printJob->set_text_left_align();
        foreach ($order->getLineItems() as $item) {
            $orderNumber = $item->getPayload()['productNumber'];
            $label = "{$item->getQuantity()}x {$item->getLabel()}" . ($orderNumber ? " ({$orderNumber})" : "");

            $printJob->add_column_separated_line([
                mb_substr($label, 0, ($columns - 10)),
                $this->twig->createTemplate("{{ item.getPrice().getTotalPrice() | currency }}")->render(['item' => $item])
            ]);

            if ($item->getPayload()['dewa']) {
            	foreach ($item->getPayload()['dewa'] as $dewa) {
            		$dewa['value'] = is_array($dewa['value']) ? implode(', ', $dewa['value']) : $dewa['value'];
                	$printJob->print_wrapped_line("{$dewa['name']}: {$dewa['value']}");
                }
            }
        }
        $printJob->add_new_line(1);
        $printJob->add_column_separated_line([
            $this->translator->trans('dewa-shop.slip.deliveryCosts') . ":",
            $this->twig->createTemplate("{{ delivery.getShippingCosts().getTotalPrice() | currency }}")->render(['delivery' => $delivery])
        ]);
        $printJob->add_new_line(1);
        $printJob->set_text_emphasized();
        $printJob->add_column_separated_line([
            $this->translator->trans('dewa-shop.slip.totalCosts') . ":",
            $this->twig->createTemplate("{{ order.getPrice().getTotalPrice() | currency }}")->render(['order' => $order])
        ]);
        $printJob->cancel_text_emphasized();
        foreach ($order->getPrice()->getCalculatedTaxes() as $percentage => $tax) {
            $printJob->add_column_separated_line([
                "{$percentage}% {$this->translator->trans('dewa-shop.slip.vatAbbr')}",
                $this->twig->createTemplate("{{ tax.tax | currency }}")->render(['tax' => $tax])
            ]);
        }
        $printJob->add_new_line(1);
        $printJob->set_text_center_align();
        $printJob->set_text_emphasized();
        if ($order->getTransactions()->last()->getStateMachineState()->getTechnicalName() == "paid") {
            $printJob->print_wrapped_line($this->translator->trans('dewa-shop.slip.paid'));
        } else {
            $printJob->print_wrapped_line($this->translator->trans('dewa-shop.slip.notPaid'));
        }
        $printJob->cancel_text_emphasized();
        $printJob->add_new_line(1);
        $printJob->print_wrapped_line($this->translator->trans('dewa-shop.slip.thisIsNoInvoice'));
        $printJob->add_new_line(3);
        $printJob->cut();

        $output = $printJob->getPrintContent();

        return new Response($output, 200, [
            'Content-Type' => $contentType,
            'Content-Length' => strlen($output)
        ]);
    }

    private function cputilAvailable(): bool
    {
        $cputilpath = dirname(__FILE__) . "/../../Resources/bin/cputil";
        return file_exists($cputilpath);
    }

    private function getStarCloudPrntJob(PrinterEntity $printer, ?string $contentType = null): StarCloudPrntJob
    {
        switch ($contentType) {
            case 'application/vnd.star.line':
                $columns = $printer->getPrinterType() == "Star mC-Print2" ? 32 : 48;
                $printJob = new StarCloudPrntStarLineModeJob($columns);
                break;
            case 'application/vnd.star.linematrix':
                $columns = $printer->getPrinterType() == "Star mC-Print2" ? 32 : 42;
                $printJob = new StarCloudPrntStarLineModeJob($columns);
                break;
            case 'application/vnd.star.starprnt':
                $columns = $printer->getPrinterType() == "Star mC-Print2" ? 32 : 48;
                $printJob = new StarCloudPrntStarPrntJob($columns);
                break;
            default:
                $columns = $printer->getPrinterType() == "Star mC-Print2" ? 32 : 48;
                $printJob = new StarCloudPrntTextPlainJob($columns);
                break;
        }

        return $printJob;
    }
}
