<?php

declare(strict_types=1);

namespace BitBag\SyliusIngPlugin\Factory\Serializer;

use Symfony\Component\Serializer\Serializer;

interface SerializerFactoryInterface
{
    public function createSerializerWithNormalizer(): Serializer;
}
