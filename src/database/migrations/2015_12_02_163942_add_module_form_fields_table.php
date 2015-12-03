<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleFormFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_form_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->string('name', 256);
            $table->string('alias', 256);
            $table->string('type', 45);
            $table->text('config')->nullable();
            $table->timestamps();
        });
        Schema::table('module_form_fields', function(Blueprint $table) {
            $table->foreign('form_id')->references('id')->on('module_forms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('module_form_fields');
    }
}
