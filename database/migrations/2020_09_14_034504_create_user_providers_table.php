<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('slug')->unique();
            $table->text('scopes')->nullable();
            $table->text('parameters')->nullable();
            $table->boolean('override_scopes')->default(false);
            $table->boolean('stateless')->default(false);
            $table->timestamps();
        });

        Schema::create('trackers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('slug')->unique();
            $table->text('scopes')->nullable();
            $table->text('parameters')->nullable();
            $table->boolean('override_scopes')->default(false);
            $table->boolean('stateless')->default(false);
            $table->timestamps();
        });

        Schema::create('user_providers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('provider_id')->unsigned();
            $table->string('open_id');
            $table->text('token');
            $table->text('secret_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_in')->nullable();
            $table->timestamps();
        });

        Schema::create('user_trackers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('tracker_id')->unsigned();
            $table->string('open_id');
            $table->string('api_key');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
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
        Schema::dropIfExists('user_trackers');
        Schema::dropIfExists('user_providers');
        Schema::dropIfExists('trackers');
        Schema::dropIfExists('providers');
    }
}
