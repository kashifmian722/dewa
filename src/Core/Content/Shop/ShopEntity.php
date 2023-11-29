<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop;

use Appflix\DewaShop\Core\Content\OpeningHours\OpeningHoursStruct;
use Appflix\DewaShop\Core\Content\Printer\PrinterCollection;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopArea\ShopAreaCollection;
use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Country\CountryEntity;

class ShopEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $categoryId;
    protected ?PrinterCollection $printers = null;
    protected ?ShopAreaCollection $shopAreas = null;
    protected ?CategoryEntity $category;
    protected ?string $countryId;
    protected ?CountryEntity $country;
    protected ?string $mediaId;
    protected string $timeZone;
    protected string $deliveryType;
    protected ?MediaEntity $media;
    protected ?bool $active;
    protected bool $isOpen = false;
    protected ?bool $isDefault;
    protected ?bool $deliveryActive;
    protected ?bool $collectActive;
    protected string $name;
    protected ?string $firstName;
    protected ?string $lastName;
    protected ?string $street;
    protected ?string $streetNumber;
    protected ?string $email;
    protected ?string $zipcode;
    protected ?string $city;
    protected ?float $locationLat;
    protected ?float $locationLon;
    protected ?float $maxRadius;
    protected ?float $distance = null;
    protected ?string $executiveDirector;
    protected ?string $placeOfFulfillment;
    protected ?string $placeOfJurisdiction;
    protected ?string $bankBic;
    protected ?string $bankIban;
    protected ?string $bankName;
    protected ?string $taxOffice;
    protected ?string $taxNumber;
    protected ?string $vatId;
    protected ?string $phoneNumber;
    protected ?string $faxNumber;
    protected ?array $openingHours;
    protected ?array $deliveryHours;
    protected ?int $preparationTime;
    protected ?int $deliveryTime;
    protected float $deliveryPrice;

    /**
     * @return bool
     */
    public function getIsOpen(): bool
    {
        /* Wenn eines aktiv, dann ist Restaurant geöffnet */
        return $this->deliveryActive || $this->collectActive;
    }

    /**
     * @param bool $isOpen
     */
    public function setIsOpen(bool $isOpen): void
    {
        $this->isOpen = $isOpen;
    }

    /**
     * @return string|null
     */
    public function getExecutiveDirector(): ?string
    {
        return $this->executiveDirector;
    }

    /**
     * @param string|null $executiveDirector
     */
    public function setExecutiveDirector(?string $executiveDirector): void
    {
        $this->executiveDirector = $executiveDirector;
    }

    /**
     * @return string|null
     */
    public function getPlaceOfFulfillment(): ?string
    {
        return $this->placeOfFulfillment;
    }

    /**
     * @param string|null $placeOfFulfillment
     */
    public function setPlaceOfFulfillment(?string $placeOfFulfillment): void
    {
        $this->placeOfFulfillment = $placeOfFulfillment;
    }

    /**
     * @return string|null
     */
    public function getPlaceOfJurisdiction(): ?string
    {
        return $this->placeOfJurisdiction;
    }

    /**
     * @param string|null $placeOfJurisdiction
     */
    public function setPlaceOfJurisdiction(?string $placeOfJurisdiction): void
    {
        $this->placeOfJurisdiction = $placeOfJurisdiction;
    }

    /**
     * @return string|null
     */
    public function getBankBic(): ?string
    {
        return $this->bankBic;
    }

    /**
     * @param string|null $bankBic
     */
    public function setBankBic(?string $bankBic): void
    {
        $this->bankBic = $bankBic;
    }

    /**
     * @return string|null
     */
    public function getBankIban(): ?string
    {
        return $this->bankIban;
    }

    /**
     * @param string|null $bankIban
     */
    public function setBankIban(?string $bankIban): void
    {
        $this->bankIban = $bankIban;
    }

    /**
     * @return string|null
     */
    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    /**
     * @param string|null $bankName
     */
    public function setBankName(?string $bankName): void
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string|null
     */
    public function getTaxOffice(): ?string
    {
        return $this->taxOffice;
    }

    /**
     * @param string|null $taxOffice
     */
    public function setTaxOffice(?string $taxOffice): void
    {
        $this->taxOffice = $taxOffice;
    }

    /**
     * @return string|null
     */
    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    /**
     * @param string|null $taxNumber
     */
    public function setTaxNumber(?string $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    /**
     * @return float
     */
    public function getDeliveryPrice(): float
    {
        return $this->deliveryPrice;
    }

    /**
     * @param float $deliveryPrice
     */
    public function setDeliveryPrice(float $deliveryPrice): void
    {
        $this->deliveryPrice = $deliveryPrice;
    }

    /**
     * @return float
     */
    public function getMinOrderValue(): float
    {
        return $this->minOrderValue;
    }

    /**
     * @param float $minOrderValue
     */
    public function setMinOrderValue(float $minOrderValue): void
    {
        $this->minOrderValue = $minOrderValue;
    }
    protected float $minOrderValue;

    /**
     * @return ShopAreaCollection|null
     */
    public function getShopAreas(): ?ShopAreaCollection
    {
        return $this->shopAreas;
    }

    /**
     * @param ShopAreaCollection|null $shopAreas
     */
    public function setShopAreas(?ShopAreaCollection $shopAreas): void
    {
        $this->shopAreas = $shopAreas;
    }

    /**
     * @return string
     */
    public function getDeliveryType(): string
    {
        return $this->deliveryType;
    }

    /**
     * @param string $deliveryType
     */
    public function setDeliveryType(string $deliveryType): void
    {
        $this->deliveryType = $deliveryType;
    }

    /**
     * @return string
     */
    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     */
    public function setTimeZone(string $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    /**
     * @return PrinterCollection|null
     */
    public function getPrinters(): ?PrinterCollection
    {
        return $this->printers;
    }

    /**
     * @param PrinterCollection|null $printers
     */
    public function setPrinters(?PrinterCollection $printers): void
    {
        $this->printers = $printers;
    }

    /**
     * @return bool|null
     */
    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool|null $isDefault
     */
    public function setIsDefault(?bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * @return int|null
     */
    public function getPreparationTime(): ?int
    {
        return $this->preparationTime;
    }

    /**
     * @param int|null $preparationTime
     */
    public function setPreparationTime(?int $preparationTime): void
    {
        $this->preparationTime = $preparationTime;
    }

    /**
     * @return int|null
     */
    public function getDeliveryTime(): ?int
    {
        return $this->deliveryTime;
    }

    /**
     * @param int|null $deliveryTime
     */
    public function setDeliveryTime(?int $deliveryTime): void
    {
        $this->deliveryTime = $deliveryTime;
    }

    /**
     * @return float|null
     */
    public function getDistance(): ?float
    {
        return $this->distance;
    }

    /**
     * @param GeoPoint|null $geoPoint
     */
    public function setDistance(?GeoPoint $geoPoint = null): void
    {
        if (!$geoPoint) {
            return;
        }

        $distance = $geoPoint->distanceTo(
            (new GeoPoint($this->getLocationLat(), $this->getLocationLon())),
            'km'
        );

        $this->distance = $distance;
    }

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
    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    /**
     * @param string|null $categoryId
     */
    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return CategoryEntity|null
     */
    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    /**
     * @param CategoryEntity|null $category
     */
    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string|null
     */
    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    /**
     * @param string|null $countryId
     */
    public function setCountryId(?string $countryId): void
    {
        $this->countryId = $countryId;
    }

    /**
     * @return CountryEntity|null
     */
    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    /**
     * @param CountryEntity|null $country
     */
    public function setCountry(?CountryEntity $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    /**
     * @param string|null $mediaId
     */
    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return MediaEntity|null
     */
    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    /**
     * @param MediaEntity|null $media
     */
    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
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

    /**
     * @return bool|null
     */
    public function getDeliveryActive(?string $datetime = null): ?bool
    {
        $c1 = ($this->getDeliveryHours()->isOpen($datetime));

        return ($c1 && $this->deliveryActive);
    }

    /**
     * @return string
     */
    public function getDeliveryTimeLeft(?string $datetime = null): string
    {
        $openingHours = $this->getDeliveryHours();
        $openingHours->isOpen($datetime);

        return $openingHours->getTimeLeft();
    }

    /**
     * @return string
     */
    public function getCollectTimeLeft(?string $datetime = null): string
    {
        $openingHours = $this->getOpeningHours();
        $openingHours->isOpen($datetime);

        return $openingHours->getTimeLeft();
    }

    /**
     * @return string
     */
    public function getDeliveryNextTime(?string $datetime = null): string
    {
        $openingHours = $this->getDeliveryHours();
        $openingHours->isOpen($datetime);

        return $openingHours->getNextTime();
    }

    /**
     * @param string|null $datetime
     * @return string
     */
    public function getCollectNextTime(?string $datetime = null): string
    {
        $openingHours = $this->getOpeningHours();
        $openingHours->isOpen($datetime);

        return $openingHours->getNextTime();
    }

    /**
     * @param bool|null $deliveryActive
     */
    public function setDeliveryActive(?bool $deliveryActive): void
    {
        $this->deliveryActive = $deliveryActive;
    }

    /**
     * @return OpeningHoursStruct|null
     */
    public function getDeliveryHours(): ?OpeningHoursStruct
    {
        return new OpeningHoursStruct($this->deliveryHours, $this->timeZone);
    }

    /**
     * @param OpeningHoursStruct|null $deliveryHours
     */
    public function setDeliveryHours(?OpeningHoursStruct $deliveryHours): void
    {
        $this->deliveryHours = $deliveryHours;
    }

    /**
     * @param string|null $datetime
     * @return bool|null
     */
    public function getCollectActive(?string $datetime = null): ?bool
    {
        $c1 = ($this->getOpeningHours()->isOpen($datetime));

        return ($c1 && $this->collectActive);
    }

    /**
     * @param bool|null $collectActive
     */
    public function setCollectActive(?bool $collectActive): void
    {
        $this->collectActive = $collectActive;
    }

    /**
     * @return OpeningHoursStruct|null
     */
    public function getOpeningHours(): ?OpeningHoursStruct
    {
        return new OpeningHoursStruct($this->openingHours, $this->timeZone);
    }

    /**
     * @param OpeningHoursStruct|null $openingHours
     */
    public function setOpeningHours(?OpeningHoursStruct $openingHours): void
    {
        $this->openingHours = $openingHours;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     */
    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string|null
     */
    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    /**
     * @param string|null $streetNumber
     */
    public function setStreetNumber(?string $streetNumber): void
    {
        $this->streetNumber = $streetNumber;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    /**
     * @param string|null $zipcode
     */
    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return float|null
     */
    public function getMaxRadius(): ?float
    {
        return $this->maxRadius;
    }

    /**
     * @param float|null $maxRadius
     */
    public function setMaxRadius(?float $maxRadius): void
    {
        $this->maxRadius = $maxRadius;
    }

    /**
     * @return string|null
     */
    public function getVatId(): ?string
    {
        return $this->vatId;
    }

    /**
     * @param string|null $vatId
     */
    public function setVatId(?string $vatId): void
    {
        $this->vatId = $vatId;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string|null
     */
    public function getFaxNumber(): ?string
    {
        return $this->faxNumber;
    }

    /**
     * @param string|null $faxNumber
     */
    public function setFaxNumber(?string $faxNumber): void
    {
        $this->faxNumber = $faxNumber;
    }
}
