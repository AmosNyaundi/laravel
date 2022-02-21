<?php

function authToken()
{

  //$test = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
  $test = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

  $auth = base64_encode('ow4WQdSzfPGcLbdZ9J3y203mcngxObNr:gWEJCOILeGPiVyh2');
  //$auth ='cFJZcjZ6anEwaThMMXp6d1FETUxwWkIzeVBDa2hNc2M6UmYyMkJmWm9nMHFRR2xWOQ==';

  $ch = curl_init($test);
  curl_setopt($ch,  CURLOPT_HTTPHEADER,
      [
        'Authorization: Basic '.$auth,
        'Content-Type: application/json; charset=utf8'
      ]);

  $data ='{
          "ShortCode": "",
          "ResponseType": "",
          "ConfirmationURL": "",
          "ValidationURL": ""
        }
        {
          "ShortCode":"600982",
          "CommandID":"CustomerPayBillOnline",
          "Amount":"10",
          "Msisdn":"254707772715",
          "BillRefNumber":"TXUY5TR "
        }';

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);

  $response = curl_exec($ch);
  curl_close($ch);

  echo $response;

$ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer HhKk8I68Uhhd3bjFEWazwelPvGFS',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{
    "ShortCode": 600989,
    "ResponseType": "Completed",
    "ConfirmationURL": "https://mydomain.com/confirmation",
    "ValidationURL": "https://mydomain.com/validation",
  }');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response     = curl_exec($ch);
curl_close($ch);
echo $response;

}





authToken();


