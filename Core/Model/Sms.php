<?php
/**
 * Created by PhpStorm.
 * User: Poizon
 * Date: 27/07/2015
 * Time: 12:12
 */

namespace Core\Model;


class Sms {
    private static $sendername = 'HelloCare';
    private static $login = 'hellocare';
    private static $password = 'password';
    private static $username = 'hellocare';
    private static $pass = 'hellocare123';
    private static $port = '22104';

    public static function sendSms($number,$message,$sender){
        $message = urlencode($message);
        $sender = urlencode($sender);
		/*$url = "https://www.didstation.com/sms_http_api&lang=en&username=".self::$username."&password=".self::$pass."&from=".$sender."&to=".$number."&message=".$message."&dlr=1&message_type=0&route_type=1"; */
		
		$url = "https://www.didstation.com/sms_http_api&lang=en&username=fabricegousse@gmail.com&password=Masterp1988&from=".$sender."&to=237".$number."&message=".$message."&dlr=1&message_type=0&route_type=1";
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$return = curl_exec($ch);
		
		if ($return === FALSE) {
		   die(curl_error($ch));
		}
        curl_close($ch);
		
        return $return;
    }

    public static function resultSms($number,$message,$sender){
		//var_dump(self::sendSms($number,$message,$sender));
        if(self::sendSms($number,$message,$sender)){
			//var_dump('Test 3');
            return true;
        }else{
            return false;
        }
    }

    

}