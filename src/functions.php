<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 *
 * @template T
 *
 * @return ($preserve_keys is true ? array<T> : list<T>)
 */
function iterator_to_array(iterable $iterator, bool $preserve_keys = true): array
{
    if ($iterator instanceof \Traversable) {
        return iterator_to_array($iterator, $preserve_keys);
    }

    if (\is_array($iterator)) {
        return $preserve_keys ? $iterator : array_values($iterator);
    }

    throw new \InvalidArgumentException('Invalid type of parameter $iterator.');
}
