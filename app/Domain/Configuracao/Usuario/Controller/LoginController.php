<?php

namespace App\Domain\Configuracao\Usuario\Controller;

use App\Domain\Configuracao\Usuario\Exceptions\MaxLoginAttemptsException;
use App\Domain\Configuracao\Usuario\Requests\LoginRequest;
use App\Domain\Configuracao\Usuario\Requests\UnblockRequest;
use App\Domain\Configuracao\Usuario\Services\AuthService;
use App\Domain\Configuracao\Usuario\Services\PasswordResetService;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Core\Exceptions\SecuriImageException;
use App\Http\Controllers\Controller;
use BusinessException;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    private function formatMessage($message)
    {
        if (function_exists('db_utils') && class_exists('db_utils') && method_exists('db_utils', 'isUTF8')) {
            return \db_utils::isUTF8($message) ? $message : utf8_encode($message);
        }
        return mb_check_encoding($message, 'UTF-8') ? $message : utf8_encode($message);
    }

    /**
     * @param AuthService $service
     * @param LoginRequest $request
     * @return DBJsonResponse|JsonResponse
     * @throws Exception
     */
    public function authenticate(AuthService $service, LoginRequest $request)
    {
        try {
            $token = $service->authenticate($request);

            return new DBJsonResponse($token);
        } catch (SecuriImageException $e) {
            return response()->json([
                'code' => 'E_CAPTCHA',
                'error' => true,
                'message' => $this->formatMessage($e->getMessage())
            ], $e->getCode());
        } catch (MaxLoginAttemptsException $e) {
            return response()->json([
                'code' => 'E_MAX_ATTEMPTS',
                'error' => true,
                'tentativas' => $e->getTentativas(),
                'usuario' => $e->getUsername(),
                'message' => $this->formatMessage($e->getMessage())
            ], 403);
        } catch (BusinessException $e) {
            return response()->json([
                'code' => 'E_LOGIN',
                'error' => true,
                'message' => $this->formatMessage($e->getMessage())
            ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
        }
    }

    /**
     * @param AuthService $service
     * @param UnblockRequest $request
     * @return DBJsonResponse
     * @throws Exception
     */
    public function unblock(AuthService $service, UnblockRequest $request)
    {
        $token = $service->unblock($request);

        return new DBJsonResponse($token);
    }

    /**
     * @param AuthService $service
     * @param Request $request
     * @return ResponseFactory|Application|Response
     */
    public function logout(AuthService $service, Request $request)
    {
        $service->logout($request->user());

        return response('dead');
    }

    /**
     * @param AuthService $service
     * @return ResponseFactory|Application|Response
     */
    public function kill(AuthService $service)
    {
        $service->kill();

        return response('dead');
    }

    /**
     * Endpoint para solicitação de recuperação de senha.
     *
     * @param PasswordResetService $service
     * @param Request $request
     * @return JsonResponse
     */
    public function solicitarRecuperacaoSenha(PasswordResetService $service, Request $request)
    {
        try {
            $login = $request->input('login');
            $email = $request->input('email');

            $resultado = $service->solicitarRecuperacao($login, $email);
            $resultado['message'] = $this->formatMessage($resultado['message']);

            return response()->json($resultado, 200);
        } catch (BusinessException $e) {
            return response()->json([
                'error' => true,
                'message' => $this->formatMessage($e->getMessage())
            ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $this->formatMessage('Ocorreu um erro ao processar a solicitação de recuperação de senha.')
            ], 500);
        }
    }

    /**
     * Endpoint para validar se um token de recuperação ainda é válido.
     *
     * @param PasswordResetService $service
     * @param Request $request
     * @return JsonResponse
     */
    public function validarToken(PasswordResetService $service, Request $request)
    {
        try {
            $token = $request->input('token');
            $record = $service->validarToken($token);

            return response()->json([
                'valid' => true,
                'login' => $record->login
            ], 200);
        } catch (BusinessException $e) {
            return response()->json([
                'valid' => false,
                'message' => $this->formatMessage($e->getMessage())
            ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
        } catch (Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => $this->formatMessage('Token inválido ou expirado.')
            ], 500);
        }
    }

    /**
     * Endpoint para salvar a nova senha redefinida.
     *
     * @param PasswordResetService $service
     * @param Request $request
     * @return JsonResponse
     */
    public function redefinirSenha(PasswordResetService $service, Request $request)
    {
        try {
            $token = $request->input('token');
            $novaSenha = $request->input('nova_senha');
            $confirmacaoSenha = $request->input('confirmacao_senha');

            $resultado = $service->redefinirSenha($token, $novaSenha, $confirmacaoSenha);
            $resultado['message'] = $this->formatMessage($resultado['message']);

            return response()->json($resultado, 200);
        } catch (BusinessException $e) {
            return response()->json([
                'error' => true,
                'message' => $this->formatMessage($e->getMessage())
            ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $this->formatMessage('Ocorreu um erro ao redefinir a senha.')
            ], 500);
        }
    }
}
