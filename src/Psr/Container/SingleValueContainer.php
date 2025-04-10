<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Psr\Container;

use Psr\Container\ContainerInterface;

/**
 * @api
 */
final class SingleValueContainer implements ContainerInterface
{
    private MapContainer $inner;

    public function __construct(string $id, mixed $value)
    {
        $this->inner = new MapContainer([
            $id => $value,
        ]);
    }

    #[\Override]
    public function get(string $id): mixed
    {
        return $this->inner->get($id);
    }

    #[\Override]
    public function has(string $id): bool
    {
        return $this->inner->has($id);
    }
}
