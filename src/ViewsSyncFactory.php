<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;
use Kenny1911\DoctrineViewsSync\Metadata\MetadataStorage;
use Kenny1911\DoctrineViewsSync\Persistence\SingleConnectionRegistry;
use Kenny1911\DoctrineViewsSync\Psr\Container\MapContainer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class ViewsSyncFactory
{
    /** @var array<string, ViewsSync> */
    private array $viewsSync = [];

    public function __construct(
        private readonly ConnectionRegistry $connectionRegistry,
        private readonly ContainerInterface $viewsProviderLocator,
        private readonly ContainerInterface $metadataStorageLocator,
    ) {}

    public static function fromSingleConnection(
        Connection $connection,
        ViewsProvider $viewsProvider,
        MetadataStorage $metadataStorage,
    ): self {
        $connectionRegistry = new SingleConnectionRegistry($connection);

        return new self(
            connectionRegistry: $connectionRegistry,
            viewsProviderLocator: new MapContainer([$connectionRegistry->getDefaultConnectionName() => $viewsProvider]),
            metadataStorageLocator: new MapContainer([$connectionRegistry->getDefaultConnectionName() => $metadataStorage]),
        );
    }

    public function create(?string $connectionName = null, OutputInterface $output = new NullOutput()): ViewsSync
    {
        $connection = $this->connectionRegistry->getConnection($connectionName);

        if (isset($this->viewsSync[$connectionName])) {
            return $this->viewsSync[$connectionName];
        }

        $connectionName ??= $this->connectionRegistry->getDefaultConnectionName();

        if (false === $connection instanceof Connection) {
            throw new \LogicException(\sprintf('Invalid connection instance. Expected %s, got %s.', Connection::class, get_debug_type($connection)));
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $viewsProvider = $this->viewsProviderLocator->get($connectionName);

        if (false === $viewsProvider instanceof ViewsProvider) {
            throw new \LogicException(\sprintf('Invalid views provider. Expected %s, got %s.', ViewsProvider::class, get_debug_type($viewsProvider)));
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $metadataStorage = $this->metadataStorageLocator->get($connectionName);

        if (false === $metadataStorage instanceof MetadataStorage) {
            throw new \LogicException(\sprintf('Invalid metadata storage. Expected %s, got %s.', MetadataStorage::class, get_debug_type($viewsProvider)));
        }

        return $this->viewsSync[$connectionName] = new ViewsSync(
            connection: $connection,
            viewsProvider: $viewsProvider,
            metadataStorage: $metadataStorage,
            output: $output,
        );
    }
}
