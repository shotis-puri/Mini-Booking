# Mini Booking

Бронирование одной переговорной (ежедневно 09:00–18:00, часовые слоты).
- `backend/` — Laravel 13 + SQLite, 
- `client/` — Vue 3 + Vite + PrimeVue.

## Зависимости

- PHP 8.3+, Laravel 13 с `pdo_sqlite`, Composer 2
- Node.js 20.19+ или 22.12+, npm

PrimeVue зафиксирован на 4.5.5 (`@primeuix/themes` 2.0.3, `primeicons` 7.0.0): это последние MIT-версии, новые выпускаются по коммерческой лицензии с ключом.

## Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
php artisan serve # http://localhost:8000
```

Настройки в `backend/.env`:

- `DB_CONNECTION=sqlite` — база `database/database.sqlite`
- `APP_TIMEZONE=Asia/Tbilisi` — часовой пояс расписания
- `DB_BUSY_TIMEOUT`, `DB_JOURNAL_MODE` — настройки SQLite для параллельной записи (5000 и WAL)

Сидер создаёт слоты на 7 дней, считая сегодня, по 9 в день. Повторный запуск (`php artisan db:seed`) не создаёт дубликатов и не трогает существующие слоты и брони, в другой день добавляет недостающие.

Тесты (SQLite в памяти):

```bash
php artisan test
```

## Frontend

```bash
cd client
npm install
npm run dev # http://localhost:5173
```

В dev-режиме `/api` проксируется на `http://localhost:8000` (`VITE_PROXY_TARGET`), backend должен быть запущен. Если API на другом адресе, задайте `VITE_API_URL`.

## API

- `GET /api/slots` — слоты по возрастанию времени: `id`, `starts_at`, `ends_at`, `status` (`available` / `booked` / `past`), `booking_id`
- `POST /api/bookings` — `slot_id`, `customer_name`, `customer_email`; 201 `{id}`, 422 по полям, 409 для занятого или прошедшего слота
- `DELETE /api/bookings/{id}` — 204; 404, если брони нет; повторная отмена тоже 204

## Уникальность бронирования

В `bookings` есть колонка `active_slot_id` с `UNIQUE`-индексом: у активной брони она равна `slot_id`, при отмене становится `NULL` (и ставится `cancelled_at`). Вторая активная бронь на тот же слот не вставится: база отвергает её, backend отвечает 409. Поэтому при одновременных запросах проходит ровно один, а после отмены слот снова свободен. Приём работает одинаково в SQLite, MySQL и PostgreSQL.

## Время

Расписание считается в часовом поясе `APP_TIMEZONE` и хранится в нём же по умолчанию `APP_TIMEZONE=Asia/Tbilisi`. API отдаёт ISO 8601 со смещением (`2026-11-17T09:00:00+04:00`), фронтенд показывает время расписания, а не браузера. Слот с `starts_at` не позже текущего серверного времени считается прошедшим. После смены `APP_TIMEZONE` расписание нужно пересоздать: `php artisan migrate:fresh --seed`.
