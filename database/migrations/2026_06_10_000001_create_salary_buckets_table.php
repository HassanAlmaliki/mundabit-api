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
        Schema::create('salary_buckets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['needs', 'wants', 'savings']); // الضروريات، الرفاهية، الادخار
            $table->decimal('limit_amount', 10, 2)->default(0);
            $table->decimal('consumed_amount', 10, 2)->default(0);
            $table->string('active_color')->default('green'); // green, orange, red, blue
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_buckets');
    }
};
