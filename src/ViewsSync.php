<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\View;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class ViewsSync
{
    private readonly AbstractSchemaManager $schemaManager;

    /** @var list<View> */
    private readonly array $views;

    /**
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        private readonly Connection $connection,
        ViewsProvider $viewsProvider,
        private readonly OutputInterface $output = new NullOutput(),
    ) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaManager = $this->connection->createSchemaManager();

        $viewsIter = $viewsProvider->getViews();
        $this->views = array_values($viewsIter instanceof \Traversable ? iterator_to_array($viewsIter) : $viewsIter);
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
        $views = array_reverse($this->views);

        foreach ($views as $view) {
            $this->schemaManager->dropView($view->getQuotedName($this->connection->getDatabasePlatform()));
            $this->output->writeln(\sprintf('Drop view "%s".', $view->getName()));
        }
    }

    /**
     * @throws Exception
     */
    private function doCreate(): void
    {
        foreach ($this->views as $view) {
            $this->schemaManager->createView($view);
            $this->output->writeln(\sprintf('Create view "%s".', $view->getName()));
        }
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
