<?php

//$resp = mt_rand(1000,9999);

$dat = "Ez#0707644931#muranga";
$data = explode("#", $dat);

$phone = substr($data[1], -9);;
$region = $data[2];
echo $phone."-".$region;

//    $req = '{
//     "requestId":"10185461720890381332",
//     "requestTimeStamp":"20220612123924",
//     "requestParam":{
//        "data":[
//           {
//              "name":"LinkId",
//              "value":"00160110185461720892252331"
//           },
//           {
//              "name":"OfferCode",
//              "value":"001016031225"
//           },
//           {
//              "name":"RefernceId",
//              "value":"10185461720890381332"
//           },
//           {
//              "name":"ClientTransactionId",
//              "value":"333259946"
//           },
//           {
//              "name":"Language",
//              "value":"1"
//           },
//           {
//              "name":"Channel",
//              "value":"1"
//           },
//           {
//              "name":"Type",
//              "value":"NOTIFY_LINKID"
//           },
//           {
//              "name":"USER_DATA",
//              "value":"Ez#0707644931#muranga"
//           },
//           {
//              "name":"Msisdn",
//              "value":"254799248518"
//           }
//        ]
//     },
//     "operation":"CP_NOTIFICATION"
//  }';





//     $data = json_decode($req, false);
//     $requestId=$data->requestId;
//     $requestTimeStamp=date_format(date_create($data->requestTimeStamp), 'Y-m-d H:i:s');
//     $linkId=$data->requestParam->data[0]->value;
//     $OfferCode=$data->requestParam->data[1]->value;
//     $RefernceId=$data->requestParam->data[2]->value;
//     $ClientTransactionId=$data->requestParam->data[3]->value;
//     $Channel=$data->requestParam->data[5]->value;
//     $Type=$data->requestParam->data[6]->value;
//     $user_data=$data->requestParam->data[7]->value;
//     $Msisdn=$data->requestParam->data[8]->value;
//     $cr=explode(" ",$user_data);
//     $cid=$cr[0];
//     $shortcode = 20750;

//     $resp="RESP| MS:".$Msisdn."|OFCODE:".$OfferCode."|USERDATA:".$user_data."|TYPE:".$Type."|SC:".$shortcode."|CTID".$ClientTransactionId."|REQID:".$requestId."|RTIME:".$requestTimeStamp."|RfID:".$RefernceId;
    //$misql=sprintf("INSERT INTO ondemand(criteria,message,msisdn,shortcode,offercode,keyword,linkid,requestid,clientTransactionId,referenceId,spid,requestTimeStamp) values('%s','%s','%s','%s','%s','%s','%s','%s','%s',NOW(),'%s','%s','%s','%s','%s','%s')",$criteria,$user_data,$Msisdn,$shortcode,$OfferCode,$keyword,$linkId,$requestId,$ClientTransactionId,$RefernceId,$cpid,$requestTimeStamp);
    //$res=mysqli_query($connection,$misql);



// $string = "39133213700503961781";
// $myarray = str_split($string, 4);
// $token = implode("-", $myarray);

// echo $token;


// $value = "Innocent Nyerere Bosire";
// echo strtok($value, " "); // Test

// $number = "+25472 2222222";
// $phone = preg_replace('/\D+/', '', $number);

//echo $phone;

// function log_this($lmsg)
//  {
//         $flog = sprintf("recharge_%s.log",date("Ymd-H"));
//         $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
//         $f = fopen($flog, "a");
//         fwrite($f,$tlog);
//         fclose($f);
//  }

//  $msisdn = "707772715";
//  $amount = 10;
//  $transId = rand(5,10);
//  $transId = strtoupper($transId);

//         $test = 'https://157.230.92.224:4835/call.php';
//         $ch = curl_init($test);
//         curl_setopt($ch,  CURLOPT_HTTPHEADER,
//             [
//             'Content-Type: application/json'
//             ]);

//         $request = '{
//             "msisdn":"'.$msisdn.'",
//             "transId":"'.$transId.'",
//             "amount":"'.$amount.'",
//             }';

//         curl_setopt($ch, CURLOPT_POST,1);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
//         //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//         $result = curl_exec($ch);
//         $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
//         curl_close($ch);

/*
function recharge($amount,$msisdn,$service,$result)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_etrans.php3?agentid=61&transid='.$transId.'&retailerid=15&operatorcode=4&circode=*&product&denomination=0&recharge='.$amount.'&mobileno='.$phone.'&bulkqty=1&narration=buy%20airtime&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    $result = curl_exec($ch);
    echo $result;

    if(curl_errno($ch))
    {
        $resp ='Request Error:' . curl_error($ch);
        log_this($resp);
        echo $resp;
    }
    else
    {
        $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        switch($http_code)
        {
            case "200":  # OK
            $data = json_decode($response);
            $responseId=$data->responseId;
            $responseDesc=$data->responseDesc;
            $responseStatus =$data->responseStatus;
            $transId=$data->transId;
            $resp="HTTP_CODE: ".$http_code."|ResponseID: ".$responseId."|Status: ".$responseStatus."|TransactionID: ".$transId."|Message: ".$responseDesc;
             log_this($resp);

             if($http_code === 200)
             {
               //StkPush($msisdn,$amount,$service,$result);
             }
            break;

            default:
            $data = json_decode($response);
            $errorCode=$data->errorCode;
            $errorMessage=$data->errorMessage;
            $resp="|HTTP_CODE: ".$http_code."|errorCode: ".$errorCode."|message: ".$errorMessage;
            log_this($resp);
            if($errorCode == '401.002.01'){

                //refresh();
                echo "Try again";
            }
            break;
        }
    }

    curl_close($ch);

}
*/


