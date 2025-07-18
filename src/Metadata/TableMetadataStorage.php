<?php

declare(strict_types=1);

namespace Kenny1911\DoctrineViewsSync\Metadata;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Types\Types;

/**
 * @api
 */
final class TableMetadataStorage implements MetadataStorage
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $table = 'doctrine_views_sync_metadata',
    ) {}

    public function configureSchema(Schema $schema): void
    {
        $table = $schema->createTable($this->table);
        $table->addColumn('view_name', Types::STRING);
        $table->addColumn('data', Types::TEXT)->setNotnull(true);
        $table->setPrimaryKey(['view_name']);
    }

    /**
     * @throws Exception
     */
    #[\Override]
    public function loadViews(): array
    {
        $sm = $this->connection->createSchemaManager();

        if (false === $sm->tablesExist([$this->table])) {
            return [];
        }

        /** @var list<View> $views */
        $views = [];

        /** @var array{view_name: string, data: string} $data */
        foreach ($this->connection->executeQuery("SELECT * FROM {$this->table}")->iterateAssociative() as $data) {
            try {
                $views[] = self::unserialize($data['data']);
            } catch (\Throwable) {
            }
        }

        return $views;
    }

    /**
     * @throws Exception
     */
    #[\Override]
    public function saveViews(array $views): void
    {
        $this->connection->executeStatement("DELETE FROM {$this->table}");

        foreach ($views as $view) {
            $this->connection->executeStatement(
                sql: "INSERT INTO {$this->table} VALUES (:viewName, :data)",
                params: [
                    'viewName' => $view->getName(),
                    'data' => self::serialize($view),
                ],
            );
        }
    }

    private static function serialize(View $view): string
    {
        return base64_encode(serialize($view));
    }

    private static function unserialize(string $data): View
    {
        $view = unserialize((string) base64_decode($data, true));

        if ($view instanceof View) {
            return $view;
        }

        throw new \RuntimeException('Can not unserialize view.');
    }
}
