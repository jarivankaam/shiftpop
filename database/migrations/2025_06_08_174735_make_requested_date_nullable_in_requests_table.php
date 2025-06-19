<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->date('requested_date')->nullable()->change(); // ✅ make nullable
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->date('requested_date')->nullable(false)->change(); // ⛔ revert if needed
        });
    }
};

