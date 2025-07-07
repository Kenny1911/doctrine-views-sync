<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

use function iterator_to_array as iterator_to_array_orig;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 *
 * @template T
 *
 * @param iterable<T> $iterator
 *
 * @return ($preserve_keys is true ? array<T> : list<T>)
 */
function iterator_to_array(iterable $iterator, bool $preserve_keys = true): array
{
    if ($iterator instanceof \Traversable) {
        return iterator_to_array_orig($iterator, $preserve_keys);
    }

    return $preserve_keys ? $iterator : array_values($iterator);
}
