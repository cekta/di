## Загрузчики

Если для разрешения одной зависимости требуются другие, то provider возвращает объект реализующий 
[LoaderInterface](../../src/LoaderInterface.php).

[Container](../../src/Container.php) получив такое от провайдера, передаёт себя для того чтобы загрузить нужные зависимости.

---
* Loaders:
    * [Alias](loaders/alias.md)
    * [Service](loaders/service.md)
---
[Вернуться на главную](readme.md)
