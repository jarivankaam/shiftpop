<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add tenant_id to shifts table
        Schema::table('shifts', function (Blueprint $table) {
            $table->uuid('tenant_id')->after('id')->index()->nullable();
        });

        // Add tenant_id to templates table
        Schema::table('templates', function (Blueprint $table) {
            $table->uuid('tenant_id')->after('id')->index()->nullable();
        });

        // Add tenant_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('tenant_id')->after('id')->index()->nullable();
        });

        // Add tenant_id to requests table
        Schema::table('requests', function (Blueprint $table) {
            $table->uuid('tenant_id')->after('id')->index()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};