//    $url = 'https://157.230.92.224:4835/call.php';


//     $ch = curl_init($url);

//     //$status="200";
//     $msisdn = "707772715";
//     $amount = 10;
//     $transId = rand(5,10);
//     $transId = strtoupper($transId);

//     $customHeaders = array(
//         'Content-Type: application/json');
//     $request= '{
//         "msisdn":"'.$msisdn.'",
//         "transId":"'.$transId.'",
//         "amount":"'.$amount.'"
//    }';

//     curl_setopt($ch, CURLOPT_POST,1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     $result = curl_exec($ch);
//     echo $result;
//     if(curl_errno($ch))
//     {
//         echo 'Request Error:' . curl_error($ch);
//     }
//     else
//     {
//        $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
//         echo $result;
//    }



// $data=file_get_contents('php://input');

// $request = json_decode($data);

/*
$msisdn = $request->msisdn;
$transId = $request->transId;
$amount = $request->$amount;

function recharge($amount,$msisdn,$transId)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_etrans.php3?agentid=61&transid='.$transId.'&retailerid=15&operatorcode=4&circode=*&product&denomination=0&recharge='.$amount.'&mobileno='.$msisdn.'&bulkqty=1&narration=buy%20airtime&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    $result = curl_exec($ch);
   // echo $result;

    if(curl_errno($ch))
    {
        $resp ='Request Error:' . curl_error($ch);
        log_this($resp);
        echo $resp;
    }
    else
    {
            $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
            switch($http_code)
            {
                  case "200":  # OK
                  echo $result;
                  break;

                  default:
                        echo "Failed. Try again";

                  break;
            }
      }

      curl_close($ch);
}
*/
// function log_flow($lmsg)
// {
//       $flog = sprintf("/var/log/popsms/KimTai_%s.log",date("Ymd-H"));
//       $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
//       $f = fopen($flog, "a");
//       fwrite($f,$tlog);
//       fclose($f);
// }

// //echo recharge($amount,$msisdn,$transId);

// echo log_flow("RESP:".$data);

// if ($_SERVER['REQUEST_METHOD'] == 'POST')
// {
//       function get_data() {
//             $datae = array(
//                   'Name' => $_POST['name'],
//                   'Email' => $_POST['email'],
//                   'Message' => $_POST['message'],
//             );

//             return json_encode($datae);
//       }
// }


//$data = get_data();
//log_flow("RESP:".$data);

    // $link = mysqli_connect("localhost", "root", "M1234!agre", "laravel");

    //   $request = json_decode($data);

    //  $ResultCode=$request->Body->stkCallback->ResultCode;


    // //echo $ResultCode;

    // if($ResultCode === 0)
    // {
    //   $MerchantRequestID = $request->Body->stkCallback->MerchantRequestID;
    //   $CheckoutRequestID = $request->Body->stkCallback->CheckoutRequestID;
    //   $amount = $request->Body->stkCallback->CallbackMetadata->Item[0]->Value;
    //   $MpesaReceiptNumber = $request->Body->stkCallback->CallbackMetadata->Item[1]->Value;
    //   $TransactionDate = $request->Body->stkCallback->CallbackMetadata->Item[3]->Value;
    //   $PhoneNumber = $request->Body->stkCallback->CallbackMetadata->Item[4]->Value;
    //   $Balance = $request->Body->stkCallback->CallbackMetadata->Item[2]->Value;
    //   $ResultDesc = $request->Body->stkCallback->ResultDesc;

    //     $resp = "Amount: ".$amount." MpesaReceiptNumber: ".$MpesaReceiptNumber." TransactionDate: ".$TransactionDate." PhoneNumber: ".$PhoneNumber;

    //     $mql = "UPDATE mpesa_txn  SET ResultCode='$ResultCode',ResultDesc='$ResultDesc',Amount='$amount',MpesaReceiptNumber='$MpesaReceiptNumber',
    //     TransactionDate='$TransactionDate',PhoneNumber='$PhoneNumber',Balance='$Balance' WHERE MerchantRequestID='$MerchantRequestID'";

    //     $mq = "INSERT INTO purchase(mpesaReceipt,amount,mstatus,msisdn) VALUES('','','','',)";

    //     mysqli_query($link, $mql);

    //     //mysqli_close($link);

    //     log_flow("RESP:".$resp);
    // }
    // else
    // {
    //   $CheckoutRequestID = $request->Body->stkCallback->CheckoutRequestID;
    //   $MerchantRequestID = $request->Body->stkCallback->MerchantRequestID;
    //   $ResultDesc = $request->Body->stkCallback->ResultDesc;

    //   $mql2 = "UPDATE mpesa_txn  SET ResultCode='$ResultCode',ResultDesc='$ResultDesc',MerchantRequestID='$MerchantRequestID',
    //   CheckoutRequestID='$CheckoutRequestID', MpesaReceiptNumber='NA' WHERE MerchantRequestID='$MerchantRequestID'";

    //   mysqli_query($link, $mql2);

    //   //mysqli_close($link);

    //   log_flow("RESP:".$data);

    // }

    //log_flow("RESP:".$datae);


