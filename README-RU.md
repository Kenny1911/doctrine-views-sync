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

Тегом `doctrine.views_provider` должны быть помечены сервисы, реализующие интерфейс
`Kenny1911\DoctrineViewsSync\ViewsProvider`.

Пример имплементации `ViewsProvider`:

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

Для конфигурации схемы БД следует использовать метод `TableMetadataStorage::configureSchema()`.

## Разработка

По-умолчанию при разработке тесты запускаются с in-memory sqlite.

Если нужно запустить тесты с другими СУБД, при запуске тестов указывайте переменную окружения `DATABASE_URL`.

В файле `docker-compose.yml` есть уже подготовленные настройки для разных СУБД. Перед тестами необходимо запустить
сервера баз данных, а после прохождения тестов - выключить.

Примеры запуска тестов:

```bash
# Postgres
DATABASE_URL='pdo-pgsql://postgres:123@localhost:5432/views-sync' ./vendor/bin/phpunit
```

## Лицензия

MIT
