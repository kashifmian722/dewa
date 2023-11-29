<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\OptionProduct;

use Appflix\DewaShop\Core\Content\Option\OptionDefinition;
use Appflix\DewaShop\Core\Content\OptionProduct\Aggregate\OptionProductTranslation\OptionProductTranslationDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OptionProductDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_option_product';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OptionProductCollection::class;
    }

    public function getEntityClass(): string
    {
        return OptionProductEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('dewa_option_id', 'optionId', OptionDefinition::class))->addFlags(new Required()),

            (new IntField('priority', 'priority'))->addFlags(new EditField('number', ['tooltip'=>'priority'])),
            (new BoolField('is_collapsible', 'isCollapsible'))->addFlags(new EditField('switch', ['tooltip'=>'isCollapsible'])),
            (new StringField('info', 'info'))->addFlags(new EditField('text', ['tooltip'=>'internalUse'])),

            (new TranslatedField('name'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),

            new TranslationsAssociationField(OptionProductTranslationDefinition::class, 'dewa_option_product_id'),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('option', 'dewa_option_id', OptionDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
        ]);
    }
}
