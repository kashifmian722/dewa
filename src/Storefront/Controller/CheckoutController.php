<?php

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Service\DewaShopService;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class CheckoutController extends StorefrontController
{
    private DewaShopService $dewaShopService;

    public function __construct(
        DewaShopService $dewaShopService
    )
    {
        $this->dewaShopService = $dewaShopService;
    }

    /**
     * @Route("/dewa-shop/checkout-shop-selection", name="dewa-shop.checkout-shop-selection", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function shopSelection(Request $request, RequestDataBag $requestDataBag, SalesChannelContext $salesChannelContext): Response
    {
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);
        $this->dewaShopService->setSession($requestDataBag->all());

        $cookie = Cookie::create(Defaults::ACTIVE_STORE_COOKIE_NAME, json_encode($requestDataBag->all()));
        $cookie->setSecureDefault($request->isSecure());
        $cookie = $cookie->withExpires(time() + 60 * 60 * 24 * 30);

        $response = $this->createActionResponse($request);
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * @Route("/dewa-shop/checkout-order-state/{orderId}", name="dewa-shop.checkout-order-state", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */
    public function orderState(string $orderId, RequestDataBag $requestDataBag, SalesChannelContext $salesChannelContext): JsonResponse
    {
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);

        $shopOrder = $this->dewaShopService->getShopOrder($orderId);

        $payload = [
            'location' => [
                'lat' => $shopOrder->getShop()->getLocationLat(),
                'lon' => $shopOrder->getShop()->getLocationLon(),
                'popup' => $this->renderView('dewa-shop/checkout/checkout-location-popup.html.twig', ['shopOrder' => $shopOrder])
            ],
            'destination' => [
                'lat' => $shopOrder->getLocationLat(),
                'lon' => $shopOrder->getLocationLon(),
                'popup' => $this->renderView('dewa-shop/checkout/checkout-location-popup.html.twig', ['shopOrder' => $shopOrder])
            ],
            'deliverer' => $shopOrder->getDeliverer() ? [
                'lat' => $shopOrder->getDeliverer()->getLocationLat(),
                'lon' => $shopOrder->getDeliverer()->getLocationLon(),
                'popup' => $this->renderView('dewa-shop/checkout/checkout-deliverer-popup.html.twig', ['shopOrder' => $shopOrder])
            ] : null,
            'state' => $this->renderView('dewa-shop/checkout/checkout-state-message.html.twig', ['shopOrder' => $shopOrder]),
            'calculatedTime' => $shopOrder->getDesiredTime()
        ];

        return new JsonResponse($payload);
    }

    /**
     * @Route("/dewa-shop/checkout-confirm-phone-number", name="dewa-shop.checkout-confirm-phone-number", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function confirmPhoneNumber(RequestDataBag $requestDataBag, SalesChannelContext $salesChannelContext): Response
    {
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);
        $this->dewaShopService->setSession($requestDataBag->all());

        return new Response();
    }
}
