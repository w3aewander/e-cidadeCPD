<?php

namespace App\Domain\Configuracao\Usuario\Services;

use App\Domain\Configuracao\Usuario\Exceptions\MaxLoginAttemptsException;
use App\Domain\Configuracao\Usuario\Models\Usuario;
use App\Domain\Configuracao\Usuario\Requests\LoginRequest;
use App\Domain\Configuracao\Usuario\Requests\UnblockRequest;
use App\Domain\Core\Exceptions\SecuriImageException;
use ECidade\V3\Window\Session;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use ECidade\V3\Extension\Registry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

const MENSAGEM = 'configuracao.configuracao.abrir.';

/**
 * Serviço de Autenticação do e-Cidade
 */
class AuthService
{
    private $authorizationServer;
    const MAX_LOGIN_ATTEMPTS = 3;

    public function __construct(AuthorizationServer $authorizationServer)
    {
        $this->authorizationServer = $authorizationServer;
    }

    /**
     * @param LoginRequest $request
     * @return array
     * @throws SecuriImageException
     * @throws MaxLoginAttemptsException
     * @throws \Exception
     */
    public function authenticate(LoginRequest $request)
    {
        try {
            $this->start($request);

            $loginKey = 'login_attempts_' . strtolower(trim($request->username));
            $tentativas = (int) Cache::get($loginKey, 0);

            // Se já excedeu as 3 tentativas, bloqueia o acesso
            if ($tentativas >= self::MAX_LOGIN_ATTEMPTS) {
                if ($request->usuario instanceof Usuario && !$request->usuario->isAdministrador()) {
                    DB::table('configuracoes.db_usuarios')
                        ->where('id_usuario', (int)$request->usuario->id_usuario)
                        ->update(['usuarioativo' => 2]);
                    DB::commit();
                }

                throw new MaxLoginAttemptsException(
                    "Você atingiu o limite de " . self::MAX_LOGIN_ATTEMPTS . " tentativas de login. Seu acesso foi bloqueado por segurança. Solicite a recuperação de senha.",
                    $tentativas,
                    $request->username,
                    403
                );
            }

            $this->validaCaptcha($request->usuario, $request->username, $request->conteudoCaptcha, $tentativas);

            if ($request->usuario === null || (int)$request->usuario->usuext !== 0) {
                $tentativas++;
                Cache::put($loginKey, $tentativas, 1800); // 30 minutos

                $mensagem = _M(MENSAGEM . "login_invalido");
                if (function_exists('db_logsmanual_demais')) {
                    db_logsmanual_demais($mensagem);
                }

                if ($tentativas >= self::MAX_LOGIN_ATTEMPTS) {
                    throw new MaxLoginAttemptsException(
                        "Você atingiu o limite de " . self::MAX_LOGIN_ATTEMPTS . " tentativas de login. Usuário bloqueado por segurança.",
                        $tentativas,
                        $request->username,
                        403
                    );
                }

                throw new \BusinessException("Login ou senha inválido. Tentativa {$tentativas} de " . self::MAX_LOGIN_ATTEMPTS . ".", 400);
            }

            if (!$request->usuario->validateForPassportPasswordGrant($request->password)) {
                $tentativas++;
                Cache::put($loginKey, $tentativas, 1800);

                $log = _M(MENSAGEM . 'logs_senha_invalida', (object)['sCampo' => $request->usuario->login]);
                if (function_exists('db_logsmanual_demais')) {
                    db_logsmanual_demais($log, $request->usuario->getCodigo());
                }

                if ($tentativas >= self::MAX_LOGIN_ATTEMPTS) {
                    if (!$request->usuario->isAdministrador()) {
                        DB::table('configuracoes.db_usuarios')
                            ->where('id_usuario', (int)$request->usuario->id_usuario)
                            ->update(['usuarioativo' => 2]);
                        DB::commit();
                    }

                    throw new MaxLoginAttemptsException(
                        "Você atingiu o limite de " . self::MAX_LOGIN_ATTEMPTS . " tentativas de login. Seu usuário foi bloqueado por segurança.",
                        $tentativas,
                        $request->usuario->login,
                        403
                    );
                }

                throw new \BusinessException("Senha ou login inválido. Tentativa {$tentativas} de " . self::MAX_LOGIN_ATTEMPTS . ".", 400);
            }

            $this->validaUsuario($request->usuario);
            $this->validaVersao($request->usuario);

            $token = $this->createToken($request->username, $request->password);

            $this->buildSession($request);

            // Login bem-sucedido: limpa o contador de tentativas
            Cache::forget($loginKey);

            return $token;
        } catch (\Exception $e) {
            session_unset();
            session_destroy();

            throw $e;
        }
    }

