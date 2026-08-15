<?php

namespace App\Domain\Patrimonial\Protocolo\Repository\AnexosCgm;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domain\Configuracao\Helpers\StorageHelper;
use App\Domain\Patrimonial\Protocolo\Model\AnexosCgm;

class AnexosCgmRepository
{

    public function uploadArquivo($arqPath, $arqName)
    {
        try {
            $storage = new StorageHelper;
            $idArquivo = $storage->uploadArquivo($arqPath, null, true, null, null, $arqName);
            return ['sucesso' => $idArquivo];
        } catch (Exception $e) {
            return ['error' => $e];
        }
    }

    public function getArquivosCgm($cgm)
    {
        if ($cgm == null) {
            return 'error';
        }

        $arquivos = DB::select("select a.z34_sequencial, 
		a.z34_arquivo, 
		a.z34_descricao, 
		a.z34_observacao, 
		a.z34_idstorage,
        du.nome,
		TO_CHAR(a.z34_data, 'DD/MM/YYYY HH24:MI') AS z34_data
        from protocolo.anexoscgm a 
		inner join protocolo.cgm c
		on c.z01_numcgm = a.z34_cgm
        inner join configuracoes.db_usuarios du 
        on du.id_usuario = a.z34_usuario 
        where a.z34_cgm = {$cgm}  
        and a.z34_usuarioexclusao is null
        and a.z34_dataexclusao is null");

        return $arquivos;
    }

    public function deleteArquivo($sequencial, $idArq, $idUser)
    {

        try {
            $anexo = AnexosCgm::where('z34_sequencial', $sequencial)
                ->where('z34_idstorage', $idArq)->first();
            $storage = new StorageHelper;
            $status = $storage->deleteArquivo($idArq);
            $anexo->z34_usuarioexclusao = $idUser;
            $anexo->z34_dataexclusao = Carbon::now();
            $anexo->save();
            return ['status' => $status];
        } catch (Exception $e) {
            return ['error' => $e];
        }
    }

    public function saveDadosUpload($cgm, $desc, $obs, $user, $arqName, $idStorage)
    {
        try {
            $anexo = new AnexosCgm();
            $anexo->z34_arquivo = $arqName;
            $anexo->z34_descricao = $desc;
            $anexo->z34_observacao = $obs;
            $anexo->z34_data = Carbon::now();
            $anexo->z34_usuario = $user;
            $anexo->z34_cgm = $cgm;
            $anexo->z34_idstorage = $idStorage;
            $anexo->save();
            return ['sucesso' => $anexo];
        } catch (Exception $e) {
            return ['error' => $e];
        }
    }

    public function updateDadosArquivo($sequencial, $desc, $obs)
    {

        try {
            $anexo = AnexosCgm::where('z34_sequencial', $sequencial)->first();
            $anexo->z34_descricao = $desc;
            $anexo->z34_observacao = $obs;
            $anexo->save();
            return ['sucesso' => true];
        } catch (Exception $e) {
        }
    }

    public function downloadArquivo($idArq)
    {
        $storage = new StorageHelper;
        $storage = $storage->downloadArquivo($idArq);
        $anexo = AnexosCgm::where('z34_idstorage', $idArq)->first();
        $pathArquivo = "tmp/{$anexo->z34_arquivo}";

        if (!rename($storage, $pathArquivo)) {
            return 'erro';
        }

        return ['path' => $pathArquivo, 'nomeArquivo' => $anexo->z34_arquivo];
    }
}
