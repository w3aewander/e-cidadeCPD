<?php

namespace ECidade\RecursosHumanos\RH\Portaria;

use cl_db_geradorrelatoriotemplate;
use dbGeradorRelatorio;
use DBLargeObject;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;
use SimpleXMLElement;

class PortariaPDF
{
    /**
     * @var SimpleXMLElement
     */
    public $xml;
    /**
     * @var string
     */
    private $nomeRelatorio;
    /**
     * @var dbGeradorRelatorio
     */
    private $relatorio;

    const RELATORIO_ORIGEM_SQL = 1;
    const RELATORIO_ORIGEM_VIEW = 2;
    /**
     * @var string
     */
    private $municipio;
    /**
     * @var []
     */
    private $filtros = [];

    public function __construct($codigoRelatorio, $filtros = [])
    {
        $this->relatorio = new dbGeradorRelatorio($codigoRelatorio);

        $dao = new \cl_db_relatorio();
        $rs = db_query($dao->sql_query_file($codigoRelatorio));
        if (!$rs) {
            throw new \Exception("Erro ao buscar o relatório. \n" . pg_last_error());
        }

        $dados = \db_utils::fieldsMemory($rs, 0);
        $this->xml = new SimpleXMLElement($dados->db63_xmlestruturarel);
        $this->nomeRelatorio = $this->xml->Propriedades->attributes()[1]->__toString();

        $sql = "select munic from db_config where codigo = " . db_getsession("DB_instit");
        $resultMunicipio = db_query($sql);
        $this->municipio = pg_fetch_array($resultMunicipio)[0];

        $this->filtros = $filtros;
    }

    public function emitir($uuid)
    {
        $dados = $this->buscarDados();
        $template = $this->getTemplate($this->relatorio->getCodRelatorio());
        /**
         * Carregar URL do processo eletrônico
         */
        $dotenv = new \Dotenv\Dotenv('./');
        $dotenv->load();
        $url_autenticidade = env('BASE_URL_AUTENTICIDADE');
        $fileQRCode = "tmp/qrcode_{$uuid}.png";
        \PHPQRCode\QRcode::png("{$url_autenticidade}/documento/{$uuid}", $fileQRCode, 'L', 5, 1);
        $template->setImageValue('imagem-qrcode', $fileQRCode);
        $template->setValue("db_municinst", $this->municipio);
        if (isset($dados["rh01_admiss"])) {
            $dados["rh01_admiss"] = date('d/m/Y', strtotime($dados["rh01_admiss"]));
        }
        if (isset($dados["h16_dtconc"])) {
            $dados["h16_dtconc"] = date('d/m/Y', strtotime($dados["h16_dtconc"]));
        }
        if (isset($dados["h31_dtportaria"])) {
            $dados["h31_dtportaria"] = date('d/m/Y', strtotime($dados["h31_dtportaria"]));
        }
        foreach ($dados as $key => $valor) {
            $template->setValue($key, $valor);
        }
        $nome = \DBString::slugify($this->nomeRelatorio);

        $nomeDocumento = sprintf('%s-%s', $nome, time());
        $filePathDoc = sprintf('%stmp/%s.docx', ECIDADE_PATH, $nomeDocumento);
        $filePathPdf = sprintf('%stmp/', ECIDADE_PATH);
        $template->saveAs($filePathDoc);

        convertToPdf($filePathDoc, $filePathPdf);

        return sprintf('%stmp/%s.pdf', ECIDADE_REQUEST_PATH, $nomeDocumento);
    }

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function getTemplate($idRelatorio)
    {
        $dao = new cl_db_geradorrelatoriotemplate();

        $where = "db15_db_relatorio = {$idRelatorio}";
        $rsDocumento = db_query($dao->sql_query_file(null, "db15_documento", null, $where));

        $oid = \db_utils::fieldsMemory($rsDocumento, 0)->db15_documento;
        $filepath = sprintf('%s/tmp/documento_%s.docx', ECIDADE_PATH, time());
        DBLargeObject::leitura($oid, $filepath);

        $template = new TemplateProcessor($filepath);

        unlink($filepath);
        return $template;
    }

    private function executaSql($sql)
    {
        $rs = db_query($sql);
        if (!$rs) {
            throw new \Exception("Erro ao executar a query do relatório.\n " . pg_last_error());
        }

        return pg_fetch_assoc($rs);
    }

    private function buscarDados()
    {
        $dados = $this->executaSql($this->parseSql());
        return $dados;
    }

    public function parseSql()
    {
        $sql = $this->xml->Consultas->Consulta->From->__toString();
        if ($this->relatorio->getOrigemRelatorio() == self::RELATORIO_ORIGEM_VIEW) {
            $sql = "select * from {$sql}";

            $where = [];
            foreach ($this->relatorio->aFiltros as $aFiltros) {
                foreach ($aFiltros as $cond => $oFiltro) {
                    foreach ($this->filtros as $filtro) {
                        $campo = $oFiltro->sValor;
                        if ($filtro->sNome == $oFiltro->sValor) {
                            $valor = $filtro->sValor;
                            if ($this->relatorio->aVariaveis[$campo]->getTipoDado() == 'varchar') {
                                $valor = "'$valor'";
                            }
                            if ($oFiltro->sCondicao == ' in ') {
                                $valor = "({$valor})";
                            }
                            $where[] = str_replace($campo, $valor, $cond);
                        }
                    }
                }
            }

            if (!empty($where)) {
                $sWhere = implode(' AND ', $where);
                $sql .= " where {$sWhere}";
            }
        }

        return $sql;
    }
}
