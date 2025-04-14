<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Types\Type;
use Kenny1911\DoctrineViewsSync\ViewsProvider\CallableViewsProvider;
use Kenny1911\DoctrineViewsSync\ViewsSync;
use PHPUnit\Framework\TestCase;

/**
 * @api
 */
final class ViewsSyncTest extends TestCase
{
    private const USERS = [
        ['id' => '019635ee-5a98-7cad-b6bf-1d5c16d9fc84', 'username' => 'foo', 'password' => '123', 'enabled' => true],
        ['id' => '019635ef-0492-7359-bc47-7cd3b2b5ace3', 'username' => 'bar', 'password' => '321', 'enabled' => false],
        ['id' => '019635ef-4255-7c68-ae23-e6db1ebbd451', 'username' => 'baz', 'password' => '456', 'enabled' => true],
        ['id' => '019635ef-7827-7c26-b739-6d204f5f68fc', 'username' => 'qux', 'password' => '654', 'enabled' => false],
    ];

    private Connection $connection;

    /**
     * @throws Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        $this->connection
            ->createSchemaManager()
            ->createTable(
                (new Table(
                    name: 'users',
                    columns: [
                        (new Column('id', Type::getType('guid')))->setNotnull(true),
                        (new Column('username', Type::getType('string')))->setNotnull(true),
                        (new Column('password', Type::getType('string')))->setNotnull(true),
                        (new Column('enabled', Type::getType('boolean')))->setNotnull(true),
                    ],
                ))->setPrimaryKey(['id']),
            );

        foreach (self::USERS as $user) {
            $this->connection->executeQuery(
                sql: 'INSERT INTO users VALUES (:id, :username, :password, :enabled)',
                params: $user,
                types: ['id' => 'guid', 'enabled' => 'boolean'],
            );
        }
    }

    /**
     * @throws Exception
     */
    public function test(): void
    {
        $sync = new ViewsSync(
            connection: $this->connection,
            viewsProvider: new CallableViewsProvider(static fn() => yield new View(
                name: 'users_enabled',
                sql: 'SELECT id, username FROM users WHERE enabled = 1',
            )),
        );

        // Check Create view
        $sync->create();

        $result = $this->connection
            ->executeQuery('SELECT * FROM users_enabled')
            ->fetchAllAssociative();

        self::assertCount(1, $this->connection->createSchemaManager()->listViews());
        self::assertSame('users_enabled', $this->connection->createSchemaManager()->listViews()[0]->getName());
        self::assertSame(
            expected: [
                ['id' => '019635ee-5a98-7cad-b6bf-1d5c16d9fc84', 'username' => 'foo'],
                ['id' => '019635ef-4255-7c68-ae23-e6db1ebbd451', 'username' => 'baz'],
            ],
            actual: $result,
        );

        // Check Drop view
        $sync->drop();

        self::assertCount(0, $this->connection->createSchemaManager()->listViews());

        // Check Create Again
        $sync->create();

        self::assertCount(1, $this->connection->createSchemaManager()->listViews());
        self::assertSame('users_enabled', $this->connection->createSchemaManager()->listViews()[0]->getName());

        unset($sync, $result);

        // Check sync view
        $sync2 = new ViewsSync(
            connection: $this->connection,
            viewsProvider: new CallableViewsProvider(static fn() => yield new View(
                name: 'users_enabled',
                sql: 'SELECT username FROM users WHERE enabled = 1',
            )),
        );
        $sync2->sync();

        $result2 = $this->connection
            ->executeQuery('SELECT * FROM users_enabled')
            ->fetchAllAssociative();

        self::assertCount(1, $this->connection->createSchemaManager()->listViews());
        self::assertSame('users_enabled', $this->connection->createSchemaManager()->listViews()[0]->getName());
        self::assertSame(
            expected: [
                ['username' => 'foo'],
                ['username' => 'baz'],
            ],
            actual: $result2,
        );
    }
}
