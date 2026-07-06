# Что посмотреть

Бэкенд API для онлайн-кинотеатра: каталог фильмов с фильтрацией, списком «К просмотру», ролями пользователей
и загрузкой данных о фильмах из OMDb API в фоновых задачах.

## Требования

- Docker + Docker Compose
- Composer

## Установка и запуск

**1. Клонировать репозиторий**

**2. Создать файл окружения и заполнить ключ OMDb**

```bash
cp .env.example .env
# в .env указать OMDB_API_KEY=ваш_ключ
```

**3. Установить зависимости и запустить контейнеры**

```bash
composer install
./vendor/bin/sail up -d
```

**4. Сгенерировать ключ приложения**

```bash
./vendor/bin/sail artisan key:generate
```

**5. Запустить миграции**

```bash
./vendor/bin/sail artisan migrate
```

## Основные команды

```bash
# Запустить контейнеры
./vendor/bin/sail up -d

# Остановить контейнеры
./vendor/bin/sail down

# Запустить миграции заново
./vendor/bin/sail artisan migrate:fresh

# Тесты и статический анализ
./vendor/bin/sail artisan test
./vendor/bin/sail php ./vendor/bin/phpcs
./vendor/bin/sail php ./vendor/bin/psalm
```

## Фоновые задачи

Добавление фильма (`POST /api/films`) создает запись со статусом `pending`
и ставит в очередь задачу загрузки данных из OMDb. Задачу обрабатывает
отдельный контейнер `queue` (запускается автоматически вместе с `sail up`).
Интенсивность обращений к OMDb ограничена (`OMDB_RATE_LIMIT` запросов
в минуту, по умолчанию 10).


