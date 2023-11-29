<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Deliverer;

use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class DelivererDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_deliverer';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DelivererCollection::class;
    }

    public function getEntityClass(): string
    {
        return DelivererEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class)),
            (new FkField('dewa_shop_id', 'shopId', ShopDefinition::class)),
            (new BoolField('active', 'active'))->addFlags(new EditField('switch')),
            (new StringField('name', 'name'))->addFlags(new EditField('text'), new Required()),
            (new StringField('tracking_code', 'trackingCode'))->addFlags(new EditField('text', ['tooltip' => 'delivererTrackingCode'])),
            (new FloatField('location_lat','locationLat')),
            (new FloatField('location_lon','locationLon')),
            (new StringField('phone_number', 'phoneNumber'))->addFlags(new EditField('text')),

            (new ManyToOneAssociationField('shop', 'dewa_shop_id', ShopDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id'))->addFlags(/*new EditField(null, ['label' => 'avatar']), */new LabelProperty('fileName')),
        ]);
    }
}