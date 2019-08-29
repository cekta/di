# Cekta\DI на русском языке

* [Как работает Container](container.md)
* Стандартные провайдеры:
    * [KeyValue из](providers/key-value/key-value.md):
        * [Environment](providers/key-value/environment.md)
        * [JSON](providers/key-value/json.md)
        * [PHP](providers/key-value/PHP.md)
        * [Custom format](providers/key-value/custom-format.md)
        * Зависимости и анонимных функций:
            * [KeyValue return LoaderInterface](providers/key-value/loader-interface.md)
            * [KeyValue transform](providers/key-value/transform.md)
    * [Autowiring](providers/autowiring/autowiring.md):
        * [Autowiring и interface](providers/autowiring/interface.md) 
        * [Autowiring и RuleInterface](providers/autowiring/rule-interface.md) 
        * [Autowiring и производительность](providers/autowiring/perfomance.md) 
        * [AutowiringSimpleCache](providers/autowiring/simple-cache.md) 
        * [AutowiringCache](providers/autowiring/cache.md) 
    * [Loaders](loaders.md):
        * [Alias](loaders/alias.md)
        * [Service](loaders/service.md)
* Практические советы:
    * [Создание объекта Container](best-practices/container-creation.md)
    * [Использования класса](best-practices/class.md)
    * [Использование файла](best-practices/file.md)
    * [Регистрирование реализации интерфейсов в одном месте](best-practices/reg-in-one.md)
    * [Использование autocomplete](best-practices/autocomplete.md)
 * [Feedback](feedback.md)
