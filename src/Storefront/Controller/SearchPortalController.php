<?php declare(strict_types=1);

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\Service\DewaShopService;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Log\LoggerFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class SearchPortalController extends StorefrontController
{
    private LoggerInterface $logger;
    private DewaShopService $dewaShopService;
    private SystemConfigService $systemConfigService;

    public function __construct(
        LoggerFactory $loggerFactory,
        DewaShopService $dewaShopService,
        SystemConfigService $systemConfigService
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->systemConfigService = $systemConfigService;

        $this->logger = $loggerFactory->createRotating(
            'dewa_search_portal',
            7,
            Logger::DEBUG
        );
    }

    /**
     * @Route("/store-api/poll", name="dewa-shop.search_portal_poll", methods={"GET"})
     */
    public function poll(Request $request, SalesChannelContext $salesChannelContext): JsonResponse
    {
        $this->logger->info("Poll", $request->query->all());

        if (!$this->systemConfigService->get('AppflixDewaShop.config.searchPortalActive')) {
            //return new JsonResponse("Shop feed disabled");
        }

        $this->dewaShopService->setSalesChannelContext($salesChannelContext);
        $shops = $this->dewaShopService->getShops();

        return new JsonResponse($shops);
    }
}
