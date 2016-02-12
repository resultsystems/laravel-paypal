<?php

namespace ResultSystems\Paypal;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'paypal_transactions';
    protected $fillable = ['invoice', 'custom', 'txn_type', 'txn_id', 'payer_id', 'currency', 'gross', 'fee', 'handling', 'shipping', 'tax', 'payment_status', 'pending_reason', 'reason_code'];
}
