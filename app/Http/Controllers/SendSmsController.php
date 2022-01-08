<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class SendSmsController extends Controller
{

    public function sendsms()
    {

        // if(!Auth::check())
        // {
        //     $message = "Session timeout!";
        //     return redirect()->route('login')->with(['message' => $message]);
        // }
        // //$keyword = DB::table('service_conf')->get();
        // $user = auth()->user()->username;
        // $keyword = DB::table('service_conf')->where('username', $user)->pluck('keyword');

        // return view('pages.sendsms', ['keyword' => $keyword]);

        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('saf_que_run')
                ->select(DB::raw('count(*) as count, flag,keyword,message,created_at'))
                ->where('username', $user)
                ->groupBy('keyword')
                ->get();

            return view('pages.sendsms',['table' => $table]);
        }
        $message = "Session timeout!";
        return redirect()->route('login')->with(['message' => $message]);
    }

    public function outbox()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('saf_que_run')
                ->select(DB::raw('count(*) as count, flag,keyword,message,created_at'))
                ->where('username', $user)
                ->groupBy('keyword')
                ->get();

            return view('pages.sms_outbox',['table' => $table]);
        }
        $message = "Session timeout!";
        return redirect()->route('login')->with(['message' => $message]);
    }



        /**
         * Get the default context variables for logging.
         *
         * @return array
         */

    public function submitsendsms(Request $request)
    {
        $request->validate([
            'keyword' => 'required',
            'to' => 'required',
            'message' => 'required',
        ]);

        $keyword = $request['keyword'];
        $text = $request['message'];
        $to=$_REQUEST['to'];
        $dest=$_REQUEST['txtSingle'];

        switch($to)
        {

            case 'single':
                if($dest=='')
                {
                    $message = "Please fill in Single number field";
                    $status = "danger";
                    return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                }
                else
                {
                    $offercode=DB::table('service_conf')->where('spid','160')->where('shortcode','20750')->where('keyword',$keyword)->value('offercode');

                    if(!$offercode)
                    {

                        $message = "One of the parameters is missing! Check SPID, shortcode or Keyword in the service table and try again.";
                        $status = "danger";
                        return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                    }
                    else
                    {
                        $mql=DB::table('safaricom_subs')->where('msisdn',$dest)->where('offercode',$offercode)->first();

                        //$rcnt = count($mql);

                        if(!$mql)
                        {
                            $message = "The combination of $dest and offercode $offercode does not exist in the subscription database for $keyword";
                            $status = "danger";
                            return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                        }
                        else
                        {
                            if (DB::table('saf_que_run')->where('offercode', $offercode)->exists())
                            {

                                $message="Duplicate entry! Message for keyword $keyword already exists!";
                                $status = "danger";
                                return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                            }
                            else
                            {
                                $user = auth()->user()->username;
                                $mql2=DB::table('saf_que_run')->insert([
                                    'msisdn' => $dest,
                                    'offercode' => $offercode,
                                    'keyword' => $keyword,
                                    'spid' => '160',
                                    'message' => $text,
                                    'shortcode' => '20750',
                                    'flag' =>'LOAD',
                                    'username' => $user,
                                    'created_at' => Carbon::now()
                                ]);}

                            if($mql2)
                            {
                                $message="Your Message is queued for delivery";
                                $status = "info";
                                return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                            }
                            else
                            {

                                //if (DB::table('saf_que_run')->where(['msisdn' =>$dest, 'offercode'=>$offercode])->exists()) {

                                    $message="Error! Try again.";
                                    $status = "danger";
                                    return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                                //}

                            }
                        }

                    }

                }
                break;

            case 'all':

                $offercode=DB::table('service_conf')
                ->where('spid','160')
                ->where('shortcode','20750')
                ->where('keyword',$keyword)
                ->value('offercode');

                if(!$offercode)
                {

                    $message = "One of the parameters is missing! Check SPID, shortcode or Keyword in the service table and try again.";
                    $status = "danger";
                    return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                }
                else
                {
                    $mql=DB::table('safaricom_subs')->where('subtype', 'ACTIVATION')->where('offercode',$offercode)->pluck('msisdn');

                    foreach($mql as $data)
                    {
                        $destarray=$data;

                        $user = auth()->user()->username;
                        $mql3=DB::table('saf_que_run')->insert([
                            'msisdn' => $destarray,
                            'offercode' => $offercode,
                            'keyword' => $keyword,
                            'spid' => '160',
                            'message' => $text,
                            'shortcode' => '20750',
                            'flag' =>'LOAD',
                            'username' => $user,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                    if($mql3)
                    {
                        $message="Your Message is queued for delivery";
                        $status = "info";
                        return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                    }
                    else
                    {
                        $message="Your Message has not been queued for delivery!";
                        $status = "danger";
                        return redirect()->route('sendsms')->with(['message' => $message,'status' =>$status]);
                    }
                }
        }
    }
}
