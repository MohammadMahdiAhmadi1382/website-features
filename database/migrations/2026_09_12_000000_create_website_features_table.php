<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('website-features.table', 'website_features'), function (Blueprint $table): void {
            $table->id();

            $table->string('title', 255);
            $table->string('slug', 255)->unique();

            $table->text('description')->nullable();

            $table->string('icon', 100)->nullable();
            $table->string('color', 20)->nullable();

            $table->unsignedInteger('sort_order')->default(
                (int) config('website-features.defaults.sort_order', 1)
            );

            $table->boolean('is_active')->default(
                (bool) config('website-features.defaults.is_active', true)
            );

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            config('website-features.table', 'website_features')
        );
    }
};