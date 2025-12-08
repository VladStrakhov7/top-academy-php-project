# top-academy-php-project
PHP project for Top Academy 2025"

## NodeJS Express server (SQLite)

1. Установите зависимости (если нужно): `npm install`
2. Инициализируйте базу: `npm run db:init` (создает файл `data/app.db` и таблицу `records`).
3. Запустите сервер: `npm start` (порт по умолчанию `3000`).

Доступные HTTP-эндпоинты:
- `POST /records` — создание записи, тело `{ "title": "...", "content": "..." }`.
- `GET /records` — список всех записей.
- `GET /records/:id` — получение записи по идентификатору.

SQL-скрипт для создания таблицы: `sql/init.sql`.

## Frontend (React / CRA)

- Каркас создан в папке `frontend` (`create-react-app`).
- Основной компонент: `frontend/src/App.jsx` + дочерние `Header`, `RecordForm`, `RecordList`.
- Для запуска фронтенда: `cd frontend && npm start` (ожидает backend на `http://localhost:3000` или задайте `REACT_APP_API_BASE`).
- Build: `cd frontend && npm run build`.