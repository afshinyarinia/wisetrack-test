<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('visitor_hash', 64);
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->date('viewed_date');
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(['post_id', 'visitor_hash', 'viewed_date'], 'post_views_unique_daily_visitor');
            $table->index(['post_id', 'viewed_date']);
            $table->index(['post_id', 'user_id', 'viewed_date']);
            $table->index('viewed_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};
