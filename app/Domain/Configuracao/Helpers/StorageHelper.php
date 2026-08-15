<?php

namespace App\Domain\Configuracao\Helpers;

use App\Domain\Core\Base\Repository\BaseRepository;
use App\Domain\Configuracao\Instituicao\Model\DBConfig as Instituicao;
use App\Domain\Configuracao\Instituicao\Repository\Contracts\InstituicaoRepository as RepositoryInterface;
use ECidade\Lib\Request\Storage\Curl\Autenticacao;
use ECidade\Lib\Request\Storage\Curl\CurrentVersion;
use ECidade\Lib\Request\Storage\Curl\Get;
use ECidade\Lib\Request\Storage\Curl\Post;
use ECidade\Lib\Request\Storage\Curl\Delete;
use ECidade\Lib\Request\Storage\File;
use ECidade\Lib\File\FileEstorage;
use ECidade\V3\Extension\Registry;
use Illuminate\Support\Facades\Log;
use ParameterException;
use Exception;

/**
 * Classe helper que reune as funções para trabalhar com o storage
 *
 * @var string
 */
class StorageHelper
{
    /**
     * Realiza upload de um arquivo para o E-Storage.
     *
     * @param string $caminho
     * @param array|null $allowed
     * @param boolean $onlyId
     * @param \stdClass $metadata
     * @param  $fileFather
     * @param string $clientOriginalName
     * @return \stdclass|integer $data|$id9
     * @throws Exception
     */
    public static function uploadArquivo(
        $caminho,
        array $allowed = null,
        $onlyId = false,
        \stdClass $metadata = null,
        $fileFather = null,
        $clientOriginalName = null
    ) {
        if ($clientOriginalName === null) {
            $caminhoPartes = explode("/", $caminho);
            $clientOriginalName = end($caminhoPartes);
        }

        $post = new Post(Autenticacao::getInstance());
        $file = new File();
        $file->realPath($caminho)->clientOriginalName($clientOriginalName);
        if (!empty($metadata)) {
            $file->metadata($metadata);
        }

        $file->visibility("private");
        $file->allowed($allowed);
        if (!empty($fileFather)) {
            $file->fileFather($fileFather);
        }

        $retorno = $post->execute($file);
        if ($onlyId) {
            return $retorno->data->id;
        }

        return $retorno->data;
    }


    public static function atualizarArquivo(
        $id,
        $caminho,
        array $allowed = null,
        $onlyId = false,
        \stdClass $metadata = null
    ) {
        $arquivo = explode("/", $caminho);
        $post = new Post(Autenticacao::getInstance());
        $file = new File();
        $file->realPath($caminho)->clientOriginalName($arquivo[count($arquivo) - 1]);
        if (!empty($metadata)) {
            $file->metadata($metadata);
        }
        $file->visibility("private");
        $file->allowed($allowed);
        $retorno = $post->change($id, $file);

        if ($onlyId) {
            return $retorno->data->id;
        }

        return $retorno->data;
    }

    /**
     * Realiza download de um arquivo do E-Storage.
     *
     * @param string $idStorage
     * @return string $caminhoArquivo
     */
    public static function downloadArquivo($idStorage)
    {
        return (new FileEstorage())->getPath($idStorage);
    }

    /**
     * Busca as configurações do e-storage
     *
     * @return \stdclass
     */
    public static function getStorageConfig()
    {
        if (!Registry::get('app.config')->has('app.api')) {
            $msg = "Erro ao buscar as credencias das api's";
            $msg .= "\nVerifique o arquivo de configuração (application).";
            throw new ParameterException($msg);
        }

        $configApi = (object)Registry::get('app.config')->get('app.api');

        if (empty($configApi) || empty($configApi->estorage)) {
            $msg = "Erro ao buscar as credencias do e-Storage";
            $msg .= "\nVerifique o arquivo de configuração (application).";
            throw new ParameterException($msg);
        }

        if (empty($configApi->estorage["url"])) {
            $msg = "Erro ao buscar as credencias das api's";
            $msg .= "\nVerifique o arquivo de configuração (application).";
            throw new Exception($msg);
        }

        return (object)$configApi->estorage;
    }


    /**
     * @param $idStorage
     * @return array
     * @throws Exception
     */
    public static function getContentsBase64($idStorage)
    {
        return (new FileEstorage())->getBase64($idStorage);
    }


    /**
     * @param $idStorage
     * @return bool
     * @throws Exception
     */
    public static function deleteArquivo($idStorage)
    {
        $delete = new Delete(Autenticacao::getInstance());
        return $delete->delete($idStorage);
    }

    /**
     * @throws Exception
     */
    public static function currentVersion($idStorage)
    {
        $currentVersion = new CurrentVersion(Autenticacao::getInstance());
        return $currentVersion->execute($idStorage);
    }
}
