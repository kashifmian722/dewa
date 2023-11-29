<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionItem\Aggregate\OptionItemTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class OptionItemTranslationEntity extends TranslationEntity
{
    protected string $name;

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
}
