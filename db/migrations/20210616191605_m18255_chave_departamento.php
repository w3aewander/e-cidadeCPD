<?php

use Classes\PostgresMigration;

class M18255ChaveDepartamento extends PostgresMigration
{
    public function up()
    {
	    $this->execute(<<<SQL
	delete from proctransferproc where p63_codtran in (select p62_codtran from proctransfer where not exists (select 1 from db_depart where coddepto = p62_coddeptorec));
	delete from proctransfer where not exists (select 1 from db_depart where coddepto = p62_coddeptorec);
        alter table proctransfer 
          add constraint proctransfer_coddeptorec_fk FOREIGN KEY(p62_coddeptorec) references db_depart(coddepto) MATCH FULL DEFERRABLE;
SQL
        );
    }

    public function down()
    {
        $this->execute(<<<SQL
        alter table proctransfer 
         drop constraint proctransfer_coddeptorec_fk ;
SQL
        );
    }
}
