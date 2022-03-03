<?php

function log_this($lmsg)
 {
        $flog = sprintf("recharge_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
 }

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


   $url = 'https://157.230.92.224:4835/call.php';


    $ch = curl_init($url);

    //$status="200";
    $msisdn = "707772715";
    $amount = 10;
    $transId = rand(5,10);
    $transId = strtoupper($transId);

    $customHeaders = array(
        'Content-Type: application/json');
    $request= '{
        "msisdn":"'.$msisdn.'",
        "transId":"'.$transId.'",
        "amount":"'.$amount.'"
   }';

    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    echo $result;
    if(curl_errno($ch))
    {
        echo 'Request Error:' . curl_error($ch);
    }
    else
    {
       $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo $result;
   }



