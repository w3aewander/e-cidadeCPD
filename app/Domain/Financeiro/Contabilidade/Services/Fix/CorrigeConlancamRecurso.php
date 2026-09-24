<?php

namespace App\Domain\Financeiro\Contabilidade\Services\Fix;

use Illuminate\Support\Facades\DB;

class CorrigeConlancamRecurso
{
    /**
     * @var mixed
     */
    private $exercicio;
    /**
     * @var string
     */
    private $tableProblemas;
    /**
     * @var string
     */
    private $tableInserir;

    public function arrumar($exercicio)
    {
        $this->exercicio = $exercicio;
        $this->tableProblemas = 'w_lancamentos_arrumar_' . time();
        $this->tableInserir = 'w_lancamentos_inserir_' . time();
        $this->bucaLancamento();

        $this->deleta();
        $this->recriar();
    }

    private function bucaLancamento()
    {
        DB::statement("drop table if exists {$this->tableProblemas}");

        DB::statement(<<<SQL
create table $this->tableProblemas as
with debitos as (
	select c70_codlan, c70_data, c02_instit, c53_tipo, c53_coddoc
	from conlancam
	join conlancamdoc on c71_codlan = c70_codlan
	join conhistdoc on c71_coddoc = c53_coddoc
	left join conlancaminstit on c02_codlan = c70_codlan
	join conlancamval on c69_codlan = c70_codlan
	left join conlancamrecurso on c130_conlancam = c70_codlan
         and c130_conta = c69_debito
         and c130_anousu = c70_anousu
         and c130_natureza = 'D'
	where c70_anousu = $this->exercicio
	  and c53_coddoc not in (142, 143)
      and c130_sequencial is null
), creditos as (
  select c70_codlan, c70_data, c02_instit, c53_tipo, c53_coddoc
	from conlancam
	join conlancamdoc on c71_codlan = c70_codlan
	join conhistdoc on c71_coddoc = c53_coddoc
	join conlancamval on c69_codlan = c70_codlan
	left join conlancaminstit on c02_codlan = c70_codlan
	left join conlancamrecurso on c130_conlancam = c70_codlan
         and c130_conta = c69_credito
         and c130_anousu = c70_anousu
         and c130_natureza = 'C'
	where c70_anousu = $this->exercicio
	  and c53_coddoc not in (142, 143)
      and c130_sequencial is null
), divergencia_onlancamrecurso as (
	select c70_codlan, c70_data, c02_instit, c53_tipo, c53_coddoc
	  from (
	    select c70_codlan, c70_data, c02_instit, c53_tipo, c53_coddoc,
	           (select count(c69_sequen)
	              from conlancamval
	             where c69_codlan = c70_codlan
	           ) as count_conlancamval,
	           (select count(c130_sequencial)
	              from conlancamrecurso
	             where c130_conlancam = c70_codlan
	           ) as count_conlancamrecurso
	      from conlancam
	      join conlancamdoc on c71_codlan = c70_codlan
	      join conhistdoc on c71_coddoc = c53_coddoc
	      left join conlancaminstit on c02_codlan = c70_codlan
	    where c70_anousu = $this->exercicio
	) as xx
    where (count_conlancamval * 2) != count_conlancamrecurso
), unifica_dados as (
	select distinct *
	  from (
          select * from debitos
          union all
          select * from creditos
          union all
          select * from divergencia_onlancamrecurso
	) as x
)
select *,
       case
         when c53_tipo in (10, 11, 20, 21, 30, 31, 40, 41, 50, 51, 60, 61, 70, 71, 90, 91, 92, 100, 101, 110,
                           111, 112, 113, 200, 201, 414, 415, 900, 901, 1000, 1500, 2000, 2001)
         then (select o201_orctiporec from conlancamcomplementorecurso where o201_codlan = c70_codlan)
         else null
       end recurso
  from unifica_dados
;
SQL
        );
    }

    private function deleta()
    {
        DB::statement(<<<SQL
delete from conlancamrecurso
using $this->tableProblemas
 where c130_conlancam = c70_codlan
SQL
        );
    }

    private function recriar()
    {
        DB::statement("drop table if exists {$this->tableInserir}");

        DB::statement(<<<SQL
create table $this->tableInserir as
with problemas as (
   select * from $this->tableProblemas
), com_recurso as (
    select c70_codlan,
           recurso,
           c69_credito as conta,
           c69_anousu,
           'C' as natureza
      from problemas
      join conlancamval on c70_codlan = c69_codlan
    where recurso is not null

    union

    select c70_codlan,
           recurso,
           c69_debito as conta,
           c69_anousu,
           'D' as natureza
      from problemas
      join conlancamval on c70_codlan = c69_codlan
    where recurso is not null
), sem_recurso as (
    select c70_codlan,
           c61_codigo,
           c69_credito as conta,
           c69_anousu,
           'C' as natureza
      from problemas
      join conlancamval on c70_codlan = c69_codlan
      join conplanoreduz on c61_reduz = c69_credito
           and c61_anousu = c69_anousu
    where recurso is null
    union
    select c70_codlan,
           c61_codigo,
           c69_debito as conta,
           c69_anousu,
           'D' as natureza
      from problemas
      join conlancamval on c70_codlan = c69_codlan
      join conplanoreduz on c61_reduz = c69_debito
           and c61_anousu = c69_anousu
    where recurso is null
)
select * from com_recurso
union
select * from sem_recurso;
SQL
        );

        DB::statement(<<<SQL
insert into conlancamrecurso
select nextval('conlancamrecurso_c130_sequencial_seq'), c70_codlan, recurso, conta, c69_anousu, natureza
from $this->tableInserir
SQL
        );
    }
}
