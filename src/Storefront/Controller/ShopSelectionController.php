<?php

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Service\DewaShopService;
use MoorlFoundation\Core\Service\LocationService;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class ShopSelectionController extends StorefrontController
{
    private DewaShopService $dewaShopService;
    private LocationService $locationService;

    public function __construct(
        DewaShopService $dewaShopService,
        LocationService $locationService
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->locationService = $locationService;
    }

    /**
     * @Route("/dewa-shop/shop-modal/{id}", name="dewa-shop.shop-modal", methods={"GET","POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function shopModal(string $id, SalesChannelContext $salesChannelContext): Response
    {
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);
        $shop = $this->dewaShopService->getShop($id);

        $body = $this->renderView('dewa-shop/shop/shop-info.html.twig', ['shop' => $shop]);

        return $this->renderStorefront('dewa-shop/component/modal.html.twig', [
            'modal' => [
                'title' => $shop->getName(),
                'size' => 'lg',
                'body' => $body
            ]
        ]);
    }

    /**
     * @Route("/dewa-shop/shop-selection-modal", name="dewa-shop.shop-selection-modal", methods={"GET","POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function shopSelectionModal(SalesChannelContext $salesChannelContext): Response
    {
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);
        $shops = $this->dewaShopService->getShops();

        return $this->renderStorefront('dewa-shop/shop/shop-selection-modal.html.twig', [
            'modal' => [
                'title' => $this->trans('dewa-shop.checkout.shopSelection'),
                'size' => 'lg',
                'body' => 'test'
            ],
            'shops' => $shops
        ]);
    }

    /**
     * @Route("/dewa-shop/shop-selection-search", name="dewa-shop.shop-selection-search", methods={"GET","POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function shopSelectionSearch(Request $request, RequestDataBag $requestDataBag, SalesChannelContext $salesChannelContext): JsonResponse
    {
        $action = $requestDataBag->get('action');
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);

        if ($action === 'select') {
            $this->dewaShopService->setSession($requestDataBag->all());
            $cookie = Cookie::create(Defaults::ACTIVE_STORE_COOKIE_NAME, json_encode($requestDataBag->all()));
            $cookie->setSecureDefault($request->isSecure());
            $cookie = $cookie->withExpires(time() + 60 * 60 * 24 * 30);

            $response = new JsonResponse([
                'reload' => true
            ]);
            $response->headers->setCookie($cookie);

            return $response;
        } elseif ($action === 'search') {
            $term = $requestDataBag->get('term');
            $geoPoint = $this->locationService->getLocationByTerm($term);
            $shops = $this->dewaShopService->getShops(null, $geoPoint);

            $options = $this->renderView('dewa-shop/shop/shop-selection-options.html.twig', [
                'shops' => $shops
            ]);

            return new JsonResponse([
                'options' => $options
            ]);
        }

        return new JsonResponse([
            'options' => 'error'
        ]);
    }
}
