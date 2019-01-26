<?php

/**
 * Class To Handle CURL Requests
 *
 * @author SAIF UD DIN
 *
 */
class CURL
{

    /**
     * Function To Handle CURL POST Request
     *
     * @param STRING $Url
     * @param ARRAY $Header
     * @param STRING $DataString
     * @return ARRAY
     */
    public static function Post($Url, $Header, $DataString)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $DataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($DataString)
        ));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        // Timeout in seconds
        // curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        return $result;
    }

    /**
     * Function To Handle CURL GET Request
     *
     * @param STRING $Url
     * @param ARRAY $Header
     * @return ARRAY
     */
    public static function Get($Url, $header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $body = '{}';
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        // curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        //print_r($result);
        return $result;
    }
}