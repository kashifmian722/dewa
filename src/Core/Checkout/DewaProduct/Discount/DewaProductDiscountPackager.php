<?php

namespace Appflix\DewaShop\Core\Checkout\DewaProduct\Discount;

use Appflix\DewaShop\Core\Defaults;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantity;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Cart\Rule\LineItemScope;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackager;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DewaProductDiscountPackager extends DiscountPackager
{
    private DiscountPackager $cartScopeDiscountPackager;

    public function __construct(DiscountPackager $cartScopeDiscountPackager)
    {
        $this->cartScopeDiscountPackager = $cartScopeDiscountPackager;
    }

    public function getDecorated(): DiscountPackager
    {
        return $this->cartScopeDiscountPackager;
    }

    public function getMatchingItems(DiscountLineItem $discount, Cart $cart, SalesChannelContext $context): DiscountPackageCollection
    {
        $matchingItems = $this->cartScopeDiscountPackager->getMatchingItems($discount, $cart, $context);

        $configuratorItems = $cart->getLineItems()->filterType(Defaults::LINE_ITEM);

        if ($configuratorItems->count() === 0) {
            return $matchingItems;
        }

        $items = [];

        foreach ($configuratorItems as $lineItem) {
            $productLineItem = clone $lineItem;
            $productLineItem->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);

            if (!$this->isRulesFilterValid($productLineItem, $discount->getPriceDefinition(), $context)) {
                continue;
            }

            $items[] = new LineItemQuantity($productLineItem->getId(), $productLineItem->getQuantity());
        }

        if ($items !== []) {
            $matchingItems->add(new DiscountPackage(new LineItemQuantityCollection($items)));
        }

        return $matchingItems;
    }

    private function isRulesFilterValid(LineItem $item, PriceDefinitionInterface $priceDefinition, SalesChannelContext $context): bool
    {
        if (!\method_exists($priceDefinition, 'getFilter')) {
            return true;
        }

        $filter = $priceDefinition->getFilter();
        if (!$filter instanceof Rule) {
            return true;
        }

        $scope = new LineItemScope($item, $context);
        return $filter->match($scope);
    }
}
