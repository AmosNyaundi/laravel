<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LoyaltyControler extends Controller
{

    public function log_this($lmsg)
    {
        $flog = sprintf("/var/log/popsms/reward%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function log_notify($lmsg)
    {
        $flog = sprintf("/var/log/popsms/notifySMS_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function index()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;
            $date = Carbon::now()->subDays(7);
           // DB::table('loyalty')->truncate();
            $detail = DB::table('purchase')
                    ->join('mpesa_txn', 'mpesa_txn.MpesaReceiptNumber', '=', 'purchase.mpesaReceipt')
                    ->select('msisdn')
                    ->select("*", DB::raw('SUM(purchase.Amount) as total'))
                    ->where(['astatus' => 200])
                    //->where('purchase.created_at', '>=', $date)
                    ->groupBy("msisdn")
                    ->orderBy('total', 'desc')
                    ->having('total', '>', 10)
                    ->get();

            // foreach ($detail as $pr)
            // {
            //     $phone = $pr->msisdn;
            //     $name = $pr->ResultDesc;
            //     $amount = $pr->total;
            //     $bonus = number_format($pr->total * 0.02);

            //     $mql = DB::table('loyalty')
            //             ->upsert(
            //                 ['fname' => $name, 'phone' => $phone, 'amount' => $amount, 'bonus' => $bonus, 'status' => '1', 'initiator' => 'amos'],
            //                 ['fname' => $name, 'phone' => $phone, 'amount' => $amount, 'bonus' => $bonus, 'status' => '1', 'initiator' => 'amos']
            //             );
            // }

            $loyalty = DB::table('loyalty')
                    ->get();

            return view('pages.loyalty',['table' => $loyalty]);
        }
        else
        {
            return redirect()->route('login');
        }
        //return redirect()->route('login');
    }

    public function bonus()
    {
        // $transId = "CHA".Str::random(10);
        // $transId = strtoupper($transId);
        $now = Carbon::now();

        $loyalty = DB::table('loyalty')
                    ->where(['status' => '0'])
                    ->where('bonus', '>=', 5)
                    ->limit(30)
                    //->count()
                    ->get();
        $count = count($loyalty);

        foreach ($loyalty as $key => $pr)
        {
            $transId = "CHA".Str::random(10);
            $transId = strtoupper($transId);
            $msisdn =substr( $pr->phone, -9);
            $FName = strtok($pr->fname, " ");
            $amount = $pr->bonus;

            if($count <= 30)
            {
                $this->notify($msisdn,$FName,$amount);
            }


           /* if($count <= 30)
            {
                $ch = curl_init();
                $headers = array();
                $headers[] = 'Content-Length: 0';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_etrans.php3?agentid=61&transid='.$transId.'&retailerid=15&operatorcode=4&circode=*&product&denomination=0&recharge='.$amount.'&mobileno='.$msisdn.'&bulkqty=1&narration=buy%20airtime&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                $result = curl_exec($ch);
                $this->log_this($result);

                $data = explode("%$", $result);
                $merchanttransid = $data[0];
                $pktransid =$data[1];
                $transdatetime = $data[2];
                $res = explode(".", $data[3]);
                $responsecode = $res[1];
                $responsemessage = trim($data[4],"[SUCCESS:200] ");
                $status = trim($data[5],"$$$");
                curl_close($ch);

                $this->log_this($result);

                DB::table('purchase')->insert([
                    'mpesaReceipt' => 'LOYALTY',
                    'amount' => $amount,
                    'mstatus'=> 0,
                    'astatus' =>  $responsecode,
                    'PhoneNumber' => '0'.$msisdn,
                    'transId' => $merchanttransid,
                    'msisdn' => 'EAZYTOPUP',
                    'reason' => $result,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                $mql = DB::table('loyalty')
                        ->where(['phone' => $pr->phone])
                        ->update(['status' => '0']);

                $this->notify($msisdn,$FName,$amount);
            }
            else
            {
                exit();
            }
            */
        }
       // return redirect()->route('loyalty');

    }

    public function notify($msisdn,$FName,$amount)
    {
        $sms = "Dear $FName, Congratulations for buying airtime from us through Paybill 4040333. You have been rewarded a bonus of $amount Buy more to increase your limit.";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mobilesasa.com/v1/send/message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "senderID": "EAZYTOPUP",
            "message": "Dear '.$FName.', Congratulations for buying airtime from us through Paybill 4040333. You have been rewarded a bonus of '.$amount.'. Buy more to increase your limit.",
            "phone": "254'.$msisdn.'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer NnmjJeifhmUWaXHHChzQgFtUe0KdYaRnEXSnRmlKABsbUAFe6YB6RINQDNsl'
        ),
        ));

        $response = curl_exec($curl);
        $this->log_notify($response);

        curl_close($curl);

        $mql = DB::table('loyalty')
                ->where('phone', $msisdn)
                ->update(['msg' => $sms]);
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
