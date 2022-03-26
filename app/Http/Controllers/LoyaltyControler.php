<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoyaltyControler extends Controller
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
           //$user = auth()->user()->username;

            $detail = DB::table('purchase')
                    ->select("*", DB::raw('SUM(amount) as total'))
                    ->where(['astatus' => 200])
                    ->whereDate('created_at', Carbon::now()->month)
                    ->groupBy("msisdn")
                    ->orderBy('total', 'desc')
                    ->having('total', '>', 100)
                    ->get();

                    // ->whereDate('created_at', Carbon::today())
                    // ->orderBy('created_at', 'desc')
                    // ->latest()
                    // ->get();
            $name = DB::table('mpesa_txn')
                    //->limit(1)
                    ->get();

            $table = $detail->concat($name);

            return view('pages.loyalty',['table' => $table]);
        }
        return redirect()->route('login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
