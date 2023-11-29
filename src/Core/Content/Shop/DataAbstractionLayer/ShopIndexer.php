<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\DataAbstractionLayer;

use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Service\SearchPortalService;
use MoorlFoundation\Core\Service\LocationService;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Uuid\Uuid;

class ShopIndexer extends EntityIndexer
{
    private IteratorFactory $iteratorFactory;
    private Connection $connection;
    private EntityRepository $repository;
    private LocationService $locationService;
    private SearchPortalService $searchPortalService;

    public function __construct(
        EntityRepository $repository,
        Connection $connection,
        IteratorFactory $iteratorFactory,
        LocationService $locationService,
        SearchPortalService $searchPortalService
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
        $this->connection = $connection;
        $this->locationService = $locationService;
        $this->searchPortalService = $searchPortalService;
    }

    public function getName(): string
    {
        return 'dewa_shop.indexer';
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
        $shopEvent = $event->getEventByEntityName(ShopDefinition::ENTITY_NAME);

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
LOWER(HEX(dewa_shop.id)) AS id,
dewa_shop.street AS street,
dewa_shop.street_number AS streetNumber,
dewa_shop.zipcode AS zipcode,
dewa_shop.city AS city,
country.iso AS iso,
country.iso3 AS iso3
FROM dewa_shop
LEFT JOIN country ON (country.id = dewa_shop.country_id)
WHERE dewa_shop.id IN (:ids) AND dewa_shop.auto_location = 1;';

        $data = $this->connection->fetchAll(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($data as $item) {
            $geoPoint = $this->locationService->getLocationByAddress($item);
            if (!$geoPoint) {
                continue;
            }

            $sql = 'UPDATE dewa_shop SET location_lat = :lat, location_lon = :lon WHERE id = :id;';

            $this->connection->executeUpdate(
                $sql,
                [
                    'id' => Uuid::fromHexToBytes($item['id']),
                    'lat' => $geoPoint->getLatitude(),
                    'lon' => $geoPoint->getLongitude()
                ]
            );
        }

        $this->searchPortalService->ping();
    }
}
