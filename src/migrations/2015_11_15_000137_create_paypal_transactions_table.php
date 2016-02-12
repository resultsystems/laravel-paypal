<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaypalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice', 127)->nullable();
            $table->string('custom', 255)->nullable();
            $table->string('txn_type', 55);
            $table->string('txn_id');
            $table->string('payer_id', 13);
            $table->string('currency', 3);
            $table->double('gross');
            $table->double('fee');
            $table->double('handling')->nullable();
            $table->double('shipping')->nullable();
            $table->double('tax')->nullable();
            $table->string('payment_status', 17)->nullable();
            $table->string('pending_reason', 17)->nullable();
            $table->string('reason_code', 31)->nullable();
            $table->timestamps();

            //$table->index(['payer_id', ' payment_status']);
            //$table->index(['txn_id', ' payment_status']);
            //$table->index(['custom', ' payment_status']);
            //$table->index(['invoice', ' payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_transactions');
    }
}
