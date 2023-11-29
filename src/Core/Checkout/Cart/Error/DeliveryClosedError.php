<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class DeliveryClosedError extends Error
{
    private const KEY = 'dewa-delivery-closed';

    private string $name;
    private string $shop;
    private string $desiredTime;

    public function __construct(string $name, string $shop, string $desiredTime)
    {
        $this->name = $name;
        $this->shop = $shop;
        $this->desiredTime = $desiredTime;
        $this->message = 'Closed.';

        parent::__construct($this->message);
    }

    public function isPersistent(): bool
    {
        return false;
    }

    public function getParameters(): array
    {
        return [
            'name' => $this->name,
            'shop' => $this->shop,
            'desiredTime' => $this->desiredTime,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function blockOrder(): bool
    {
        return true;
    }

    public function getId(): string
    {
        return sprintf('%s-%s', self::KEY, $this->name);
    }

    public function getLevel(): int
    {
        return self::LEVEL_NOTICE;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }
}
