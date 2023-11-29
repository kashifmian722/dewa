<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionItem;

use Appflix\DewaShop\Core\Content\Option\OptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class OptionItemEntity extends Entity
{
    use EntityIdTrait;

    protected ?float $price;
    protected ?float $purchaseUnit;
    protected ?float $priceFactor;

    /**
     * @return float|null
     */
    public function getPurchaseUnit(): ?float
    {
        return $this->purchaseUnit;
    }

    /**
     * @param float|null $purchaseUnit
     */
    public function setPurchaseUnit(?float $purchaseUnit): void
    {
        $this->purchaseUnit = $purchaseUnit;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float|null
     */
    public function getPriceFactor(): ?float
    {
        return $this->priceFactor;
    }

    /**
     * @param float|null $priceFactor
     */
    public function setPriceFactor(?float $priceFactor): void
    {
        $this->priceFactor = $priceFactor;
    }
}