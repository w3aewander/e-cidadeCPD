<?php

namespace App\Domain\Educacao\Escola\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ManualEducacaoController extends Controller
{
    /**
     * Caminho relativo padrao para o storage dos manuais
     */
    private $storageRelativo = 'manuais/educacao';

    /**
     * Metodo de fallback resiliente para localizacao fisica do arquivo
     * Varre multiplos caminhos de storage para garantir compatibilidade
     *
     * @param string $caminhoRelativo
     * @return string|null
     */
    public function getCaminhoFisico($caminhoRelativo)
    {
        $caminhos = array(
            storage_path('app/' . $caminhoRelativo),
            '/var/www/html/storage/app/' . $caminhoRelativo,
            '/var/www/html/e-cidadeCPD/storage/app/' . $caminhoRelativo,
            '/home/administrador/containers/web/storage/app/' . $caminhoRelativo,
            public_path('manuais/' . basename($caminhoRelativo)),
            storage_path($caminhoRelativo)
        );

        foreach ($caminhos as $caminho) {
            if (file_exists($caminho) && is_file($caminho)) {
                return $caminho;
            }
        }

        return null;
    }

    /**
     * Renderiza a view principal de gerenciamento de manuais
     */
    public function index(Request $request)
    {
        $manuais = $this->carregarManuais();
        return view('educacao.escola.manuais.index', array(
            'manuais' => $manuais
        ));
    }

    /**
     * Listagem de manuais via API JSON
     */
    public function listar()
    {
        $manuais = $this->carregarManuais();
        return new DBJsonResponse($manuais);
    }

    /**
     * Upload e persistencia fisica resiliente do manual
     */
    public function salvar(Request $request)
    {
        $titulo = $request->input('titulo');
        $descricao = $request->input('descricao', '');
        $modulo = $request->input('modulo', 'Educação');

        if (!$request->hasFile('arquivo')) {
            return response()->json(array(
                'status' => false,
                'message' => 'Nenhum arquivo enviado.'
            ), 400);
        }

        $file = $request->file('arquivo');
        if (!$file->isValid()) {
            return response()->json(array(
                'status' => false,
                'message' => 'Arquivo inválido ou corrompido.'
            ), 400);
        }

        $extensao = strtolower($file->getClientOriginalExtension());
        $extensoesPermitidas = array('pdf', 'doc', 'docx', 'odt', 'png', 'jpg', 'jpeg');
        if (!in_array($extensao, $extensoesPermitidas)) {
            return response()->json(array(
                'status' => false,
                'message' => 'Extensão de arquivo não permitida. Permitidas: ' . implode(', ', $extensoesPermitidas)
            ), 400);
        }

        $nomeOriginal = $file->getClientOriginalName();
        $id = uniqid('manual_');
        $nomeArquivo = $id . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $nomeOriginal);

        // Diretorios de destino para gravacao resiliente
        $diretoriosDestino = array(
            storage_path('app/' . $this->storageRelativo),
            '/var/www/html/storage/app/' . $this->storageRelativo
        );

