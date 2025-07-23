# Doctrine Views Sync

\[ English | [Русский](./README-RU.md) \]

**Doctrine Views Sync** is a lightweight utility for managing database views in Doctrine-based projects. It allows you
to **synchronize** or **drop** all views via console commands.

## Installation

Install the package via Composer:

```bash
composer require kenny1911/doctrine-views-sync
```

## Usage

Two console commands are available:

### Drop all views

```bash
php bin/console doctrine:views:drop
```

Drops all views in the database that are defined in your project.

### Synchronize views

```bash
php bin/console doctrine:views:sync
```

Synchronizes all views — recreates them based on the current application state.

## Requirements

- PHP >= 8.1
- Doctrine DBAL
- Symfony Console (if used outside of Symfony, additional setup may be required)

## Configuration

### Symfony Framework

Example Symfony configuration using a single Doctrine connection.

Basic service registration and command wiring:

```yaml
services:
  doctrine.views_provider:
    class: Kenny1911\DoctrineViewsSync\ViewsProvider\ChainViewsProvider
    arguments:
      - !tagged_iterator 'doctrine.views_provider'

  doctrine.views_sync.metadata_storage:
    class: Kenny1911\DoctrineViewsSync\Metadata\TableMetadataStorage
    arguments:
      - '@doctrine.dbal.default_connection'

  doctrine.views_sync.factory:
    class: Kenny1911\DoctrineViewsSync\ViewsSyncFactory
    factory: [null, 'fromSingleConnection']
    arguments:
      - '@doctrine.dbal.default_connection'
      - '@doctrine.views_provider'
      - '@doctrine.views_sync.metadata_storage'

  Kenny1911\DoctrineViewsSync\Console\ViewsDropCommand:
    arguments:
      - '@doctrine.views_sync.factory'
    autoconfigure: true

  Kenny1911\DoctrineViewsSync\Console\ViewsSyncCommand:
    arguments:
      - '@doctrine.views_sync.factory'
    autoconfigure: true
```

Services implementing the `Kenny1911\DoctrineViewsSync\ViewsProvider` interface must be tagged with `doctrine.views_provider`.

Example `ViewsProvider` implementation:

```php
use Doctrine\DBAL\Schema\View;

final readonly class UsersViewsProvider implements \Kenny1911\DoctrineViewsSync\ViewsProvider
{
    public function getViews() : iterable
    {
        yield new View(
            name: 'users_credentials',
            sql: 'SELECT id, username, password FROM users',
        );
    }
}
```

```yaml
services:
  UsersViewsProvider:
    tags:
      - 'doctrine.views_provider'
```

To configure the database schema, use the `TableMetadataStorage::configureSchema()` method.

## Development

Tests will run with in-memory sqlite db by default.

If you want to run tests with other dbms, set environment variable `DATABASE_URL`.

File `docker-compose.yml` contains prepared settings for different dbms. You must run database servers before run tests
and down after tests was passed.

Running tests example:

```bash
# Postgres
DATABASE_URL='pdo-pgsql://postgres:123@localhost:5432/views-sync' ./vendor/bin/phpunit
```

## License

MIT
