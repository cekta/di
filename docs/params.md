# Params (Параметры)

**По умолчанию:** `[]`

## Назначение

Параметры позволяют задавать конкретные значения для аргументов зависимостей:
- Встроенные типы (`string`, `int`, `array` и т.д.)
- Конкретные экземпляры объектов
- Значения по умолчанию

## 📌 Основной синтаксис
```
{Имя аргумента без $} => {значение аргумента}
```

## 📋 Пример использования

[Пример на GitHub](https://github.com/cekta/di-example-usage/commit/6fb30371083ffb38c8940adf5b948ce07ff3b5c0)

### Конфигурация:

```php

class Example
{
    public function __construct(private string $username, private string $password){}
}

new \Cekta\DI\Compiler(
    containers: [Example::class],
    params: [
        'username' => 'my default username',
        'password' => 'my default password',
    ],
    fqcn: 'App\\Runtime\\Container',
)->compile();
```

### Использование:
```php
$container = new \App\Runtime\Container([
    'username' => 'my current username', // Переопределяем!
    'password' => 'my current password',
]);

$example = $container->get(Example::class);
// $example->username = 'my current username'
// $example->password = 'my current password'
```

**Важно**: Значения из runtime имеют приоритет над значениями, заданными при компиляции.

##  Обязательные параметры

Если параметры используются при компиляции, они становятся обязательными при создании контейнера:

```php
// Вызовет исключение - отсутствуют обязательные параметры
$container = new \App\Runtime\Container([]); 

// Правильно:
$container = new \App\Runtime\Container([
    'username' => '...',
    'password' => '...',
]);
```

## 🎯 Параметры для конкретных зависимостей

### Синтаксис:

```
{ClassName}${argumentName} => {значение аргумента}
```

### Пример:

```php
class Example {
    public function __construct(private string $username, private string $password) {}
}

class Example2 {
    public function __construct(private string $username, private string $password) {}
}

new \Cekta\DI\Compiler(
    containers: [Example::class, Example2::class],
    params: [
        'username' => 'default_username',
        'password' => 'default_password',
        Example2::class . '$username' => 'special_username', // Только для Example2
    ],
)->compile();
```

### Приоритеты:
1. `ClassName$argumentName` (наивысший)
2. Общие параметры
3. Autowiring (если не указано явно)

### 🔄 Работа с одинаковыми типами

Используйте имя для точного указания аргумента, если у нескольких аргументов одинаковый тип.

```php
class Example {
    public function __construct(
        private A $a1, 
        private A $a2,  // Только этот переопределяем
        private A $a3,
    ) {}
}

new \Cekta\DI\Compiler(
    containers: [Example::class],
    params: [
        Example::class . '$a2' => new B(), // B extends A
    ],
)->compile();
```

## 🕐 Lazy-параметры (отложенные значения)

Если параметр реализует [Lazy](../src/Lazy.php) interface то он будет загружаться в runtime.

### Для чего:

* Динамические вычисления значений
* Фабрики и билдеры
* Работа с legacy-кодом

### Интерфейс Lazy:

```php
interface Lazy {
    public function resolve(ContainerInterface $container): mixed;
}
```

### Пример с LazyClosure:

```php
new \Cekta\DI\Compiler(
    containers: [Example::class, 'db_type', 'db_path'],
    params: [
        'username' => $env['DB_USERNAME'] ?? null,
        'password' => $env['DB_PASSWORD'] ?? null,
        'dsn' => new \Cekta\DI\LazyClosure(
            function (ContainerInterface $c) {
                return "{$c->get('db_type')}:{$c->get('db_path')}";
            }
        ),
        'db_type' => $env['DB_TYPE'] ?? 'sqlite',
        'db_path' => $env['DB_PATH'] ?? './db.sqlite',
    ],
)->compile();
```

**Рекомендация:** Если параметры используются внутри LazyClosure, добавьте их в containers для гарантии доступности.