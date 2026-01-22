<?= '<?php' . PHP_EOL; ?>
<?php
/**
 * @var string $namespace
 * @var string $class
 * @var array<string> $entries
 * @var array<string, string> $dependencies
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
     * @var string[]
     */
    private array $targets;

    /**
     * @param array<string, mixed> $params
     * @throws \RuntimeException if required params not declared
     */
    public function __construct(
        private array $params = [], 
    ) {
        $keys = array_keys($this->params);
        $diff = array_diff(<?= var_export($required_keys, true) ?>, $keys);
        if (!empty($diff)) {
            $diff = implode(', ', $diff);
            throw new \InvalidArgumentException("Entries: {$diff} must be declared in params");
        }
        $this->list_factories = <?= var_export($factories, true) ?>;
        $this->list_singletons = <?= var_export($singletons, true) ?>;
        $this->targets = <?= var_export($entries, true) ?>;
    }

    public function get(string $id)
    {
        switch ($id) {
            case array_key_exists($id, $this->context) ? $id: false:
                return $this->context[$id];
            case array_key_exists($id, self::$singletons) ? $id: false:
                return self::$singletons[$id];
            case array_key_exists($id, $this->params) ? $id: false:
                if ($this->params[$id] instanceof \Cekta\DI\Lazy) {
                    if (in_array($id, $this->list_singletons)) {
                        self::$singletons[$id] = $this->params[$id]->load($this);
                        return self::$singletons[$id];
                    } elseif (in_array($id, $this->list_factories)) {
                        return $this->params[$id]->load($this);
                    } else {
                        $this->context[$id] = $this->params[$id]->load($this);
                        return $this->context[$id];
                    }
                }
                return $this->params[$id];
        <?php foreach ($dependencies as $key => $value) { ?>
            case <?= var_export($key, true) ?>:
            <?php if (in_array($key, $singletons)) { ?>
                self::$singletons[$id] = <?= $value ?>;
                return self::$singletons[$id];
            <?php } elseif (in_array($key, $factories)) { ?>
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
        return in_array($id, $this->targets);
    }
}
