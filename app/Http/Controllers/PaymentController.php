<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function checkout()
    {
        $sadad_checksum_array = [];
        $sadad__checksum_data = [];
        $txnDate = date('Y-m-d H:i:s');
        $email = 'developer@linkia.qa';
        $secretKey = 'tuZkgBe0jdIOHqth';
        $merchantID = '3754062';
        $sadad_checksum_array['merchant_id'] = $merchantID;
        $sadad_checksum_array['ORDER_ID'] = '5978';
        $sadad_checksum_array['WEBSITE'] = 'payment.amaladvertisement.com'; //Should be same as domain name which is used to generate the secret key
        $sadad_checksum_array['TXN_AMOUNT'] = '50.00';
        $sadad_checksum_array['CUST_ID'] = $email;
        $sadad_checksum_array['EMAIL'] = $email;
        $sadad_checksum_array['MOBILE_NO'] = '99999999';
        $sadad_checksum_array['SADAD_WEBCHECKOUT_PAGE_LANGUAGE'] = 'ENG';
        $sadad_checksum_array['CALLBACK_URL'] =
            'http://payment.amaladvertisement.com/callback'; //replace with your callback url
        $sadad_checksum_array['txnDate'] = $txnDate;
        $sadad_checksum_array['productdetail'] =
            array(
                array(
                    'order_id' => $sadad_checksum_array['ORDER_ID'],
                    'quantity' => '1',
                    'amount' => '50',

                    'type' => 'line_item',
                    'itemname' => 'Sample Product'

                )
            );


        $sAry1 = [];
        $sadad_checksum_array1 = [];

        foreach ($sadad_checksum_array as $pK => $pV) {
            if ($pK == 'checksumhash')
                continue;
            if (is_array($pV)) {
                $prodSize = sizeof($pV);
                for ($i = 0; $i < $prodSize; $i++) {
                    foreach ($pV[$i] as $innK => $innV) {
                        $sAry1[] = "<input type='hidden' name='productdetail[$i][" . $innK . "]' value='" . trim($innV) . "'/>";
                        $sadad_checksum_array1['productdetail'][$i][$innK] = trim($innV);
                    }
                }
            } else {
                $sAry1[] = "<input type='hidden' name='" . $pK . "' id='" . $pK . "' value='" . trim($pV) . "'/>";
                $sadad_checksum_array1[$pK] = trim($pV);
            }
        }

        $sadad__checksum_data['postData'] = $sadad_checksum_array1;
        $sadad__checksum_data['secretKey'] = $secretKey;

        $checksum = getChecksumFromString(json_encode($sadad__checksum_data), $secretKey . $merchantID);

        echo $checksum;

        $sAry1[] = "<input type='hidden' name='checksumhash' value='" . $checksum . "'/>";

        $action_url = 'https://sadadqa.com/webpurchase';

        echo '
            <form action="' . $action_url . '" method="post" name="gosadad">' . implode('', $sAry1) . '</form> 
            <script type="text/javascript"> document.gosadad.submit(); </script>
        ';
    }
    function verifychecksum_eFromStr($str, $key, $checksumvalue)
    {
        $sadad_hash = decrypt_e($checksumvalue, $key);
        $salt = substr($sadad_hash, -4);
        $finalString = $str . "|" . $salt;
        $website_hash = hash("sha256", $finalString);
        $website_hash .= $salt;
        $validFlag = "FALSE";

        if ($website_hash == $sadad_hash) {
            $validFlag = "TRUE";
        } else {
            $validFlag = "FALSE";
        }
        return $validFlag;
    }

    function decrypt_e($crypt, $ky)
    {
        $ky = html_entity_decode($ky);
        $iv = "@@@@&&&&####$$$$";
        $data = openssl_decrypt($crypt, "AES-128-CBC", $ky, 0, $iv);
        return $data;
    }

    function getChecksumFromString($str, $key)
    {
        $salt = generateSalt_e(4);
        $finalString = $str . "|" . $salt;
        $hash = hash("sha256", $finalString);
        $hashString = $hash . $salt;
        $checksum = encrypt_e($hashString, $key);
        return $checksum;
    }

    function generateSalt_e($length)
    {
        $random = "";
        srand((float) microtime() * 1000000);
        $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
        $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
        $data .= "0FGH45OP89";
        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }
        return $random;
    }

    function encrypt_e($input, $ky)
    {
        $ky = html_entity_decode($ky);
        $iv = "@@@@&&&&####$$$$";
        $data = openssl_encrypt($input, "AES-128-CBC", $ky, 0, $iv);
        return $data;
    }
}
