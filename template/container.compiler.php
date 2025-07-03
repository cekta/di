<?= '<?php' . PHP_EOL; ?>
<?php
/**
 * @var string $namespace
 * @var string $class
 * @var array<string> $targets
 * @var array<string, string> $dependencies
 * @var array<string, string> $alias
 * @var string[] $param_keys
 * @var string[] $definition_keys
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
     * @param array<string, mixed> $params
     * @param array<string, callable> $definitions
     * @throws \RuntimeException if required params or definition not declared
     */
    public function __construct(
        private array $params = [], 
        private array $definitions = []
    ) {
        $diff = array_diff(<?= var_export($param_keys, true) ?>, array_keys($this->params));
        if (!empty($diff)) {
            $diff = implode(', ', $diff);
            throw new \RuntimeException("params: {$diff} must be declared");
        }

        $diff = array_diff(<?= var_export($definition_keys, true) ?>, array_keys($this->definitions));
        if (!empty($diff)) {
            $diff = implode(', ', $diff);
            throw new \RuntimeException("definitions: {$diff} must be declared");
        }
    }

    public function get(string $id)
    {
        $this->params[$id] = match($id) {
            array_key_exists($id, $this->params) ? $id: false => $this->params[$id],
            array_key_exists($id, $this->definitions) ? $id: false => call_user_func($this->definitions[$id], $this),
    <?php foreach ($alias as $key => $value) { ?>
        <?= var_export($key, true) . " => \$this->get('{$value}')," . PHP_EOL ?>
    <?php } ?>

    <?php foreach ($dependencies as $key => $value) { ?>
        <?= var_export($key, true) . " => {$value}," . PHP_EOL ?>
    <?php } ?>

            default => throw new \Cekta\DI\Exception\NotFound($id),
        };
        return $this->params[$id];
    }

    public function has(string $id): bool
    {
        return in_array($id, <?= var_export($targets, true) ?>);
    }
}
