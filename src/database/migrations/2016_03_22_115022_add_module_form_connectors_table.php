<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleFormConnectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_form_connectors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->integer('form_id')->unsigned();
            $table->string('content_type', 255);
            $table->string('event', 64)->default('post-save');
            $table->timestamps();
        });
        Schema::table('module_form_connectors', function(Blueprint $table) {
            $table->foreign('form_id')->references('id')->on('module_forms')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('module_form_connectors');
    }
}
