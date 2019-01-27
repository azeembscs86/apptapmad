<?php
include_once 'Libraries/PDOWrapper/class.db.php';
include_once '../geoip/geoip.php';

$routeFiles = ( array ) glob ( 'Helpers/*.php' );
foreach ( $routeFiles as $routeFile ) {
	include_once $routeFile;
}
/**
 * Class To Handle All Configuration Settings
 *
 * @author SAIF UD DIN
 *        
 */
class Config {
	protected static $imagesDomainName = 'http://www.pitelevision.com/';
	protected static $trialPeriodLimit = 15;
	protected static $getVODsAndMoviesLimit = 14;
	protected static $ChannelsANDVODsLimit = 15;
	protected static $CatchupVODsLimit = 15;
	protected static $WebPageSize = 96;
	protected static $AllowedPackages = '(6,7,8)';
	protected static $MerchantWebKey = "b5l6alobm3n9o9j4scdsa474b8";
        protected static $MerchantSdkKey = "YijhewUuOmnbVCqprqwm";
	public static $db;
	/**
	 * Function to get DataBase Connection
	 *
	 * @return pdodb
	 */
        
	public static function getDataBase() {
		$dsn = 'localhost';
                $dbname='tapmaddb';
		$username = 'root';
		$password = '';
		
		$pdodb = new pdodb ("mysql:host=$dsn;dbname=$dbname", $username, $password );
		$pdodb->exec("SET CHARACTER SET utf8");
		return $pdodb;
	}
	public static function getDataBase2() {
		$dsn = 'mysql:host=50.7.150.10;port=30000;dbname=testingdb';
		$username = 'pipay';
		$password = 'HgUy%425@mN&';
	
		$pdodb = new pdodb ( $dsn, $username, $password );
	
		return $pdodb;
	}
	public static function setConfig($lang) {
		if ($lang == 'en') {
			require 'Messages/Message_en.php';
		} else if ($lang == 'ur') {
			require 'Messages/Message_ur.php';
		} else {
			require 'Messages/Message_en.php';
		}
	}
}