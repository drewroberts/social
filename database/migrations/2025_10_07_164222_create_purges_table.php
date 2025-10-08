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
        Schema::create('purges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->unique();
            $table->timestamp('posted_at')->nullable();
            $table->text('text')->nullable();
            $table->boolean('save')->default(false);
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();
        });
    }
};
