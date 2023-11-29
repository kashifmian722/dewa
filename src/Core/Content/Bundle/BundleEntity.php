<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Bundle;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class BundleEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;
    protected ?string $accessoryProductId = null;
    protected ?string $accessoryStreamId = null;
    protected ?ProductEntity $product = null;
    protected ?ProductEntity $accessoryProduct = null;
    protected ?ProductCollection $accessoryProducts = null;
    protected ?ProductStreamEntity $accessoryStream = null;

    /**
     * @return ProductCollection|null
     */
    public function getAccessoryProducts(): ?ProductCollection
    {
        return $this->accessoryProducts;
    }

    /**
     * @param ProductCollection|null $accessoryProducts
     */
    public function setAccessoryProducts(?ProductCollection $accessoryProducts): void
    {
        $this->accessoryProducts = $accessoryProducts;
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
    public function getAccessoryProductId(): ?string
    {
        return $this->accessoryProductId;
    }

    /**
     * @param string|null $accessoryProductId
     */
    public function setAccessoryProductId(?string $accessoryProductId): void
    {
        $this->accessoryProductId = $accessoryProductId;
    }

    /**
     * @return string|null
     */
    public function getAccessoryStreamId(): ?string
    {
        return $this->accessoryStreamId;
    }

    /**
     * @param string|null $accessoryStreamId
     */
    public function setAccessoryStreamId(?string $accessoryStreamId): void
    {
        $this->accessoryStreamId = $accessoryStreamId;
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
     * @return ProductEntity|null
     */
    public function getAccessoryProduct(): ?ProductEntity
    {
        return $this->accessoryProduct;
    }

    /**
     * @param ProductEntity|null $accessoryProduct
     */
    public function setAccessoryProduct(?ProductEntity $accessoryProduct): void
    {
        $this->accessoryProduct = $accessoryProduct;
    }

    /**
     * @return ProductStreamEntity|null
     */
    public function getAccessoryStream(): ?ProductStreamEntity
    {
        return $this->accessoryStream;
    }

    /**
     * @param ProductStreamEntity|null $accessoryStream
     */
    public function setAccessoryStream(?ProductStreamEntity $accessoryStream): void
    {
        $this->accessoryStream = $accessoryStream;
    }
}
