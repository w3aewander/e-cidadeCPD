<?php

namespace App\Domain\Patrimonial\Protocolo\Services\AnexosCgm;

use App\Domain\Patrimonial\Protocolo\Repository\AnexosCgm\AnexosCgmRepository;

class AnexosCgmService
{

    public function processarArquivo($cgm, $desc, $obs, $user, $file)
    {
        $anexoRepository = new AnexosCgmRepository();

        $arqPath = $file->getRealPath();
        $arqName = $file->getClientOriginalName();
        $upload = null;
        $saveInfo = null;
        if ($arqPath != null && $arqName != null) {
            $upload = $anexoRepository->uploadArquivo($arqPath, $arqName);
            if (isset($upload["sucesso"]) && $upload["sucesso"] != null) {
                $saveInfo = $anexoRepository->saveDadosUpload(
                    $cgm,
                    $desc,
                    $obs,
                    $user,
                    $arqName,
                    $upload["sucesso"]
                );
            }
        }

        return ['upload' => $upload, 'saveInfo' => $saveInfo];
    }

    public function getArquivosCgm($cgm)
    {
        $anexoRepository = new AnexosCgmRepository();
        return $anexoRepository->getArquivosCgm($cgm);
    }

    public function deleteArquivo($sequencial, $idArq, $idUser)
    {
        $anexoRepository = new AnexosCgmRepository();
        return $anexoRepository->deleteArquivo($sequencial, $idArq, $idUser);
    }

    public function updateDadosArquivo($sequencial, $desc, $obs)
    {
        $anexoRepository = new AnexosCgmRepository();
        return $anexoRepository->updateDadosArquivo($sequencial, $desc, $obs);
    }

    public function downloadArquivo($idArq)
    {
        $anexoRepository = new AnexosCgmRepository();
        return $anexoRepository->downloadArquivo($idArq);
    }
}
