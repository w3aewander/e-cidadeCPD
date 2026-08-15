<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class M21199TfdAlertaMunicipio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upEstrutura();
        $this->upDicionario();      
    }

    public function upEstrutura()
    {
        DB::unprepared(<<<SQL
ALTER TABLE tfd_parametros ADD COLUMN tf11_alertamunicipio boolean not null default false;

SQL
        );        
    }
    
    public function upDicionario()
    {
        DB::unprepared(<<<SQL

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

COMMENT ON COLUMN tfd.tfd_parametros.tf11_alertamunicipio IS '{
  "descricao": "Não: Não exibir o alerta de paciente que não mora no município;  Sim: Exibir o alerta de paciente que não mora no município. Nesse caso, informar o código IBGE do município da prefeitura.",
  "rotulo": "Alerta município",
  "rotulorel": "Alerta município",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

SELECT fc_gera_dicionario_apartir_tabela('tfd', 'tfd_parametros');
SELECT fc_gera_dicionario_apartir_tabela('tfd', 'tfd_parametros');

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;

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
        DB::unprepared(<<<SQL
ALTER TABLE tfd_parametros DROP COLUMN tf11_alertamunicipio;
SQL
        );    
    }   
}
