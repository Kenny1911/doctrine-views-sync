<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\ViewsProvider;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 */
final class DuplicateView extends \RuntimeException
{
    public static function create(string $viewName): self
    {
        return new self(\sprintf('Duplicate view "%s"', $viewName));
    }
}
