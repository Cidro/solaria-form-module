<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleFormResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_form_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned()->nullable();
            $table->integer('assigned_user_id')->unsigned()->nullable();
            $table->text('results');
            $table->timestamps();
        });
        Schema::table('module_form_results', function(Blueprint $table) {
            $table->foreign('form_id')->references('id')->on('module_forms')->onDelete('set null');
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('module_form_results');
    }
}
