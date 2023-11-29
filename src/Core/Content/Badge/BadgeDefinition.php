<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Badge;

use Appflix\DewaShop\Core\Content\Badge\Aggregate\BadgeOptionItem\BadgeOptionItemDefinition;
use Appflix\DewaShop\Core\Content\Badge\Aggregate\BadgeProduct\BadgeProductDefinition;
use Appflix\DewaShop\Core\Content\Badge\Aggregate\BadgeTranslation\BadgeTranslationDefinition;
use Appflix\DewaShop\Core\Content\SvgIcon\SvgIconDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class BadgeDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_badge';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return BadgeCollection::class;
    }

    public function getEntityClass(): string
    {
        return BadgeEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('dewa_svg_icon_id', 'svgIconId', SvgIconDefinition::class))->addFlags(new Required()),
            (new StringField('icon', 'icon'))->addFlags(new EditField('text')),
            (new StringField('background_color', 'backgroundColor'))->addFlags(new EditField('text')),
            (new StringField('icon_color', 'iconColor'))->addFlags(new EditField('text')),
            (new TranslatedField('name'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            new TranslationsAssociationField(BadgeTranslationDefinition::class, 'dewa_badge_id'),

            new ManyToManyAssociationField(
                'products',
                ProductDefinition::class,
                BadgeProductDefinition::class,
                'dewa_badge_id',
                'product_id'
            ),

            (new ManyToOneAssociationField('svgIcon', 'dewa_svg_icon_id', SvgIconDefinition::class))->addFlags(new CascadeDelete()),
        ]);
    }
}
