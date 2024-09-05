<?php

use Database\Seeders\RoleSeeder;
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
         //
    Schema::create("user_roles", function (Blueprint $table) {
        $table->increments("id");
        $table->string("name");
        $table->string("guard_name")->default("web");
        $table->timestamps();
        $table->unique(["name", "guard_name"]);
      });
      Schema::create("user_has_role", function (Blueprint $table) {
        $table->increments("id");
        $table->integer("user_id");
        $table->integer("role_id");
        $table->timestamps();
      });
      Schema::create("permission", function (Blueprint $table) {
        //$table->bigIncrements("id");
        $table->integer("model_id"); //user id , role id
        $table->string("model_type"); //user,role
        $table->longText("permission"); //permission string JSON
        $table->timestamps();
      });
  
      $roleSeed= new RoleSeeder(); // also Initial Assignment with User as Super admin
      $roleSeed->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::drop("user_roles");
        Schema::drop("user_has_role");
        Schema::drop("permission");
    }
};
