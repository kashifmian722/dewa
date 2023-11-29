<?php

namespace Appflix\DewaShop\Core\Service;

use Appflix\DewaShop\Core\Checkout\Cart\Error\CollectClosedError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\DeliveryClosedError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\DeliveryOutOfRangeError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\MixedCartError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\NoShopSelectedError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\OrderMinValueError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\PhoneNumberNotConfirmedError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\ShopClosedError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\ShopNotAvailableError;
use Appflix\DewaShop\Core\Checkout\Cart\Error\WrongShippingMethodError;
use Appflix\DewaShop\Core\Content\Shop\ShopCollection;
use Appflix\DewaShop\Core\Content\Shop\ShopEntity;
use Appflix\DewaShop\Core\Defaults;
use MoorlFoundation\Core\Service\LocationService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Content\Product\Cart\ProductOutOfStockError;
use Shopware\Core\System\Currency\CurrencyFormatter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CartValidatorService
{
    private DewaShopService $dewaShopService;
    private StockService $stockService;
    private LocationService $locationService;
    private SystemConfigService $systemConfigService;
    private CurrencyFormatter $currencyFormatter;

    private ?ShopEntity $shop = null;
    private ?ShopCollection $shops = null;
    private ?CustomerEntity $customer = null;
    private ?CustomerAddressEntity $address = null;
    private ?ShippingMethodEntity $shippingMethod = null;
    private ?\DateTimeImmutable $desiredTime = null;

    public function __construct(
        DewaShopService $dewaShopService,
        LocationService $locationService,
        StockService $stockService,
        SystemConfigService $systemConfigService,
        CurrencyFormatter $currencyFormatter
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->locationService = $locationService;
        $this->stockService = $stockService;
        $this->systemConfigService = $systemConfigService;
        $this->currencyFormatter = $currencyFormatter;
    }

    /**
     * If usage of all delivery methods is allowed
     *
     * @param Cart $original
     * @param SalesChannelContext $salesChannelContext
     */
    public function preValidateCollect(Cart $original, SalesChannelContext $salesChannelContext): void
    {
        if ($this->systemConfigService->get("AppflixDewaShop.config.checkoutOpenDeliveryMethods")) {
            $this->validateCollect($original, $salesChannelContext);
        }
    }

    /**
     * If usage of all delivery methods is not allowed
     *
     * @param Cart $original
     * @param SalesChannelContext $salesChannelContext
     */
    public function afterValidateCollect(Cart $original, SalesChannelContext $salesChannelContext): void
    {
        if (!$this->systemConfigService->get("AppflixDewaShop.config.checkoutOpenDeliveryMethods")) {
            $this->validateMixedCart($original);
            $this->validateCollect($original, $salesChannelContext);
        }
    }

    public function validateCollect(Cart $original, SalesChannelContext $salesChannelContext): void
    {
        $this->customer = $salesChannelContext->getCustomer();

        $this->dewaShopService->setSalesChannelContext($salesChannelContext);

        $this->shop = $this->dewaShopService->getShop();

        $this->shops = $this->dewaShopService->getShops(null, $this->locationService->getCustomerLocation($this->customer));

        if (!$this->validateShops($original)) {
            return;
        }

        if (!$this->validateShopIsOpen($original)) {
            return;
        }

        if (!$this->customer) {
            return;
        }

        $this->address = $this->customer->getActiveShippingAddress();
        if (!$this->address) {
            return;
        }

        if (!$this->validateShippingMethod($original, $salesChannelContext)) {
            return;
        }

        if ($this->customer->getGuest() && $salesChannelContext->getPaymentMethod()->getAfterOrderEnabled()) {
            $original->getErrors()->add(new PhoneNumberNotConfirmedError('dewa-phone-number-not-confirmed'));
        }

        if ($this->systemConfigService->get('AppflixDewaShop.config.checkoutPhoneNumber')) {
            $data = $this->dewaShopService->getSession();
            if (empty($data['phoneNumber'])) {
                $original->getErrors()->add(new PhoneNumberNotConfirmedError('dewa-phone-number-not-confirmed'));
            }
        }
    }

    public function validate(Cart $original, Cart $toCalculate, SalesChannelContext $salesChannelContext): void
    {
        $this->validateStocks($toCalculate, $salesChannelContext);
    }

    public function validateShopIsOpen(Cart $original): bool
    {
        if (!$this->systemConfigService->get('AppflixDewaShop.config.posAutoOpen')) {
            return true;
        } elseif ($this->shop->getIsOpen()) {
            return true;
        }

        $original->getErrors()->add(
            new ShopClosedError($this->shop->getName())
        );

        return false;
    }

    public function validateMinOrderValue(Cart $original, SalesChannelContext $salesChannelContext): void
    {
        if ($this->shop->getDeliveryType() === 'area' && $this->shop->getShopAreas() && $this->address) {
            $shopArea = $this->shop->getShopAreas()->getByZipcode($this->address->getZipcode());
            if (!$shopArea) {
                $shopArea = $this->shop->getShopAreas()->first();
            }

            if ($original->getPrice()->getTotalPrice() < $shopArea->getMinOrderValue()) {
                $orderValueLeft = $this->currencyFormatter->formatCurrencyByLanguage(
                    $shopArea->getMinOrderValue() - $original->getPrice()->getTotalPrice(),
                    $salesChannelContext->getCurrency()->getIsoCode(),
                    $salesChannelContext->getContext()->getLanguageId(),
                    $salesChannelContext->getContext()
                );

                $minOrderValue = $this->currencyFormatter->formatCurrencyByLanguage(
                    $shopArea->getMinOrderValue(),
                    $salesChannelContext->getCurrency()->getIsoCode(),
                    $salesChannelContext->getContext()->getLanguageId(),
                    $salesChannelContext->getContext()
                );

                $original->getErrors()->add(
                    new OrderMinValueError($this->shippingMethod->getName(), $minOrderValue, $orderValueLeft)
                );
            }
        } elseif ($this->shop->getDeliveryType() === 'radius' || !$this->address) {
            if ($original->getPrice()->getTotalPrice() < $this->shop->getMinOrderValue()) {
                $orderValueLeft = $this->currencyFormatter->formatCurrencyByLanguage(
                    $this->shop->getMinOrderValue() - $original->getPrice()->getTotalPrice(),
                    $salesChannelContext->getCurrency()->getIsoCode(),
                    $salesChannelContext->getContext()->getLanguageId(),
                    $salesChannelContext->getContext()
                );

                $minOrderValue = $this->currencyFormatter->formatCurrencyByLanguage(
                    $this->shop->getMinOrderValue(),
                    $salesChannelContext->getCurrency()->getIsoCode(),
                    $salesChannelContext->getContext()->getLanguageId(),
                    $salesChannelContext->getContext()
                );

                $original->getErrors()->add(
                    new OrderMinValueError($this->shippingMethod->getName(), $minOrderValue, $orderValueLeft)
                );
            }
        }
    }

    /**
     * Do not allow mixed carts for food
     *
     * @param Cart $original
     */
    public function validateMixedCart(Cart $original): void
    {
        foreach ($original->getLineItems() as $lineItem) {
            if (!$lineItem->getReferencedId()) {
                continue;
            }

            if ($lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $original->getErrors()->add(new MixedCartError('dewa-mixed-cart'));
            }
        }
    }

    /**
     * @param Cart $cart
     * @param SalesChannelContext $salesChannelContext
     */
    public function validateNotDewaShippingMethod(Cart $cart, SalesChannelContext $salesChannelContext): void
    {
        if ($this->systemConfigService->get("AppflixDewaShop.config.checkoutOpenDeliveryMethods")) {
            return;
        }

        $this->shippingMethod = $salesChannelContext->getShippingMethod();

        if (in_array($this->shippingMethod->getId(), [
            Defaults::SHIPPING_METHOD_DELIVERY_ID,
            Defaults::SHIPPING_METHOD_COLLECT_ID
        ])) {
            $cart->getErrors()->add(new ShippingMethodBlockedError($this->shippingMethod->getName()));
        }
    }

    /**
     * @param Cart $cart
     * @param SalesChannelContext $salesChannelContext
     */
    public function validateShippingMethod(Cart $original, SalesChannelContext $salesChannelContext): bool
    {
        $this->shippingMethod = $salesChannelContext->getShippingMethod();

        if (!in_array($this->shippingMethod->getId(), [
            Defaults::SHIPPING_METHOD_DELIVERY_ID,
            Defaults::SHIPPING_METHOD_COLLECT_ID
        ])) {
            $original->getErrors()->add(new WrongShippingMethodError($this->shippingMethod->getName()));

            return false;
        } else {
            if ($this->systemConfigService->get('AppflixDewaShop.config.posAutoOpen')) {
                $validateTime = false;
            } else {
                $data = $this->dewaShopService->getSession();
                $data = array_merge(['desiredTime' => 'now'], $data);

                preg_match('/(\+\d+.\w+)/', $data['desiredTime'], $matches, PREG_UNMATCHED_AS_NULL);
                if (!empty($matches[1])) {
                    $desiredTime = new \DateTimeImmutable('now', new \DateTimeZone($this->shop->getTimeZone()));
                    $this->desiredTime = $desiredTime->modify($data['desiredTime']);
                } else {
                    $this->desiredTime = new \DateTimeImmutable($data['desiredTime'], new \DateTimeZone($this->shop->getTimeZone()));
                }

                $checkoutTimepicker = $this->systemConfigService->get('AppflixDewaShop.config.checkoutTimepicker') ?: 'now';
                $validateTime = in_array($checkoutTimepicker, ['now', 'datetime', 'dropdownMinutes']);
            }

            if ($this->shippingMethod->getId() === Defaults::SHIPPING_METHOD_DELIVERY_ID) {
                $this->validateShippingMethodDelivery($original, $validateTime);
                $this->validateMinOrderValue($original, $salesChannelContext);
            } else if ($this->shippingMethod->getId() === Defaults::SHIPPING_METHOD_COLLECT_ID) {
                $this->validateShippingMethodCollect($original, $validateTime);
            }
        }

        return true;
    }

    /**
     * @param Cart $cart
     * @param SalesChannelContext $salesChannelContext
     */
    public function validateStocks(Cart $cart, SalesChannelContext $salesChannelContext): void
    {
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);

        $shop = $this->dewaShopService->getShop();
        if (!$shop) {
            return;
        }

        $stocks = $this->stockService->getShopStockItems($shop->getId());

        foreach ($cart->getLineItems() as $lineItem) {
            /* TODO: Check also if a Product is bound to a other Shop */
            $stock = $stocks->getByProductId($lineItem->getReferencedId());
            if (!$stock) {
                continue;
            }

            $stockUnit = $lineItem->getPayloadValue('stockUnit') ?: 1;
            $quantity = ceil($lineItem->getQuantity() * $stockUnit);

            $stock->setStock($stock->getStock() - $quantity);
        }

        foreach ($stocks->filterModified() as $stock) {
            if ($stock->getStock() < 0) {
                $cart->getErrors()->add(new ProductOutOfStockError($stock->getProductId(), $stock->getProduct()->getName()));
            }
        }
    }

    /**
     * @param Cart $original
     * @param bool $validateTime
     */
    public function validateShippingMethodDelivery(Cart $original, bool $validateTime): void
    {
        if ($this->shop->getDeliveryType() === 'radius') {
            if ($this->shop->getDistance() > $this->shop->getMaxRadius()) {
                $original->getErrors()->add(
                    new DeliveryOutOfRangeError(
                        $this->shop->getName(),
                        sprintf("max. %skm", $this->shop->getMaxRadius())
                    )
                );
            }
        } elseif ($this->shop->getDeliveryType() === 'area') {
            if (!$this->shop->getShopAreas() || !$this->shop->getShopAreas()->getByZipcode($this->address->getZipcode())) {
                $original->getErrors()->add(
                    new DeliveryOutOfRangeError(
                        $this->shop->getName(),
                        implode(", ", $this->shop->getShopAreas()->getZipcodes())
                    )
                );
            }
        }

        if (!$validateTime || $this->shop->getDeliveryActive($this->desiredTime->format(DATE_ATOM))) {
            return;
        }

        $original->getErrors()->add(
            new DeliveryClosedError($this->shippingMethod->getName(), $this->shop->getName(), $this->desiredTime->format("Y-m-d H:i (e)"))
        );
    }

    /**
     * @param Cart $original
     * @param bool $validateTime
     */
    public function validateShippingMethodCollect(Cart $original, bool $validateTime): void
    {
        if (!$validateTime || $this->shop->getCollectActive($this->desiredTime->format(DATE_ATOM))) {
            return;
        }

        $original->getErrors()->add(
            new CollectClosedError($this->shippingMethod->getName(), $this->shop->getName(), $this->desiredTime->format("Y-m-d H:i (e)"))
        );
    }

    /**
     * @param Cart $original
     */
    public function validateShops(Cart $original): bool
    {
        if (!$this->shops) {
            $original->getErrors()->add(
                new ShopNotAvailableError('dewa-shop-not-available')
            );

            return false;
        } elseif (!$this->shop || !$this->shops->get($this->shop->getId())) {
            $original->getErrors()->add(
                new NoShopSelectedError('dewa-no-shop-selected')
            );

            return false;
        }

        $this->shop = $this->shops->get($this->shop->getId());

        return true;
    }
}
