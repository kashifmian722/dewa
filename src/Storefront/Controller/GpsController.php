<?php

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\Content\Deliverer\DelivererEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class GpsController extends StorefrontController
{
    private EntityRepository $delivererRepository;

    public function __construct(
        EntityRepository $delivererRepository
    )
    {
        $this->delivererRepository = $delivererRepository;
    }

    /**
     * @Route("/gps-track", name="dewa-shop.gps-track", defaults={"csrf_protected"=false}, methods={"GET","POST"})
     */
    public function gpsTrack(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('trackingCode', (string) $request->get('id')));

        /** @var $deliverer DelivererEntity */
        $deliverer = $this->delivererRepository->search($criteria, $salesChannelContext->getContext())->first();

        if (!$deliverer) {
            return new Response("ERROR");
        }

        $payload = [
            'id' => $deliverer->getId(),
            'locationLat' => (float) $request->get('lat'),
            'locationLon' => (float) $request->get('lon')
        ];

        $this->delivererRepository->upsert([$payload], $salesChannelContext->getContext());

        return new Response("OK");
    }
}
