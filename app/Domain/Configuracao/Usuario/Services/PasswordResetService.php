<?php

namespace App\Domain\Configuracao\Usuario\Services;

use App\Domain\Configuracao\Usuario\Models\Usuario;
use BusinessException;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    /**
     * Solicita a recuperação de senha validando se o e-mail informado
     * coincide estritamente com o cadastro do usuário.
     *
     * @param string $login
     * @param string $emailInformado
     * @return array
     * @throws Exception
     */
    public function solicitarRecuperacao($login, $emailInformado)
    {
        $login = trim($login);
        $emailInformado = strtolower(trim($emailInformado));

        if (empty($login) || empty($emailInformado)) {
            throw new BusinessException("Informe o login e o e-mail cadastrado.", 400);
        }

        if (!filter_var($emailInformado, FILTER_VALIDATE_EMAIL)) {
            throw new BusinessException("E-mail com formato inválido.", 400);
        }

        /** @var Usuario $usuario */
        $usuario = Usuario::where('login', $login)->first();

        if ($usuario && (int)$usuario->usuext === 0) {
            $emailCadastrado = strtolower(trim($usuario->email));
            $emailCgm = $this->buscarEmailCgm($usuario->id_usuario);

            $emailValido = false;
            if (!empty($emailCadastrado) && $emailCadastrado === $emailInformado) {
                $emailValido = true;
            } elseif (!empty($emailCgm) && $emailCgm === $emailInformado) {
                $emailValido = true;
            }

            if ($emailValido) {
                // Invalida tokens anteriores não utilizados
                DB::table('configuracoes.db_usuariorecuperasenha')
                    ->where('id_usuario', $usuario->id_usuario)
                    ->update(['used' => true]);

                // Gera token criptograficamente seguro
                $token = bin2hex(openssl_random_pseudo_bytes(32));
                $expiresAt = Carbon::now()->addMinutes(30);

                DB::table('configuracoes.db_usuariorecuperasenha')->insert([
                    'id_usuario' => $usuario->id_usuario,
                    'login' => $usuario->login,
                    'email' => $emailInformado,
                    'token' => $token,
                    'expires_at' => $expiresAt->toDateTimeString(),
                    'used' => false,
                    'created_at' => Carbon::now()->toDateTimeString()
                ]);

                $baseUrl = env('APP_URL');
                if (empty($baseUrl) && isset($_SERVER['HTTP_HOST'])) {
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                    $baseUrl = $protocol . $_SERVER['HTTP_HOST'];
                }
                $linkRecuperacao = rtrim($baseUrl, '/') . '/recuperar_senha.php?token=' . $token;

                $this->enviarEmailRecuperacao($emailInformado, $usuario->nome, $linkRecuperacao);

                if (function_exists('db_logsmanual_demais')) {
                    db_logsmanual_demais("Solicitacao de recuperacao de senha para o usuario: " . $usuario->login, $usuario->id_usuario);
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Se os dados informados coincidirem com o cadastro, um link seguro de recuperação foi enviado para o seu e-mail (válido por 30 minutos).'
        ];
    }

    /**
     * Valida se um token de recuperação existe, não foi usado e não expirou.
     *
     * @param string $token
     * @return object
     * @throws BusinessException
     */
    public function validarToken($token)
    {
        $token = trim($token);
        if (empty($token)) {
            throw new BusinessException("Token de recuperação não fornecido.", 400);
        }

        $record = DB::table('configuracoes.db_usuariorecuperasenha')
            ->where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now()->toDateTimeString())
            ->first();

        if (!$record) {
            throw new BusinessException("Link de recuperação inválido ou expirado. Por favor, faça uma nova solicitação.", 400);
        }

        return $record;
    }

    /**
     * Redefine a senha do usuário utilizando o token validado.
     *
     * @param string $token
     * @param string $novaSenha
     * @param string $confirmacaoSenha
     * @return array
     * @throws BusinessException
     */
    public function redefinirSenha($token, $novaSenha, $confirmacaoSenha)
    {
        $record = $this->validarToken($token);

        if (empty($novaSenha) || strlen($novaSenha) < 6) {
            throw new BusinessException("A nova senha deve ter no mínimo 6 caracteres.", 400);
        }

        if ($novaSenha !== $confirmacaoSenha) {
            throw new BusinessException("A confirmação da senha não confere com a nova senha.", 400);
        }

        /** @var Usuario $usuario */
        $usuario = Usuario::find($record->id_usuario);
        if (!$usuario) {
            throw new BusinessException("Usuário não encontrado no sistema.", 404);
        }

        if (function_exists('modification')) {
            require_once \modification('model/configuracao/Encriptacao.model.php');
        } else {
            require_once '/var/www/html/model/configuracao/Encriptacao.model.php';
        }

        // Atualiza a senha no padrão DBSeller sha1(md5())
        $usuario->senha = \Encriptacao::encriptaSenha($novaSenha);
        $usuario->usuarioativo = 1; // Reativa / desbloqueia usuário
        $usuario->save();

        // Invalida o token utilizado
        DB::table('configuracoes.db_usuariorecuperasenha')
            ->where('id', $record->id)
            ->update(['used' => true]);

        // Zera contadores de tentativas falhas
        Cache::forget('login_attempts_' . strtolower(trim($usuario->login)));
        if (isset($_SESSION['DB_tentativasAcesso'])) {
            unset($_SESSION['DB_tentativasAcesso']);
        }

        if (function_exists('db_logsmanual_demais')) {
            db_logsmanual_demais("Senha redefinida com sucesso via link de recuperacao para o usuario: " . $usuario->login, $usuario->id_usuario);
        }

        return [
            'success' => true,
            'message' => 'Senha alterada com sucesso! Seu usuário foi reativado e você já pode acessar o e-Cidade.'
        ];
    }

    /**
     * Busca o e-mail no CGM vinculado ao usuário, se houver.
     *
     * @param int $idUsuario
     * @return string|null
     */
    private function buscarEmailCgm($idUsuario)
    {
        try {
            $cgmData = DB::table('configuracoes.db_usuacgm')
                ->join('cgm', 'cgm.z01_numcgm', '=', 'configuracoes.db_usuacgm.cgmlogin')
                ->where('configuracoes.db_usuacgm.id_usuario', $idUsuario)
                ->select('cgm.z01_email')
                ->first();

            if ($cgmData && !empty($cgmData->z01_email)) {
                return strtolower(trim($cgmData->z01_email));
            }
        } catch (Exception $e) {
            // Ignora erro de busca em caso de ausência de vínculo
        }

        return null;
    }

    /**
     * Envia o e-mail formatado de recuperação de senha.
     *
     * @param string $destinatario
     * @param string $nomeUsuario
     * @param string $linkRecuperacao
     */
    private function enviarEmailRecuperacao($destinatario, $nomeUsuario, $linkRecuperacao)
    {
        $assunto = "e-Cidade - Recuperação de Senha de Acesso";
        $mensagemHtml = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$assunto}</title>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
                .header { background: #1f4b7a; color: #ffffff; padding: 20px; text-align: center; }
                .header h2 { margin: 0; font-size: 22px; }
                .content { padding: 30px 25px; line-height: 1.6; }
                .btn { display: inline-block; padding: 12px 24px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
                .footer { background: #f8fafc; padding: 15px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
                .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin-top: 20px; font-size: 13px; color: #92400e; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>e-Cidade CPD-MUNICIPAL</h2>
                </div>
                <div class='content'>
                    <p>Olá, <strong>{$nomeUsuario}</strong>,</p>
                    <p>Recebemos uma solicitação para redefinir a senha de acesso da sua conta no sistema e-Cidade.</p>
                    <p>Para criar uma nova senha, clique no botão abaixo:</p>
                    <p style='text-align: center;'>
                        <a href='{$linkRecuperacao}' class='btn' target='_blank'>Redefinir Minha Senha</a>
                    </p>
                    <p>Ou copie e cole o seguinte endereço em seu navegador:</p>
                    <p style='word-break: break-all; font-size: 13px; color: #2563eb;'>{$linkRecuperacao}</p>
                    <div class='warning'>
                        <strong>Atenção:</strong> Este link é de uso único e expira em <strong>30 minutos</strong>. Se você não solicitou a alteração da sua senha, desconsidere esta mensagem.
                    </div>
                </div>
                <div class='footer'>
                    Este e-mail foi gerado automaticamente pelo sistema e-Cidade. Por favor, não responda.
                </div>
            </div>
        </body>
        </html>
        ";

        // Tenta enviar via Laravel Mail
        try {
            Mail::send([], [], function ($message) use ($destinatario, $assunto, $mensagemHtml) {
                $message->to($destinatario)
                    ->subject($assunto)
                    ->setBody($mensagemHtml, 'text/html');
            });
            return;
        } catch (Exception $e) {
            // Se Laravel Mail não estiver configurado, tenta a classe legada Smtp
        }

        try {
            if (file_exists('libs/smtp.class.php')) {
                if (function_exists('modification')) {
                    require_once \modification('libs/smtp.class.php');
                } else {
                    require_once '/var/www/html/libs/smtp.class.php';
                }
                $smtp = new \Smtp();
                $smtp->html = true;
                $emailRemetente = env('MAIL_FROM_ADDRESS', 'naoresponda@cpd-municipal.com.br');
                $smtp->Send($destinatario, $emailRemetente, $assunto, $mensagemHtml);
                return;
            }
        } catch (Exception $e) {
            // Fallback para mail nativo do PHP
        }

        @mail($destinatario, $assunto, $mensagemHtml, "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n");
    }
}
