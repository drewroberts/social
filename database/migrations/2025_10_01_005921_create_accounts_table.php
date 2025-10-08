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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service'); // twitter, facebook, telegram
            $table->string('service_user_id')->nullable(); // Platform's user ID
            $table->string('username')->nullable(); // Display handle/name
            $table->text('access_token'); // OAuth token (encrypted)
            $table->text('access_token_secret')->nullable(); // OAuth 1.0a secret (encrypted)
            $table->text('refresh_token')->nullable(); // OAuth 2.0 refresh token (encrypted)
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable(); // Granted permissions
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Platform-specific data (profile pic, bio, etc.)
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'service']);
            $table->index(['service', 'service_user_id']);
            $table->unique(['user_id', 'service', 'service_user_id']);
        });
    }
};
