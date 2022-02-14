<?php

declare(strict_types=1);

namespace BitBag\SyliusIngPlugin\Factory\Model;

use BitBag\SyliusIngPlugin\Configuration\IngClientConfigurationInterface;
use BitBag\SyliusIngPlugin\Factory\Request\RedirectFactoryInterface;
use BitBag\SyliusIngPlugin\Model\Blik\BlikModelInterface;
use BitBag\SyliusIngPlugin\Model\TransactionBlikModel;
use BitBag\SyliusIngPlugin\Model\TransactionModelInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class TransactionBlikModelFactory implements TransactionBlikModelFactoryInterface
{
    private CustomerModelFactoryInterface $customerFactory;

    private BillingModelFactoryInterface $billingFactory;

    private ShippingModelFactoryInterface $shippingFactory;

    private RedirectFactoryInterface $redirectModelFactory;

    public function __construct(
        CustomerModelFactoryInterface $customerFactory,
        BillingModelFactoryInterface $billingFactory,
        ShippingModelFactoryInterface $shippingFactory,
        RedirectFactoryInterface $redirectModelFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->billingFactory = $billingFactory;
        $this->shippingFactory = $shippingFactory;
        $this->redirectModelFactory = $redirectModelFactory;
    }

    public function create(
        OrderInterface $order,
        IngClientConfigurationInterface $ingClientConfiguration,
        string $type,
        string $paymentMethod,
        string $paymentMethodCode,
        string $serviceId,
        BlikModelInterface $blikModel
    ): TransactionModelInterface {
        $redirectModel = $this->redirectModelFactory->create();
        $amount = $order->getTotal();
        $currency = $order->getCurrencyCode();
        $orderId = (string) $order->getId();
        $title = (string) $order->getId();
        $successReturnUrl = $redirectModel->getSuccessUrl();
        $failureReturnUrl = $redirectModel->getFailureUrl();
        $customer = $this->customerFactory->create($order);
        $billing = $this->billingFactory->create($order);
        $shipping = $this->shippingFactory->create($order);
        $blikCode = $blikModel->getBlikCode();
        $clientIp = $blikModel->getClientIp();

        return new TransactionBlikModel(
            $type,
            $serviceId,
            $amount,
            $currency,
            $title,
            $orderId,
            $paymentMethod,
            $paymentMethodCode,
            $successReturnUrl,
            $failureReturnUrl,
            $clientIp,
            $blikCode,
            $customer,
            $billing,
            $shipping
        );
    }
}