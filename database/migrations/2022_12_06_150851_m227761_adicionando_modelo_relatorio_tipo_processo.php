<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class M227761AdicionandoModeloRelatorioTipoProcesso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(<<<SQL
insert into db_syscampo values(1014629,'p51_relatorio','int8','Modelo de Relatório com Documento Template','0', 'Modelo',10,'t','f','f',1,'text','Modelo');
insert into db_sysarqcamp values(393,1014629,11,0);
insert into db_sysforkey values(393,1014629,1,2134,0);

alter table tipoproc add column p51_relatorio bigint default null;
SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()->getPdo()->exec(<<<SQL
delete from db_sysforkey where codcam = 1014629;
delete from db_sysarqcamp where codcam = 1014629;
delete from db_syscampo where codcam = 1014629;

alter table tipoproc drop column;
SQL
        );
    }
}
