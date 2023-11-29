<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover;

use Appflix\DewaShop\Core\Content\Printer\PrinterEntity;
use Appflix\DewaShop\Core\Content\Shop\ShopEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PrintTurnoverEntity extends Entity
{
    use EntityIdTrait;

    protected string $printerId;
    protected string $shopId;
    protected string $interval;
    protected string $key;
    protected bool $isPrinting;
    protected ?ShopEntity $shop = null;
    protected ?PrinterEntity $printer = null;

    /**
     * @return PrinterEntity|null
     */
    public function getPrinter(): ?PrinterEntity
    {
        return $this->printer;
    }

    /**
     * @param PrinterEntity|null $printer
     */
    public function setPrinter(?PrinterEntity $printer): void
    {
        $this->printer = $printer;
    }

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

    /**
     * @return string
     */
    public function getPrinterId(): string
    {
        return $this->printerId;
    }

    /**
     * @param string $printerId
     */
    public function setPrinterId(string $printerId): void
    {
        $this->printerId = $printerId;
    }

    /**
     * @return string
     */
    public function getShopId(): string
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId(string $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * @param string $interval
     */
    public function setInterval(string $interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return ShopEntity|null
     */
    public function getShop(): ?ShopEntity
    {
        return $this->shop;
    }

    /**
     * @param ShopEntity|null $shop
     */
    public function setShop(?ShopEntity $shop): void
    {
        $this->shop = $shop;
    }
}
