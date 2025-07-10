<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\ViewsProvider;

use Kenny1911\DoctrineViewsSync\ViewsProvider;
use function Kenny1911\DoctrineViewsSync\iterator_to_array;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 */
final class ReverseViewsProvider implements ViewsProvider
{
    public function __construct(
        private readonly ViewsProvider $inner,
    ) {}

    #[\Override]
    public function getViews(): iterable
    {
        return array_reverse(iterator_to_array($this->inner->getViews(), false));
    }
}
