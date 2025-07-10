<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Metadata;

use Doctrine\DBAL\Schema\View;

/**
 * @api
 */
interface MetadataStorage
{
    /**
     * @return list<View>
     */
    public function loadViews(): array;

    /**
     * @param list<View> $views
     */
    public function saveViews(array $views): void;
}
