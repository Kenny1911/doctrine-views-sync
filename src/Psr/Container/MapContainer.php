<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Psr\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @api
 */
final class MapContainer implements ContainerInterface
{
    public function __construct(
        private readonly array $map,
    ) {}

    #[\Override]
    public function get(string $id): mixed
    {
        return $this->map[$id] ?? throw new class ("Item with {$id} not found.") extends \LogicException implements NotFoundExceptionInterface {};
    }

    #[\Override]
    public function has(string $id): bool
    {
        return isset($this->map[$id]);
    }
}
