# Начало

## Установка

```
composer require cekta/di
```

## Первичная настройка (Полный пример)

Может показаться что слишком много действий по настройке, но какие-то действия делаются единожды.

Также первичная настройка содержит в опциональные, но рекомендуемые действия, для того чтобы в дальнейшем было
максимально просто пользоваться библиотекой.

Возможно список действий вам покажется слишком детальным и очевидным, подробный список позволит другим не допустить
"детских" ошибок.

1. Все команды выполняем в папке с проектом (ОЧЕВИДНОЕ), / - корень проекта.
2. Давайте вынесем генеримый код нашим приложением в отдельную папку,  
   например /runtime (ОПИЦОНАЛЬНО)
    1. Создаем папку
       ```
       $ mkdir /runtime
       ```
    2. Выделяем namespace для сгенерированных классов (например App\Runtime\*) и добавляем его в composer.json
       ```json
       ...
       "autoload": {
         "psr-4": {
           "App\\": "src/",
           "App\\Runtime\\": "runtime/"
         }
       },
       ...
       ```
    3. Обновляем autoload чтобы изменения применялись
        ```
       $ composer dumpautoload
       ```
3. Создадим самую простую зависимость, которые мы будем получать,  
   **/src/Example.php**
    ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App;
    
    class Example
    {
    }
    
    ```
4. Создадим class где будем задавать основную конфигурацию проекта,  
   **/src/Project.php**
    ```php
    <?php
    
    declare(strict_types=1);
    
    namespace App;
    
    use Cekta\DI\Compiler;
    use Psr\Container\ContainerInterface;
    use RuntimeException;
    
    class Project
    {
        private string $container_file;
        private string $container_fqcn;
        private int $container_permission;
    
        public function __construct(private array $env)
        {
            $this->container_file = realpath(__DIR__ . '/..') . '/runtime/Container.php';
            $this->container_fqcn = 'App\\Runtime\\Container';
            $this->container_permission = 0777;
        }
    
        public function createContainer(): ContainerInterface
        {
            if (!class_exists($this->container_fqcn)) {
                throw new RuntimeException("$this->container_fqcn class not found, maybe need generate ?");
            }
            return new ($this->container_fqcn)($this->params());
        }
    
        public function compile(): void
        {
            // Ваша конфигурация для кода генерации
            $content = (new Compiler(
                containers: [
                    Example::class,
                ],
                params: $this->params(),
                fqcn: $this->container_fqcn,
            ))->compile();
            if (file_put_contents($this->container_file, $content, LOCK_EX) === false) {
                throw new RuntimeException("$this->container_file cant compile");
            }
            chmod($this->container_file, $this->container_permission);
        }
    
        private function params()
        {
            return [
                // Ваши параметры, можно использовать $this->env
            ];
        }
    }
    
    ```
5. Сделаем скрипт, который будет генерировать код.  
    Вы можете использовать [symfony/console](https://packagist.org/packages/symfony/console) 
    и преобразовать скрипт в команду или использовать другой CLI.  
    **/bin/compile.php**
    ```php
    #!/usr/bin/env php
    <?php
    
    declare(strict_types=1);
    
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $project = new \App\Project($_ENV);
    $project->compile();
    ```
6. Сгенерируем код
    ```
    $ php bin/compile.php
    ```
7. Используем сгенерированный код у себя, простейший пример вашего приложения.  
   **/app.php**
    ```php
    #!/usr/bin/env php
    <?php
    
    declare(strict_types=1);
    
    $project = new \App\Project($_ENV);
    $container = $project->createContainer();
    
    var_dump($container->get(App\Example::class));
    ```
8. Убедимся что все работает
    ```
    $ php app.php
    object(App\Example)#1 (0) {
    }
    ```

## Использование

Все эти действия выполняются единожды, в дальнейшем надо будет лишь пользоваться:
1. Изменять конфигурацию вашего проекта.  
    (редактировать класс App\Project в двух помеченных комментариями местах из шага 4)
2. Генерировать код. (выполнять одну команду из шага 6)
3. Использовать полученный результат у себя в проекте и внедрять зависимости.