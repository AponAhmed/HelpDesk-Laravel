<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        //
        Schema::create("options", function (Blueprint $table) {
            //$table->id();
            $table->string("key");
            $table->longText("val")->nullable();
            $table->bigInteger("user")->default('0');
            // $table
            //     ->foreign("user")
            //     ->references("id")
            //     ->on("users")
            //     ->onDelete("cascade"); //User options// Delete Option with cascade with User

            $table->unique(["key", "user"], "unique_with_user");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop("options");
    }
};
