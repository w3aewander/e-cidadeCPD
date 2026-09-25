<?php

namespace ECidade\Lib\Config;

class DbConn
{
    /**
     * @var DbConn
     */
    public static $instance;

    /**
     * @var string
     */
    private $servidor;

    /**
     * @var string
     */
    private $base;

    /**
     * @var string
     */
    private $porta;

    /**
     * @var string
     */
    private $usuario;

    /**
     * @var null string
     */
    private $senha;

    private function __construct(
        $servidor = null,
        $base = null,
        $porta = null,
        $usuario = null,
        $senha = null
    ) {
        $this->servidor = $servidor;
        $this->base = $base;
        $this->porta = $porta;
        $this->usuario = $usuario;
        $this->senha = $senha;
        $this->load();
    }

    public function load()
    {
        $host = function_exists('env') ? env('DB_HOST') : getenv('DB_HOST');
        $base = function_exists('env') ? env('DB_DATABASE') : getenv('DB_DATABASE');
        $port = function_exists('env') ? env('DB_PORT') : getenv('DB_PORT');
        $user = function_exists('env') ? env('DB_USERNAME') : getenv('DB_USERNAME');
        $pass = function_exists('env') ? env('DB_PASSWORD') : getenv('DB_PASSWORD');

        $this->servidor = !empty($this->servidor) ? $this->servidor : (!empty($host) ? $host : '127.0.0.1');
        $this->base = !empty($this->base) ? $this->base : (!empty($base) ? $base : 'ecidade');
        $this->porta = !empty($this->porta) ? $this->porta : (!empty($port) ? $port : '5433');
        $this->usuario = !empty($this->usuario) ? $this->usuario : (!empty($user) ? $user : 'ecidade');
        $this->senha = !empty($this->senha) ? $this->senha : (!empty($pass) ? $pass : 'ecidade');
    }

    /**
     * @return DbConn
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    public function servidor()
    {
        if (empty($this->servidor)) {
            $this->load();
        }
        return $this->servidor;
    }

    /**
     * @return string
     */
    public function base()
    {
        if (empty($this->base)) {
            $this->load();
        }
        return $this->base;
    }

    /**
     * @return string
     */
    public function porta()
    {
        if (empty($this->porta)) {
            $this->load();
        }
        return $this->porta;
    }

    /**
     * @return string
     */
    public function usuario()
    {
        if (empty($this->usuario)) {
            $this->load();
        }
        return $this->usuario;
    }

    /**
     * @return null
     */
    public function senha()
    {
        if (empty($this->senha)) {
            $this->load();
        }
        return $this->senha;
    }
}
