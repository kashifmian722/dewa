<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Product\Aggregate\ProductIndex;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProductIndexEntity extends Entity
{
    use EntityIdTrait;

    protected array $weekdayConfig;
    protected bool $productConfigurator;
    protected bool $productIngredient;
    protected string $productId;
    protected string $productVersionId;

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
     * @return string
     */
    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    /**
     * @param string $productVersionId
     */
    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    /**
     * @return bool
     */
    public function isProductIngredient(): bool
    {
        return $this->productIngredient;
    }

    /**
     * @param bool $productIngredient
     */
    public function setProductIngredient(bool $productIngredient): void
    {
        $this->productIngredient = $productIngredient;
    }

    /**
     * @return bool
     */
    public function isProductConfigurator(): bool
    {
        return $this->productConfigurator;
    }

    /**
     * @param bool $productConfigurator
     */
    public function setProductConfigurator(bool $productConfigurator): void
    {
        $this->productConfigurator = $productConfigurator;
    }

    /**
     * @return array
     */
    public function getWeekdayConfig(): array
    {
        return $this->weekdayConfig;
    }

    /**
     * @param array $weekdayConfig
     */
    public function setWeekdayConfig(array $weekdayConfig): void
    {
        $this->weekdayConfig = $weekdayConfig;
    }

    public function getTodayConfig(): ?array
    {
        if (empty($this->weekdayConfig)) {
            return null;
        }

        $dayIndex = ((new \DateTime())->format('N') - 1); // 0 Monday - 6 Sunday
        if (empty($this->weekdayConfig[$dayIndex])) {
            return null;
        }

        return $this->weekdayConfig[$dayIndex];
    }
}
