<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M22761AddColunaH31PortariaseqTablePortaria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portaria', function (Blueprint $table) {
            $table->bigInteger('h31_portariaseq')
            ->nullable()
            ->after('h31_status');
            $table->foreign('h31_portariaseq')->references('h31_sequencial')->on('portaria');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portaria', function (Blueprint $table) {
            $table->dropForeign(['h31_portariaseq']);
            $table->dropColumn('h31_portariaseq');
        });
    }
}
