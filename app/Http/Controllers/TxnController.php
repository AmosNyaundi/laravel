<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TxnController extends Controller
{
    public function show()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('purchase')
                    ->where('mstatus', 0)
                    ->latest()
                    ->get();

            return view('pages.txn',['table' => $table]);
        }
        return redirect()->route('login');
    }
}
