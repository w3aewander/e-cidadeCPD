<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M22761AddColunaH31StatusTablePortaria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portaria', function (Blueprint $table) {
            $table->string('h31_status')
            ->nullable()
            ->after('h31_portariaassinatura');
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
            $table->dropColumn('h31_status');
        });
    }
}
