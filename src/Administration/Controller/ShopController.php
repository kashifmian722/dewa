<?php

namespace Appflix\DewaShop\Administration\Controller;

use Doctrine\DBAL\Connection;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ShopController
 * @package Appflix\DewaShop\Administration\Controller
 * @Route(defaults={"_routeScope"={"api"}})
 * @deprecated: Not used anymore
 */
class ShopController
{
    private Connection $connection;
    private SystemConfigService $systemConfigService;

    public function __construct(
        Connection $connection,
        SystemConfigService $systemConfigService
    )
    {
        $this->connection = $connection;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * Open shop if logged in admin or POS device is online
     * @Route("/api/dewa/update-shop/{shopId}", name="api.dewa.update-shop", methods={"GET"})
     */
    public function updateShop(string $shopId): JsonResponse
    {
        return new JsonResponse([]);

        $updatedAt = (new \DateTimeImmutable('now'));

        $sql = <<<SQL
UPDATE `dewa_shop` SET `is_open` = 1, `updated_at` = :updated_at WHERE `id` = UNHEX(:id);
SQL;
        $result = $this->connection->executeStatement($sql, [
            'id' => $shopId,
            'updated_at' => $updatedAt->format(DATE_ATOM)
        ]);

        return new JsonResponse($result);
    }
}
