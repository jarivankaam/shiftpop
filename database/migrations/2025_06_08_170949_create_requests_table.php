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
        Schema::create('requests', function (Blueprint $table) {
            $table->id(); // id (primary key)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // foreign key to users table
            $table->string('type'); // string for type of request
            $table->date('requested_date'); // date of the request
            $table->text('Reason')->nullable(); // reason for the request (nullable)
            $table->string('status')->default('pending'); // status with default
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null'); // foreign key to shifts table
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
