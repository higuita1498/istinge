<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->integer('company_id');
            $table->string('addr');
            $table->string('api_key')->unique();
            $table->string('uuid_whatsapp');
            $table->enum('status', ['PAIRED', 'UNPAIRED'])->default('UNPAIRED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instances');
    }
}
