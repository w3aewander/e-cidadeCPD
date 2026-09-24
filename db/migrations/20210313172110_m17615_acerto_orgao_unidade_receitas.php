<?php

use Classes\PostgresMigration;

class M17615AcertoOrgaoUnidadeReceitas extends PostgresMigration
{

    public function change()
    {
        // coloca o orgão e unidade das receitas que ainda não tem.
        $this->execute(<<<SQL
	select 1;
SQL
        );

        $this->execute(<<<SQL
update orcamento.orcreceita
   set o70_esferaorcamentaria = 10
  from (select codigo, case when db21_tipoinstit in (5,6) then 20 else 10 end as esfera from db_config) as x
 where o70_instit = codigo
  and (o70_esferaorcamentaria is null or o70_esferaorcamentaria = 0)
  and o70_anousu > 2021
SQL
        );
    }
}
