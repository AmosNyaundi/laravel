<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MpesaTxnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('mpesa_txn')
                    //->orderBy('TransactionDate','DESC')
                    ->latest('TransactionDate')
                    ->get();

            return view('pages.mpesatxn',['table' => $table]);
        }
        //$message = "Session timeout!";
        return redirect()->route('login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function bal()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('mpesa_txn')

                ->orderBy('created_at','DESC')
                ->get();

                // ->chunk(100, function($rows){});

            return view('pages.mpesatxn',['table' => $table]);
        }
        //$message = "Session timeout!";
        return redirect()->route('login');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
