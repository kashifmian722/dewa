<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Exception;

use Shopware\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;

class DewaExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Exception $e, WriteCommand $command): ?\Exception
    {
        if (preg_match('/SQLSTATE\[23000\]:.*1062 Duplicate.*uniq.dewa.*/', $e->getMessage())) {
            $payload = $command->getPayload() ?: null;

            return new DewaDuplicateEntryException($payload, $e);
        }

        return null;
    }
}
