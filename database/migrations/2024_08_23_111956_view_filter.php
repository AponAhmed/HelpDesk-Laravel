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
        Schema::create("view_filters", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("department")->nullable();

            $table
                ->foreign("department")
                ->references("id")
                ->on("departments")
                ->onDelete("cascade");

            $table->string("role", 50);
            $table->text("keys")->nullable();
            $table->Integer("user");
            $table->enum('status', ['0', '1'])->default('1');
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
        //
        Schema::drop("view_filters");
    }
};
