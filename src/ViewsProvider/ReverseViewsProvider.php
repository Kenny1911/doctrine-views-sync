<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\ViewsProvider;

use Doctrine\DBAL\Schema\View;
use Kenny1911\DoctrineViewsSync\ViewsProvider;
use function Kenny1911\DoctrineViewsSync\iterator_to_array;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 */
final class ReverseViewsProvider implements ViewsProvider
{
    /** @var array<View>|null */
    private ?array $reversedViews = null;

    public function __construct(
        private readonly ViewsProvider $inner,
    ) {}

    public function getViews(): iterable
    {
        if (null !== $this->reversedViews) {
            return $this->reversedViews;
        }

        $this->reversedViews = $reversedViews = array_reverse(iterator_to_array($this->inner->getViews()));

        return $reversedViews;
    }
}
