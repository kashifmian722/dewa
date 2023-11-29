<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintJob;

use Appflix\DewaShop\Core\Content\Printer\PrinterDefinition;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PrintJobDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_print_job';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PrintJobCollection::class;
    }

    public function getEntityClass(): string
    {
        return PrintJobEntity::class;
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
            (new FkField('dewa_shop_order_id', 'shopOrderId', ShopOrderDefinition::class)),
            (new BoolField('is_printing', 'isPrinting')),
            (new CreatedAtField()),
            (new UpdatedAtField()),

            (new ManyToOneAssociationField('printer', 'dewa_printer_id', PrinterDefinition::class, 'id')),
            (new ManyToOneAssociationField('shopOrder', 'dewa_shop_order_id', ShopOrderDefinition::class, 'id'))
        ]);
    }
}
