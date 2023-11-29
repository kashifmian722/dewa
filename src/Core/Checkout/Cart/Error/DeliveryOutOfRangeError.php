<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class DeliveryOutOfRangeError extends Error
{
    private const KEY = 'dewa-delivery-out-of-range';

    private string $name;
    private string $detail;

    public function __construct(string $name, string $detail)
    {
        $this->name = $name;
        $this->detail = $detail;
        $this->message = 'Delivery out of Range. Please come back later';

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
            'detail' => $this->detail
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
