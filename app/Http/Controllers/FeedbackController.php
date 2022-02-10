<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        $request = array(
            'Name' => $_POST['name'],
            'Email' => $_POST['email'],
            'Message' => $_POST['message'],
      );

        $rt = json_encode($request);

        $dat = json_decode($rt);
        $jina = $dat->Name;
        $email = $dat->Email;
        $msg = $dat->Message;

        DB::table('feedback')->insert([
            'name' => $jina,
            'email' => $email,
            'message' => $msg

        ]);

        $this->log_this($rt);
    }

    public function log_this($lmsg)
    {
        $flog = sprintf("/var/log/popsms/feedback%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }
}
