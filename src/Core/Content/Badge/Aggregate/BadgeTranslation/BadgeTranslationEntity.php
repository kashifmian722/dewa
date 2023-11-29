<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Badge\Aggregate\BadgeTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class BadgeTranslationEntity extends TranslationEntity
{
    protected ?string $name;

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
}
