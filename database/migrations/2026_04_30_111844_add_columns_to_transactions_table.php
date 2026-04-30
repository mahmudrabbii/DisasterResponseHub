<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('transactions', 'order_id')) {
                $table->string('order_id')->unique()->after('campaign_id');
            }
            if (!Schema::hasColumn('transactions', 'donor_phone')) {
                $table->string('donor_phone')->nullable()->after('donor_email');
            }
            if (!Schema::hasColumn('transactions', 'payment_id')) {
                $table->string('payment_id')->nullable()->unique()->after('payment_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'order_id')) {
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('transactions', 'donor_phone')) {
                $table->dropColumn('donor_phone');
            }
            if (Schema::hasColumn('transactions', 'payment_id')) {
                $table->dropColumn('payment_id');
            }
        });
    }
}
