<?php
declare(strict_types=1);

namespace Cekta\DI\Test;

class Database
{
    /**
     * @var array
     */
    public $options;
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $dsn;
    /**
     * @var string
     */
    public $password;

    public function __construct(string $dsn, string $username, string $password, array $options)
    {
        $this->username = $username;
        $this->dsn = $dsn;
        $this->password = $password;
        $this->options = $options;
    }
}
