<?php

function log_this($lmsg)
 {
        $flog = sprintf("recharge_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
 }


function recharge($amount,$msisdn,$service,$result)
{

    $test = 'https://sandbox.safaricom.co.ke/v1/pretups/api/recharge';
    $prod = 'https://prod.safaricom.co.ke/v1/pretups/api/recharge';

    $auth = "ytyt";
    $pin = base64_encode('9090');
    $amt = (int)$amount*100;

    $ch = curl_init($test);
    curl_setopt($ch,  CURLOPT_HTTPHEADER,
        ['Authorization: Bearer '.$auth,
        'Content-Type: application/json'
        ]);

    $request = '{
        "senderMsisdn":"254748248717",
        "amount":"'.$amt.'",
        "servicePin":"'.$pin.'",
        "receiverMsisdn":"'.$msisdn.'"
        }';

    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);


    if(curl_errno($ch))
    {
        $resp ='Request Error:' . curl_error($ch);
        log_this($resp);
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
