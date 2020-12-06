<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThreadInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thread_invites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('thread_id');
            messengerMorphType('owner', $table);
            $table->string('code')->unique();
            $table->integer('max_use')->default(0);
            $table->integer('uses')->default(0);
            $table->timestamp('expires_at')->nullable()->default(null)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('thread_id')
                ->references('id')
                ->on('threads')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thread_invites');
    }
}
