<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaypalCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('address_country', 64)->nullable()->default(null);
            $table->string('address_city', 40)->nullable()->default(null);
            $table->string('address_country_code', 2)->nullable()->default(null);
            $table->string('address_name', 128)->nullable()->default(null);
            $table->string('address_state', 40)->nullable()->default(null);
            $table->string('address_status', 11)->nullable()->default(null);
            $table->string('address_street', 200)->nullable()->default(null);
            $table->string('address_zip', 20)->nullable()->default(null);
            $table->string('contact_phone', 20)->nullable()->default(null);
            $table->string('first_name', 64)->nullable()->default(null);
            $table->string('last_name', 64)->nullable()->default(null);
            $table->string('business_name', 127)->nullable()->default(null);
            $table->string('email', 127)->nullable()->unique();
            $table->string('paypal_id', 13)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_customers');
    }
}
