<?php

namespace App\Domain\Educacao\CentralMatriculas\Helpers;

class ConfiguracaoCentralHelper extends CentralMatriculasHelper
{
    public function __construct()
    {
        parent::__construct();
    }

    public function noticias()
    {
        return new NoticicasHelper();
    }

    public function mensagens()
    {
        return new MensagensHelper();
    }

    public function cores()
    {
        return new CoresHelper();
    }

    public function documentos()
    {
        return new DocumentosHelper();
    }

    public function imagens()
    {
        return new ImagensHelper();
    }

    public function camposOpcionais()
    {
        return new CamposOpcionaisHelper();
    }

    public function parametros()
    {
        return new ParametrosHelper();
    }
}
