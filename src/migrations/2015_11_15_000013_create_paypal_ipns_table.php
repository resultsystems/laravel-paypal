<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaypalIpnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_ipns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('txn_id');
            $table->string('txn_type', 45)->nullable()->default(null);
            $table->string('receiver_email', 127);
            $table->string('payment_status', 17)->nullable()->default(null);
            $table->string('pending_reason', 17)->nullable()->default(null);
            $table->string('reason_code', 31)->nullable()->default(null);
            $table->string('custom', 45)->nullable()->default(null);
            $table->string('invoice', 45)->nullable()->default(null);
            $table->text('notification');
            $table->string('hash', 32)->unique();
            $table->timestamps();

            $table->index(['custom', 'payment_status']);
            $table->index(['invoice', 'payment_status']);
            $table->index(['txn_type', 'payment_status']);
            $table->index(['txn_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_ipns');
    }
}
