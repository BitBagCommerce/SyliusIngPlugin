<?php

declare(strict_types=1);

namespace BitBag\SyliusIngPlugin\Provider;

use BitBag\SyliusIngPlugin\Configuration\IngClientConfiguration;
use BitBag\SyliusIngPlugin\Configuration\IngClientConfigurationInterface;
use BitBag\SyliusIngPlugin\Exception\IngNotConfiguredException;
use BitBag\SyliusIngPlugin\Repository\PaymentMethodRepositoryInterface;
use BitBag\SyliusIngPlugin\Resolver\Configuration\ConfigurationResolverInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class IngClientConfigurationProvider implements IngClientConfigurationProviderInterface
{
    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    private ConfigurationResolverInterface $configurationResolver;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ConfigurationResolverInterface $configurationResolver
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->configurationResolver = $configurationResolver;
    }

    public function getPaymentMethodConfiguration(string $code): IngClientConfigurationInterface
    {
        $paymentMethod = $this->paymentMethodRepository->findOneForIngCode($code);

        if (null === $paymentMethod) {
            throw new IngNotConfiguredException(
                \sprintf('Payment method with code %s is not configured', $code)
            );
        }

        $config = $this->getGatewayConfig($paymentMethod)->getConfig();
        $resolved = $this->configurationResolver->resolve($config);

        return new IngClientConfiguration(
            $resolved['token'],
            $resolved['merchantId'],
            $resolved['sandboxUrl'],
            $resolved['prodUrl'],
            $resolved['isProd'],
            $resolved['serviceId'],
            $resolved['shopKey'],
        );
    }

    private function getGatewayConfig(PaymentMethodInterface $paymentMethod): GatewayConfigInterface
    {
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (null === $gatewayConfig) {
            throw new IngNotConfiguredException((string) $paymentMethod->getCode());
        }

        return $gatewayConfig;
    }
}
