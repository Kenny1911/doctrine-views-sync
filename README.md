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

  doctrine.views_provider.locator:
    class: Kenny1911\DoctrineViewsSync\Psr\Container\SingleValueContainer
    arguments:
      - default
      - '@doctrine.views_provider'

  Kenny1911\DoctrineViewsSync\Console\ViewsDropCommand:
    arguments:
      - '@doctrine'
      - '@doctrine.views_provider.locator'
    autoconfigure: true

  Kenny1911\DoctrineViewsSync\Console\ViewsSyncCommand:
    arguments:
      - '@doctrine'
      - '@doctrine.views_provider.locator'
    autoconfigure: true
```

Services implementing the `Kenny1911\DoctrineViewsSync\ViewsProvider` interface must be tagged with `doctrine.views_provider`.

The `doctrine.views_provider.locator` service is a PSR container where the connection name is used as the key, and the corresponding `ViewsProvider` as the value.

If you only use a single connection, you can use the `Kenny1911\DoctrineViewsSync\Psr\Container\SingleValueContainer`.  
For multiple connections, consider using `Kenny1911\DoctrineViewsSync\Psr\Container\MapContainer`  
or the [Symfony Tagged Locator](https://symfony.com/doc/current/service_container/service_subscribers_locators.html#defining-a-service-locator).

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

## License

MIT
