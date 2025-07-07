<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\ViewsProvider;

use Kenny1911\DoctrineViewsSync\Metadata\MetadataStorage;
use Kenny1911\DoctrineViewsSync\ViewsProvider;

/**
 * @api
 */
final class MetadataStorageViewsProvider implements ViewsProvider
{
    public function __construct(
        private readonly MetadataStorage $metadataStorage,
    ) {}

    #[\Override]
    public function getViews(): iterable
    {
        return $this->metadataStorage->loadViews();
    }
}
