<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder;

use Appflix\DewaShop\Core\Content\Deliverer\DelivererEntity;
use Appflix\DewaShop\Core\Content\Shop\ShopEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ShopOrderEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var float|null
     */
    protected $locationLat;
    /**
     * @var float|null
     */
    protected $locationLon;
    /**
     * @var float|null
     */
    protected $distance;
    /**
     * @var string|null
     */
    protected $comment;
    /**
     * @var \DateTimeImmutable|null
     */
    protected $desiredTime;
    /**
     * @var string|null
     */
    protected $orderId;
    /**
     * @var string|null
     */
    protected $shopId;
    /**
     * @var string|null
     */
    protected $delivererId;
    /**
     * @var OrderEntity|null
     */
    protected $order;
    /**
     * @var ShopEntity|null
     */
    protected $shop;
    /**
     * @var DelivererEntity|null
     */
    protected $deliverer;

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
     * @return float|null
     */
    public function getDistance(): ?float
    {
        return $this->distance;
    }

    /**
     * @param float|null $distance
     */
    public function setDistance(?float $distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDesiredTime(): ?\DateTimeImmutable
    {
        return $this->desiredTime;
    }

    /**
     * @param \DateTimeImmutable|null $desiredTime
     */
    public function setDesiredTime(?\DateTimeImmutable $desiredTime): void
    {
        $this->desiredTime = $desiredTime;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @param string|null $orderId
     */
    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string|null
     */
    public function getShopId(): ?string
    {
        return $this->shopId;
    }

    /**
     * @param string|null $shopId
     */
    public function setShopId(?string $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @return string|null
     */
    public function getDelivererId(): ?string
    {
        return $this->delivererId;
    }

    /**
     * @param string|null $delivererId
     */
    public function setDelivererId(?string $delivererId): void
    {
        $this->delivererId = $delivererId;
    }

    /**
     * @return OrderEntity|null
     */
    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    /**
     * @param OrderEntity|null $order
     */
    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
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

    /**
     * @return DelivererEntity|null
     */
    public function getDeliverer(): ?DelivererEntity
    {
        return $this->deliverer;
    }

    /**
     * @param DelivererEntity|null $deliverer
     */
    public function setDeliverer(?DelivererEntity $deliverer): void
    {
        $this->deliverer = $deliverer;
    }
}