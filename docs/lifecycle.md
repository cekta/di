# Жизненный цикл

В долгоживущих процессах — приложениях на [RoadRunner](https://roadrunner.dev/),
[FrankenPHP](https://frankenphp.dev/), фоновых workers или консольных командах, обрабатывающих
множество задач — важно контролировать, сколько экземпляров сервиса создаётся и где они живут.

Для этого в библиотеке предусмотрены три типа жизненного цикла. Их можно применять к любым
зависимостям: entries, params, alias и autowiring.

## Обзор

| Тип | Описание | Когда использовать |
|-----|----------|---------------------|
| **Scoped** | Один экземпляр на контейнер | Обработка запросов, пользовательские сессии, изоляция данных |
| **Singleton** | Один экземпляр на весь PHP-процесс | Конфигурация, подключения к БД, кеши |
| **Factory** | Новый экземпляр при каждом вызове `get()` | Stateless-сервисы, DTO, временные данные |

> 💡 Классы, указанные в `entries`, по умолчанию ведут себя как **scoped**.
> Указывать их в отдельных разделах не нужно.

## Конфигурация

Жизненный цикл определяется в методе `definition()` класса [`AbstractProject`](api:AbstractProject):

```php
<?php
declare(strict_types=1);

namespace App;

class Project extends \Cekta\DI\AbstractProject
{
    public function definition(): array
    {
        return [
            'entries' => [
                \App\HttpController::class,  // Scoped
            ],
            'singletons' => [
                \App\Database::class,         // Singleton
            ],
            'factories' => [
                \App\HttpRequest::class,      // Factory
            ],
        ];
    }
}
```

## Пример

Рассмотрим, как ведёт себя каждый тип при вызове `get()` на одном и том же и на разных контейнерах:

```php
<?php
$container1 = new \App\Container();
$container2 = new \App\Container();

$scoped1 = $container1->get(\App\Scoped::class);
$scoped2 = $container1->get(\App\Scoped::class);
// $scoped1 === $scoped2

$singleton1 = $container1->get(\App\Singleton::class);
$singleton2 = $container2->get(\App\Singleton::class);
// $singleton1 === $singleton2

$factory1 = $container1->get(\App\Factory::class);
$factory2 = $container2->get(\App\Factory::class);
// $factory1 !== $factory2
// $factory1 !== $factory2
```

## Сравнение

| Тип | Внутри одного контейнера | Между разными контейнерами | Когда использовать |
|-----|--------------------------|----------------------------|---------------------|
| **Scoped** | Один объект | Разные объекты | Обработка запросов, пользовательские сессии, изоляция данных |
| **Singleton** | Один объект | Один объект | Конфигурация, подключения к БД, кеши |
| **Factory** | Разные объекты | Разные объекты | Stateless-сервисы, DTO, временные данные |

## Важно

- **Конфликты** — один и тот же класс нельзя указать одновременно как `Singleton` и `Factory`
- **Производительность** — `Factory` создаёт наибольшую нагрузку, `Singleton` — наименьшую
- **Потокобезопасность** — `Singleton` должен быть потокобезопасным в многопоточных средах

Полное описание параметров конфигурации — [Проект](configuration.md).
