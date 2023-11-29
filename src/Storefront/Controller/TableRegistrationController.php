<?php

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\Content\Shop\ShopCollection;
use Appflix\DewaShop\Core\Event\TableRegistrationMailEvent;
use Shopware\Core\Framework\RateLimiter\Exception\RateLimitExceededException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Storefront\Framework\Captcha\Annotation\Captcha;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class TableRegistrationController extends StorefrontController
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/appflix/table-registration", name="appflix.table-registration.send", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     * @Captcha
     */
    public function tableRegistration(RequestDataBag $data, SalesChannelContext $salesChannelContext): JsonResponse
    {
        $response = [];

        try {
            if ($salesChannelContext->hasExtension('shops')) {
                /** @var ShopCollection $shops */
                $shops = $salesChannelContext->getExtension('shops');
                $shopId = $data->get("shopId");
                $shop = $shops->get($shopId);

                $event = new TableRegistrationMailEvent(
                    $salesChannelContext,
                    $data,
                    $shop
                );

                $this->eventDispatcher->dispatch($event);
            }

            $message = null;

            if (!$message) {
                $message = $this->trans('moorl-foundation.tableRegistration.success');
            }
            $response[] = [
                'type' => 'success',
                'alert' => $message,
            ];
        } catch (ConstraintViolationException $formViolations) {
            $violations = [];
            foreach ($formViolations->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $response[] = [
                'type' => 'danger',
                'alert' => $this->renderView('@Storefront/storefront/utilities/alert.html.twig', [
                    'type' => 'danger',
                    'list' => $violations,
                ]),
            ];
        } catch (RateLimitExceededException $exception) {
            $response[] = [
                'type' => 'info',
                'alert' => $this->renderView('@Storefront/storefront/utilities/alert.html.twig', [
                    'type' => 'info',
                    'content' => $this->trans('error.rateLimitExceeded', ['%seconds%' => $exception->getWaitTime()]),
                ]),
            ];
        }

        return new JsonResponse($response);
    }
}
