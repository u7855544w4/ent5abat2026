<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('family_name')->nullable();
            $table->string('electoral_code')->nullable();
            $table->string('electoral_center')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('committee_id')->nullable()->constrained('committees')->nullOnDelete();
            $table->string('follower_name')->nullable();
            $table->date('follow_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('family_name');
            $table->index('electoral_center');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voters');
        Schema::dropIfExists('committees');
    }
};