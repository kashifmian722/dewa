<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Product\DataAbstractionLayer;

use Appflix\DewaShop\Core\Defaults;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductIndexer extends EntityIndexer
{
    private IteratorFactory $iteratorFactory;
    private Connection $connection;
    private EntityRepository $repository;

    public function __construct(
        EntityRepository $repository,
        Connection $connection,
        IteratorFactory $iteratorFactory
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'dewa_product.indexer';
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
        $entityEvent = $event->getEventByEntityName(ProductDefinition::ENTITY_NAME);

        if (!$entityEvent) {
            return null;
        }

        foreach ($entityEvent->getWriteResults() as $result) {
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
    LOWER(HEX(product.id)) AS id,
    LOWER(HEX(product.version_id)) AS versionId,
    product.created_at AS createdAt,
    COUNT(distinct parent.id) AS parentCount,
    COUNT(distinct dewa_product_index.product_id) AS indexCount,
    COUNT(distinct categoryOption.id) AS categoryOptionCount,
    COUNT(distinct productOption.id) AS productOptionCount,
    COUNT(distinct productIngredient.product_id) AS productIngredientCount
FROM product
    LEFT JOIN dewa_product_index ON (product.id = dewa_product_index.product_id)
    LEFT JOIN product_category ON (product.id = product_category.product_id)
    LEFT JOIN category child ON (child.id = product_category.category_id)
    LEFT JOIN category parent ON (parent.id = child.parent_id AND parent.cms_page_id = :cms_page_id)
    LEFT JOIN dewa_option_category categoryOption ON (categoryOption.category_id = product_category.category_id)
    LEFT JOIN dewa_option_product productOption ON (productOption.product_id = product.id)
    LEFT JOIN dewa_ingredient_product productIngredient ON (productIngredient.product_id = product.id)
WHERE product.id IN (:ids)
GROUP BY product.id;';

        $data = $this->connection->fetchAll(
            $sql,
            [
                'ids' => Uuid::fromHexToBytesList($ids),
                'cms_page_id' => Uuid::fromHexToBytes(Defaults::CMS_PAGE_ID)
            ],
            [
                'ids' => Connection::PARAM_STR_ARRAY
            ]
        );

        foreach ($data as $item) {
            if ((int) $item['parentCount'] > 0) {
                $sql = <<<SQL
INSERT INTO dewa_product_index 
    (id, product_id, product_version_id, product_configurator, product_ingredient, created_at) 
VALUES  
    (:id, :id, :version_id, :product_configurator, :product_ingredient, :created_at)
ON DUPLICATE KEY UPDATE `product_configurator` = :product_configurator, `product_ingredient` = :product_ingredient;
SQL;
            } else if ((int) $item['parentCount'] === 0) {
                $sql = 'DELETE FROM dewa_product_index WHERE product_id = :id;';
            } else {
                continue;
            }

            $this->connection->executeUpdate($sql, [
                'id' => Uuid::fromHexToBytes($item['id']),
                'version_id' => Uuid::fromHexToBytes($item['versionId']),
                'product_configurator' => ($item['categoryOptionCount'] || $item['productOptionCount']) ? 1 : 0,
                'product_ingredient' => ($item['productIngredientCount']) ? 1 : 0,
                'created_at' => $item['createdAt']
            ]);
        }
    }
}
