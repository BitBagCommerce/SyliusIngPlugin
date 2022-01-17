<?php

declare(strict_types=1);

namespace BitBag\SyliusIngPlugin\Client;

use BitBag\SyliusIngPlugin\Model\TransactionModelInterface;
use Psr\Http\Message\ResponseInterface;

interface IngApiClientInterface
{
    public const POST_METHOD = 'POST';

    public function createRequest(
        TransactionModelInterface $createTransactionModel,
        string $action
    ): ?ResponseInterface;
}
