<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 256);
            $table->string('alias', 256);
            $table->text('config')->nullable();
            $table->integer('site_id')->unsigned();
            $table->integer('default_assigned_user_id')->unsigned()->nullable();
            $table->timestamps();
        });
        Schema::table('module_forms', function(Blueprint $table) {
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->foreign('default_assigned_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('module_forms');
    }
}
