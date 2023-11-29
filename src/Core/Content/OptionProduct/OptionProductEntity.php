<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionProduct;

use Appflix\DewaShop\Core\Content\Option\OptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class OptionProductEntity extends Entity
{
    use EntityIdTrait;

    protected ?OptionEntity $option;
    protected bool $isCollapsible;

    /**
     * @return bool
     */
    public function isCollapsible(): bool
    {
        return $this->isCollapsible;
    }

    /**
     * @param bool $isCollapsible
     */
    public function setIsCollapsible(bool $isCollapsible): void
    {
        $this->isCollapsible = $isCollapsible;
    }

    /**
     * @return OptionEntity|null
     */
    public function getOption(): ?OptionEntity
    {
        return $this->option;
    }

    /**
     * @param OptionEntity|null $option
     */
    public function setOption(?OptionEntity $option): void
    {
        $this->option = $option;
    }
}