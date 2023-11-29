<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover;

use Appflix\DewaShop\Core\Content\Printer\PrinterDefinition;
use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PrintTurnoverDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_print_turnover';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PrintTurnoverCollection::class;
    }

    public function getEntityClass(): string
    {
        return PrintTurnoverEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('dewa_printer_id', 'printerId', PrinterDefinition::class)),
            (new FkField('dewa_shop_id', 'shopId', ShopDefinition::class)),
            (new StringField('interval', 'interval')),
            (new StringField('key', 'key')),
            (new BoolField('is_printing', 'isPrinting')),
            (new CreatedAtField()),
            (new UpdatedAtField()),

            (new ManyToOneAssociationField('printer', 'dewa_printer_id', PrinterDefinition::class, 'id')),
            (new ManyToOneAssociationField('shop', 'dewa_shop_id', ShopDefinition::class, 'id'))
        ]);
    }
}
