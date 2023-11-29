<?php

namespace Appflix\DewaShop\Administration\Controller;

use Appflix\DewaShop\Core\Service\DewaShopService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PrintController
 * @package Appflix\DewaShop\Administration\Controller
 * @Route(defaults={"_routeScope"={"api"}})
 */
class OrderController
{
    private DewaShopService $dewaShopService;

    private SystemConfigService $systemConfigService;

    public function __construct(
        DewaShopService $dewaShopService,
        SystemConfigService $systemConfigService
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @Route("/api/dewa/order-time/{shopOrderId}/{minutes}", name="api.dewa.order-time", methods={"GET"})
     */
    public function orderTime(string $shopOrderId, string $minutes): JsonResponse
    {
        $desiredTime = (new \DateTimeImmutable('now'))->modify(sprintf("+%s minutes", $minutes));

        $data = [
            'id' => $shopOrderId,
            'desiredTime' => $desiredTime
        ];

        $this->dewaShopService->updateShopOrder($data);

        return new JsonResponse($data);
    }
}
