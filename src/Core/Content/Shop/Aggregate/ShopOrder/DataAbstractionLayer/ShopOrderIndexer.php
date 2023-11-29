<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\DataAbstractionLayer;

use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderDefinition;
use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;
use MoorlFoundation\Core\Service\LocationService;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Uuid\Uuid;

class ShopOrderIndexer extends EntityIndexer
{
    /**
     * @var IteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(
        EntityRepository $repository,
        Connection $connection,
        IteratorFactory $iteratorFactory,
        LocationService $locationService
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
        $this->connection = $connection;
        $this->locationService = $locationService;
    }

    public function getName(): string
    {
        return 'dewa_shop_order.indexer';
    }

    public function iterate($offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new EntityIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $shopEvent = $event->getEventByEntityName(ShopOrderDefinition::ENTITY_NAME);

        if (!$shopEvent) {
            return null;
        }

        foreach ($shopEvent->getWriteResults() as $result) {
            if (!$result->getExistence()) {
                continue;
            }

            $payload = $result->getPayload();
            if (isset($payload['id'])) {
                $ids[] = $payload['id'];
            }
        }

        if (empty($ids)) {
            return null;
        }

        return new EntityIndexingMessage(array_values($ids), null, $event->getContext(), \count($ids) > 20);
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();

        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $sql = 'SELECT 
LOWER(HEX(dewa_shop_order.id)) AS id,
dewa_shop.location_lat AS locationLat,
dewa_shop.location_lon AS locationLon,
order_address.street AS street,
order_address.zipcode AS zipcode,
order_address.city AS city,
country.iso AS iso,
country.iso3 AS iso3
FROM dewa_shop_order
LEFT JOIN dewa_shop ON (dewa_shop.id = dewa_shop_order.dewa_shop_id)
LEFT JOIN order_address ON (order_address.order_id = dewa_shop_order.order_id)
LEFT JOIN country ON (country.id = order_address.country_id)
WHERE dewa_shop_order.id IN (:ids)
AND dewa_shop_order.distance IS NULL;';

        $data = $this->connection->fetchAll(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($data as $item) {
            $geoPoint = $this->locationService->getLocationByAddress($item);
            $geoPointShop = new GeoPoint($item['locationLat'], $item['locationLon']);

            if (!$geoPoint || !$geoPointShop) {
                continue;
            }
            $distance = $geoPoint->distanceTo($geoPointShop, 'km');

            $sql = 'UPDATE dewa_shop_order SET location_lat = :lat, location_lon = :lon, distance = :distance WHERE id = :id;';

            $this->connection->executeUpdate(
                $sql,
                [
                    'id' => Uuid::fromHexToBytes($item['id']),
                    'lat' => $geoPoint->getLatitude(),
                    'lon' => $geoPoint->getLongitude(),
                    'distance' => $distance
                ]
            );
        }
    }
}
