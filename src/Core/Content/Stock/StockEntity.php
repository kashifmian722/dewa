<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Stock;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class StockEntity extends Entity
{
    protected string $shopId;
    protected string $productId;
    use EntityIdTrait;
    protected ?ProductEntity $product = null;
    protected ?bool $isStock = null;
    protected bool $isModified = false;
    protected ?string $info = null;
    protected int $stock = 0;
    protected ?string $restockRule = null;
    protected ?string $restockInterval = null;
    protected int $restockAmount = 0;
    protected ?\DateTimeImmutable $restockedAt = null;

    /**
     * @return bool|null
     */
    public function getIsStock(): ?bool
    {
        return $this->isStock;
    }

    /**
     * @param bool|null $isStock
     */
    public function setIsStock(?bool $isStock): void
    {
        $this->isStock = $isStock;
    }

    /**
     * @return ProductEntity|null
     */
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity|null $product
     */
    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    /**
     * @return bool
     */
    public function isModified(): bool
    {
        return $this->isModified;
    }

    /**
     * @param bool $isModified
     */
    public function setIsModified(bool $isModified): void
    {
        $this->isModified = $isModified;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return string|null
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param string|null $info
     */
    public function setInfo(?string $info): void
    {
        $this->info = $info;
    }

    /**
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock(int $stock): void
    {
        $this->stock = $stock;
        $this->isModified = true;
    }

    /**
     * @return string|null
     */
    public function getRestockRule(): ?string
    {
        return $this->restockRule;
    }

    /**
     * @param string|null $restockRule
     */
    public function setRestockRule(?string $restockRule): void
    {
        $this->restockRule = $restockRule;
    }

    /**
     * @return string|null
     */
    public function getRestockInterval(): ?string
    {
        return $this->restockInterval;
    }

    /**
     * @param string|null $restockInterval
     */
    public function setRestockInterval(?string $restockInterval): void
    {
        $this->restockInterval = $restockInterval;
    }

    /**
     * @return int
     */
    public function getRestockAmount(): int
    {
        return $this->restockAmount;
    }

    /**
     * @param int $restockAmount
     */
    public function setRestockAmount(int $restockAmount): void
    {
        $this->restockAmount = $restockAmount;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getRestockedAt(): ?\DateTimeImmutable
    {
        return $this->restockedAt;
    }

    /**
     * @param \DateTimeImmutable|null $restockedAt
     */
    public function setRestockedAt(?\DateTimeImmutable $restockedAt): void
    {
        $this->restockedAt = $restockedAt;
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
}
