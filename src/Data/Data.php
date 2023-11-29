<?php declare(strict_types=1);

namespace Appflix\DewaShop\Data;

use Appflix\DewaShop\Core\System\DewaShopDataExtension;
use MoorlFoundation\Core\System\DataInterface;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Connection;

class Data extends DewaShopDataExtension implements DataInterface
{
    private Connection $connection;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    public function __construct(
        Connection $connection,
        DefinitionInstanceRegistry $definitionInstanceRegistry
    )
    {
        $this->connection = $connection;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
    }

    public function getName(): string
    {
        return 'data';
    }

    public function getType(): string
    {
        return 'data';
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    public function process(): void
    {
        $this->addSvgIcons();
    }

    public function getPreInstallQueries(): array
    {
        return [
            "UPDATE `cms_page` SET `locked` = '0' WHERE `id` = UNHEX('{CMS_PAGE_ID}');"
        ];
    }

    public function getInstallQueries(): array
    {
        return [
            "UPDATE `cms_page` SET `locked` = '1' WHERE `id` = UNHEX('{CMS_PAGE_ID}');"
        ];
    }

    private function addSvgIcons(): void
    {
        $filePaths = glob(__DIR__ . '/media/svg/*.svg');

        foreach ($filePaths as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            $content = file_get_contents($filePath);
            $fileName = basename($filePath);

            try {
                $this->connection->insert(
                    'dewa_svg_icon',
                    [
                        'id' => Uuid::fromHexToBytes(md5($fileName)),
                        'file_name' => $fileName,
                        'locked' => true,
                        'content' => $content,
                        'created_at' => $this->getCreatedAt(),
                    ]
                );
            } catch (\Exception $exception) {}
        }
    }
}
