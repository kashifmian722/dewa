<?php declare(strict_types=1);

namespace Appflix\DewaShop\Storefront\Subscriber;

use Appflix\DewaShop\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cache\Event\HttpCacheGenerateKeyEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class KernelSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $systemConfigService;
    private RequestStack $requestStack;
    private RouterInterface $router;
    private EntityRepository $categoryRepository;

    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        SystemConfigService $systemConfigService,
        EntityRepository $categoryRepository
    )
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->systemConfigService = $systemConfigService;
        $this->categoryRepository = $categoryRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'redirectToMenu',
            HttpCacheGenerateKeyEvent::class => 'onHttpCacheGenerateKey',
        ];
    }

    public function onHttpCacheGenerateKey(HttpCacheGenerateKeyEvent $event): void
    {
        $request = $event->getRequest();
        $selected = $this->getActiveStoreByRequest($request);
        if (count($selected) === 0) {
            return;
        }

        $pathInfo = $request->getPathInfo();
        $paths = [
            $pathInfo === "/",
            $pathInfo === "/search",
        ];
        if (in_array(true, $paths)) {
            return;
        }

        if (!$selected || empty($selected['shopId'])) {
            return;
        }

        $hash = $event->getHash();
        $hash = hash('sha256', $hash . '-' . serialize($selected['shopId']));
        $event->setHash($hash);
    }

    public function redirectToMenu(RequestEvent $event): void
    {
        if (!$event->getRequest()->attributes->has(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST)) {
            return;
        }

        $salesChannelId = $event->getRequest()->attributes->get('sw-sales-channel-id');

        if (!$salesChannelId) {
            return;
        }

        if (!$this->systemConfigService->get('AppflixDewaShop.config.useMenuAsLandingPage', $salesChannelId)) {
            return;
        }

        $master = $this->requestStack->getMasterRequest();

        if (!in_array($master->attributes->get('_route'), [
            'frontend.home.page',
            'frontend.navigation.page'
        ])) {
            return;
        }

        $navigationId = $master->attributes->get('navigationId');

        if (!$navigationId) {
            $criteria = new Criteria();
            $criteria->setLimit(1);
            $criteria->addFilter(new EqualsFilter('cmsPageId', Defaults::CMS_PAGE_ID));
            $category = $this->categoryRepository->search($criteria, Context::createDefaultContext())->first();

            if (!$category) {
                return;
            }

            $redirectResponse = new RedirectResponse(
                $this->router->generate('frontend.navigation.page', ['navigationId' => $category->getId()])
            );
            $event->setResponse($redirectResponse);
        }
    }

    private function getActiveStoreByRequest(Request $request): array
    {
        $cookie = $request->cookies->get(Defaults::ACTIVE_STORE_COOKIE_NAME);

        try {
            $selected = $cookie ? json_decode($cookie, true) : [];
        } catch (\Exception $exception) {
            return [];
        }

        if (!empty($selected)) {
            return $selected;
        }

        return [];
    }
}
