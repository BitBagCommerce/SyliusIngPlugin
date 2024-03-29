<?php

declare(strict_types=1);

namespace BitBag\SyliusIngPlugin\Factory\Model;

use BitBag\SyliusIngPlugin\Configuration\IngClientConfigurationInterface;
use BitBag\SyliusIngPlugin\Model\TransactionModelInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface TransactionModelFactoryInterface
{
    public const REDIRECT_URL = 'bitbag_ing_redirect';

    public const REDIRECT_ONECLICK_URL = 'bitbag_ing_one_click_redirect';

    public const SALE_TYPE = 'sale';

    public function create(
        OrderInterface $order,
        IngClientConfigurationInterface $ingClientConfiguration,
        string $type,
        string $paymentMethod,
        string $paymentMethodCode,
        string $serviceId
    ): TransactionModelInterface;
}
