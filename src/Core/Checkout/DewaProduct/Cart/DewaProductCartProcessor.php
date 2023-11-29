<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Checkout\DewaProduct\Cart;

use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Service\CartValidatorService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryInformation;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryTime;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\Cart\ProductGatewayInterface;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DewaProductCartProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;

    private ProductGatewayInterface $productGateway;

    private CartValidatorService $cartValidatorService;

    public function __construct(
        ProductGatewayInterface $productGateway,
        QuantityPriceCalculator $quantityPriceCalculator,
        CartValidatorService $cartValidatorService
    )
    {
        $this->productGateway = $productGateway;
        $this->quantityPriceCalculator = $quantityPriceCalculator;
        $this->cartValidatorService = $cartValidatorService;
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->cartValidatorService->preValidateCollect($original, $context);

        /** @var LineItemCollection $lineItems */
        $lineItems = $original->getLineItems()->filterType(Defaults::LINE_ITEM);

        if (\count($lineItems) === 0) {
            return;
        }

        $this->cartValidatorService->afterValidateCollect($original, $context);

        $products = $this->fetchProducts($lineItems, $data, $context);
        foreach ($products as $product) {
            $data->set('product-' . $product->getId(), $product);
        }

        foreach ($lineItems as $lineItem) {
            $product = $data->get('product-' . $lineItem->getReferencedId());

            if (!$product) {
                $lineItems->remove($lineItem->getId());
                $original->setLineItems($lineItems);
                continue;
            }

            $this->enrichProduct($lineItem, $product);
        }
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $toCalculate->setErrors($original->getErrors());

        /** @var LineItemCollection $lineItems */
        $lineItems = $original->getLineItems()->filterType(Defaults::LINE_ITEM);

        if (\count($lineItems) !== 0) {
            foreach ($lineItems as $lineItem) {
                $priceDefinition = new QuantityPriceDefinition(
                    $lineItem->getPrice()->getUnitPrice(),
                    $lineItem->getPrice()->getTaxRules(),
                    $lineItem->getQuantity()
                );

                $lineItem->setPrice(
                    $this->quantityPriceCalculator->calculate($priceDefinition, $context)
                );

                $toCalculate->add($lineItem);
            }
        } else {
            $this->cartValidatorService->validateNotDewaShippingMethod($toCalculate, $context);
        }

        $this->cartValidatorService->validate($original, $toCalculate, $context);
    }

    private function fetchProducts(LineItemCollection $lineItems, CartDataCollection $data, SalesChannelContext $context): ProductCollection
    {
        $productIds = $lineItems->getReferenceIds();

        $filtered = [];
        foreach ($productIds as $productId) {
            if ($data->has('product-' . $productId)) {
                continue;
            }

            $filtered[] = $productId;
        }

        if (!empty($filtered)) {
            /** @var ProductCollection $products */
            $products = $this->productGateway->get($filtered, $context);

            return $products;
        }

        return new ProductCollection();
    }

    private function enrichProduct(LineItem $lineItem, SalesChannelProductEntity $product): void
    {
        if (!$lineItem->getLabel()) {
            $lineItem->setLabel($product->getTranslation('name'));
        }

        if (!$lineItem->getDescription()) {
            $lineItem->setDescription($product->getTranslation('name'));
        }

        if (!$lineItem->getPayloadValue('customFields')) {
            $lineItem->setPayloadValue('customFields', $product->getTranslation('customFields'));
        }

        if ($product->getCover()) {
            $lineItem->setCover($product->getCover()->getMedia());
        }

        $quantityInformation = new QuantityInformation();

        $quantityInformation->setMinPurchase(
            $product->getMinPurchase() ?? 1
        );

        $quantityInformation->setMaxPurchase(
            $product->getCalculatedMaxPurchase()
        );

        $quantityInformation->setPurchaseSteps(
            $product->getPurchaseSteps() ?? 1
        );

        $lineItem->setQuantityInformation($quantityInformation);

        $deliveryTime = null;
        if ($product->getDeliveryTime() !== null) {
            $deliveryTime = DeliveryTime::createFromEntity($product->getDeliveryTime());
        }

        $lineItem->setDeliveryInformation(
            new DeliveryInformation(
                (int)$product->getAvailableStock(),
                (float)$product->getWeight(),
                $product->getShippingFree(),
                $product->getRestockTime(),
                $deliveryTime,
                $product->getHeight(),
                $product->getWidth(),
                $product->getLength()
            )
        );
    }
}
