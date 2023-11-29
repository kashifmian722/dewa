<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Checkout\DewaProduct\Cart;

use Appflix\DewaShop\Core\Defaults;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryHandler\LineItemFactoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DewaProductHandler implements LineItemFactoryInterface {

    public function supports(string $type): bool
    {
        return $type === Defaults::LINE_ITEM;
    }

    public function create(array $data, SalesChannelContext $context): LineItem
    {
        return new LineItem($data['id'], Defaults::LINE_ITEM, $data['referencedId'] ?? null, 1);
    }

    public function update(LineItem $lineItem, array $data, SalesChannelContext $context): void
    {
        if (isset($data['referencedId'])) {
            $lineItem->setReferencedId($data['referencedId']);
        }
    }
}