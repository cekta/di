<?= '<?php' . PHP_EOL; ?><?php
/**
 * @var string $class
 * @var bool $reflection_enabled
 * @var bool $alias_overridable
 * @var bool $definition_overridable
 * @var array $alias
 * @var array $alias
 * @var array $containers
 */
?>

declare(strict_types=1);

<?php if (!empty($namespace)) { ?>namespace <?= $namespace ?>; <?php } ?>

class <?= $class ?> implements \Psr\Container\ContainerInterface
{
    private array $params;
    private array $alias;
<?php if ($definition_overridable) { ?>
    private array $definitions;
<?php } ?>
    private array $containers;
<?php if ($reflection_enabled) { ?>
    private \Cekta\DI\Strategy\Autowiring $autowiring;
<?php } ?>

    public function __construct(array $params, array $alias, array $definitions)
    {
        $this->params = $params;
<?php if ($alias_overridable) { ?>
        $this->alias = $alias;
<?php } ?>
<?php if ($definition_overridable) { ?>
        $this->definitions = $definitions;
<?php } ?>
<?php if ($reflection_enabled) { ?>
        $this->autowiring = new \Cekta\DI\Strategy\Autowiring(new \Cekta\DI\Reflection(), $this, $this->alias);
<?php } ?>
        $this->containers = <?= var_export(array_unique(array_merge(array_keys($containers), array_keys($alias)))) ?>;
    }

    public function get($name)
    {
        if (!array_key_exists($name, $this->params)) {
            $this->params[$name] = match($name) {
            <?php if ($definition_overridable) { ?>
                array_key_exists($name, $this->definitions) ? $name: false => $this->definitions[$name]($this),
            <?php } ?>
            <?php if ($alias_overridable) { ?>
                array_key_exists($name, $this->alias) ? $name: false => $this->get($this->alias[$name]),
            <?php } ?>
            <?php foreach ($containers as $key => $value) { ?>
                <?= var_export($key, true) ?> => <?= $value ?>,
            <?php } ?>
            <?php foreach ($alias as $key => $value) { ?>
                <?= var_export($key, true) ?> => $this->get('<?= $value ?>'),
            <?php } ?>
            <?php if ($reflection_enabled) { ?>
                $this->autowiring->has($name) === true ? $name: false => $this->autowiring->get($name),
            <?php } ?>
                default => throw new \Cekta\DI\Exception\NotFound($name),
            };
        }
        return $this->params[$name];
    }

    public function has($name)
    {
        return array_key_exists($name, $this->params)
<?php if ($alias_overridable) { ?>
            || array_key_exists($name, $this->alias)
<?php } ?>
<?php if ($definition_overridable) { ?>
            || array_key_exists($name, $this->definitions)
<?php } ?>
            || in_array($name, $this->containers)
            <?php if ($reflection_enabled) { ?>
            || $this->autowiring->has($name)
            <?php } ?>
            ;
    }
}
