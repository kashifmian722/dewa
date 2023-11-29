<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Option;

use Appflix\DewaShop\Core\Content\OptionCategory\OptionCategoryDefinition;
use Appflix\DewaShop\Core\Content\OptionItem\OptionItemDefinition;
use Appflix\DewaShop\Core\Content\OptionProduct\OptionProductDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Unit\UnitDefinition;

class OptionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_option';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OptionCollection::class;
    }

    public function getEntityClass(): string
    {
        return OptionEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'referenceUnit' => 1
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('unit_id', 'unitId', UnitDefinition::class)),

            (new StringField('name', 'name'))->addFlags(new Required(), new EditField('text')),
            (new StringField('type', 'type'))->addFlags(new Required(), new EditField('text')),
            (new BoolField('ignore_price_factor', 'ignorePriceFactor'))->addFlags(new EditField('switch')),
            (new FloatField('reference_unit', 'referenceUnit')),

            (new ManyToOneAssociationField('unit', 'unit_id', UnitDefinition::class, 'id')),
            (new ManyToManyAssociationField('categories', CategoryDefinition::class, OptionCategoryDefinition::class, 'dewa_option_id', 'category_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('optionCategories', OptionCategoryDefinition::class, 'dewa_option_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('optionProducts', OptionProductDefinition::class, 'dewa_option_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('items', OptionItemDefinition::class, 'dewa_option_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
