<?php
/**
 * Class To Handle General Methods
 * 
 * @author SAIF UD DIN
 *
 */
class General {
	/**
	 * Function to Get User IP Address
	 */
	public static function getUserIP() {
		if (getenv ( 'HTTP_CLIENT_IP' ))
			$ipaddress = getenv ( 'HTTP_CLIENT_IP' );
		else if (getenv ( 'HTTP_X_FORWARDED_FOR' ))
			$ipaddress = getenv ( 'HTTP_X_FORWARDED_FOR' );
		else if (getenv ( 'HTTP_X_FORWARDED' ))
			$ipaddress = getenv ( 'HTTP_X_FORWARDED' );
		else if (getenv ( 'HTTP_FORWARDED_FOR' ))
			$ipaddress = getenv ( 'HTTP_FORWARDED_FOR' );
		else if (getenv ( 'HTTP_FORWARDED' ))
			$ipaddress = getenv ( 'HTTP_FORWARDED' );
		else if (getenv ( 'REMOTE_ADDR' ))
			$ipaddress = getenv ( 'REMOTE_ADDR' );
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	public static function IsNullOrEmptyString($Variable) {
		return (! isset ( $Variable ) || trim ( $Variable ) === '');
	}
	/**
	 * Function to Create Globally Unique ID
	 *
	 * @param STRING $TempUser
	 */
	public static function createGUID($TempUser = NULL) {
		$token = General::guidv4 () . '-' . round ( microtime ( true ) * 1000 );
		return isset ( $TempUser ) ? $token . 'tmp' : $token;
	}
	public static function guidv4() {
		if (function_exists ( 'com_create_guid' ) === true)
			return trim ( com_create_guid (), '{}' );
			
			$data = openssl_random_pseudo_bytes ( 16 );
			$data [6] = chr ( ord ( $data [6] ) & 0x0f | 0x40 ); // set version to 0100
			$data [8] = chr ( ord ( $data [8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
			return vsprintf ( '%s%s-%s-%s-%s-%s%s%s', str_split ( bin2hex ( $data ), 4 ) );
	}
	/**
	 * Function to Create Tokens
	 *
	 * @param INT $length        	
	 */
	public static function createNewToken($length = 8, $TempUser = NULL) {
		$token = "";
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen ( $characters );
		for($i = 0; $i < $length - 1; $i ++) {
			$token = $token . $characters [rand ( 0, $charactersLength - 1 )];
		}
		return isset ( $TempUser ) ? $token . 'tmp' : $token;
	}
	public static function getDCBOperator($OperatorID) {
		if ($OperatorID === "1000001") {
			return "Moblink";
		} else if ($OperatorID === "1000002") {
			return "Telenor";
		} else if ($OperatorID === "1000003") {
			return "Zong";
		} else {
			return $OperatorID;
		}
	}
    public static function createNumericToken($length = 8, $TempUser = NULL) {
		$token = "";
		$characters = '0123456789';
		$charactersLength = strlen ( $characters );
		for($i = 0; $i < $length - 1; $i ++) {
			$token = $token . $characters [rand ( 0, $charactersLength - 1 )];
		}
		return isset ( $TempUser ) ? $token . 'temp' : $token;
	}
	public static function insertDeviceInfo(&$db, $DeviceInfo) {
		$InsertionArray = array (
				"DeviceID" => $DeviceInfo ["DeviceID"],
				"DeviceMAC" => $DeviceInfo ["DeviceMAC"],
				"DeviceToken" => $DeviceInfo ["DeviceToken"],
				"DeviceOS" => $DeviceInfo ["DeviceOS"],
				"DeviceBrand" => $DeviceInfo ["DeviceBrand"],
				"DeviceName" => $DeviceInfo ["DeviceName"],
				"DeviceModel" => $DeviceInfo ["DeviceModel"],
				"DeviceManufacturer" => $DeviceInfo ["DeviceManufacturer"],
				"DeviceProduct" => $DeviceInfo ["DeviceProduct"] 
		);
		return $db->insert ( "deviceinformation", $InsertionArray );
	}
	public static function insertNewDeviceInfo(&$db, $DeviceInfo) {
		$InsertionArray = array (
				"DeviceID" => $DeviceInfo ["DeviceID"],
				"DeviceMAC" => $DeviceInfo ["DeviceMAC"],
				"DeviceTokenNew" => $DeviceInfo ["DeviceTokenNew"],
				"DeviceOS" => $DeviceInfo ["DeviceOS"],
				"DeviceBrand" => $DeviceInfo ["DeviceBrand"],
				"DeviceName" => $DeviceInfo ["DeviceName"],
				"DeviceModel" => $DeviceInfo ["DeviceModel"],
				"DeviceManufacturer" => $DeviceInfo ["DeviceManufacturer"],
				"DeviceProduct" => $DeviceInfo ["DeviceProduct"]
		);
		return $db->insert ( "deviceinformation", $InsertionArray );
	}
	public static function getResponse($Response) {
		return $Response->withHeader ( 'Content-type', 'application/json' );
	}
	public static function getXMLResponse($Response) {
		return $Response->withHeader ( 'Content-type', 'text/xml' );
	}
}