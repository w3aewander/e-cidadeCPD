<?php

namespace App\Http\Middleware;

use Closure;

class EnsureLegacySession
{
    public function handle($request, Closure $next)
    {
        $idUsuario = function_exists('db_getsession') ? \db_getsession('DB_id_usuario', false) : null;
        if (!$idUsuario && isset($_SESSION['DB_id_usuario'])) {
            $idUsuario = $_SESSION['DB_id_usuario'];
        }
        if (!$idUsuario && session()->has('DB_id_usuario')) {
            $idUsuario = session('DB_id_usuario');
        }

        if (!$idUsuario) {
            abort(401, 'Sessão do e-Cidade não encontrada.');
        }

        return $next($request);
    }
}
