<?php

$data= '{
    "Body": {
      "stkCallback": {
        "MerchantRequestID": "6340-22331878-1",
        "CheckoutRequestID": "ws_CO_200120221110105094",
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "CallbackMetadata": {
          "Item": [
            {
              "Name": "Amount",
              "Value": 1
            },
            {
              "Name": "MpesaReceiptNumber",
              "Value": "QA90D65TE8"
            },
            {
              "Name": "Balance"
            },
            {
              "Name": "TransactionDate",
              "Value": 20220109162847
            },
            {
              "Name": "PhoneNumber",
              "Value": 254707772715
            }
          ]
        }
      }
    }
  }';

function remote($data)
{

    $url = 'https://164.90.133.19:4040/api/resp/stk';


    $ch = curl_init($url);

    $status="200";
    $customHeaders = array(
        'Content-Type: application/json');
    $request= $data;

    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    if(curl_errno($ch))
    {
        echo 'Request Error:' . curl_error($ch);
    }
    else
    {
    $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo $http_code;

    }

}

echo remote($data);