    /**
     * @param UnblockRequest $request
     * @return array
     * @throws \Exception
     */
    public function unblock(UnblockRequest $request)
    {
        $session = new Session('MAIN');
        $session->create()->start();

        if (!$session->has('DB_id_usuario')) {
            throw new \BusinessException('Sessão inválida.');
        }

        $usuario = Usuario::find($session->get('DB_id_usuario'));
        if (!$usuario->validateForPassportPasswordGrant($request->password)) {
            $log = _M(MENSAGEM . 'logs_senha_invalida', (object)['sCampo' => $usuario->login]);
            if (function_exists('db_logsmanual_demais')) {
                db_logsmanual_demais($log, $usuario->getCodigo());
            }

            throw new \BusinessException(_M(MENSAGEM . 'senha_invalida'), 403);
        }

        $token = $this->refreshToken($request->refresh_token);

        $_SESSION['blocked'] = false;

        return $token;
    }

    /**
     * @param Usuario $usuario
     * @return void
     */
    public function logout(Usuario $usuario)
    {
        $usuario->token()->revoke();
        $this->kill();
    }

    /**
     * Destrói os cookies de sessão
     *
     * @return void
     */
    public function kill()
    {
        Session::destroyAll(true);
    }

    /**
     * @param LoginRequest $request
     * @return void
     * @throws \Exception
     */
    private function start(&$request)
    {
        Session::destroyAll();
        $session = new Session('MAIN');
        $session->create()->start();

        if (!empty($_SESSION['DB_id_usuario'])) {
            session_unset();
            session_destroy();
            session_start();
        }

        if (function_exists('db_query')) {
            db_query("select fc_startsession()");
        }

        if (function_exists('db_logsmanual_demais')) {
            db_logsmanual_demais(_M(MENSAGEM . "abrindo_sistema", (object)['sCampo' => $request->username]));
        }

        $request->usuario = (new Usuario())->findForPassport($request->username);
    }

    /**
     * @param Usuario|null $usuario
     * @param string $login
     * @param string $conteudoCaptcha
     * @param int $tentativas
     * @return void
     * @throws SecuriImageException
     */
    private function validaCaptcha($usuario, $login, $conteudoCaptcha = '', $tentativas = 0)
    {
        $utilizaCaptcha = false;

        if ($utilizaCaptcha || $tentativas >= self::MAX_LOGIN_ATTEMPTS) {
            require_once modification('securimage/securimage.php');
            $securiImage = new \Securimage();

            if (!$securiImage->check($conteudoCaptcha)) {
                throw new SecuriImageException(_M(MENSAGEM . "codigo_seguranca_invalido"), 403);
            }
        }
    }

    /**
     * @param Usuario $usuario
     * @throws \Exception
     */
    private function validaUsuario($usuario)
    {
        if (!$usuario->isUsuarioAtivo()) {
            throw new \BusinessException(_M(MENSAGEM . 'usuario_bloqueado'), 403);
        }

        $dataExpira = $usuario->getDataExpiracao();
        if (!empty($dataExpira) && $dataExpira->getTimestamp() < strtotime(date('Y-m-d'))) {
            $log = _M(MENSAGEM . 'logs_data_expira', (object)['sCampo' => $usuario->login]);
            if (function_exists('db_logsmanual_demais')) {
                db_logsmanual_demais($log, $usuario->getCodigo());
            }

            throw new \BusinessException(_M(MENSAGEM . 'data_expira'), 403);
        }

        if (function_exists('db_verifica_ip_banco') && db_verifica_ip_banco($usuario->id_usuario) != '1') {
            throw new \BusinessException(_M(MENSAGEM . 'ip_nao_autorizado'), 403);
        }

        if ($usuario->login === 'dbseller' || $usuario->isAdministrador()) {
            return;
        }

        $rs = db_query("SELECT db21_ativo FROM db_config WHERE prefeitura = TRUE");
        if (!$rs) {
            throw new \DBException('Erro ao verificar se o sistema está liberado! Contate o suporte.');
        }

        $ativo = pg_fetch_result($rs, 0, 0);
        if (pg_num_rows($rs) == 0) {
            $mensagemLogs = _M(MENSAGEM . "logs_login_sem_departamento", (object)['sCampo' => $usuario->login]);
            if (function_exists('db_logsmanual_demais')) {
                db_logsmanual_demais($mensagemLogs);
            }

            throw new \BusinessException(_M(MENSAGEM . "login_sem_departamento"), 403);
        }

        if ($ativo == 3) {
            $mensagem = _M(MENSAGEM . "sistema_desativado");
            if (function_exists('db_logsmanual_demais')) {
                db_logsmanual_demais($mensagem);
            }
            throw new \BusinessException($mensagem, 403);
        } elseif ($ativo == 2) {
            $mensagemLogs = _M(MENSAGEM . "logs_acesso_negado", (object)['sCampo' => $usuario->login]);
            if (function_exists('db_logsmanual_demais')) {
                db_logsmanual_demais($mensagemLogs);
            }

            throw new \BusinessException(_M(MENSAGEM . "acesso_negado"), 403);
        }
    }

