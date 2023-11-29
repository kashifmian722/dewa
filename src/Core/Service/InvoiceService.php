<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Service;

use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Shopware\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Document\DocumentIdStruct;
use Shopware\Core\Checkout\Document\DocumentService;
use Shopware\Core\Checkout\Document\FileGenerator\FileTypes;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class InvoiceService
{
    private ?Context $context;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private SystemConfigService $systemConfigService;
    private DocumentService $documentService;
    private NumberRangeValueGeneratorInterface $valueGenerator;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService,
        DocumentService $documentService,
        NumberRangeValueGeneratorInterface $valueGenerator
    ) {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->documentService    = $documentService;
        $this->valueGenerator     = $valueGenerator;

        $this->context = Context::createDefaultContext();
    }

    public function create(OrderEntity $order, Context $context): ?DocumentIdStruct
    {
        if (!$this->systemConfigService->get('AppflixDewaShop.config.orderInvoice')) {
            return null;
        }

        try {
            /** @var ShopOrderEntity $shopOrder */
            $shopOrder = $order->get('dewa');
            if (!$shopOrder) {
                return null;
            }
        } catch (\InvalidArgumentException $exception) {
            return null;
        }

        $shop = $shopOrder->getShop();
        if (!$shop) {
            return null;
        }

        /* Prevent Duplicates */
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $documentRepository = $this->definitionInstanceRegistry->getRepository('document');
        /** @var DocumentEntity $document */
        $document = $documentRepository->search($criteria, $context)->first();
        if ($document) {
            return new DocumentIdStruct($document->getId(), $document->getDeepLinkCode());
        }

        $documentNumber = $this->valueGenerator->getValue('document_invoice', $context, $order->getSalesChannelId());
        $config = DocumentConfigurationFactory::createConfiguration([
            'documentNumber' => $documentNumber,
            'logo' => $shop->getMedia(),
            'companyName' => $shop->getName(),
            'executiveDirector' => $shop->getExecutiveDirector(),
            'placeOfFulfillment' => $shop->getPlaceOfFulfillment(),
            'placeOfJurisdiction' => $shop->getPlaceOfJurisdiction(),
            'bankBic' => $shop->getBankBic(),
            'bankIban' => $shop->getBankIban(),
            'bankName' => $shop->getBankName(),
            'taxOffice' => $shop->getTaxOffice(),
            'taxNumber' => $shop->getTaxNumber(),
            'vatId' => $shop->getVatId(),
            'companyAddress' => implode(' - ', [
                $shop->getName(),
                trim($shop->getStreet() . ' ' . $shop->getStreetNumber()),
                trim($shop->getZipcode() . ' ' . $shop->getCity())
            ]),
            'custom' => [
                'invoiceNumber' => $documentNumber
            ]
        ]);

        return $this->documentService->create(
            $order->getId(),
            'invoice',
            FileTypes::PDF,
            $config,
            $context
        );
    }
}
