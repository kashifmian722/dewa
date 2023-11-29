<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Shop;

use Appflix\DewaShop\Core\Content\Deliverer\DelivererDefinition;
use Appflix\DewaShop\Core\Content\Printer\PrinterDefinition;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopArea\ShopAreaDefinition;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderDefinition;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopProduct\ShopProductDefinition;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopSalesChannel\ShopSalesChannelDefinition;
use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\CountryDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class ShopDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_shop';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ShopCollection::class;
    }

    public function getEntityClass(): string
    {
        return ShopEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false,
            'searchPortalActive' => false,
            'isOpen' => false,
            'openingHours' => Defaults::getOpeningHours(),
            'deliveryHours' => Defaults::getOpeningHours(),
            'preparationTime' => 30,
            'deliveryTime' => 30,
            'deliveryPrice' => 2,
            'minOrderValue' => 20,
            'deliveryType' => 'radius',
            'timeZone' => 'Europe/Berlin',
            'maxRadius' => 5,
            'deliveryActive' => true,
            'collectActive' => true,
            'autoLocation' => true,
            'limitRule' => 'disabled',
            'limitInterval' => 'halfHour',
            'limitAmount' => 5,
            'limitedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'executiveDirector' => 'Max Mustermann',
            'placeOfFulfillment' => 'Musterstadt',
            'placeOfJurisdiction' => 'Musterstadt',
            'bankBic' => 'BYLADEM1001',
            'bankIban' => 'DE12500105170648489890',
            'bankName' => 'Musterbank',
            'taxOffice' => 'Finanzamt Musterstadt',
            'taxNumber' => '123 4567 89',
            'vatId' => 'DE999999999'
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class)),
            (new FkField('category_id', 'categoryId', CategoryDefinition::class)),
            (new FkField('country_id', 'countryId', CountryDefinition::class)),

            (new BoolField('active', 'active'))->addFlags(new EditField('switch')),
            (new BoolField('is_open', 'isOpen'))->addFlags(new EditField('switch')),
            (new BoolField('search_portal_active', 'searchPortalActive'))->addFlags(new EditField('switch')),
            (new BoolField('is_default', 'isDefault'))->addFlags(new EditField('switch')),
            (new BoolField('delivery_active', 'deliveryActive'))->addFlags(new EditField('switch')),
            (new BoolField('collect_active', 'collectActive'))->addFlags(new EditField('switch')),
            (new BoolField('is_limit', 'isLimit'))->addFlags(new EditField('switch')),
            (new BoolField('auto_location', 'autoLocation'))->addFlags(new EditField('switch')),

            (new StringField('executive_director', 'executiveDirector'))->addFlags(new EditField('text')),
            (new StringField('place_of_fulfillment', 'placeOfFulfillment'))->addFlags(new EditField('text')),
            (new StringField('place_of_jurisdiction', 'placeOfJurisdiction'))->addFlags(new EditField('text')),
            (new StringField('bank_bic', 'bankBic'))->addFlags(new EditField('text')),
            (new StringField('bank_iban', 'bankIban'))->addFlags(new EditField('text')),
            (new StringField('bank_name', 'bankName'))->addFlags(new EditField('text')),
            (new StringField('tax_office', 'taxOffice'))->addFlags(new EditField('text')),
            (new StringField('tax_number', 'taxNumber'))->addFlags(new EditField('text')),
            (new StringField('vat_id', 'vatId'))->addFlags(new EditField('text')),

            new LongTextField('description', 'description'),
            new JsonField('shop_categories', 'shopCategories'),

            (new StringField('name', 'name'))->addFlags(new EditField('text'), new Required()),
            (new StringField('time_zone', 'timeZone'))->addFlags(new EditField('text'), new Required()),
            (new StringField('first_name', 'firstName'))->addFlags(new EditField('text')),
            (new StringField('last_name', 'lastName'))->addFlags(new EditField('text')),
            (new StringField('street', 'street'))->addFlags(new EditField('text'), new Required()),
            (new StringField('street_number', 'streetNumber'))->addFlags(new EditField('text')),
            (new StringField('email', 'email'))->addFlags(new EditField('text'), new Required()),
            (new StringField('zipcode', 'zipcode'))->addFlags(new EditField('text'), new Required()),
            (new StringField('delivery_type', 'deliveryType'))->addFlags(new EditField('text'), new Required()),
            (new StringField('city', 'city'))->addFlags(new EditField('text'), new Required()),
            (new FloatField('location_lat', 'locationLat'))->addFlags(new EditField('number')),
            (new FloatField('location_lon', 'locationLon'))->addFlags(new EditField('number')),
            (new FloatField('max_radius', 'maxRadius'))->addFlags(new EditField('number')),
            (new StringField('phone_number', 'phoneNumber'))->addFlags(new EditField('text')),
            (new StringField('fax_number', 'faxNumber'))->addFlags(new EditField('text')),
            (new StringField('limit_rule', 'limitRule'))->addFlags(new EditField('select', ['options' => ['disabled', 'remove', 'reset']])),
            (new StringField('limit_interval', 'limitInterval'))->addFlags(new EditField('select', ['options' => array_keys(Defaults::getRestockIntervals())])),

            new JsonField('opening_hours', 'openingHours'),
            new JsonField('delivery_hours', 'deliveryHours'),

            new IntField('preparation_time', 'preparationTime'),
            new IntField('delivery_time', 'deliveryTime'),
            (new FloatField('delivery_price', 'deliveryPrice'))->addFlags(new EditField('number'), new Required()),
            (new FloatField('min_order_value', 'minOrderValue'))->addFlags(new EditField('number'), new Required()),
            new IntField('in_progress', 'inProgress'),
            new IntField('limit_amount', 'limitAmount'),

            (new DateTimeField('limited_at', 'limitedAt'))->addFlags(new EditField('text')),

            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('fileName')),
            (new ManyToOneAssociationField('category', 'category_id', CategoryDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('country', 'country_id', CountryDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            new OneToManyAssociationField('deliverers', DelivererDefinition::class, 'dewa_shop_id'),
            new OneToManyAssociationField('printers', PrinterDefinition::class, 'dewa_shop_id'),
            new OneToManyAssociationField('shopOrders', ShopOrderDefinition::class, 'dewa_shop_id'),
            new OneToManyAssociationField('shopAreas', ShopAreaDefinition::class, 'dewa_shop_id'),

            new ManyToManyAssociationField('products', ProductDefinition::class, ShopProductDefinition::class, 'dewa_shop_id', 'product_id'),
            new ManyToManyAssociationField('salesChannels', SalesChannelDefinition::class, ShopSalesChannelDefinition::class, 'dewa_shop_id', 'sales_channel_id')
        ]);
    }
}
