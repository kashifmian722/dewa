<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Option;

use Appflix\DewaShop\Core\Content\OptionItem\OptionItemCollection;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Unit\UnitEntity;

class OptionEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $type;
    protected ?string $name;
    protected ?string $unitId;
    protected ?float $referenceUnit;
    protected ?CategoryCollection $categories = null;
    protected ?OptionItemCollection $items = null;
    protected ?UnitEntity $unit = null;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getUnitId(): ?string
    {
        return $this->unitId;
    }

    /**
     * @param string|null $unitId
     */
    public function setUnitId(?string $unitId): void
    {
        $this->unitId = $unitId;
    }

    /**
     * @return float|null
     */
    public function getReferenceUnit(): ?float
    {
        return $this->referenceUnit;
    }

    /**
     * @param float|null $referenceUnit
     */
    public function setReferenceUnit(?float $referenceUnit): void
    {
        $this->referenceUnit = $referenceUnit;
    }

    /**
     * @return UnitEntity|null
     */
    public function getUnit(): ?UnitEntity
    {
        return $this->unit;
    }

    /**
     * @param UnitEntity|null $unit
     */
    public function setUnit(?UnitEntity $unit): void
    {
        $this->unit = $unit;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return CategoryCollection|null
     */
    public function getCategories(): ?CategoryCollection
    {
        return $this->categories;
    }

    /**
     * @param CategoryCollection|null $categories
     */
    public function setCategories(?CategoryCollection $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return OptionItemCollection|null
     */
    public function getItems(): ?OptionItemCollection
    {
        return $this->items;
    }

    /**
     * @param OptionItemCollection|null $items
     */
    public function setItems(?OptionItemCollection $items): void
    {
        $this->items = $items;
    }
}
