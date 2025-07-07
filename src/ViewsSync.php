<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kenny1911\DoctrineViewsSync\Metadata\MetadataStorage;
use Kenny1911\DoctrineViewsSync\ViewsProvider\MetadataStorageViewsProvider;
use Kenny1911\DoctrineViewsSync\ViewsProvider\UniqueViewsProvider;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class ViewsSync
{
    private readonly AbstractSchemaManager $schemaManager;

    private readonly ViewsProvider $viewsProvider;

    private readonly ViewsProvider $reverseViewsProvider;

    /**
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        private readonly Connection $connection,
        ViewsProvider $viewsProvider,
        private readonly MetadataStorage $metadataStorage,
        private readonly OutputInterface $output = new NullOutput(),
    ) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaManager = $this->connection->createSchemaManager();
        $this->viewsProvider = new UniqueViewsProvider($viewsProvider);
        $this->reverseViewsProvider = new MetadataStorageViewsProvider($this->metadataStorage);
    }

    public function drop(): void
    {
        $this->transactional(fn() => $this->doDrop());
    }

    public function create(): void
    {
        $this->transactional(fn() => $this->doCreate());
    }

    public function sync(): void
    {
        $this->transactional(function (): void {
            $this->doDrop();
            $this->doCreate();
        });
    }

    /**
     * @throws Exception
     */
    private function doDrop(): void
    {
        $views = $this->reverseViewsProvider->getViews();

        foreach ($views as $view) {
            $this->schemaManager->dropView($view->getQuotedName($this->connection->getDatabasePlatform()));
            $this->output->writeln(\sprintf('Drop view "%s".', $view->getName()));
        }

        $this->metadataStorage->saveViews([]);
    }

    /**
     * @throws Exception
     */
    private function doCreate(): void
    {
        $views = iterator_to_array($this->viewsProvider->getViews(), false);

        foreach ($views as $view) {
            $this->schemaManager->createView($view);
            $this->output->writeln(\sprintf('Create view "%s".', $view->getName()));
        }

        $this->metadataStorage->saveViews($views);
    }

    /**
     * @param callable():void $callback
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function transactional(callable $callback): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->connection->transactional(static fn() => $callback());
    }
}
