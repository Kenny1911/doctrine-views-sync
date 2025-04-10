<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\View;

/**
 * @api
 */
final class ViewsSync
{
    private AbstractSchemaManager $schemaManager;

    public function __construct(
        private readonly Connection $connection,
        private readonly ViewsProvider $viewsProvider,
    ) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaManager = $this->connection->createSchemaManager();
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
        $views = $this->getDatabaseViews();

        foreach ($views as $view) {
            $this->schemaManager->dropView($view->getQuotedName($this->connection->getDatabasePlatform()));
        }
    }

    /**
     * @throws Exception
     */
    private function doCreate(): void
    {
        foreach ($this->viewsProvider->getViews() as $view) {
            $this->schemaManager->createView($view);
        }
    }

    /**
     * @return list<View>
     *
     * @throws Exception
     */
    private function getDatabaseViews(): array
    {
        $views = $this->schemaManager->listViews();

        try {
            $schemaNames = $this->schemaManager->listSchemaNames();
        } catch (NotSupported) {
            $schemaNames = null;
        }

        // Filter views from non-system schema namespaces
        $views = array_filter($views, function (View $view) use ($schemaNames): bool {
            $namespace = $view->getNamespaceName();

            return null === $namespace
                || null === $schemaNames
                || \in_array($namespace, $this->schemaManager->listSchemaNames(), true);
        });

        return array_values($views);
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
