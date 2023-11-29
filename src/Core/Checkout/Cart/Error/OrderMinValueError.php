<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class OrderMinValueError extends Error
{
    private const KEY = 'dewa-order-min-value';

    private string $name;
    private string $orderValueLeft;
    private string $minOrderValue;

    public function __construct(string $name, string $minOrderValue, string $orderValueLeft)
    {
        $this->name = $name;
        $this->orderValueLeft = $orderValueLeft;
        $this->minOrderValue = $minOrderValue;
        $this->message = 'Min Order Value not reached';

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
            'orderValueLeft' => $this->orderValueLeft,
            'minOrderValue' => $this->minOrderValue,
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
