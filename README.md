# REST API for Price Monitoring & Analytics

> 💼 **Portfolio Project** / **Проект для портфолио**  
> This repository showcases clean code, design patterns, and automated testing in a production-ready environment.  
> Данный проект создан для демонстрации навыков чистой архитектуры, применения паттернов и автоматического тестирования.

---

## English Version

A lightweight Headless REST API built with **Laravel 11** and **Docker** designed for automated scraping, historical price tracking, and marketplace analytics.

### 🎛️ Architecture
The parser core is designed for seamless scalability, allowing new stores to be added without breaking existing code:
* 👩‍💻 **`ParserStrategyInterface`** — Defines a strict contract for all marketplace crawlers.
* 🕹 **`PriceParserManager`** — A central dispatcher that analyzes the product URL and dynamically triggers the correct strategy by matching the domain string.
* ⚙️ **Parsed Platforms:**
  * `21vek` (`TOVStrategy`) — Performs live HTML scraping using regular expressions.
  * `Wildberries` (`WBStrategy`) — Simulates price fluctuations via a dynamic mathematical trend loop based on database history.

### ⏱️ Business Logic Features
* **Anti-Spam Rate Limiter:** Strategies check the `updated_at` timestamp using `isToday()`. If a product has already been parsed today, subsequent requests are safely frozen (`status: skipped`).
* **Artisan Automation:** The built-in console command `php artisan prices:parse`, which is configured to run automatically through the scheduler every day at `00:30` at night.

### 🐳 Quick Docker Setup
To run a project on a computer, you only need a running Docker Desktop:

1. **Navigate to the project root directory:**
   ```bash
   cd laravel
   ```

2. **Start the containers (Web server will boot automatically):**
   ```bash
   docker-compose up -d
   ```
3. **Run migrations and populate seed data (loads historical test prices):**
   ```bash
   docker-compose exec app php artisan migrate:fresh --seed
   ```
4. **Start the built-in Laravel network server:**
   ```bash
   docker-compose exec -d app php artisan serve --host=0.0.0.0 --port=8000

The API is instantly live at: `http://localhost:8080/api/products`

### 📊 Instant Postman Testing
Simply import the **`postman_collection.json`** file from the root folder directly into your Postman app (`File -> Import`). It contains 3 pre-configured requests to port `8080`: Fetching cached data, launching the scrapers, and viewing advanced product analytics (discount trends, market best deals, and graph charts).

---

## Русская Версия

Простой и расширяемый Headless REST API сервис на **Laravel 11** и **Docker** для парсинга, трекинга истории цен и вывода аналитики по товарам на маркетплейсах.

### 🎛️ Логика (Strategy)
Парсер обеспечивает плавную масштабируемость, позволяя добавлять новые магазины, не нарушая существующий код:
* 👩‍💻 **`ParserStrategyInterface`** — задает общий стандарт для всех парсеров.
* 🕹 **`PriceParserManager`** — диспетчер, который анализирует URL товара и выбирает нужный парсер (по совпадению домена в ссылке).
* ⚙️ **Парсеры:**
  * `TOVStrategy` (21vek) — парсит живой HTML-код сайта через регулярные выражения.
  * `WBStrategy` (Wildberries) — симулирует изменение цен по математической формуле на основе истории БД.

### ⏱️ Особенности бизнес-логики
* **Защита от спама:** Стратегии проверяют дату обновления через `isToday()`. Если товар сегодня уже обновлялся, повторный запрос блокируется (`status: skipped`).
* **Автоматика:** Встроена консольная команда `php artisan prices:parse`, которая настроена на автоматический запуск через планировщик каждый день в `00:30` ночи.

### 🐳 Быстрый запуск
Для запуска проекта на компьютере необходим только запущенный Docker Desktop:

1. **Перейдите в корневую папку проекта:**
   ```bash
   cd laravel
   ```

2. **Поднимите контейнеры (веб-сервер включится сам):**
   ```bash
   docker-compose up -d
   ```
3. **Накатите базу данных и тестовые данные (сиды):**
   ```bash
   docker-compose exec app php artisan migrate:fresh --seed
   ```
4. **Включите веб-сервер:**
   ```bash
   docker-compose exec -d app php artisan serve --host=0.0.0.0 --port=8000
   ```
Проект доступен по адресу: `http://localhost:8080/api/products`

### 📊 Тестирование в Postman
Вам не нужно вбивать запросы руками. Импортируйте готовый файл **`postman_collection.json`** из корня проекта в свой Postman (`File -> Import`), и у вас появятся три настроенных запроса на порт `8080` (вывод кэша цен, запуск обновления и детальная аналитика товара с графиком).