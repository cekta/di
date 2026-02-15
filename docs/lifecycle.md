# Управление жизненным циклом зависимостей

Библиотека предоставляет три типа жизненного цикла для зависимостей, что особенно полезно в долгоживущих процессах:

* Приложениях на [RoadRunner](https://roadrunner.dev/) или [FrankenPHP](https://frankenphp.dev/)
* Фоновых workers
* В консольных командах, обрабатывающих множество задач

Вы можете управлять жизненным циклом любых зависимостей: entries, params, alias и autowiring.

## 📋 Демонстрация разницы.

```php
<?php
declare(strict_types=1);

namespace App;

class Scoped {}
class Singleton {}
class Factory {}

new \Cekta\DI\ContainerBuilder(
    entries: [
        \App\Scoped::class,
        \App\Singleton::class,
        \App\Factory::class,
    ],
    fqcn: 'App\\Container',
    singletons: [\App\Singleton::class],   // Singleton-зависимости
    factories: [\App\Factory::class],      // Factory-зависимости
    // Scoped-зависимости не указываются явно (используются по умолчанию)
)->build();
```

**index.php** - Usage (Использование)
```php
<?php
declare(strict_types=1);

namespace App;

function testLifecycle(string $className) {
    $container1 = new \App\Container();
    $container2 = new \App\Container();
    
    $a = $container1->get($className);
    $b = $container1->get($className);  // Второй запрос к тому же контейнеру
    $c = $container2->get($className);  // Запрос к другому контейнеру
    
    echo "Внутри одного контейнера: " . ($a === $b ? "одинаковый" : "разный") . "\n";
    echo "Между разными контейнерами: " . ($a === $c ? "одинаковый" : "разный") . "\n";
    echo "---\n";
}

echo "Scoped (по умолчанию):\n";
testLifecycle(Scoped::class);

echo "Singleton:\n";
testLifecycle(Singleton::class);

echo "Factory:\n";
testLifecycle(Factory::class);
```

**Результат:**

```
Scoped (по умолчанию):
Внутри одного контейнера: одинаковый
Между разными контейнерами: разный
---
Singleton:
Внутри одного контейнера: одинаковый
Между разными контейнерами: одинаковый
---
Factory:
Внутри одного контейнера: разный
Между разными контейнерами: разный
```

## 🎯 Сравнение жизненных циклов

| Тип       | Внутри одного контейнера | Между разными контейнерами	 | Когда использовать                          |
|-----------|--------------------------|-----------------------------|---------------------------------------------|
| Scoped ⭐  | 	Один объект	            | Разные объекты	             | Обработка запросов, пользовательские сессии |
| Singleton | 	Один объект             | Один объект	                | Конфигурация, подключения к БД, кеши        |
| Factory   | 	Разные объекты	         | Разные объекты	             | Stateless-сервисы, DTO, временные данные    |

## ⚠️ Важные замечания

1. **Scoped по умолчанию** - если не указан другой тип, используется Scoped
2. **Конфликты приоритетов** - нельзя указать один класс одновременно как Singleton и Factory
3. **Производительность** - Factory создаёт наибольшую нагрузку, Singleton - наименьшую
4. **Потокобезопасность** - Singleton должен быть потоко-безопасным в многопоточных средах

## 🚀 Пример для долгоживущего приложения

```php
<?php
new \Cekta\DI\Compiler(
    entries: [
        HttpController::class,
        UserRepository::class,
        EmailService::class,
    ],
    singletons: [
        Database::class,           // Одно подключение
        RedisCache::class,         // Общий кеш
        Config::class,             // Конфигурация
    ],
    factories: [
        HttpRequest::class,        // Новый для каждого запроса
        UserSession::class,        // Новый для каждого пользователя
    ],
);
```

Правильное управление жизненным циклом позволяет:

* [x] Экономить ресурсы (Singleton)
* [x] Изолировать данные (Scoped)
* [x] Предотвращать утечки памяти (Factory)
* [x] Легко масштабировать приложение