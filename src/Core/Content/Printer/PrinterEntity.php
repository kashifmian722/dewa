<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Content\Printer;

use Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintJob\PrintJobCollection;
use Appflix\DewaShop\Core\Content\Printer\Aggregate\PrintTurnover\PrintTurnoverCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PrinterEntity extends Entity
{
    use EntityIdTrait;

    public bool $active = true;

    public string $name = "";

    public string $template = "";

    public string $mac = "";

    public string $status = "";

    public int $dotWidth = 0;

    public string $printerType = "";

    public string $printerVersion = "";

    /**
     * @var PrintJobCollection|null
     */
    protected $printJobs;

    /**
     * @var PrintTurnoverCollection|null
     */
    protected $printTurnovers;

    /**
     * @return PrintTurnoverCollection|null
     */
    public function getPrintTurnovers(): ?PrintTurnoverCollection
    {
        return $this->printTurnovers;
    }

    /**
     * @param PrintTurnoverCollection|null $printTurnovers
     */
    public function setPrintTurnovers(?PrintTurnoverCollection $printTurnovers): void
    {
        $this->printTurnovers = $printTurnovers;
    }

    public function getActive() : bool
    {
        return $this->active;
    }

    public function isActive() : bool
    {
        return $this->getActive();
    }

    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getTemplate() : string
    {
        return $this->template;
    }

    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    public function getMac() : string
    {
        return $this->mac;
    }

    public function setMac(string $mac)
    {
        $this->mac = $mac;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getDotWidth() : int
    {
        return $this->dotWidth;
    }

    public function setDotWidth(int $dotWidth)
    {
        $this->dotWidth = $dotWidth;
    }

    public function getPrinterType() : string
    {
        return $this->printerType;
    }

    public function setPrinterType(string $printerType)
    {
        $this->printerType = $printerType;
    }

    public function getPrinterVersion() : string
    {
        return $this->printerVersion;
    }

    public function setPrinterVersion(string $printerVersion)
    {
        $this->printerVersion = $printerVersion;
    }

    public function getPrintJobs() : ?PrintJobCollection
    {
        return $this->printJobs;
    }

    public function setPrintJobs(?PrintJobCollection $printJobs)
    {
        $this->printJobs = $printJobs;
    }


}