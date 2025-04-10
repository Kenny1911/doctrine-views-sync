<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Console;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;
use Kenny1911\DoctrineViewsSync\Persistence\SingleConnectionRegistry;
use Kenny1911\DoctrineViewsSync\Psr\Container\MapContainer;
use Kenny1911\DoctrineViewsSync\ViewsProvider;
use Kenny1911\DoctrineViewsSync\ViewsSync;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync\Console
 */
abstract class BaseCommand extends Command
{
    final public function __construct(
        private readonly ConnectionRegistry $connectionRegistry,
        private readonly ContainerInterface $viewsProviderLocator,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    final public static function createByConnection(
        Connection $connection,
        ViewsProvider $viewsProvider,
        ?string $name = null,
    ): self {
        $connectionRegistry = new SingleConnectionRegistry($connection);

        return new static(
            connectionRegistry: $connectionRegistry,
            viewsProviderLocator: new MapContainer([$connectionRegistry->getDefaultConnectionName() => $viewsProvider]),
            name: $name,
        );
    }

    #[\Override]
    final protected function configure(): void
    {
        $this->addOption(
            name: 'conn',
            mode: InputOption::VALUE_REQUIRED,
            description: 'The name of the connection to use.',
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connectionName = $input->hasArgument('conn') ? (string) $input->getArgument('conn') : $this->connectionRegistry->getDefaultConnectionName();
        $connection = $this->connectionRegistry->getConnection($connectionName);

        if (false === $connection instanceof Connection) {
            throw new \LogicException(\sprintf('Invalid connection instance. Expected %s, got %s.', Connection::class, get_debug_type($connection)));
        }

        $viewsProvider = $this->viewsProviderLocator->get($connectionName);

        if (false === $viewsProvider instanceof ViewsProvider) {
            throw new \LogicException(\sprintf('Invalid views provider. Expected %s, got %s.', ViewsProvider::class, get_debug_type($viewsProvider)));
        }

        $viewsSync = new ViewsSync($connection, $viewsProvider);

        $this->doExecute($viewsSync);

        return self::SUCCESS;
    }

    abstract protected function doExecute(ViewsSync $viewsSync): void;
}
