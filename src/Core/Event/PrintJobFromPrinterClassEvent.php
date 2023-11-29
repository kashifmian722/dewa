<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Event;

use Appflix\DewaShop\Core\CloudPrnt\StarCloudPrntJob;
use Appflix\DewaShop\Core\Content\Shop\Aggregate\ShopOrder\ShopOrderEntity;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PrintJobFromPrinterClassEvent extends Event
{
    public const EVENT_NAME = 'appflix.dewa-shop.print-job-from-printer-class';

    private StarCloudPrntJob $printJob;
    private ShopOrderEntity $shopOrder;
    private Environment $twig;
    private TranslatorInterface $translator;
    private string $salesChannelId;
    private bool $custom;

    public function __construct(
        StarCloudPrntJob $printJob,
        ShopOrderEntity $shopOrder,
        Environment $twig,
        TranslatorInterface $translator
    )
    {
        $this->printJob = $printJob;
        $this->shopOrder = $shopOrder;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->salesChannelId = $shopOrder->getOrder()->getSalesChannelId();
        $this->custom = false;
    }

    /**
     * @return StarCloudPrntJob
     */
    public function getPrintJob(): StarCloudPrntJob
    {
        return $this->printJob;
    }

    /**
     * @return ShopOrderEntity
     */
    public function getShopOrder(): ShopOrderEntity
    {
        return $this->shopOrder;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @return bool
     */
    public function isCustom(): bool
    {
        return $this->custom;
    }

    /**
     * @param bool $custom
     */
    public function setCustom(bool $custom): void
    {
        $this->custom = $custom;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }
}
