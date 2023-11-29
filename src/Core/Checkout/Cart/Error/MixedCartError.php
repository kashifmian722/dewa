<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class MixedCartError extends Error
{
    private const KEY = 'dewa-mixed-cart';

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->message = 'Mixed Cart not possible';

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
