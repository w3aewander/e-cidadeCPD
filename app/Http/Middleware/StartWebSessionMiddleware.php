<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

/**
 * Class StartWebSessionMiddleware
 * @package App\Http\Middleware
 */
class StartWebSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->logAcesso(substr($request->getPathInfo(), 1));
        if (isset($_SESSION)) {
            session($_SESSION);
        }

        return $next($request);
    }

    /**
     * @param string $pathMenu
     * @return void
     */
    private function logAcesso($pathMenu)
    {
        $item = $this->getItemMenu($pathMenu);
        if ($item !== null) {
            $this->insertLog($item->id, $pathMenu);
        }
    }

    /**
     * Retorna a chave primária do item de menu acessado, conforme a rota da requisição
     *
     * @param string $pathMenu
     * @return object
     */
    private function getItemMenu($pathMenu)
    {
        return DB::table('db_itensmenu')
            ->select('db_itensmenu.id_item as id')
            ->join('db_menu', 'db_menu.id_item_filho', 'db_itensmenu.id_item')
            ->where(function (Builder $query) use ($pathMenu) {
                $query->whereRaw("trim(funcao) = '{$pathMenu}'");

                $modulo = function_exists('db_getsession') ? db_getsession('DB_modulo', false) : (isset($_SESSION['DB_modulo']) ? $_SESSION['DB_modulo'] : null);
                if ($modulo) {
                    $query->where('modulo', $modulo);
                }
            })
            ->first();
    }

    /**
     * Loga o acesso no banco e salva o item na sessão
     *
     * @param string $item
     * @param string $pathMenu
     * @throws \Exception
     */
    private function insertLog($item, $pathMenu)
    {
        $codsequen = DB::select("SELECT nextval('db_logsacessa_codsequen_seq')")[0]->nextval;

        $_SESSION['DB_itemmenu_acessado'] = $item;
        $_SESSION['DB_acessado'] = $codsequen;

        $ip = isset($_SESSION['DB_ip']) ? $_SESSION['DB_ip'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');
        $idUsuario = function_exists('db_getsession') ? (int) db_getsession('DB_id_usuario', false) : (isset($_SESSION['DB_id_usuario']) ? (int) $_SESSION['DB_id_usuario'] : 1);
        if (!$idUsuario) {
            $idUsuario = isset($_SESSION['DB_id_usuario']) ? (int) $_SESSION['DB_id_usuario'] : 1;
        }
        $idModulo = function_exists('db_getsession') ? (int) db_getsession('DB_modulo', false) : (isset($_SESSION['DB_modulo']) ? (int) $_SESSION['DB_modulo'] : 209);
        if (!$idModulo) {
            $idModulo = isset($_SESSION['DB_modulo']) ? (int) $_SESSION['DB_modulo'] : 209;
        }
        $codDepto = function_exists('db_getsession') ? (int) db_getsession('DB_coddepto', false) : (isset($_SESSION['DB_coddepto']) ? (int) $_SESSION['DB_coddepto'] : 20);
        if (!$codDepto) {
            $codDepto = isset($_SESSION['DB_coddepto']) ? (int) $_SESSION['DB_coddepto'] : 20;
        }
        $instit = function_exists('db_getsession') ? (int) db_getsession('DB_instit', false) : (isset($_SESSION['DB_instit']) ? (int) $_SESSION['DB_instit'] : 1);
        if (!$instit) {
            $instit = isset($_SESSION['DB_instit']) ? (int) $_SESSION['DB_instit'] : 1;
        }

        $rs = DB::table('db_logsacessa')->insert([
            'codsequen' => $codsequen,
            'ip' => $ip,
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'arquivo' => $pathMenu,
            'obs' => '',
            'id_usuario' => $idUsuario,
            'id_modulo' => $idModulo,
            'id_item' => $item,
            'coddepto' => $codDepto,
            'instit' => $instit
        ]);

        if (!$rs) {
            throw new \Exception('Houve um erro ao iniciar a auditoria do sistema.');
        }
    }
}
