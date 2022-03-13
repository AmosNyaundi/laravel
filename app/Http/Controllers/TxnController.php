<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class TxnController extends Controller
{
    public function show()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('purchase')
                    ->whereDate('created_at', Carbon::today())
                    ->orderBy('created_at', 'desc')
                    ->latest()
                    ->get();

            return view('pages.txn',['table' => $table]);
        }
        return redirect()->route('login');
    }

    public function txn()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('purchase')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->orderBy('created_at', 'desc')
                    ->latest()
                    ->get();

            return view('pages.txn',['table' => $table]);
        }
        return redirect()->route('login');
    }
}
