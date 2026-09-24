<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Domain\Configuracao\Helpers\StorageHelper;
use ECidade\V3\Extension\Registry;
use ECidade\V3\Extension\Request;

class M25720MigracaoDocumentosBensbaix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Validação para verificar se e-storage esta verificado
        $autenticacaoEstorage = \ECidade\Lib\Request\Storage\Curl\Autenticacao::getInstance();
        try {
            $autenticacaoEstorage->execute();
            $autenticacaoEstorage->getAccessToken();
        } catch (Exception $e) {
            $this->logMe("Erro ao validar E-storage: {$e->getMessage()}");
            return;
        }

        $this->requireDependencies();
        $rs = db_query('select t55_codbem, t55_documento, t55_estorage from bensbaix where nullif(t55_documento, 0) is not null and t55_estorage is false');
        $bens = \db_utils::getCollectionByRecord($rs);
        foreach ($bens as $bem) {
            $id = null;
            db_inicio_transacao();
            try {
                $arquivo = "tmp/bem_" . $bem->t55_codbem;
                $gerou = pg_lo_export($bem->t55_documento, $arquivo);
                if (!$gerou) {
                    throw new Exception('Erro ao exportar o arquivo do banco');
                }
                if (!pg_lo_unlink($bem->t55_documento)) {
                    throw new Exception('Erro ao apagar do banco de dados');
                }

                $id = StorageHelper::uploadArquivo($arquivo, [], true);

                $rs = db_query("update bensbaix set t55_documento = {$id}, t55_estorage = true where t55_codbem = {$bem->t55_codbem}");
                if (!$rs) {
                    throw new Exception('');
                }
                db_fim_transacao();
            } catch (Exception $e) {
                db_fim_transacao(true);
                $this->logMe("Erro no bem {$bem->t55_codbem}\n {$e->getMessage()}");
                if ($id) {
                    try {
                        StorageHelper::deleteArquivo($id);
                    } catch (Exception $e) {
                        $this->logMe($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function logMe($msg)
    {
        file_put_contents('tmp/log_migracao_bensbaix.txt', $msg, FILE_APPEND);
    }

    private function requireDependencies()
    {
        session_start();
        $fakeRequest = new Request();
        Registry::set('app.request', $fakeRequest);

        $_SESSION["DB_datausu"] = time();
        $_SESSION["DB_login"] = "dbseller";
        $_SESSION["DB_id_usuario"] = "1";
        $_SESSION["DB_coddepto"] = 1;
        $_SESSION["DB_instit"] = 1;

        $_SERVER['REQUEST_URI'] = 'localhost';

        require_once modification('libs/db_stdlib.php');
        require_once modification('libs/db_utils.php');
        require_once modification('libs/db_app.utils.php');
        require_once modification('libs/db_conecta_cli.php');
        require_once modification('dbforms/db_funcoes.php');
    }
}
