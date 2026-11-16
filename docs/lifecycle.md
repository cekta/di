# Жизненный цикл

В долгоживущих процессах — приложениях на [RoadRunner](https://roadrunner.dev/),
[FrankenPHP](https://frankenphp.dev/), фоновых workers или консольных командах, обрабатывающих
множество задач — важно контролировать, сколько экземпляров сервиса создаётся.

Для этого в библиотеке предусмотрены три типа жизненного цикла.
Их можно применять к любым зависимостям.

Управлением жизненным циклом зависимостей привязано к созданию экземпляра контейнера.

## Scoped

Зависимость существует в единственном экземпляра в рамках **экземпляра контейнера**.

Экземпляр зависимости создается единожды (в момент когда он понадобится),
сохраняется в in memory storage конкретного экземпляра контейнера (в свойство экземпляра контейнера).

Если будет несколько экземпляров контейнера, может быть несколько разных экземпляров зависимости.

> 💡 Значение по умолчанию для всех.

## Singleton

Зависимомсть существует в единственном экземпляре в рамках **ВСЕХ** экзепляров контейнеров.

in memory storage **общий между всеми контейнерами** (статическое свойство)

Сколько бы не было экземпляров контейнеров, зависимость будет в одном экземпляре.

> 💡В многопоточной среде, желательно чтобы такая зависимость была **потокабезопасна**.

## Factory

Зависимость создается каждый раз как ее запросят (она не кэшируется in memory storage).

## Пример

Рассмотрим, как ведёт себя каждый тип при вызове `get()` на одном и том же и на разных контейнерах:

```php
<?php
$manifest = new Cekta\DI\Manifest(
    filename: __DIR__ . '/src/Container.php',
    fqcn: 'App\\Container',
    definition: [
        [
            'entries' => [
                \App\HttpController::class,  // Scoped
                \App\Database::class,
                \App\HttpRequest::class,
            ],
            'singletons' => [
                \App\Database::class,         // Singleton
            ],
            'factories' => [
                \App\HttpRequest::class,      // Factory
            ],
        ]
    ]
);
$factory = new Cekta\DI\ContainerFactory();
$container1 = $factory->create($manifest); // первый экземпляр контейнера
$container2 = $factory->create($manifest); // второй экземпляр контейнера

$scoped1 = $container1->get(\App\HttpController::class); // создали зависимость и положили в кэш первого экземпляра контейнера
assert($scoped1 === $container1->get(\App\HttpController::class));
assert($scoped1 !== $container2->get(\App\HttpController::class)); // в кэще второго экземпляра контейнера зависимости нет!!!

$singleton1 = $container1->get(\App\Database::class); // создали зависимость и положили в кэш ВСЕХ контейнеров
assert($singleton1 === $container1->get(\App\Database::class));
assert($singleton1 === $container2->get(\App\Database::class));

$factory1 = $container1->get(\App\HttpRequest::class); // создали зависимость и не закэшировали ее
assert($factory1 !== $container1->get(\App\HttpRequest::class)); // каждый следующий вызов создает новый экземпляр
assert($factory1 !== $container2->get(\App\HttpRequest::class));
```

Данный пример наглядно демонстрирует что жизненный цикл зависимостей привязан к экземпляру контейнера, 
если у вас долгоживущие процессы, то вы можетете управлять временем жизни экземпляра контейнер.

[Подробней о долгоживущих процессах](reset.md)
