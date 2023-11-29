<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class ShopClosedError extends Error
{
    private const KEY = 'dewa-shop-closed';
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->message = 'Shop closed. Please come back later';

        parent::__construct($this->message);
    }

    public function isPersistent(): bool
    {
        return false;
    }

    public function getParameters(): array
    {
        return ['name' => $this->name];
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
