<?php

declare(strict_types=1);

namespace Cekta\DI;

use Closure;
use Psr\Container\ContainerInterface;

/**
 * @external
 */
class ContainerFactory
{
    /**
     * @var Closure(string $filename): bool
     */
    private readonly Closure $fileExists;
    /**
     * @var Closure(string $filename, mixed $data, int $flags =, ?resource $context =): int
     */
    private readonly Closure $filePutContents;
    /**
     * @var Closure(string $filename, ?resource $context=): bool
     */
    private readonly Closure $unlink;

    public function __construct(
        private ContainerGenerator $compiler = new ContainerGenerator(),
        ?Closure $fileExists = null,
        ?Closure $filePutContents = null,
        ?Closure $unlink = null,
    ) {
        $this->fileExists = $fileExists ?? file_exists(...);
        $this->filePutContents = $filePutContents ?? file_put_contents(...);
        $this->unlink = $unlink ?? unlink(...);
    }

    public function build(
        Manifest $manifest,
        bool $forceRecreate = false,
    ): ContainerInterface {
        if ($forceRecreate && ($this->fileExists)($manifest->filename())) {
            ($this->unlink)($manifest->filename());
        }
        if (!($this->fileExists)($manifest->filename())) {
            ($this->filePutContents)($manifest->filename(), $this->compiler->generate($manifest));
        }
        $container = new ($manifest->fqcn()->fqcn)($manifest->params());
        if (!$container instanceof ContainerInterface) {
            throw new \RuntimeException(
                "`{$manifest->fqcn()->fqcn}` must implement " .
                    ContainerInterface::class,
            );
        }
        return $container;
    }
}
