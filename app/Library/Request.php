<?php
/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 26/06/2018
 * Time: 15:08
 */

namespace App\Library;


class Request
{
    public static function rest($endpoint, $post = [], $header = [], $CURLOPT = 'POST', $user = false, $senha = false) {
        $arrReturn = [];

        if($CURLOPT == 'GET')
            $endpoint = $endpoint . "?" . http_build_query($post);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 150);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $CURLOPT);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        if(count($header) > 0)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if($user && $senha)
            curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $senha);

        $arrReturn['return'] = curl_exec($ch);
        $arrReturn['info'] = curl_getinfo($ch);
        $arrReturn['error'] = curl_error($ch);
        $arrReturn['url'] = $endpoint;
        $arrReturn['header'] = $header;
        curl_close($ch);
        return (Object)$arrReturn;
    }
}