<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreativeSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creative_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->tinyInteger('type');
            $table->timestamps();
        });

        Schema::create('media_sets', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('video');
            $table->timestamps();
        });

        Schema::create('title_sets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('creative_set_sets', function (Blueprint $table) {
            $table->unsignedInteger('creative_set_id');
            $table->unsignedInteger('set_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('creative_sets');
        Schema::dropIfExists('media_sets');
        Schema::dropIfExists('title_sets');
        Schema::dropIfExists('creative_set_sets');
    }
}
