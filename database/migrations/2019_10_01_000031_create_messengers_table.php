<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messengers', function (Blueprint $table) {
            $table->uuidMorphs('owner');
            $table->boolean('message_popups')->default(1);
            $table->boolean('message_sound')->default(1);
            $table->boolean('call_ringtone_sound')->default(1);
            $table->boolean('notify_sound')->default(1);
            $table->boolean('dark_mode')->default(1);
            $table->integer('online_status')->default(1);
            $table->string('ip')->nullable()->default(null);
            $table->string('timezone')->nullable()->default(null);
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
        Schema::dropIfExists('messengers');
    }
}
