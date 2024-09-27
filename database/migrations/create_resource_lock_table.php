<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MoonShine\Laravel\MoonShineAuth;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resource_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MoonShineAuth::getProvider()->getModel())->constrained()->cascadeOnDelete();
            $table->morphs('lockable');
            $table->timestamp('locking_at');
            $table->timestamp('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_locks');
    }
};
