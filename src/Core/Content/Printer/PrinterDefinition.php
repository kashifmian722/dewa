<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer;

use Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintJob\PrintJobDefinition;
use Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover\PrintTurnoverDefinition;
use Appflix\DewaShop\Core\Content\Shop\ShopDefinition;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PrinterDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_printer';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PrinterCollection::class;
    }

    public function getEntityClass(): string
    {
        return PrinterEntity::class;
    }

    public function getDefaults(): array
    {
        $template = <<<TWIG
[feed: length 30mm]

{% set shop = shopOrder.shop %}
{% set order = shopOrder.order %}
{% set delivery = order.deliveries.first() %}
{% set address = delivery.shippingOrderAddress %}

[align: center]

[image: url http://dewa.dev.flinkfactory.com/media/c1/46/25/1619389755/demo-logo_black.jpg; width 60%; min-width 48mm]

{{ shop.name }}
{{ shop.street }} {{ shop.streetNumber }}
{{ shop.zipcode }} {{ shop.city }}
{% if shop.phoneNumber %}{{ shop.phoneNumber }}{% endif %}

{{ "dewa-shop.slip.orderNumber" | trans }}: {{ order.orderNumber }}
{{ order.orderDateTime | date("d.m.Y H:i") }}

[bold: on]

{{ delivery.shippingMethod.name }}
{{ shopOrder.desired_time | date("d.m.Y H:i") }}

[bold: off]

{{ address.firstName }} {{ address.lastName }}
{{ address.street }}
{{ address.zipcode }} {{ address.city }}
{% if address.phoneNumber %}{{ address.phoneNumber }}{% endif %}

[align: left]

{% for item in order.lineItems %}
    [column: left: {{ item.quantity }}x {{ item.label }} {% if item.payload.productNumber %}({{ item.payload.productNumber }}){% endif %}; right: {{ item.price.totalPrice|currency }}]
    {% if item.payload.dewa %}
        {% for dewa in item.payload.dewa %}
            {{ dewa.name}}:
            {% if dewa.value is iterable %}
                {{ dewa.value|join(", ") }}
            {% else %}
                {{ dewa.value }}
            {% endif %}
        {% endfor %}
    {% endif %}
{% endfor %}

[column: left: {{ "dewa-shop.slip.deliveryCosts" | trans }}:; right: {{ delivery.shippingCosts.totalPrice|currency }}]

[bold: on]

[column: left: Total:; right: {{order.price.totalPrice|currency}}]

[bold: off]

{% for percentage,tax in order.price.calculatedTaxes %}\n[column: left: {{ percentage }}% MwSt.; right: {{ tax.tax|currency }}]\n{% endfor %}\n[align: center]\n[bold: on]

{% if order.transactions.last.stateMachineState.technicalName == "paid" %}
    {{ "dewa-shop.slip.paid" | trans }}
{% else %}
    {{ "dewa-shop.slip.notPaid" | trans }}
{% endif %}

[bold: off]

{{ "dewa-shop.slip.thisIsNoInvoice" | trans }}

[cut]
TWIG;

        return [
            'template' => $template
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class)),
            (new FkField('dewa_shop_id', 'shopId', ShopDefinition::class)),
            (new BoolField('active', 'active'))->addFlags(new EditField('switch')),
            (new StringField('name', 'name'))->addFlags(new EditField('text'), new Required()),
            (new StringField('mac', 'mac'))->addFlags(new EditField('text'), new Required()),
            (new LongTextField('template', 'template'))->addFlags(new EditField('code', ['tooltip'=>'printerTemplate'])),
            (new DateTimeField('last_polled', 'lastPolled')),
            (new StringField('status', 'status')),
            (new IntField('dot_width', 'dotWidth')),
            (new StringField('printer_type', 'printerType')),
            (new StringField('printer_version', 'printerVersion')),
            (new CreatedAtField()),
            (new UpdatedAtField()),

            (new ManyToOneAssociationField('shop', 'dewa_shop_id', ShopDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id'),
            (new OneToManyAssociationField('printJobs', PrintJobDefinition::class, 'dewa_printer_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('printTurnovers', PrintTurnoverDefinition::class, 'dewa_printer_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
