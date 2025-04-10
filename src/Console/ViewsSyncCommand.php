<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Console;

use Kenny1911\DoctrineViewsSync\ViewsSync;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @api
 */
#[AsCommand(name: 'doctrine:views:sync', description: 'Sync all database views.')]
final class ViewsSyncCommand extends BaseCommand
{
    #[\Override]
    protected function doExecute(ViewsSync $viewsSync): void
    {
        $viewsSync->sync();
    }
}
