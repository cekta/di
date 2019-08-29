#### Autowiring и производительность

Для получения аргументов конструктора, используется [Reflection](https://www.php.net/manual/ru/book.reflection.php).

Reflection в PHP не слишком быстрый, существуют провайдеры позволяющие кэшировать обращения к
Reflection используя [psr/cache](https://www.php-fig.org/psr/psr-6/) и
[psr/simple-cache](https://www.php-fig.org/psr/psr-16/).

---
* [Autowiring](autowiring.md):
    * [Autowiring и interface](interface.md) 
    * [Autowiring и RuleInterface](rule-interface.md) 
    * [AutowiringSimpleCache](simple-cache.md) 
    * [AutowiringCache](cache.md) 
---
[Вернуться на главную](../../readme.md)
