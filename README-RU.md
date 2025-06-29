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
parameters:
  doctrine.views.ignored:
    - 'ignored_view'
    - '*_ignored_view'
    - '/^[\w\d]_ignored_view$/'
    
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
      - '%doctrine.views.ignored%'
    autoconfigure: true

  Kenny1911\DoctrineViewsSync\Console\ViewsSyncCommand:
    arguments:
      - '@doctrine'
      - '@doctrine.views_provider.locator'
      - '%doctrine.views.ignored%'
    autoconfigure: true
```

Тегом `doctrine.views_provider` должны быть помечены сервисы, реализующие интерфейс
`Kenny1911\DoctrineViewsSync\ViewsProvider`.

Сервис `doctrine.views_provider.locator` - это psr контейнер, где в качестве ключа используется название подключения, а
в качестве значения `ViewsProvider`.

В параметре `doctrine.views.ignored` указывается список представлений БД, которые следует игнорировать. Поддерживаются
обычные названия представлений, glob шаблоны и регулярные выражения.

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
