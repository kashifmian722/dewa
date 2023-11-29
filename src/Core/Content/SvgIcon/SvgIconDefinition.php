<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\SvgIcon;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class SvgIconDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'dewa_svg_icon';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return SvgIconCollection::class;
    }

    public function getEntityClass(): string
    {
        return SvgIconEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'locked' => false,
            'fileName' => "custom.svg"
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new LongTextField('content', 'content'))->addFlags(new Required(), new AllowHtml()),
            (new StringField('file_name', 'fileName'))->addFlags(new Required()),
            (new BoolField('locked', 'locked'))->addFlags(new Required()),
        ]);
    }
}
