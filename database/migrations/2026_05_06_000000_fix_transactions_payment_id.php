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
        Schema::table('transactions', function (Blueprint $table) {
            // Modify payment_id to be nullable if it exists and isn't already
            if (Schema::hasColumn('transactions', 'payment_id')) {
                try {
                    $table->string('payment_id')->nullable()->change();
                } catch (\Exception $e) {
                    // Column might already be nullable, that's fine
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse action needed
    }
};
