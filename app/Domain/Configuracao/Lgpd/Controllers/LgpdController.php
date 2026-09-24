<?php

namespace App\Domain\Configuracao\Lgpd\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LgpdController extends Controller
{
    /**
     * Retorna os termos de privacidade e consentimento ativos
     */
    public function obterTermos(Request $request)
    {
        $termos = array(
            'versao' => '1.0.0',
            'data_atualizacao' => '2026-09-01',
            'titulo' => 'Termo de Privacidade e Tratamento de Dados Pessoais (LGPD)',
            'conteudo' => 'Em conformidade com a Lei Geral de Proteção de Dados Pessoais (Lei Federal nº 13.709/2018), a Administração Pública Municipal assegura a privacidade, transparência e segurança no tratamento dos dados pessoais de cidadãos, servidores e alunos.',
            'finalidades' => array(
                'Execução de políticas públicas e cumprimento de obrigação legal ou regulatória.',
                'Processamento de matrículas, diários de classe e registros escolares.',
                'Emissão de guias, certidões e gestão tributária/patrimonial.',
                'Gestão de recursos humanos e folha de pagamento.'
            )
        );

        return new DBJsonResponse($termos);
    }

    /**
     * Registra o consentimento ou ciência do titular de dados
     */
    public function registrarConsentimento(Request $request)
    {
        $cpf = preg_replace('/[^0-9]/', '', $request->input('cpf', ''));
        $nome = $request->input('nome', '');
        $aceite = (bool) $request->input('aceite', true);
        $versaoTermo = $request->input('versao_termo', '1.0.0');

        $idUsuario = function_exists('db_getsession') ? db_getsession('DB_id_usuario', false) : 1;
        if (!$idUsuario) {
            $idUsuario = 1;
        }

        $registro = array(
            'id' => uniqid('lgpd_'),
            'cpf' => $cpf ? $cpf : '00000000000',
            'nome' => $nome,
            'aceite' => $aceite,
            'versao_termo' => $versaoTermo,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'usuario_sessao' => $idUsuario,
            'data_registro' => date('Y-m-d H:i:s')
        );

        $this->salvarLogConsentimento($registro);

        return response()->json(array(
            'status' => true,
            'message' => 'Consentimento registrado com sucesso em conformidade com a LGPD.',
            'dados' => $registro
        ));
    }

    /**
     * Consulta os dados pessoais vinculados a um titular (Direito de Acesso - Art. 18 LGPD)
     */
    public function consultarTitular($cpf)
    {
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpfLimpo) !== 11) {
            return response()->json(array('status' => false, 'message' => 'CPF inválido.'), 400);
        }

        // Busca registros no CGM do e-Cidade (compatível com banco PostgreSQL)
        $cgm = null;
        try {
            $cgm = DB::table('cgm')
                ->select(array('z01_numcgm', 'z01_nome', 'z01_cgccpf', 'z01_munic', 'z01_uf', 'z01_email', 'z01_telef'))
                ->where('z01_cgccpf', $cpfLimpo)
                ->first();
        } catch (\Exception $e) {
            // Em caso de tabela indisponível, fallback resiliente
            $cgm = null;
        }

        // Registro de log de auditoria de consulta LGPD
        $this->registrarAuditoriaConsulta($cpfLimpo, 'Consulta aos Direitos do Titular (Art. 18)');

        return response()->json(array(
            'status' => true,
            'cpf' => $this->mascararCpf($cpfLimpo),
            'titular' => $cgm ? $cgm : array('mensagem' => 'Titular não localizado ou sem registros sensíveis.'),
            'direitos_garantidos' => array(
                'Confirmacao da existencia de tratamento',
                'Acesso aos dados',
                'Correcao de dados incompletos, inexatos ou desatualizados',
                'Anonimizacao, bloqueio ou eliminacao de dados desnecessarios'
            )
        ));
    }

    /**
     * Mascara CPF para exibicao protegida (LGPD Privacy by Default)
     */
    private function mascararCpf($cpf)
    {
        if (strlen($cpf) !== 11) {
            return '***.***.***-**';
        }
        return substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2);
    }

    /**
     * Persistência de logs e auditoria LGPD
     */
    private function salvarLogConsentimento($registro)
    {
        $dir = storage_path('app/lgpd');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0777, true, true);
        }

        $arquivo = $dir . '/consentimentos.json';
        $registros = array();
        if (file_exists($arquivo)) {
            $registros = json_decode(file_get_contents($arquivo), true);
            if (!is_array($registros)) {
                $registros = array();
            }
        }

        $registros[] = $registro;
        file_put_contents($arquivo, json_encode($registros, JSON_PRETTY_PRINT));
    }

    private function registrarAuditoriaConsulta($cpf, $motivo)
    {
        $dir = storage_path('app/lgpd');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0777, true, true);
        }

        $idUsuario = function_exists('db_getsession') ? db_getsession('DB_id_usuario', false) : 1;
        $arquivo = $dir . '/auditoria_acesso.log';
        $linha = date('Y-m-d H:i:s') . ' | USER: ' . ($idUsuario ?: 1) . ' | CPF_CONSULTADO: ' . $cpf . ' | MOTIVO: ' . $motivo . PHP_EOL;
        @file_put_contents($arquivo, $linha, FILE_APPEND);
    }
}

