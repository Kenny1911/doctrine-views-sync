# Doctrine Views Sync

\[ [English](./README.md) | Русский \]

**Doctrine Views Sync** — это небольшая утилита для управления представлениями (views) базы данных в проектах на
Doctrine. Позволяет синхронизировать или удалить все представления через консольные команды.

## Установка

Установите пакет через Composer:

```bash
composer require kenny1911/doctrine-views-sync
```

## Использование

Доступны две консольные команды:

### Удаление всех представлений

```bash
php bin/console doctrine:views:drop
```

Удаляет все представления в базе данных, определённые в вашем проекте.

### Синхронизация представлений

```bash
php bin/console doctrine:views:sync
```

Синхронизирует все представления — пересоздаёт их на основе текущего состояния кода.

## Требования

- PHP >= 8.1
- Doctrine DBAL
- Symfony Console (если используется вне Symfony — возможны дополнительные настройки)

## Конфигурация

### Symfony Framework

Пример конфигурации Symfony с использованием одного подключения Doctrine.

Базовая конфигурация и регистрация команд:

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

Тегом `doctrine.views_provider` должны быть помечены сервисы, реализующие интерфейс
`Kenny1911\DoctrineViewsSync\ViewsProvider`.

Сервис `doctrine.views_provider.locator` - это psr контейнер, где в качестве ключа используется название подключения, а
в качестве значения `ViewsProvider`.

Если подключение одно, то можно использовать `Kenny1911\DoctrineViewsSync\Psr\Container\SingleValueContainer`.
В случае, если используется несколько подключений, можно использовать
`Kenny1911\DoctrineViewsSync\Psr\Container\MapContainer` или
[Symfony Tagged Locator](https://symfony.com/doc/current/service_container/service_subscribers_locators.html#defining-a-service-locator).

```php
use Doctrine\DBAL\Schema\View;

final readonly class UsersViewsProvider implements \Kenny1911\DoctrineViewsSync\ViewsProvider
{
    public function getViews() : iterable
    {
        yield new View(
            name: 'users_credentials',
            sql: 'SELECT id, username, password FROM users',
        )
    }
}
```

```yaml
services:
  UsersViewsProvider:
    tags:
      - 'doctrine.views_provider'
```

## Лицензия

MIT
