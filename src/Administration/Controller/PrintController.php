<?php

namespace Appflix\DewaShop\Administration\Controller;

use Appflix\DewaShop\Core\Service\PrintService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PrintController
 * @package Appflix\DewaShop\Administration\Controller
 * @Route(defaults={"_routeScope"={"api"}})
 */
class PrintController
{
    private PrintService $printService;

    private SystemConfigService $systemConfigService;

    public function __construct(
        PrintService $printService,
        SystemConfigService $systemConfigService
    )
    {
        $this->printService = $printService;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @Route("/api/dewa/print-job/{printerId}/{shopOrderId}", name="api.dewa.print-job", methods={"GET"})
     */
    public function printJob(string $printerId, string $shopOrderId): JsonResponse
    {
        if (!$this->systemConfigService->get('AppflixDewaShop.config.orderPrintJob')) {
            $this->printService->addPrintJob($printerId, $shopOrderId);
        }

        return new JsonResponse([]);
    }

    /**
     * @Route("/api/dewa/print-turnover/{shopId}/{interval}", name="api.dewa.print-turnover", methods={"POST"})
     */
    public function printTurnover(Request $request): JsonResponse
    {
        $this->printService->addPrintTurnover(
            $request->request->get('shopId'),
            $request->request->get('interval'),
            $request->request->get('key')
        );

        return new JsonResponse($request->request->all());
    }
}
