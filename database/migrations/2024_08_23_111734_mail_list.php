<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("mail_list", function (Blueprint $table) {
            $table->id();
            $table->string("msg_id")->unique()->nullable();

            $table->string("msg_theread")->nullable();
            $table->longText("snippet")->nullable();
            $table->string("subject")->nullable();

            $table->bigInteger("user")->nullable()->default(0);
            $table->unsignedBigInteger("customer")->nullable();
            $table->unsignedBigInteger("department");

            $table->integer("rs")->nullable(); //Mail In or Out(1:out,0:in)
            $table->bigInteger("reply_of")->nullable();

            $table->dateTime('reminder')->nullable();
            
            $table->string("labels")->nullable(); //Labels:trash,Important,followUp,Remiender,new
            $table->string("date")->nullable();

            $table->timestamps();

            $table->foreign("department")
                ->references("id")
                ->on("departments"); //Department ID
            // $table
            //     ->foreign("user")
            //     ->references("id")
            //     ->on("users"); //Department ID
            $table->foreign("customer")
                ->references("id")
                ->on("customers"); //Customer ID
        });

        Schema::create("mail_details", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("list_id");
            $table
                ->foreign("list_id")
                ->references("id")
                ->on("mail_list")
                ->onDelete("cascade");
            $table->longText("msg_body")->nullable();
            $table->longText("header")->nullable();
            $table->longText("attachments")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         //
         Schema::dropIfExists("mail_list");
         Schema::dropIfExists("mail_details");
    }
};
