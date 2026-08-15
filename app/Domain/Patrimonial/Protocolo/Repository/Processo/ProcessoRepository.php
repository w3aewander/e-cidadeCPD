<?php
namespace App\Domain\Patrimonial\Protocolo\Repository\Processo;

use App\Domain\Core\Base\Repository\BaseRepository;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Processo;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\Contracts\ProcessoRepository as RepositoryInterface;
use App\Domain\Tributario\Cadastro\Models\DbSyscampo;
use cl_protprocesso;
use DBDepartamentoRepository;

use ECidade\Lib\Session\DatabaseSession;
use ECidade\Lib\Session\DefaultSession;
use Exception;

/**
 * Classe abstrata para Repositorys. Usa eloquent
 *
 * @var string
 */
final class ProcessoRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * Model class for repo.
     *
     * @var string
     */
    protected $modelClass = Processo::class;

    /**
     * @var cl_protprocesso
     */
    private $dao;


    /**
     * Construtor da classe
     *
     * @param cl_protprocesso $dao
     */
    public function __construct()
    {
        $this->dao = new cl_protprocesso();
    }

    /**
     * Fun��o que salva um novo registro
     *
     * @param Processo $model
     * @return Processo
     * @throws Exception
     */
    public function persist(Processo $model, $siglaDoDocumentoNaNumeracao = false)
    {
        DefaultSession::getInstance()->set(DefaultSession::DB_CODDEPTO, db_getsession('DB_coddepto'));
        DatabaseSession::getInstance()->addSessionToDatabase();
        $this->dao->p58_codigo     = $model->getCodigo();
        $this->dao->p58_numcgm     = $model->getCgm();
        $this->dao->p58_requer     = $model->getRequerente();
        $this->dao->p58_codandam   = $model->getCodigoAndamento();
        $this->dao->p58_obs        = str_replace("'", "\"", $model->getObservacao());
        $this->dao->p58_despacho   = $model->getDespacho();
        $this->dao->p58_interno    = ($model->getInterno()) ? 'true' : 'false';
        $this->dao->p58_publico    = ($model->getPublico()) ? 'true' : 'false';
        $this->dao->p58_ano        = $model->getAno();

        if (empty($model->getCodigoProcesso())) {
            $this->dao->p58_coddepto    = db_getsession('DB_coddepto');
            $this->dao->p58_dtproc      = date('Y-m-d', db_getsession('DB_datausu'));
            $this->dao->p58_id_usuario  = db_getsession('DB_id_usuario');
            $this->dao->p58_hora        = db_hora();
            $this->dao->p58_instit      = db_getsession('DB_instit');
            $model->setDepartamento($this->dao->p58_coddepto);
            $model->setInstituicao($this->dao->p58_instit);
            $model->setData($this->dao->p58_dtproc);
            $model->setUsuario($this->dao->p58_id_usuario);

            $orgao = null;
            $tipoDocumentoProcesso = null;
            if (\ProcessoProtocoloNumeracao::TIPOORGAO == \ProcessoProtocoloNumeracao::getTipoConfiguracao()
                or $siglaDoDocumentoNaNumeracao
            ) {
                $orgao = DBDepartamentoRepository::getIdOrgaoByCodigo(db_getsession('DB_coddepto'));
                $daoTipoProcesso = new \cl_tipoproc;
                $sql = $daoTipoProcesso->sql_query(
                    null,
                    'p51_prottipodocumentoprocesso',
                    null,
                    "p51_codigo = {$this->dao->p58_codigo}"
                );
                $postgresObject = db_query($sql);

                $rsTipoProcesso = pg_fetch_assoc($postgresObject);
                $tipoDocumentoProcesso = $rsTipoProcesso['p51_prottipodocumentoprocesso'];
            }

            $this->dao->p58_numero = \ProcessoProtocoloNumeracao::getProximoNumero(
                $orgao,
                $tipoDocumentoProcesso,
                0,
                true
            );

            $this->dao->p58_tipoprocesso = $model->getTipoProcesso();
            $this->dao->incluir(null);

            $model->setCodigoProcesso($this->dao->p58_codproc);
            $model->setNumero($this->dao->p58_numero);
            $model->setData($this->dao->p58_dtproc);
            $model->setHora($this->dao->p58_hora);
            $model->setUsuario($this->dao->p58_id_usuario);
        } else {
            $this->dao->p58_codproc = $model->getCodigoProcesso();
            $this->dao->alterar($model->getCodigoProcesso());
        }

        if ($this->dao->erro_status == 0) {
            throw new Exception("Erro ao salvar dados do processo!\\n{$this->dao->erro_msg}");
        }

        return $model;
    }

    public function rotulosPesquisa()
    {
        $data = [];
        $nomeCampos = [
            'p58_codproc',
            'p58_codigo',
            'p58_dtproc',
            'p58_id_usuario',
            'p58_numcgm',
            'p58_requer',
            'p58_obs',
            'p58_ano'
        ];

        $nomeLabels = array_map(function ($nomeCampo) {
            return DbSyscampo::select("rotulo")->where('nomecam', '=', $nomeCampo)->first()->toArray();
        }, $nomeCampos);
        if (count($nomeCampos) == count($nomeLabels)) {
            for ($index = 0; $index < count($nomeCampos); $index++) {
                $data[$nomeCampos[$index]] = ucfirst($nomeLabels[$index]["rotulo"]);
            }
        }

        return $data;
    }

    public static function getByParams(
        $p58_dtproc,
        $p58_codproc,
        $p58_requer,
        $p58_obs,
        $p58_ano,
        $porPagina
    ) {

        $lista = new Processo();

        $where = " 1 = 1 ";

        if (trim($p58_codproc) != "") {
            $where .= " and p58_codproc = {$p58_codproc} ";
        }

        if (trim($p58_requer) != "") {
            $where .= " and p58_requer ilike '{$p58_requer}' ";
        }

        if (trim($p58_obs) != "") {
            $where .= " and p58_obs ilike '{$p58_obs}' ";
        }

        if (trim($p58_ano) != "") {
            $where .= " and p58_ano = {$p58_ano} ";
        }

        if (trim($p58_dtproc) != "") {
            $where .= " and p58_dtproc = '{$p58_dtproc}' ";
        }

        $pesquisa = $lista->whereRaw($where)
            ->select([
                'p58_codproc',
                'p58_requer',
                'p58_obs',
                'p58_ano',
                \DB::raw('TO_CHAR(p58_dtproc, \'dd/mm/yyyy\') as p58_dtproc')
            ])->orderBy('p58_codproc', 'ASC')
            ->paginate($porPagina);

        return $pesquisa;
    }
}
