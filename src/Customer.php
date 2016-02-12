<?php

namespace ResultSystems\Paypal;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'paypal_customers';
    protected $fillable = ['address_country', 'address_city', 'address_country_code', 'address_name', 'address_state', 'address_status', 'address_street', 'address_zip', 'contact_phone', 'first_name', 'last_name', 'business_name', 'email', 'paypal_id'];
}
