<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChatsWhatsapp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE TABLE `chats_whatsapp` ( 
                                                        `id` INT(8) NOT NULL AUTO_INCREMENT , 
                                                        `number` VARCHAR(20) NOT NULL , 
                                                        `name` VARCHAR(20) NOT NULL , 
                                                        `last_message` TEXT NOT NULL , 
                                                        `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                                                        `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                                                        `asigned_to` INT(10) NOT NULL , 
                                                        `estado` VARCHAR(30) NOT NULL DEFAULT 'abierto' , 
                                                        `type` VARCHAR(30) NOT NULL DEFAULT 'chat',
                                                        `notRead` INT(10) NOT NULL DEFAULT 0,
                                                        `fromMe` INT(1) NOT NULL DEFAULT 0, 
                                                        `photo` MEDIUMBLOB NULL DEFAULT NULL,
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
        DB::statement("DROP TABLE IF EXISTS `chats_whatsapp`");
    }
}
