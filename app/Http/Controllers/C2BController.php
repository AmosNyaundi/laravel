<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class C2BController extends BuyAirtimeController
{
    public function log_stk($lmsg)
    {
        $flog = sprintf("/var/log/popsms/C2B_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function lipa(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);

    }

    public function reversal(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);

    }

    public function balance(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);

    }


}

