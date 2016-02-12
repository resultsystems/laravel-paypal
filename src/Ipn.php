<?php

namespace ResultSystems\Paypal;

use Illuminate\Database\Eloquent\Model;

class Ipn extends Model
{
    protected $table = 'paypal_ipns';
    protected $fillable = ['txn_id', 'txn_type', 'receiver_email', 'payment_status', 'pending_reason', 'reason_code', 'custom', 'invoice', 'notification', 'hash'];
}
