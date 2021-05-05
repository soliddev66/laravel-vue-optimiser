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
            $table->string('name');
            $table->tinyInteger('type');
            $table->timestamps();
        });

        Schema::create('image_sets', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('hq_image')->nullable();
            $table->tinyInteger('optimiser');
            $table->string('hq_800x800_image')->nullable();
            $table->string('hq_1200x627_image')->nullable();
            $table->string('hq_1200x628_image')->nullable();
            $table->timestamps();
        });

        Schema::create('video_sets', function (Blueprint $table) {
            $table->id();
            $table->string('portrait_image');
            $table->string('landscape_image');
            $table->string('video');
            $table->timestamps();
        });

        Schema::create('title_sets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('description_sets', function (Blueprint $table) {
            $table->id();
            $table->text('description');
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
        Schema::dropIfExists('image_sets');
        Schema::dropIfExists('video_sets');
        Schema::dropIfExists('title_sets');
        Schema::dropIfExists('description_sets');
        Schema::dropIfExists('creative_set_sets');
    }
}
