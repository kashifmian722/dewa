<?php declare(strict_types=1);

namespace Appflix\DewaShop\Core\Service;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\LoggerFactory;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;

class SearchPortalService
{
    private LoggerInterface $logger;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private DewaShopService $dewaShopService;
    private SystemConfigService $systemConfigService;
    protected ClientInterface $client;

    public function __construct(
        LoggerFactory $loggerFactory,
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        DewaShopService $dewaShopService,
        SystemConfigService $systemConfigService
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->dewaShopService = $dewaShopService;
        $this->systemConfigService = $systemConfigService;

        $this->client = new Client(['timeout' => 200, 'allow_redirects' => false]);
        $this->logger = $loggerFactory->createRotating(
            'dewa_search_portal',
            7,
            Logger::DEBUG
        );
    }

    public function ping(): void
    {
        if (!$this->systemConfigService->get('AppflixDewaShop.config.searchPortalActive')) {
            //return;
        }

        $salesChannelRepository = $this->definitionInstanceRegistry->getRepository(
            SalesChannelDefinition::ENTITY_NAME
        );

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));
        $criteria->addAssociation('domains');

        $domainsCriteria = $criteria->getAssociation('domains');
        $domainsCriteria->addFilter(new EqualsFilter('languageId', Defaults::LANGUAGE_SYSTEM));
        $domainsCriteria->addFilter(new ContainsFilter('url', 'https'));

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search($criteria, Context::createDefaultContext())->first();
        if (!$salesChannel) {
            return;
        }
        /** @var SalesChannelDomainEntity $domain */
        $domain = $salesChannel->getDomains()->first();
        if (!$domain) {
            return;
        }

        $url = $this->systemConfigService->get('AppflixDewaShop.config.searchPortalUrl') ?: 'https://paderfood.com';

        try {
            $this->apiRequest('GET', $url . '/api/ping', null,
                [
                    'token' => $salesChannel->getAccessKey(),
                    'group' => $this->systemConfigService->get('AppflixDewaShop.config.searchPortalCategory'),
                    'domain' => $domain->getUrl(),
                ]
            );
        } catch (\Exception $exception) {
        }
    }

    private function apiRequest(string $method, ?string $endpoint = null, ?array $data = null, array $query = [])
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        $this->logger->info(sprintf("Ping: %s", $endpoint), $query);

        $httpBody = json_encode($data);

        $query = \guzzlehttp\psr7\build_query($query);

        $request = new Request(
            $method,
            $endpoint . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );

        $response = $this->client->send($request);

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode > 299) {
            throw new \Exception(
                sprintf('[%d] Error connecting to the API (%s)', $statusCode, $request->getUri()),
                $statusCode
            );
        }

        $contents = $response->getBody()->getContents();

        try {
            return json_decode($contents, true);
        } catch (\Exception $exception) {
            throw new \Exception(
                sprintf('[%d] Error decoding JSON: %s', $statusCode, $contents),
                $statusCode
            );
        }
    }
}
