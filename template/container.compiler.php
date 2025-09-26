<?= '<?php' . PHP_EOL; ?>
<?php
/**
 * @var string $namespace
 * @var string $class
 * @var array<string> $targets
 * @var array<string, string> $dependencies
 * @var array<string, string> $alias
 * @var string[] $required_keys
 * @var string[] $singletons
 * @var string[] $factories
 */

?>

declare(strict_types=1);

<?php
if (!empty($namespace)) {
    echo "namespace {$namespace};" . PHP_EOL;
}
?>

class <?= $class ?> implements \Psr\Container\ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private static array $singletons = [];
    /**
     * @var array<string, mixed>
     */
    private array $context = [];
    /**
     * @var string[]
     */
    private array $list_singletons;
    /**
     * @var string[]
     */
    private array $list_factories;

    /**
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @throws \RuntimeException if required params or definition not declared
     */
    public function __construct(
        private array $params = [], 
        private array $definitions = []
    ) {
        $keys = array_merge(array_keys($this->params), array_keys($this->definitions));
        $diff = array_diff(<?= var_export($required_keys, true) ?>, $keys);
        if (!empty($diff)) {
            $diff = implode(', ', $diff);
            throw new \InvalidArgumentException("Containers: {$diff} must be declared in params or definitions");
        }
        $this->list_factories = <?= var_export($factories, true) ?>;
        $this->list_singletons = <?= var_export($singletons, true) ?>;
    }

    public function get(string $id)
    {
        switch ($id) {
            case array_key_exists($id, $this->params) ? $id: false:
                return $this->params[$id];
            case array_key_exists($id, $this->context) ? $id: false:
                return $this->context[$id];
            case array_key_exists($id, self::$singletons) ? $id: false:
                return self::$singletons[$id];
            case array_key_exists($id, $this->definitions) ? $id: false:
                if (in_array($id, $this->list_singletons)) {
                    self::$singletons[$id] = call_user_func($this->definitions[$id], $this);
                    return self::$singletons[$id];
                } else if (in_array($id, $this->list_factories)) {
                    return call_user_func($this->definitions[$id], $this);
                } else {
                    $this->context[$id] = call_user_func($this->definitions[$id], $this);
                    return $this->context[$id];
                }
        <?php foreach ($alias as $key => $value) { ?>
            case <?= var_export($key, true) ?>:
            <?php if (in_array($key, $singletons)) { ?>
                self::$singletons[$id] = $this->get('<?= $value ?>');
                return self::$singletons[$id];
            <?php } else if (in_array($key, $factories)) { ?>
                return $this->get('<?= $value ?>');
            <?php } else { ?>
                $this->context[$id] = $this->get('<?= $value ?>');
                return $this->context[$id];
            <?php } ?>
        <?php } ?>
        <?php foreach ($dependencies as $key => $value) { ?>
            case <?= var_export($key, true) ?>:
            <?php if (in_array($key, $singletons)) { ?>
                self::$singletons[$id] = <?= $value ?>;
                return self::$singletons[$id];
            <?php } else if (in_array($key, $factories)) { ?>
                return <?= $value ?>;
            <?php } else { ?>
                $this->context[$id] = <?= $value ?>;
                return $this->context[$id];
            <?php } ?>
        <?php } ?>
            default:
                throw new \Cekta\DI\Exception\NotFound($id);
        }
    }

    public function has(string $id): bool
    {
        return in_array($id, <?= var_export($targets, true) ?>);
    }
}
