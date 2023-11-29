<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Ingredient\Aggregate\IngredientTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class IngredientTranslationEntity extends TranslationEntity
{
    protected ?string $name;

    protected ?string $description;

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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