    /**
     * @param Usuario $usuario
     * @return void
     * @throws \DBException
     */
    private function validaVersao($usuario)
    {
        $dao = new \cl_db_versao;
        $sql = $dao->sql_query(null, "db30_codversao, db30_codrelease", "db30_codver desc limit 1");
        $rs = $dao->sql_record($sql);

        if ($dao->numrows == 0) {
            $versaoBanco = (object)[
                'db30_codversao' => '1',
                'db30_codrelease' => '1'
            ];
        } else {
            $versaoBanco = \db_utils::fieldsMemory($rs, 0);
        }

        require_once modification('libs/db_acessa.php');

        $db_fonte_codversao = isset($db_fonte_codversao) ? $db_fonte_codversao : '0';
        $db_fonte_codrelease = isset($db_fonte_codrelease) ? $db_fonte_codrelease : '0';

        $versaoDiferente = $versaoBanco->db30_codversao != $db_fonte_codversao;
        $releaseDiferente = $versaoBanco->db30_codrelease != $db_fonte_codrelease;
        if ($versaoDiferente || $releaseDiferente) {
            $opcoes = (object)[
                'sVersaoFonte' => $db_fonte_codversao . $db_fonte_codrelease,
                'sVersaoBanco' => $versaoBanco->db30_codversao . $versaoBanco->db30_codrelease
            ];

            if (function_exists('db_logsmanual_demais')) {
                db_logsmanual_demais(_M(MENSAGEM . 'logs_versao_banco', $opcoes), $usuario->getCodigo());
            }

            throw new \DBException(_M(MENSAGEM . 'versao_banco', $opcoes));
        }
    }

    /**
     * @return void
     * @throws \DBException
     */
    private function buildSession(LoginRequest $request)
    {
        unset($_SESSION['DB_tentativasAcesso']);

        if (!isset($_SESSION["DB_acessado"])) {
            $_SESSION["DB_acessado"] = '0';
        }

        $_SESSION["DB_base"] = env('DB_DATABASE', 'ecidade');
        $_SESSION["DB_NBASE"] = env('DB_DATABASE', 'ecidade');
        $_SESSION["DB_servidor"] = env('DB_HOST', '127.0.0.1');
        $_SESSION["DB_porta"] = env('DB_PORT', '5433');
        $_SESSION["DB_senha"] = env('DB_PASSWORD', 'ecidade');
        $_SESSION["DB_user"] = env('DB_USERNAME', 'ecidade');

        if (app()->isLocal() && !empty($request->DB_HOST) && !empty($request->DB_DATABASE)) {
            $_SESSION["DB_servidor"] = $request->DB_HOST;
            $_SESSION["DB_base"] = $request->DB_DATABASE;
            $_SESSION["DB_NBASE"] = $request->DB_DATABASE;
            $_SESSION["DB_porta"] = $request->DB_PORT;
            $_SESSION["DB_user"] = base64_decode($request->DB_USERNAME);
            $_SESSION["DB_senha"] = base64_decode(\db_stdClass::db_stripTagsJsonSemEscape($request->DB_PASSWORD));
        }

        $_SESSION["DB_login"] = $request->usuario->getLogin();
        $_SESSION["DB_id_usuario"] = $request->usuario->getCodigo();
        $_SESSION["DB_administrador"] = $request->usuario->isAdministrador() ? 1 : 0;
        $_SESSION["DB_desativar_account"] = false;

        $preferenciaUsuario = (new \UsuarioSistema(null, null, $request->usuario))->getPreferenciasUsuario();
        $_SESSION["DB_preferencias_usuario"] = base64_encode(serialize($preferenciaUsuario));
        $_SESSION["DB_ip"] = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '';
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws OAuthServerException
     */
    private function createToken($username, $password)
    {
        $clientId = Registry::get('app.config')->get('api.client.id');
        $clientSecret = Registry::get('app.config')->get('api.client.secret');

        return $this->requestToPassport([
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
            'client_id' => $clientId,
            'client_secret' => $clientSecret
        ]);
    }

    /**
     * @param string $refreshToken
     * @return array
     * @throws OAuthServerException
     */
    private function refreshToken($refreshToken)
    {
        $clientId = Registry::get('app.config')->get('api.client.id');
        $clientSecret = Registry::get('app.config')->get('api.client.secret');

        return $this->requestToPassport([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret
        ]);
    }

    /**
     * @param array $data
     * @return array
     * @throws OAuthServerException
     */
    private function requestToPassport(array $data)
    {
        $request = (new ServerRequest('POST', 'not-important'))->withParsedBody($data);
        $response = $this->authorizationServer->respondToAccessTokenRequest($request, new Response);

        return json_decode($response->getBody()->__toString(), true);
    }
}
