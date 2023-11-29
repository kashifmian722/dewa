<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintJob;

use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Recovery\Install\Struct\Shop;

class PrintJobEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string|null $shopOrderId
     */
    public $shopOrderId;
    /**
     * @var ShopOrderEntity|null $shopOrder
     */
    public $shopOrder;
    private bool $isPrinting;

    /**
     * @return bool
     */
    public function getIsPrinting(): bool
    {
        return $this->isPrinting;
    }

    /**
     * @param bool $isPrinting
     */
    public function setIsPrinting(bool $isPrinting): void
    {
        $this->isPrinting = $isPrinting;
    }

    public function getShopOrderId(): ?string
    {
        return $this->shopOrderId;
    }

    public function setShopOrderId(?string $shopOrderId)
    {
        $this->shopOrderId = $shopOrderId;
    }

    public function getShopOrder(): ?ShopOrderEntity
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrderEntity $shopOrder)
    {
        $this->shopOrder = $shopOrder;
    }
}