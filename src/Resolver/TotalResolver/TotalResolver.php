<?php

declare(strict_types=1);

namespace BitBag\SyliusIngPlugin\Resolver\TotalResolver;

use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

final class TotalResolver implements TotalResolverInterface
{
    private CartContextInterface $cartContext;

    private RequestStack $requestStack;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        CartContextInterface $cartContext,
        RequestStack $requestStack,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->cartContext = $cartContext;
        $this->requestStack = $requestStack;
        $this->orderRepository = $orderRepository;
    }

    public function resolve(): int
    {
        $cart = $this->cartContext->getCart();

        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $orderId = $session->get('sylius_order_id');

        if ('' !== $orderId && $cart->getId() !== null) {
            return $cart->getTotal();
        } elseif (null !== $orderId) {
            $order = $this->orderRepository->find($orderId);
            return $order->getTotal();
        }

        return 0;
    }
}