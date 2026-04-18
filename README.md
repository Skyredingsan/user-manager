Консольное приложение для управления списком пользователей с хранением данных в JSON файле.

## Требования

- PHP >= 8.0
- Composer

## Установка

```bash
git clone https://github.com/Skyredingsan/user-manager.git
cd user-manager
composer install
```

# Использование

## Показать всех пользователей
php index.php list

## Добавить нового пользователя (данные генерируются автоматически)
php index.php add

## Удалить пользователя по ID
php index.php delete 5