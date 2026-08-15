<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Melhoria
{
    /** @var string Código unico para a execução */
    private $id;

    /** @var string Nome do Pacote de Atualização */
    private $packageFilename;

    /** @var array Lista de Arquivos que serão Atualizados */
    private $listaArquivos;

    /** @var array Lista de Arquivos de release notes que serao criados */
    private $releaseNotes;

    /** @var array Lista de Arquivos de tutoriais que serao criados */
    private $tutoriais;

    /** @var string Versão o qual essa melhoria representa */
    private $versao;

    /** @var DateTime Data de criacao da melhoria */
    private $data;

    /**
     * Construtor
     *
     * @param string $packageFilename Nome do Pacote de Atualização
     * @param string $codigoMelhoria Código da Atualização
     * @param string $date Data de criacao da melhoria, vindo da API
     */
    public function __construct($packageFilename, $codigoMelhoria, $date)
    {
        // Cria um id unico para esta execução
        $this->id = uniqid();
        $this->codigoMelhoria = $codigoMelhoria;
        $this->packageFilename = $packageFilename;
        $this->releaseNotes = array();
        $this->data = new DateTime($date);
    }

    /**
     * Chama os passos de instalação do pacote de atualização
     */
    public function instalar()
    {
        $this->criaVersao();
        $this->extrair();
        $this->processaReleaseNotes();
        $this->carregarArquivos();
        $this->validarPermissoes();
        $this->iniciarLaravel();
        $this->rodarEstrutura();
        $this->moverFontes();
        $this->recarregarArquivosFila();
    }

    /**
     * Responsavel por extrair o arquivo de melhoria no tmp
     */
    private function extrair()
    {
        $dir = $this->getDiretorioFontes();

        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new BusinessException(
                "Atualização (Código {$this->codigoMelhoria}): " .
                "Erro ao tentar extrair o pacote de atualização."
            );
        }

        $phar = new PharData($this->packageFilename);
        $phar->extractTo($dir, null, true);
    }

    /**
     * Responsavel por identificar todos os arquivos contidos na melhoria
     */
    private function carregarArquivos()
    {
        $oIteratorEcidade = new RecursiveDirectoryIterator($this->getDiretorioFontes());

        foreach (new RecursiveIteratorIterator($oIteratorEcidade) as $file) {

            // se for diretório "." e ".." ignora
            if (in_array($file->getFilename(), array('.', '..'))) continue;

            // se for diretório, ignora (mesmo sendo diret?rio vazio)
            if ($file->isDir()) continue;

            $this->listaArquivos[] = str_replace($this->getDiretorioFontes() . '/', '', $file->getPathname());
        }
    }

    /**
     * Responsavel por validar se todos os arquivos da melhoria conseguirão ser salvos em produção
     */
    private function validarPermissoes()
    {
        foreach ($this->listaArquivos as $caminhoArquivo) {
            // Valida se o diretório do arquivo ja existe e se o diretório tem permissao de escrita
            if (is_dir(dirname($caminhoArquivo)) && !is_writable(dirname($caminhoArquivo))) {
                throw new BusinessException(
                    "Atualização (Código {$this->codigoMelhoria}): " .
                    "Sem permissão de escrita no diretório \" " . dirname($caminhoArquivo) . "\".");
            }

            // Valida se o arquivo ja existe e se tem permissao de escrita
            if (file_exists($caminhoArquivo) && !is_writable($caminhoArquivo)) {
                throw new BusinessException(
                    "Atualização (Código {$this->codigoMelhoria}): " .
                    "Sem permissão de escrita para o arquivo:  $caminhoArquivo"
                );
            }
        }
    }

    /**
     * inicia a aplicação do laravel para uso dos facades e do Artisan
     * @return void
     */
    private function iniciarLaravel()
    {
        $app = require_once ECIDADE_PATH . 'bootstrap/app.php';
        // App já iniciado
        if ($app === true) {
            return;
        }
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }

    /**
     * Responsável por ler os DMLs da melhoria e rodar no banco
     */
    private function rodarEstrutura()
    {
        $dirEstrutura = $this->getDiretorioFontes() . '/db/DML';

        $lLimpaCache = false;

        // atualizacao legado
        if (is_dir($dirEstrutura)) {
            global $conn;

            // Pega os scripts que precisam ser rodados.
            $sql = DBDataBaseMigration::upgradeDatabase($conn, 'dml', $dirEstrutura);

            if (trim($sql) != '') {
                $queryResult = @db_query($conn, $sql);

                if (!$queryResult) {
                    throw new DBException(
                        "Atualização (Código {$this->codigoMelhoria}): " .
                        "Não foi possível executar as mudanças de estrutura desta atualização."
                    );
                }

                $lLimpaCache = true;
            }
        }

        $dirMigrationsPhinx = $this->getDiretorioFontes() . '/db/migrations';

        // verifica se tem migrations no pacote da melhoria
        if (is_dir($dirMigrationsPhinx)) {
            if (!is_dir('db/migrations')) {
                mkdir('db/migrations', 0775, true);
            }

            // verificação pra garantir que foi criado o diretório
            if (!is_dir('db/migrations')) {
                throw new Exception("Não foi possível criar o diretório de migrações.");
            }

            $migrations = array_filter($this->listaArquivos, function ($file) {
                return strpos($file, 'db/migrations') === 0;
            });

            // copia o migrations direto para o ecidade
            // mesmo que ocorram erros depois, pois o phinx não deixa alterar o diretório de execucao
            foreach ($migrations as $file) {
                copy($this->getDiretorioFontes() . '/' . $file, $file);
            }

            // HACK
            $this->moverVendor();

            // executa os migrations do phinx
            $phinxWrapper = DBDataBaseMigration::getPhinxWrapper();
            $phinxWrapper->getMigrate();

            $lLimpaCache = true;
        }

        $dirMigrationsLaravel = $this->getDiretorioFontes() . '/database/migrations';
        if (is_dir($dirMigrationsLaravel)) {
            if (!is_dir('database/migrations')) {
                mkdir('database/migrations', 0775, true);
            }

            // verificação pra garantir que foi criado o diretório
            if (!is_dir('database/migrations')) {
                throw new Exception("Não foi possível criar o diretório de migrações do Laravel.");
            }

            $migrations = array_filter($this->listaArquivos, function ($file) {
                return strpos($file, 'database/migrations') === 0;
            });

            // copia o migrations direto para o ecidade
            foreach ($migrations as $file) {
                copy($this->getDiretorioFontes() . '/' . $file, $file);
            }

            //Atualizar migrations com laravel api
            DB::transaction(function () {
                Artisan::call("migrate", ['--force' => true]);
            });

            $lLimpaCache = true;
        }

        // Limpa o cache dos menus, pois pode haver mudança em menu nos sqls
        if ($lLimpaCache) {
            DBMenu::limpaCache();
        }
    }

    // HACK: move os vendors para o ecidade antes de atualizar :/
    public function moverVendor()
    {
        $vendorFiles = array_filter($this->listaArquivos, function ($file) {
            return strpos($file, 'vendor') === 0;
        });

        foreach ($vendorFiles as $caminhoArquivo) {

            if (!is_dir(dirname($caminhoArquivo))) {
                mkdir(dirname($caminhoArquivo), 0775, true);
            }

            copy($this->getDiretorioFontes() . '/' . $caminhoArquivo, $caminhoArquivo);
        }
    }

    /**
     * Reponsável por copiar os arquivos de fato para produção
     */
    private function moverFontes()
    {
        foreach ($this->listaArquivos as $caminhoArquivo) {
            // Se o diretório do arquivo não existir, é criado.
            if (!is_dir(dirname($caminhoArquivo))) {
                mkdir(dirname($caminhoArquivo), 0775, true);
            }

            // Copia o arquivo para producao
            copy($this->getDiretorioFontes() . '/' . $caminhoArquivo, $caminhoArquivo);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function recarregarArquivosFila()
    {
        $exitCode = Artisan::call('queue:restart');
        if ($exitCode !== 0) {
            throw new \Exception('Não foi possível recarregar os arquivos da fila. ' . Artisan::output());
        }
    }

    private function getDiretorioFontes()
    {
        return MelhoriaService::DIR_MELHORIAS . $this->id;
    }

    /**
     * Retorna a ultima melhoria atualizada no sistema
     *
     * @return integer
     * @throws Exception
     */
    public static function getUltimaMelhoriaAtualizada()
    {
        $oDaoMelhoria = new cl_atualizacaomelhoria();
        $rsBuscaMelhoria = db_query($oDaoMelhoria->sql_query_file(null, 'coalesce(max(melhoria), 0) as ultima_melhoria'));
        if (!$rsBuscaMelhoria) {
            throw new Exception("Não foi possível localizar a última melhoria disponível.");
        }

        return db_utils::fieldsMemory($rsBuscaMelhoria, 0)->ultima_melhoria;
    }

    /**
     * @param integer $iCodigoMelhoria
     * @param UsuarioSistema $oUsuario
     * @param DBDate $oData
     *
     * @return bool
     * @throws Exception
     */
    public static function salvar($iCodigoMelhoria, UsuarioSistema $oUsuario, DBDate $oData)
    {
        $oDaoMelhoria = new cl_atualizacaomelhoria();
        $oDaoMelhoria->sequencial = null;
        $oDaoMelhoria->melhoria = $iCodigoMelhoria;
        $oDaoMelhoria->usuario = $oUsuario->getIdUsuario();
        $oDaoMelhoria->data = $oData->getDate();
        $oDaoMelhoria->incluir(null);

        if ($oDaoMelhoria->erro_status == "0") {
            throw new Exception("Não foi possível atualizar a melhoria com código {$iCodigoMelhoria} no e-cidade.\n\n" . $oDaoMelhoria->erro_msg);
        }

        return true;
    }

    public function addReleaseNote($filename)
    {
        $this->releaseNotes[] = $filename;
    }

    public function processaReleaseNotes()
    {
        $releaseNotesDir = $this->getDiretorioFontes() . '/' . "release_notes/v" . $this->versao;

        if (!empty($this->releaseNotes)) {
            mkdir($releaseNotesDir, 0775, true);
        }

        foreach ($this->releaseNotes as $releaseNote) {
            $filename = $releaseNote->fileName;

            $name = basename($filename);
            $to = $releaseNotesDir . "/" . $name;
            rename($filename, $to);

            // tutoriais do release note
            if (!empty($releaseNote->tutorial)) {
                $this->processaTutorial($releaseNote->tutorial);

                $tutorialPath = basename($releaseNote->tutorial);

                $tutorialTemplate = "\nPara saber mais informações clique aqui: [Tutorial][1]\n";
                $tutorialTemplate .= "\n[1]: release_notes/v{$this->versao}/tutoriais/{$tutorialPath} {target=_blank}\n";

                file_put_contents($to, $tutorialTemplate, FILE_APPEND);
            }

        }
    }

    public function processaTutorial($filename)
    {
        $tutorialDir = $this->getDiretorioFontes() . '/' . "release_notes/v" . $this->versao . "/tutoriais";

        if (!is_dir($tutorialDir)) {
            mkdir($tutorialDir, 0775, true);
        }

        $name = basename($filename);
        $to = $tutorialDir . "/" . $name;
        rename($filename, $to);
    }

    public function criaVersao()
    {
        $data = date('Y-m-d H:i:s', $this->data->getTimestamp());
        $codigoVersao = db_utils::fieldsMemory(db_query("select nextval('configuracoes.db_versao_db30_codver_seq') as id"), 0)->id;

        // código da nova versao
        $codigoRelease = $this->codigoMelhoria;

        // query para criar a proxima versao
        $sqlVersaoAnt = "insert into db_versaoant values ({$codigoVersao},  '{$data}')";
        $sqlVersao = "insert into db_versao (db30_codver, db30_codversao, db30_codrelease, db30_data, db30_obs)  values ({$codigoVersao}, 4, {$codigoRelease}, '{$data}', 'Melhoria: {$this->id}')";

        $rsVersao = db_query($sqlVersao);
        if (!$rsVersao) {
            throw new Exception("Erro ao criar versão.");
        }

        $rsVersaoAnt = db_query($sqlVersaoAnt);
        if (!$rsVersaoAnt) {
            throw new Exception("Erro ao criar backup da versão anterior.");
        }

        $this->versao = sprintf('2.%s.%s', '4', $codigoRelease);

        $templateAcessa = "<?php \n";
        $templateAcessa .= "\$db_fonte_codversao = '4'; \n";
        $templateAcessa .= "\$db_fonte_codrelease = '$codigoRelease'; \n";
        $templateAcessa .= "?>";

        if (!is_dir($this->getDiretorioFontes() . '/libs')) {
            mkdir($this->getDiretorioFontes() . '/libs', 0775, true);
        }

        file_put_contents($this->getDiretorioFontes() . '/libs/db_acessa.php', $templateAcessa);
    }
}
