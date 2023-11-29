<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\SvgIcon;

use Appflix\DewaShop\Core\Content\SvgIconItem\SvgIconItemCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class SvgIconEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $content;

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}