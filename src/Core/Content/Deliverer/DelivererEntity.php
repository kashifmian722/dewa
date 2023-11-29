<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Deliverer;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class DelivererEntity extends Entity
{
    use EntityIdTrait;

    protected ?float $locationLat;

    protected ?float $locationLon;

    protected ?string $name;

    protected ?bool $active;

    /**
     * @return float|null
     */
    public function getLocationLat(): ?float
    {
        return $this->locationLat;
    }

    /**
     * @param float|null $locationLat
     */
    public function setLocationLat(?float $locationLat): void
    {
        $this->locationLat = $locationLat;
    }

    /**
     * @return float|null
     */
    public function getLocationLon(): ?float
    {
        return $this->locationLon;
    }

    /**
     * @param float|null $locationLon
     */
    public function setLocationLon(?float $locationLon): void
    {
        $this->locationLon = $locationLon;
    }

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
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool|null $active
     */
    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }
}