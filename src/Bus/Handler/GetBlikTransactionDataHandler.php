<?php

declare(strict_types=1);

namespace BitBag\SyliusIngPlugin\Bus\Handler;

use BitBag\SyliusIngPlugin\Bus\Query\GetBlikTransactionData;
use BitBag\SyliusIngPlugin\Entity\IngTransactionInterface;
use BitBag\SyliusIngPlugin\Exception\InvalidIngResponseException;
use BitBag\SyliusIngPlugin\Factory\Model\TransactionBlikModelFactoryInterface;
use BitBag\SyliusIngPlugin\Factory\Transaction\IngTransactionFactoryInterface;
use BitBag\SyliusIngPlugin\Provider\IngClientConfigurationProviderInterface;
use BitBag\SyliusIngPlugin\Provider\IngClientProviderInterface;
use BitBag\SyliusIngPlugin\Resolver\TransactionData\TransactionDataResolverInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class GetBlikTransactionDataHandler implements MessageHandlerInterface
{
    private IngClientConfigurationProviderInterface $configurationProvider;

    private TransactionBlikModelFactoryInterface $transactionBlikModelFactory;

    private IngClientProviderInterface $ingClientProvider;

    private IngTransactionFactoryInterface $ingTransactionFactory;

    private TransactionDataResolverInterface $transactionDataResolver;

    public function __construct(
        IngClientConfigurationProviderInterface $configurationProvider,
        TransactionBlikModelFactoryInterface $transactionBlikModelFactory,
        IngClientProviderInterface $ingClientProvider,
        IngTransactionFactoryInterface $ingTransactionFactory,
        TransactionDataResolverInterface $transactionDataResolver
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->transactionBlikModelFactory = $transactionBlikModelFactory;
        $this->ingClientProvider = $ingClientProvider;
        $this->ingTransactionFactory = $ingTransactionFactory;
        $this->transactionDataResolver = $transactionDataResolver;
    }

    public function __invoke(GetBlikTransactionData $query): IngTransactionInterface
    {
        $code = $query->getCode();
        $config = $this->configurationProvider->getPaymentMethodConfiguration($code);

        $transactionModel = $this->transactionBlikModelFactory->create(
            $query->getOrder(),
            $config,
            $this->transactionBlikModelFactory::SALE_TYPE,
            $query->getPaymentMethod(),
            $query->getPaymentMethodCode(),
            $config->getServiceId(),
            $query->getBlikModel()
        );

        $response = $this->ingClientProvider
            ->getClient($code)
            ->createTransaction($transactionModel)
        ;

        $data = $this->transactionDataResolver->resolve($response);

        $paymentUrl = $data['paymentUrl'];
        $transactionId = $data['transactionId'];
        $serviceId = $data['serviceId'];
        $orderId = $data['orderId'];

        if (!$paymentUrl || !$transactionId || !$serviceId || !$orderId) {
            throw new InvalidIngResponseException('No configured transaction');
        }

        return $this->ingTransactionFactory->create(
            $query->getOrder()->getLastPayment(),
            $transactionId,
            $paymentUrl,
            $serviceId,
            $orderId,
            $query->getCode()
        );
    }
}
