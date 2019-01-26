<?php
/**
 * Helper Class To Provide Formatting & Conversion Related Functions
 * @author SAIF UD DIN
 *
 */
class IPHelpers {
	private function get_client_ip() {
		$ipaddress = '';
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
		
		$ipaddress = MD5 ( $ipaddress );
		return $ipaddress;
	}
	private function getPaymentAllowedIPs() {
		return array (
				'26419f1cf1cf8926111a65ee5699afe7', // 43.245.204.45
				'5454121323bfafad080f259b4dc04519', // 111.119.160.222
				'9965d8d51af533f6fb55813e98bdee4c',
				'b145a0b6fdff6ba749ed1852afc33270' // 111.119.160.221
		);
	}
	public static function checkPaymentIP() {
		$ipAddress = IPHelpers::get_client_ip ();
		// echo $ipAddress;
		if (! in_array ( $ipAddress, IPHelpers::getPaymentAllowedIPs () )) {
			return false;
		} else {
			return true;
		}
	}
}