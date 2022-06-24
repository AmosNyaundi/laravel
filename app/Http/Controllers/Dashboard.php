<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Charts\TxnSummary;


class Dashboard extends Controller
{
    public function dashboard()
    {
        if(Auth::check())
        {

            $user = auth()->user()->username;

            // $bal = DB::table('mpesa_txn')
            //         ->latest()
            //         ->first();

            $total_trans = DB::table('purchase')
                        ->where(['astatus' => 200])
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->count();

            $total_air = DB::table('purchase')
                        ->where('astatus', '=', 200)
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->sum('amount');

            $trans =    DB::table('purchase')
                        ->where(['astatus' => 200])
                        ->whereDate('created_at', Carbon::today())
                        ->count();

            $air =      DB::table('purchase')
                        ->where('astatus', '=', 200)
                        ->whereDate('created_at', Carbon::today())
                        ->sum('amount');


            $airtime =  DB::table('purchase')
                        ->where(['astatus' => 200])
                        ->select(
                            DB::raw('COUNT(*) as sum'),
                            DB::raw("DATE_FORMAT(created_at,'%M') as month")
                        )
                        ->groupBy('month')
                        ->get();

            $txn =  DB::table('purchase')
                        ->where(['astatus' => 200])
                        ->select(
                            DB::raw('sum(Amount) as sum'),
                            DB::raw("DATE_FORMAT(created_at,'%M') as month")
                        )
                        ->groupBy('month')
                        ->get();



            $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
            $transData = array();
            $airData = array();
            foreach ($months as $month)
            {
                $r = $airtime->where('month', $month)->first();
                $count = isset($r) ? $r->sum : 0;
                array_push($transData, $count);

                $s = $txn->where('month', $month)->first();
                $countS = isset($s) ? $s->sum : 0;
                array_push($airData, $countS);
            }

            $chart = new TxnSummary();
            $chart->labels($months);
            $chart->dataset("Transactions", "line", $transData)
                ->color("rgb(66,133,244)")
                ->backgroundcolor("rgb(66,133,244)")
                ->fill(FALSE)
                ->linetension(0.5);
            $chart->dataset("Airtime", "line", $airData)
                ->color("rgb(255, 99, 132)")
                ->backgroundcolor("rgb(255, 99, 132)")
                ->fill(FALSE)
                ->linetension(0.5);

            $chart->displayLegend(true);

            return view('pages.home', compact('total_trans','trans','total_air','air','chart'));

        }
        else
        {
            return redirect()->route('login');
        }

        //return redirect()->route('login');
    }


}
