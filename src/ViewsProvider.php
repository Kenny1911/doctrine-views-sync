<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

use Doctrine\DBAL\Schema\View;

/**
 * @api
 */
interface ViewsProvider
{
    /**
     * @return iterable<View>
     */
    public function getViews(): iterable;
}