        foreach ($diretoriosDestino as $dir) {
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0777, true, true);
            }
        }

        $caminhoPrincipal = storage_path('app/' . $this->storageRelativo . '/' . $nomeArquivo);
        $file->move(dirname($caminhoPrincipal), $nomeArquivo);

        // Ajustar permissoes para www-data
        if (file_exists($caminhoPrincipal)) {
            @chmod($caminhoPrincipal, 0666);
            @chown($caminhoPrincipal, 'www-data');
            @chgrp($caminhoPrincipal, 'www-data');
        }

        // Espelhar copia fisica para dual storage se o caminho secundario existir
        $caminhoSecundario = '/var/www/html/e-cidadeCPD/storage/app/' . $this->storageRelativo . '/' . $nomeArquivo;
        if (File::isDirectory(dirname($caminhoSecundario))) {
            @copy($caminhoPrincipal, $caminhoSecundario);
            @chmod($caminhoSecundario, 0666);
        }

        // Obter usuario da sessao legada sem disparar alerts JS (segundo parametro false)
        $idUsuario = function_exists('db_getsession') ? db_getsession('DB_id_usuario', false) : 1;
        if (!$idUsuario) {
            $idUsuario = 1;
        }

        // Salvar metadados no index de manuais
        $novoManual = array(
            'id' => $id,
            'titulo' => $titulo ? $titulo : $nomeOriginal,
            'descricao' => $descricao,
            'modulo' => $modulo,
            'arquivo_nome' => $nomeOriginal,
            'arquivo_path' => $this->storageRelativo . '/' . $nomeArquivo,
            'tamanho' => file_exists($caminhoPrincipal) ? filesize($caminhoPrincipal) : 0,
            'extensao' => $extensao,
            'criado_por' => $idUsuario,
            'data_criacao' => date('Y-m-d H:i:s')
        );

        $this->salvarMetadadosManual($novoManual);

        if ($request->wantsJson() || $request->ajax()) {
            return new DBJsonResponse($novoManual);
        }

        return redirect()->back()->with('success', 'Manual cadastrado com sucesso!');
    }

    /**
     * Visualizacao inline de manual (PDF/Imagem)
     */
    public function visualizar($id)
    {
        $manual = $this->buscarManualPorId($id);
        if (!$manual) {
            return response('Manual não encontrado.', 404);
        }

        $caminhoFisico = $this->getCaminhoFisico($manual['arquivo_path']);
        if (!$caminhoFisico || !file_exists($caminhoFisico)) {
            return response('Arquivo físico do manual não encontrado no storage.', 404);
        }

        $mimeType = File::mimeType($caminhoFisico);
        $headers = array(
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $manual['arquivo_nome'] . '"'
        );

        return Response::make(file_get_contents($caminhoFisico), 200, $headers);
    }

    /**
     * Download do arquivo do manual
     */
    public function download($id)
    {
        $manual = $this->buscarManualPorId($id);
        if (!$manual) {
            return response('Manual não encontrado.', 404);
        }

        $caminhoFisico = $this->getCaminhoFisico($manual['arquivo_path']);
        if (!$caminhoFisico || !file_exists($caminhoFisico)) {
            return response('Arquivo físico do manual não encontrado no storage.', 404);
        }

        return response()->download($caminhoFisico, $manual['arquivo_nome']);
    }

    /**
     * Exclui o manual e remove os arquivos fisicos
     */
    public function excluir($id)
    {
        $manual = $this->buscarManualPorId($id);
        if (!$manual) {
            return response()->json(array('status' => false, 'message' => 'Manual não encontrado.'), 404);
        }

        $caminhoFisico = $this->getCaminhoFisico($manual['arquivo_path']);
        if ($caminhoFisico && file_exists($caminhoFisico)) {
            @unlink($caminhoFisico);
        }

        $this->removerMetadadosManual($id);

        return response()->json(array('status' => true, 'message' => 'Manual excluído com sucesso.'));
    }

    /**
     * Gerenciamento de persistencia de metadados em arquivo JSON seguro de configuracao
     */
    private function getMetadadosPath()
    {
        $dir = storage_path('app/manuais');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0777, true, true);
        }
        return $dir . '/manuais_educacao_metadata.json';
    }

    private function carregarManuais()
    {
        $path = $this->getMetadadosPath();
        if (!file_exists($path)) {
            return array();
        }

        $conteudo = file_get_contents($path);
        $dados = json_decode($conteudo, true);
        return is_array($dados) ? $dados : array();
    }

    private function salvarMetadadosManual($novoManual)
    {
        $manuais = $this->carregarManuais();
        $manuais[] = $novoManual;
        file_put_contents($this->getMetadadosPath(), json_encode($manuais, JSON_PRETTY_PRINT));
    }

    private function buscarManualPorId($id)
    {
        $manuais = $this->carregarManuais();
        foreach ($manuais as $m) {
            if (isset($m['id']) && $m['id'] === $id) {
                return $m;
            }
        }
        return null;
    }

    private function removerMetadadosManual($id)
    {
        $manuais = $this->carregarManuais();
        $filtrados = array();
        foreach ($manuais as $m) {
            if (isset($m['id']) && $m['id'] !== $id) {
                $filtrados[] = $m;
            }
        }
        file_put_contents($this->getMetadadosPath(), json_encode($filtrados, JSON_PRETTY_PRINT));
    }
}

