<?php

namespace Appflix\DewaShop\Storefront\Controller;

use Appflix\DewaShop\Core\Service\DewaShopService;
use PackiroLogin\Event\InitialPasswordEvent;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\Account\Login\AccountLoginPageLoader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class AccountController extends StorefrontController
{
    private DewaShopService $dewaShopService;
    private AccountLoginPageLoader $loginPageLoader;
    private EntityRepository $customerRepository;

    public function __construct(
        DewaShopService $dewaShopService,
        AccountLoginPageLoader $loginPageLoader,
        EntityRepository $customerRepository
    )
    {
        $this->dewaShopService = $dewaShopService;
        $this->loginPageLoader = $loginPageLoader;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @Route("/dewa-shop/account-login", name="dewa-shop.account-login", methods={"GET","POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function accountLogin(RequestDataBag $requestDataBag, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $page = $this->loginPageLoader->load($request, $salesChannelContext);

        $redirect = $request->query->get('redirectTo', 'frontend.account.home.page');

        $body = $this->renderView('dewa-shop/account/login.html.twig', [
            'activeRoute' => $request->get('activeRoute'),
            'data' => $requestDataBag,
            'redirectTo' => $redirect,
            'redirectParameters' => json_encode($request->get('redirectParameters', [])),
            'page' => $page
        ]);

        return $this->renderStorefront('dewa-shop/component/modal.html.twig', [
            'modal' => [
                'title' => sprintf('<span class="fi-rs-portrait"></span>%s', $this->trans('account.myAccount')),
                'size' => 'xl',
                'body' => $body,
                'centered' => true
            ]
        ]);
    }

    /**
     * @Route("/dewa-shop/account-check-mail", name="dewa-shop.account-check-mail", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function accountCheckMail(Request $request, SalesChannelContext $salesChannelContext): JsonResponse
    {
        $email = $request->request->get('email');
        $customers = $this->getCustomersByEmail($email, $salesChannelContext, false);

        if ($customers->count() > 0) {
            /* @var $customer CustomerEntity */
            $customer = $customers->first();

            $response = [
                'headline' => $this->trans('dewa-shop.account.loginMessage', ['%firstname%' => $customer->getFirstname()]),
                'step' => 'accountLogin'
            ];
        } else {
            $response = [
                'message' => $this->renderView('@Storefront/storefront/utilities/alert.html.twig', [
                    'type' => 'info',
                    'content' => $this->trans('dewa-shop.account.registerMessage', ['%email%' => $email]),
                ]),
                'headline' => $this->trans('dewa-shop.account.register'),
                'step' => 'accountRegister'
            ];
        }

        return new JsonResponse($response);
    }

    private function getCustomersByEmail(string $email, SalesChannelContext $context, bool $includeGuests = true): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customer.email', $email));
        if (!$includeGuests) {
            $criteria->addFilter(new EqualsFilter('customer.guest', 0));
        }

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('customer.boundSalesChannelId', null),
            new EqualsFilter('customer.boundSalesChannelId', $context->getSalesChannel()->getId()),
        ]));

        return $this->customerRepository->search($criteria, $context->getContext());
    }
}
