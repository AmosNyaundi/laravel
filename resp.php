<?php


$data=file_get_contents('php://input');

function log_flow($lmsg)
 {
       $flog = sprintf("/var/log/popsms/stkpush_response_%s.log",date("Ymd-H"));
       $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
       $f = fopen($flog, "a");
       fwrite($f,$tlog);
       fclose($f);
 }



 function results($data)
 {
    $link = mysqli_connect("localhost", "root", "M1234!agre", "laravel");

    $request = json_decode($data, true);
    $ResultCode = $request->input['Body.stkCallback.ResultCode'];
    $MerchantRequestID = $request->input['Body']['stkCallback']['MerchantRequestID'];

    if($ResultCode === 0)
    {   
        $amount = $request['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
        $MpesaReceiptNumber = $request['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
        $TransactionDate = $request['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
        $PhoneNumber = $request['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];
        $Balance = $request['Body']['stkCallback']['CallbackMetadata']['Item'][2]['Value'];
        $ResultDesc = $request['Body']['stkCallback']['ResultDesc'];

        $resp = "Amount: ".$amount." MpesaReceiptNumber: ".$MpesaReceiptNumber." TransactionDate: ".$TransactionDate." PhoneNumber: ".$PhoneNumber;
        
        $mql = "UPDATE mpesa_txn  SET ResultCode='$ResultCode',ResultDesc='$ResultDesc',Amount=''$amount,MpesaReceiptNumber='$MpesaReceiptNumber',
        TransactionDate='$TransactionDate',PhoneNumber='$PhoneNumber',Balance='$Balance' WHERE MerchantRequestID='$MerchantRequestID'";
        
        mysqli_query($link, $mql);

        mysqli_close($link);

        log_flow("RESP:".$resp);
    }
    else
    {
        log_flow("RESP:".$data);
    }
    
    return $resp;
    //log_flow("RESP:".$resp);
}
 results($data);