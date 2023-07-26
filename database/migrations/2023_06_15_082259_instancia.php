<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Instancia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE TABLE `instancia` ( 
                                                `id` INT(8) NOT NULL AUTO_INCREMENT , 
                                                `port` INT(7) NOT NULL , 
                                                `unique` VARCHAR(40) NOT NULL , 
                                                `status` VARCHAR(20) NOT NULL , 
                                                `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                                                `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                                                `addr` VARCHAR(200) NOT NULL ,  
                                                PRIMARY KEY (`id`)
                                            ) ENGINE = InnoDB;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP TABLE IF EXISTS `instancia`");
    }
}
