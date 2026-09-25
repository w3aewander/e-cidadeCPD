<?php

namespace App\Domain\Configuracao\Usuario\Exceptions;

use Exception;

class MaxLoginAttemptsException extends Exception
{
    private $tentativas;
    private $username;

    public function __construct($message = "", $tentativas = 3, $username = "", $code = 403, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->tentativas = $tentativas;
        $this->username = $username;
    }

    public function getTentativas()
    {
        return $this->tentativas;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
