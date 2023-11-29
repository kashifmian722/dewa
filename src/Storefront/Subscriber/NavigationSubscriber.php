<?php

namespace Appflix\DewaShop\Storefront\Subscriber;

use Appflix\DewaShop\Core\Content\Badge\BadgeCollection;
use Appflix\DewaShop\Core\Defaults;
use Appflix\DewaShop\Core\Service\DewaShopService;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Suggest\SuggestPageLoadedEvent;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Shopware\Storefront\Pagelet\Menu\Offcanvas\MenuOffcanvasPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NavigationSubscriber implements EventSubscriberInterface
{
    private DewaShopService $dewaShopService;
    private SalesChannelRepositoryInterface $salesChannelCategoryRepository;
    private SalesChannelRepositoryInterface $salesChannelProductRepository;

    public function __construct(
        DewaShopService $dewaShopService,
        SalesChannelRepositoryInterface $salesChannelCategoryRepository,
        SalesChannelRepositoryInterface $salesChannelProductRepository
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->salesChannelCategoryRepository = $salesChannelCategoryRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NavigationPageLoadedEvent::class => 'onNavigationPageLoaded',
            MenuOffcanvasPageletLoadedEvent::class => 'onMenuOffcanvasPageletLoaded',
            HeaderPageletLoadedEvent::class => 'onHeaderPageletLoaded',
            SuggestPageLoadedEvent::class => 'onSuggestPageLoaded'
        ];
    }

    public function onSuggestPageLoaded(SuggestPageLoadedEvent $event): void
    {
        $request = $event->getRequest();

        $criteria = $this->dewaShopService->enrichIsDewaShopProductCriteria();
        $criteria->addAssociation('cover');
        $criteria->addAssociation('stocks.shop');
        $criteria->setTerm($request->query->get('search'));

        $products = $this->salesChannelProductRepository->search($criteria, $event->getSalesChannelContext())->getEntities();

        $event->getPage()->assign(['dewa_search_result' => $products]);
    }

    private function enrichNavigation($event): void
    {
        $navigation = $event->getPagelet()->getNavigation();
        if (!$navigation) {
            return;
        }

        $cmsPageId = Defaults::CMS_PAGE_ID;

        foreach ($navigation->getTree() as $item) {
            if ($item->getCategory()->getCmsPageId() === $cmsPageId) {
                $item->assign([Defaults::CMS_PAGE => true]);

                $criteria = new Criteria(array_keys($item->getChildren()));
                $productsCriteria = $criteria->getAssociation('products');
                $productsCriteria->setLimit(1);
                $this->dewaShopService->enrichDewaShopProductCriteria($productsCriteria);

                /** @var CategoryCollection $categories */
                $categories = $this->salesChannelCategoryRepository->search($criteria, $event->getSalesChannelContext())->getEntities();

                $item->setChildren(array_filter($item->getChildren(), function($k) use ($categories) {
                    return $categories->get($k)->getProducts()->count();
                }, ARRAY_FILTER_USE_KEY));
            }
        }
    }

    public function onMenuOffcanvasPageletLoaded(MenuOffcanvasPageletLoadedEvent $event): void
    {
        $this->enrichNavigation($event);
    }

    public function onHeaderPageletLoaded(HeaderPageletLoadedEvent $event): void
    {
        $this->enrichNavigation($event);
    }

    public function onNavigationPageLoaded(NavigationPageLoadedEvent $event): void
    {
        $page = $event->getPage();
        if (!$page) {
            return;
        }

        $cmsPage = $page->getCmsPage();
        if (!$cmsPage) {
            return;
        }

        if ($cmsPage->getEntity() !== Defaults::CMS_PAGE) {
            return;
        }

        $categoryId = $event->getRequest()->get('navigationId');
        if (!$categoryId) {
            return;
        }

        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();
        if (!$salesChannelId) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addAssociation('products.cover');
        $criteria->addAssociation('products.unit');
        $criteria->addAssociation('products.tags');
        $criteria->addAssociation('products.ingredients');
        $criteria->addAssociation('products.badges.svgIcon');
        $criteria->addAssociation('products.dewaOptions.items');
        $criteria->addAssociation('options.option.items');
        $criteria->addAssociation('media');

        $productsCriteria = $criteria->getAssociation('products');
        $this->dewaShopService->enrichDewaShopProductCriteria($productsCriteria);

        $optionsCriteria = $criteria->getAssociation('options');
        $optionsCriteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));

        $criteria->addFilter(new EqualsFilter('parentId', $categoryId));
        $criteria->addSorting(new FieldSorting('products.productNumber', FieldSorting::ASCENDING));

        /** @var $categories CategoryCollection */
        $categories = $this->salesChannelCategoryRepository->search($criteria, $event->getSalesChannelContext())->getEntities();
        foreach ($categories as $category) {
            if (!$category->getProducts()->count()) {
                $category->setActive(false);
            }
        }

        $categoryBadges = $this->getBadges($categories);

        $cmsPage->setCategories($categories->sortByPosition());
        $cmsPage->addExtension('badges', $categoryBadges);
    }

    private function getBadges(CategoryCollection $categories): BadgeCollection
    {
        $categoryBadges = new BadgeCollection();

        foreach ($categories as $category) {
            foreach ($category->getProducts() as $product) {
                /** @var BadgeCollection $badges */
                $badges = $product->getExtension('badges');
                if (!$badges) {
                    continue;
                }

                $categoryBadges->merge($badges);
            }
        }

        return $categoryBadges;
    }
}
