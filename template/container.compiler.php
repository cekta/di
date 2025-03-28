<?= '<?php' . PHP_EOL; ?>
<?php
/**
 * @var string $namespace
 * @var string $class
 * @var array<string, string> $containers
 * @var array<string, string> $alias
 * @var array<string, mixed> $params
 * @var array<string, callable> $definitions
 */

?>

declare(strict_types=1);

<?php
if (!empty($namespace)) {
    echo "namespace {$namespace};";
}
?>

class <?= $class ?> implements \Psr\Container\ContainerInterface
{
    private array $params;
    private array $alias;
    private array $definitions;
    private array $ids;

    public function __construct(array $params = [], array $alias = [], array $definitions = [])
    {
        $this->params = $params;
        $this->alias = $alias;
        $this->definitions = $definitions;
        $this->ids = <?= var_export(array_unique(
    array_merge(
        array_keys($params),
        array_keys($definitions),
        array_keys($alias),
        array_keys($containers),
    )
)) ?>;
        $current_ids = array_unique(
            array_merge(
                array_keys($this->params),
                array_keys($this->alias),
                array_keys($this->definitions),
            )
        );
        $diff = array_diff(<?= var_export(array_unique(
    array_merge(
        array_keys($params),
        array_keys($definitions),
        array_keys($alias),
    )
)) ?>, $current_ids);
        if (!empty($diff)) {
            throw new \Cekta\DI\Exception\InvalidConfiguration($diff);
        }
    }
    public function get(string $id)
    {
        if (!array_key_exists($id, $this->params)) {
        $this->params[$id] = match($id) {
        array_key_exists($id, $this->definitions) ? $id: false => $this->definitions[$id]($this),
        array_key_exists($id, $this->alias) ? $id: false => $this->get($this->alias[$id]),
        <?php
        foreach ($containers as $key => $value) {
            $key = var_export($key, true);
            echo "{$key} => {$value}," . PHP_EOL;
        }
        ?>
        <?php
        foreach ($alias as $key => $value) {
            $key = var_export($key, true);
            echo "{$key} => \$this->get('{$value}')," . PHP_EOL;
        }
        ?>
        default => throw new \Cekta\DI\Exception\NotFound($id),
        };
        }
        return $this->params[$id];
    }

    public function has(string $id): bool
    {
        return in_array($id, $this->ids);
    }
}
