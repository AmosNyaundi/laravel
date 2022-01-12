<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $table = 'mpesa_txn';

    //$bal = Balance::table('mpesa_txn')
    // ->latest()
    // ->first();
}
