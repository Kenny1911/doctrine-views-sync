<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Tools\DsnParser;

/**
 * @api
 *
 * @psalm-import-type Params from DriverManager
 */
final class DbManager
{
    public function __construct(
        public readonly Connection $connection,
        public readonly Schema $schema,
    ) {}

    /**
     * @psalm-param Params $params
     */
    public static function fromParams(array $params): self
    {
        return new self(
            DriverManager::getConnection($params),
            new Schema(),
        );
    }

    public static function fromDsn(string $dsn): self
    {
        $dsnParser = new DsnParser();
        $params = $dsnParser->parse($dsn);

        return self::fromParams($params);
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $this->dropDatabase();

        $this->connection->createSchemaManager()->migrateSchema($this->schema);
    }

    /**
     * @throws Exception
     */
    private function dropDatabase(): void
    {
        $this->connection->close();

        /** @psalm-suppress InternalMethod */
        $params = $this->connection->getParams();
        $dbname = $params['dbname'] ?? null;
        unset($params['dbname']);

        if (false === \is_string($dbname)) {
            return;
        }

        $connection = DriverManager::getConnection($params);
        $sm = $connection->createSchemaManager();

        $shouldDropDatabase = \in_array($dbname, $sm->listDatabases(), true);
        $quotedDbname = $connection->getDatabasePlatform()->quoteSingleIdentifier($dbname); // quoted dbname


        if ($shouldDropDatabase) {
            $sm->dropDatabase($quotedDbname);
        }

        $sm->createDatabase($quotedDbname);

        $connection->close();
    }
}
