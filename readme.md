# Cekta/DI - PSR-11 Container Implementation

[![Telegram chat](https://img.shields.io/badge/telegram-RU%20chat-179cde.svg?logo=telegram)](https://t.me/dev_ru)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fcekta%2Fdi%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/cekta/di/master)
[![Latest Stable Version](https://poser.pugx.org/cekta/di/v/stable)](https://packagist.org/packages/cekta/di)
[![License](https://poser.pugx.org/cekta/di/license)](https://packagist.org/packages/cekta/di)

A modern, high-performance PSR-11 Container implementation designed for developers who value simplicity and performance.

## ✨ Features

* 🚀 **Zero Runtime Overhead** - All dependencies are resolved during compilation, not at runtime
  * **No runtime reflection** - All dependency resolution happens during compilation
  * **Predictable performance** - No dynamic analysis slowing down your application
* ⚡ **OPcache Ready** - Generated code works perfectly with PHP's opcode cache
* 🔧 **Flexible Configuration** - Mix autowiring with explicit configuration
* 📦 **Full PSR-11 Compliance** - Implements the standard Container Interface
* 🔄 **Modern PHP Support** - Works with Union Types, Intersection Types, DNF Types, and variadic arguments
* 🧩 **Interface & Abstract Class Support** - Full dependency injection for abstractions
* 🎯 **High Code Quality** - Rigorously tested with mutation testing
* ✅ **Easy debugging** - Generated container is plain PHP code you can read and understand

## 📦 Installation

```bash
composer require cekta/di
```

## 🚀 Quick Start

src/Controller.php:
```php
namespace App;

class Controller {
}
```

bin/build.php:

```php
use Cekta\DI\Compiler;

// Configure your dependencies.
$compiler = new Compiler(
    containers: [App\Controller::class],
    fqcn: 'App\\Runtime\\Container'
);

// Generate the container
$code = $compiler->compile();
file_put_contents(__DIR__ . '/../runtime/Container.php', $code);
```

app.php:

```php
use Cekta\DI\Compiler;

// Use it in your application
$container = new App\Runtime\Container();
$controller = $container->get(App\Controller::class);
```

## 📚 Documentation

https://cekta.github.io/di/

## 🤝 Community
Join the [Telegram chat](https://t.me/dev_ru) for discussions in English or Russian.

---

Cekta/DI - Dependency injection that gets out of your way and lets you focus on building great applications.