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
    /** @psalm-var Params */
    private readonly array $params;

    private readonly ?string $dbname;

    /**
     * @psalm-param Params $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->dbname = $params['dbname'] ?? null;
    }

    public static function fromDsn(string $dsn): self
    {
        $dsnParser = new DsnParser();
        $params = $dsnParser->parse($dsn);

        return new self($params);
    }

    /**
     * @throws Exception
     */
    public function init(Schema $schema): Connection
    {
        $this->dropDatabase();

        $connection = DriverManager::getConnection($this->params);
        $connection->createSchemaManager()->migrateSchema($schema);

        return $connection;
    }

    /**
     * @throws Exception
     */
    private function dropDatabase(): void
    {
        if (null === $this->dbname) {
            return;
        }

        $params = $this->params;
        unset($params['dbname']);
        $connection = DriverManager::getConnection($params);
        $sm = $connection->createSchemaManager();

        $shouldDropDatabase = \in_array($this->dbname, $sm->listDatabases(), true);
        $dbname = $connection->getDatabasePlatform()->quoteSingleIdentifier($this->dbname); // quoted dbname


        if ($shouldDropDatabase) {
            $sm->dropDatabase($dbname);
        }

        $sm->createDatabase($dbname);

        $connection->close();
    }
}
