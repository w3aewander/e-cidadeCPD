<?php

namespace App\Domain\Educacao\Manuais\Controllers;

use App\Domain\Configuracao\Usuario\Models\Usuario;
use App\Domain\Educacao\Manuais\Models\ManualEducacao;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManuaisController extends Controller
{
    /**
     * Garante que strings, arrays e objetos estejam estritamente em UTF-8 para JSON
     */
    protected function toUtf8($data)
    {
        if (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
            }
            return $data;
        }

        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? $this->toUtf8($key) : $key;
                $cleaned[$cleanKey] = $this->toUtf8($value);
            }
            return $cleaned;
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $this->toUtf8($data->toArray());
            }
            $cleaned = new \stdClass();
            foreach (get_object_vars($data) as $key => $value) {
                $cleanKey = is_string($key) ? $this->toUtf8($key) : $key;
                $cleaned->$cleanKey = $this->toUtf8($value);
            }
            return $cleaned;
        }

        return $data;
    }

    /**
     * Helper de resposta JSON protegida contra erro de encoding UTF-8
     */
    protected function jsonResponse($data, $status = 200)
    {
        return response()->json($this->toUtf8($data), $status, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Verifica se o usuário atual é administrador
     */
    protected function isUsuarioAdministrador($idUsuario)
    {
        if (empty($idUsuario)) {
            return false;
        }

        if ((string)$idUsuario === '1') {
            return true;
        }

        if (function_exists('db_getsession') && \db_getsession('DB_administrador') == 1) {
            return true;
        }

        if (isset($_SESSION['DB_administrador']) && $_SESSION['DB_administrador'] == 1) {
            return true;
        }

        $usuario = Usuario::find($idUsuario);
        if ($usuario && ($usuario->isAdministrador() || $usuario->login === 'dbseller' || $usuario->login === 'cpdmunicipal' || $usuario->id_usuario == 1)) {
            return true;
        }

        return false;
    }

    /**
     * Obtém o ID do usuário da sessão legada do e-Cidade ou do Auth
     */
    protected function getUsuarioId()
    {
        if (function_exists('db_getsession') && \db_getsession('DB_id_usuario')) {
            return (int)\db_getsession('DB_id_usuario');
        }

        if (isset($_SESSION['DB_id_usuario'])) {
            return (int)$_SESSION['DB_id_usuario'];
        }

        return auth()->id() ?: 1;
    }

    /**
     * View principal
     */
    public function index()
    {
        $usuarioId = $this->getUsuarioId();
        $isAdmin = $this->isUsuarioAdministrador($usuarioId);

        $usuario = Usuario::find($usuarioId);
        $usuarioNome = $usuario ? $usuario->nome : 'Usuário';

        return view('educacao.manuais.index', [
            'isAdmin' => $isAdmin,
            'usuarioId' => $usuarioId,
            'usuarioNome' => $usuarioNome
        ]);
    }

    /**
     * Listagem de manuais ativos
     */
    public function listar(Request $request)
    {
        $manuais = ManualEducacao::with('usuario:id_usuario,nome,login')
            ->ativo()
            ->orderBy('id', 'desc')
            ->get();

        $manuaisFormatados = $manuais->map(function ($manual) {
            return [
                'id' => (int)$manual->id,
                'titulo' => (string)$manual->titulo,
                'descricao' => (string)$manual->descricao,
                'nome_arquivo_original' => (string)$manual->nome_arquivo_original,
                'tamanho_bytes' => (int)$manual->tamanho_bytes,
                'tamanho_formatado' => $this->formatBytes($manual->tamanho_bytes),
                'usuario_nome' => $manual->usuario ? (string)$manual->usuario->nome : 'Sistema',
                'criado_em' => $manual->criado_em ? Carbon::parse($manual->criado_em)->format('d/m/Y H:i') : null,
                'url_visualizar' => url('web/educacao/manuais/visualizar/' . $manual->id),
                'url_download' => url('web/educacao/manuais/download/' . $manual->id),
            ];
        });

        $usuarioId = $this->getUsuarioId();
        $isAdmin = $this->isUsuarioAdministrador($usuarioId);

        return $this->jsonResponse([
            'status' => 'success',
            'is_admin' => $isAdmin,
            'data' => $manuaisFormatados
        ]);
    }

    /**
     * Upload de novo manual em PDF (Apenas Administrador)
     */
    public function salvar(Request $request)
    {
        $usuarioId = $this->getUsuarioId();
        $isAdmin = $this->isUsuarioAdministrador($usuarioId);

        if (!$isAdmin) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Acesso negado. Apenas usuários administradores podem enviar manuais.'
            ], 403);
        }

        $titulo = trim((string)$request->input('titulo', ''));
        if (empty($titulo)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'O título do documento é obrigatório.'
            ], 422);
        }

        if (!$request->hasFile('arquivo')) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Nenhum arquivo PDF foi selecionado ou recebido.'
            ], 422);
        }

        $file = $request->file('arquivo');

        if (!$file->isValid()) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Falha no envio do arquivo: ' . $file->getErrorMessage()
            ], 422);
        }

        $nomeOriginal = (string)$file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        
        if ($ext !== 'pdf') {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Formato inválido! O arquivo deve ser exclusivamente um documento PDF (.pdf).'
            ], 422);
        }

        $tamanhoBytes = (int)$file->getSize();
        if ($tamanhoBytes > 209715200) { // 200MB
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'O arquivo PDF excede o tamanho máximo permitido de 200MB.'
            ], 422);
        }

        try {
            $nomeSalvo = md5(uniqid(rand(), true)) . '.pdf';
            $caminhoDiretorio = storage_path('app/manuais/educacao');
            
            if (!is_dir($caminhoDiretorio)) {
                mkdir($caminhoDiretorio, 0777, true);
            }
            @chmod($caminhoDiretorio, 0777);

            $caminhoRelativo = 'manuais/educacao/' . $nomeSalvo;
            $file->move($caminhoDiretorio, $nomeSalvo);

            $caminhoArquivoFinal = $caminhoDiretorio . '/' . $nomeSalvo;
            @chmod($caminhoArquivoFinal, 0666);

            $descricao = $request->input('descricao') ? trim((string)$request->input('descricao')) : null;

            $manual = ManualEducacao::create([
                'titulo' => $titulo,
                'descricao' => $descricao,
                'caminho_arquivo' => $caminhoRelativo,
                'nome_arquivo_original' => $nomeOriginal,
                'tamanho_bytes' => $tamanhoBytes,
                'mime_type' => 'application/pdf',
                'id_usuario' => $usuarioId,
                'criado_em' => Carbon::now(),
                'ativo' => true
            ]);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Manual enviado com sucesso!',
                'data' => [
                    'id' => (int)$manual->id,
                    'titulo' => (string)$manual->titulo,
                    'descricao' => (string)$manual->descricao,
                    'nome_arquivo_original' => (string)$manual->nome_arquivo_original,
                    'tamanho_bytes' => (int)$manual->tamanho_bytes,
                    'tamanho_formatado' => $this->formatBytes($manual->tamanho_bytes),
                    'criado_em' => Carbon::parse($manual->criado_em)->format('d/m/Y H:i'),
                    'url_visualizar' => url('web/educacao/manuais/visualizar/' . $manual->id),
                    'url_download' => url('web/educacao/manuais/download/' . $manual->id),
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar manual de educação: ' . $e->getMessage());
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Erro interno ao salvar o manual: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir manual (Apenas Administrador)
     */
    public function excluir($id)
    {
        $usuarioId = $this->getUsuarioId();
        $isAdmin = $this->isUsuarioAdministrador($usuarioId);

        if (!$isAdmin) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Acesso negado. Apenas usuários administradores podem excluir manuais.'
            ], 403);
        }

        $manual = ManualEducacao::find($id);

        if (!$manual) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Manual não encontrado.'
            ], 404);
        }

        $manual->ativo = false;
        $manual->save();

        return $this->jsonResponse([
            'status' => 'success',
            'message' => 'Manual excluído com sucesso.'
        ]);
    }

    /**
     * Visualizar PDF inline no navegador / modal
     */
    public function visualizar($id)
    {
        $manual = ManualEducacao::ativo()->find($id);

        if (!$manual) {
            abort(404, 'Manual não encontrado.');
        }

        $caminhoCompleto = storage_path('app/' . $manual->caminho_arquivo);

        if (!file_exists($caminhoCompleto)) {
            abort(404, 'Arquivo físico do manual não encontrado.');
        }

        return response()->file($caminhoCompleto, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($manual->nome_arquivo_original) . '"'
        ]);
    }

    /**
     * Download do arquivo PDF
     */
    public function download($id)
    {
        $manual = ManualEducacao::ativo()->find($id);

        if (!$manual) {
            abort(404, 'Manual não encontrado.');
        }

        $caminhoCompleto = storage_path('app/' . $manual->caminho_arquivo);

        if (!file_exists($caminhoCompleto)) {
            abort(404, 'Arquivo físico do manual não encontrado.');
        }

        return response()->download($caminhoCompleto, $manual->nome_arquivo_original, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * Formatação legível de bytes
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
