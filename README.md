# BilliardCRM Client

Клиентская часть публичного бронирования клубов BilliardCRM. Проект работает на Laravel 11, PHP 8.2, Vue 3 и Vite.

## Локальное окружение

Проект запускается в общем Docker-окружении из директории:

```text
/Users/aleksandrmajlo/sites/ruks/Billiard
```

Основные адреса:

- клиент: <http://localhost:8083>
- УТЦ: <http://localhost:8083/utc>
- CRM API: <http://localhost:8000>
- Adminer: <http://localhost:8080>
- Mailpit: <http://localhost:8026>

## Первый запуск

```bash
cd /Users/aleksandrmajlo/sites/ruks/Billiard
./billiard-local.sh setup
```

Команда устанавливает зависимости, запускает контейнеры и импортирует дампы только в пустые локальные базы. Laravel-миграции не запускаются.

## Ежедневная работа

```bash
cd /Users/aleksandrmajlo/sites/ruks/Billiard

./billiard-local.sh up
./billiard-local.sh status
./billiard-local.sh check
./billiard-local.sh down
```

Логи клиента:

```bash
./billiard-local.sh logs client
```

Очистка Laravel-кеша:

```bash
./billiard-local.sh artisan client optimize:clear
```

## Frontend

После изменения Vue, JavaScript, SCSS или статических ресурсов выполните:

```bash
cd /Users/aleksandrmajlo/sites/ruks/Billiard/c.bb-crm.com
npm ci
npm run build
```

Собранные файлы `public/build` создаются Vite и не добавляются в Git.

## Проверка

```bash
cd /Users/aleksandrmajlo/sites/ruks/Billiard
./billiard-local.sh artisan client test

cd /Users/aleksandrmajlo/sites/ruks/Billiard/c.bb-crm.com
git diff --check
```

Ручная проверка УТЦ:

1. Открыть <http://localhost:8083/utc>.
2. Проверить ссылку «Схема клубу» и popup с изображением.
3. Выбрать площадку, время и тип занятия.
4. Локально войти с кодом `111111`: SMS-провайдер в `APP_ENV=local` не вызывается.
5. На экране заказа нажать «Назад» → «Створити нову бронь».
6. Выбрать ещё одно занятие. Повторный ввод телефона и SMS-кода не должен появляться.

## Базы данных

- База УТЦ: `billiards_14`.
- Локальные базы загружаются из `/Users/aleksandrmajlo/sites/ruks/Billiard/dumps`.
- Миграции для этого окружения запрещены.
- `./billiard-local.sh db-import` импортирует только пустые базы.
- `./billiard-local.sh db-refresh --yes` полностью заменяет локальные базы данными из дампов. Использовать только осознанно.

## Правила разработки

- Сохранять поведение существующих клубов и маршрутов.
- Функциональность только для УТЦ обязательно ограничивать проверкой `route === 'utc'`.
- Не добавлять в Git `.env`, дампы баз, созданные PDF, `public/build`, `vendor`, `node_modules` и служебные файлы macOS `._*`.
- Не хранить ключи API, пароли и production-доступы в коде или README.
- Не запускать миграции локально или на production без отдельного согласования.
- Перед коммитом проверять `git status`, `git diff` и явно добавлять только нужные файлы. Не использовать `git add .`.
- Не использовать `git push --force` для `main`.

Очистка служебных macOS-файлов перед коммитом:

```bash
cd /Users/aleksandrmajlo/sites/ruks/Billiard/c.bb-crm.com
dot_clean -m .
find . -type f -name '._*' -print
```

После `dot_clean` команда `find` не должна ничего выводить. Шаблоны `.DS_Store` и `._*` уже добавлены в `.gitignore`.

## Git: отправка в `main`

```bash
cd /Users/aleksandrmajlo/sites/ruks/Billiard/c.bb-crm.com

git branch --show-current
git status --short
git add <перечень-нужных-файлов>
git diff --cached --check
git --no-pager diff --cached --stat
git commit -m "Описание изменения"
git fetch origin
git rebase --autostash origin/main
git push origin main
```

Если возник конфликт rebase, не применять `reset --hard` и не выполнять force-push. Сначала сохранить вывод ошибки и разобраться с конфликтом.

## Production checklist

1. Сделать резервную копию файлов и базы.
2. Проверить текущую ветку и чистоту рабочей директории.
3. Получить изменения из `main` без force-операций.
4. Установить зависимости из lock-файлов.
5. Собрать frontend командой `npm run build`.
6. Очистить Laravel-кеши командой `php artisan optimize:clear`.
7. Не запускать миграции.
8. Проверить `/utc`, авторизацию, повторное бронирование и оплату.
