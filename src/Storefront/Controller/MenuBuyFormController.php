<?php

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\Content\Bundle\BundleCollection;
use Appflix\DewaShop\Core\Content\Bundle\BundleEntity;
use Appflix\DewaShop\Core\Service\DewaShopService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class MenuBuyFormController extends StorefrontController
{
    private DewaShopService $dewaShopService;
    private CartService $cartService;
    private SalesChannelRepositoryInterface $salesChannelProductRepository;

    public function __construct(
        DewaShopService $dewaShopService,
        CartService $cartService,
        SalesChannelRepositoryInterface $salesChannelProductRepository
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->cartService = $cartService;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
    }

    /**
     * @Route("/dewa-shop/menu-buy-form/{productId}", name="dewa-shop.menu-buy-form", methods={"GET","POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function menuBuyForm(string $productId, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $product = $this->getProduct($productId, $salesChannelContext, true);

        return $this->renderStorefront('dewa-shop/menu/menu-buy-form-modal.html.twig', [
            'product' => $product,
            'modal' => [
                'centered' => true,
                'size' => 'lg'
            ]
        ]);
    }

    /**
     * @Route("/dewa-shop/menu-ingredients/{entityName}/{id}", name="dewa-shop.menu-ingredients", methods={"GET","POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function menuIngredients(string $entityName, string $id, SalesChannelContext $salesChannelContext): Response
    {
        $this->dewaShopService->setSalesChannelContext($salesChannelContext);
        $entity = $this->dewaShopService->getEntityById($entityName, $id, ['ingredients']);

        $body = $this->renderView('dewa-shop/menu/menu-ingredients.html.twig', ['entity' => $entity]);

        return $this->renderStorefront('dewa-shop/component/modal.html.twig', [
            'modal' => [
                'title' => sprintf("%s - %s", $entity->getTranslated()['name'], $this->trans('dewa-shop.menu.ingredients')),
                'centered' => true,
                'size' => 'md',
                'body' => $body
            ]
        ]);
    }

    /**
     * @Route("/dewa-shop/add-to-cart", name="dewa-shop.add-to-cart", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function addToCart(Cart $cart, RequestDataBag $requestDataBag, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        /** @var RequestDataBag|null $lineItems */
        $lineItems = $requestDataBag->get('lineItems');
        if (!$lineItems) {
            throw new MissingRequestParameterException('lineItems');
        }

        $count = 0;

        try {
            $items = [];
            /** @var RequestDataBag $lineItemData */
            foreach ($lineItems as $lineItemData) {
                $product = $this->getProduct($lineItemData->getAlnum('id'), $salesChannelContext, false);

                $lineItem = $this->dewaShopService->calculateProductConfigurator($product, $lineItemData->get('configurator'));

                /*$lineItem = new LineItem(
                    $lineItemData->getAlnum('id'),
                    $lineItemData->getAlnum('type'),
                    $lineItemData->get('referencedId'),
                    $lineItemData->getInt('quantity', 1)
                );

                $lineItem->setStackable($lineItemData->getBoolean('stackable', true));
                $lineItem->setRemovable($lineItemData->getBoolean('removable', true));*/

                $count += $lineItem->getQuantity();

                $items[] = $lineItem;
            }

            $cart = $this->cartService->add($cart, $items, $salesChannelContext);

            if (!$this->traceErrors($cart)) {
                $this->addFlash(self::SUCCESS, $this->trans('checkout.addToCartSuccess', ['%count%' => $count]));
            }
        } catch (ProductNotFoundException $exception) {
            $this->addFlash(self::DANGER, $this->trans('error.addToCartError'));
        }

        return $this->createActionResponse($request);
    }

    /**
     * @deprecated Will be removed
     */
    public function __addToCart(Cart $cart, RequestDataBag $requestDataBag, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        try {
            $product = $this->getProduct($requestDataBag->get('productId'), $salesChannelContext);

            $lineItem = $this->dewaShopService->calculateProductConfigurator($product, $requestDataBag->get('configurator'));

            $cart = $this->cartService->add($cart, $lineItem, $salesChannelContext);

            if (!$this->traceErrors($cart)) {
                $this->addFlash(self::SUCCESS, $this->trans('checkout.addToCartSuccess', ['%count%' => 1]));
            }
        } catch (ProductNotFoundException $exception) {
            $this->addFlash(self::DANGER, $this->trans('error.addToCartError'));
        }

        return $this->createActionResponse($request);
    }

    private function getProduct(string $productId, SalesChannelContext $salesChannelContext, $bundleFeature = false): ?SalesChannelProductEntity
    {
        $productIds = [$productId];

        /**
         * DANGER ZONE - STILL IN DEVELOPMENT
         */
        if ($bundleFeature) {
            $criteria = new Criteria($productIds);
            $criteria->addAssociation('bundles');
            $criteria->setLimit(count($productIds));

            /** @var SalesChannelProductEntity $product */
            $product = $this->salesChannelProductRepository->search($criteria, $salesChannelContext)->get($productId);

            /** @var BundleCollection $bundles */
            $bundles = $product->getExtension('bundles');

            if ($bundles->count()) {
                $productIds = array_merge(
                    $productIds,
                    $bundles->getAccessoryProductIds()
                );

                if ($bundles->getAccessoryStreamIds()) {
                    $criteria = new Criteria();
                    $criteria->addFilter(new EqualsAnyFilter('streamIds', $bundles->getAccessoryStreamIds()));

                    $productIds = array_merge(
                        $productIds,
                        $this->salesChannelProductRepository->searchIds($criteria, $salesChannelContext)->getIds()
                    );
                }
            }
        }

        $criteria = new Criteria($productIds);
        $criteria->addAssociation('dewaOptions.option.items');
        $criteria->addAssociation('dewaOptions.option.unit');
        $criteria->addAssociation('categories.options.option.items');
        $criteria->addAssociation('categories.options.option.unit');
        $criteria->addAssociation('bundles.accessoryStream');
        $criteria->setLimit(count($productIds));

        $optionsCriteria = $criteria->getAssociation('dewaOptions');
        $optionsCriteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));
        $optionItemCriteria = $optionsCriteria->getAssociation('option.items');
        $optionItemCriteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));

        $categoriesOptionsCriteria = $criteria->getAssociation('categories.options');
        $categoriesOptionsCriteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));
        $categoriesOptionItemCriteria = $categoriesOptionsCriteria->getAssociation('option.items');
        $categoriesOptionItemCriteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));

        $bundleCriteria = $criteria->getAssociation('bundles');
        $bundleCriteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));

        $products = $this->salesChannelProductRepository->search($criteria, $salesChannelContext)->getEntities();

        foreach ($products as $product) {
            $this->dewaShopService->enrichProductConfigurator($product);
        }

        $mainProduct = $products->get($productId);

        /**
         * DANGER ZONE - STILL IN DEVELOPMENT
         */
        if ($bundleFeature) {
            /** @var BundleCollection $bundles */
            $bundles = $mainProduct->getExtension('bundles');
            if ($bundles->count()) {
                foreach ($bundles as $bundle) {
                    if ($bundle->getAccessoryProductId()) {
                        $bundle->setAccessoryProduct($products->get($bundle->getAccessoryProductId()));
                    } elseif ($accessoryStreamId = $bundle->getAccessoryStreamId()) {
                        $bundle->setAccessoryProducts(
                            $products->filter(function (ProductEntity $product) use ($accessoryStreamId) {
                                return in_array($accessoryStreamId, $product->getStreamIds());
                            })
                        );
                    }
                }
            }
        }

        return $mainProduct;
    }

    private function traceErrors(Cart $cart): bool
    {
        if ($cart->getErrors()->count() <= 0) {
            return false;
        }

        $cart->getErrors()->clear();

        return true;
    }
}
