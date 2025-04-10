<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;

/**
 * @internal
 * @psalm-internal Kenny1911\DoctrineViewsSync
 */
final class SingleConnectionRegistry implements ConnectionRegistry
{
    private const DEFAULT_CONNECTION_NAME = 'default';

    public function __construct(
        private readonly Connection $connection,
    ) {}

    #[\Override]
    public function getDefaultConnectionName(): string
    {
        return self::DEFAULT_CONNECTION_NAME;
    }

    #[\Override]
    public function getConnection(?string $name = null): object
    {
        if (null === $name || self::DEFAULT_CONNECTION_NAME === $name) {
            return $this->connection;
        }

        throw new \InvalidArgumentException(
            \sprintf('Doctrine Connection named "%s" does not exist.', $name),
        );
    }

    #[\Override]
    public function getConnections(): array
    {
        return [self::DEFAULT_CONNECTION_NAME => $this->connection];
    }

    #[\Override]
    public function getConnectionNames(): array
    {
        return [self::DEFAULT_CONNECTION_NAME => self::DEFAULT_CONNECTION_NAME];
    }
}
