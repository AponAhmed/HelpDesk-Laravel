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
    Schema::create("departments", function (Blueprint $table) {
      $table->id();
      $table->string("name");
      $table->string("email")->unique();
      $table->longText("signature")->nullable();
      $table->string("prefix");
      $table->longText("oauth_token")->nullable();
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
    Schema::dropIfExists("departments");
  }
};
