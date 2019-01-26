<?php
/**
 * Class To Handle All Success Response Objects
 *
 * @author SAIF UD DIN
 *
 */
class SuccessObject {
	public static function returnMessage($SuccessObject){
		$Array ['Response'] = $SuccessObject;
							   
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getPaymentSuccessObject($Data, $SuccessObject) {
		$Array ['Response'] = $SuccessObject;
		$Array ['Transaction'] = $Data;
		
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getUserSubscriptionSuccessObject($Data, $SuccessObject) {
		$Array ['Response'] = $SuccessObject;
		$Array ['UserSubscription'] = $Data;
		
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getEmptyAdSuccessObject($Data, $SuccessObject, $ItemToSlice = NULL) {
		$Array ['Response'] = $SuccessObject;
		if ($ItemToSlice) {
			$Video = array_slice ( $Data, 0, $ItemToSlice );
			$Ad = array_slice ( $Data, $ItemToSlice );
			$Array ['Video'] = $Video;
			$Array ['Ad'] = null;
		} else {
			$Array ['Ad'] = $Data;
		}
		
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getAddSuccessObject($Data, $SuccessObject, $ItemToSlice = NULL, $Midrolls = array())
	{
	    $Array['Response'] = $SuccessObject;
	    if ($ItemToSlice) {
	        $Video = array_slice($Data, 0, $ItemToSlice);
	        $Ad = array_slice($Data, $ItemToSlice);
	        $Array['Video'] = $Video;
	        $Array['Ad'] = $Ad;
	        $Array['Midrolls'] = $Midrolls;
	    } else {
	        $Array['Ad'] = $Data;
	    }
	    
	    return json_encode($Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	}
	
	
	/**
	 * Function to Get User Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 */
	public static function getUserPackagesSuccessObject($userArray, $userProfileArray, $userSubscriptionArray,$userPackagesArray, $successObject) {
		$array ['Response'] = $successObject;
		$array ['User'] = $userArray;
		$array ['UserProfile'] = $userProfileArray;
		$array ['UserSubscription'] = $userSubscriptionArray;
                $array ['UserPackages'] = $userPackagesArray;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	   /**
	 * Function to Get User Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 */
	public static function getSingleUsersPackagesSuccessObjects($userArray,$userProfileArray,$usersSubscriptionsArray, $successObject) {
		$array ['Response'] = $successObject;
		$array ['User'] = $userArray;	
                $array ['UserProfile'] = $userProfileArray;
                $array ['UserActiveSubscription'] = $usersSubscriptionsArray;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	/**
	 * Function to Get User Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 */
	public static function getUsersPackagesSuccessObjects($userArray, $userProfileArray, $userSubscriptionArray,$usersSubscriptionsArray,$userPackagesArray, $successObject) {
		$array ['Response'] = $successObject;
		$array ['User'] = $userArray;
		$array ['UserProfile'] = $userProfileArray;
		$array ['UserSubscription'] = $userSubscriptionArray;
		$array ['UserActiveSubscription'] = $usersSubscriptionsArray;
                $array ['UserPackages'] = $userPackagesArray;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function to Get Video Success Response as JSON Object 
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 * @param INT $Limit        	
	 * @param INT $Size        	
	 * @return JSON
	 */
	public static function getVideoSuccessObject($data, $successObject, $Limit = NULL, $Size = NULL, $itemName = NULL) {
		$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		if ($itemName) {
			$array [$itemName] = $data;
		} else {
			$array ['Videos'] = $data;
		}
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getRelatedVideoSuccessObject($SuccessObject, $VideoObject = NULL, $RelatedVideosObject =  array()) {
		$Array ['Response'] = $SuccessObject;
		$Array ['Video'] = $VideoObject;
		$Array ['Sections'] = $RelatedVideosObject;
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getRelatedcatchupVideoSuccessObject($SuccessObject, $VideoObject = NULL, $Limit = NULL, $Size = NULL) {
		$Array ['Response'] = $SuccessObject;
                if ($Limit) {
			$Array ['Limit'] = $Limit;
			$Array ['Size'] = $Size;
		}
		$Array ['Video'] = $VideoObject;
		//$Array ['Sections'] = $RelatedVideosObject;
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getTVVideoSuccessObject($SuccessObject, $Categories, $Channels) {
		$ResponseArray ['Response'] = $SuccessObject;
		$ResponseArray ['DateTime'] = array (
				'CurrentDateTime' => date ( 'Y-m-d H:i:s' ) 
		);
		$ResponseArray ['Categories'] = $Categories;
		$ResponseArray ['Videos'] = $Channels;
		return json_encode ( $ResponseArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	public static function getSeasonsSuccessObject($SuccessObject, $Categories = [], $Seasons = [])
	{
	    $ResponseArray['Response'] = $SuccessObject;
	    $ResponseArray['DateTime'] = array(
	        'CurrentDateTime' => date('Y-m-d H:i:s')
	    );
	    $ResponseArray['Categories'] = $Categories;
	    $ResponseArray['Seasons'] = $Seasons;
	    return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	}
	
	public static function getPageVideoSuccessObject($DataArray, $SuccessObject, $CurrentItems = NULL, $TotalItems = NULL, $ItemName = NULL) {
		$array ['Response'] = $SuccessObject;
		if ($CurrentItems) {
			$array ['TotalItems'] = $TotalItems;
			$array ['CurrentItems'] = $CurrentItems;
		}
		if (isset ( $DataArray [0] ['VideoCategoryId'] )) {
			$array ['VideoCategoryId'] = $DataArray [0] ['VideoCategoryId'];
		}
		if (isset ( $DataArray [0] ['VideoCategoryName'] )) {
			$array ['VideoCategoryName'] = $DataArray [0] ['VideoCategoryName'];
		}
		if ($ItemName) {
			$array [$ItemName] = $DataArray;
		} else {
			$array ['Videos'] = $DataArray;
		}
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 * @param INT $Limit        	
	 * @param INT $Size        	
	 * @return JSON
	 */
	public static function getSectionSuccessObject($data, $successObject, $Limit = NULL, $Size = NULL, $Ad = NULL) {
		$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		$array ['Ad'] = $Ad;
		$array ['Tabs'] = $data;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 * @param INT $Limit        	
	 * @param INT $Size        	
	 * @return JSON
	 */
	public static function getOtpSectionSuccessObject($data, $successObject, $Limit = NULL, $Size = NULL, $Ad = NULL) {
		//$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		//$array ['Ad'] = $Ad;
		$array ['otpbanners'] = $data;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function to Get User Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 */
	public static function getUserSuccessObject($userArray, $userProfileArray, $userSubscriptionArray, $successObject) {
		$array ['Response'] = $successObject;
		$array ['User'] = $userArray;
		$array ['UserProfile'] = $userProfileArray;
		$array ['UserSubscription'] = $userSubscriptionArray;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function To Get Simple Success Object
	 *
	 * @param ARRAY $successObject        	
	 * @return JSON
	 */
	public static function getSuccessObject($successObject) {
		return json_encode ( $successObject, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getGeneralSuccessObject2($SuccessObject, $DataArray = NULL, $ItemName = NULL, $Limit = NULL, $Size = NULL) {
		$Array ['Response'] = $SuccessObject;
		if ($DataArray) {
			if ($ItemName) {
				$Array [$ItemName] = $DataArray;
			} else {
				$Array ['Data'] = $DataArray;
			}
		}
		if ($Limit) {
			$Array ['Limit'] = $Limit;
			$Array ['Size'] = $Size;
		}
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	//-------------------success object for home detail with otp banners-------------------
        
        public static function getSectionsSuccessObject($data, $successObject, $Limit = NULL, $Size = NULL, $Ad = NULL,$Otp=Null) {
		$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		$array ['Ad'] = $Ad;
		$array ['Tabs'] = $data;
        $array ['OtpBanners'] = $Otp;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	
	
	//-------------------success object for home detail with otp banners and packages-------------------
        
        public static function getSectionsSuccesssObject($data, $successObject, $Limit = NULL, $Size = NULL, $Ad = NULL,$Otp=Null,$Pac=Null) {
		$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		$array ['Ad'] = $Ad;
		$array ['Tabs'] = $data;
                $array ['OtpBanners'] = $Otp;
                $array ['Packages'] = $Pac;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	/**
	 * Function to Get Temp User Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 */
	public static function getTempUserSuccessObject($data, $successObject) {
		$array ['Response'] = $successObject;
		$array ['TempUser'] = $data;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function To Get General Success Response As JSON Object
	 *
	 * @param ARRAY $Data        	
	 * @param ARRAY $SuccessObject        	
	 * @param STRING $ItemName        	
	 * @return JSON
	 */
	public static function getGeneralSuccessObject($Data, $SuccessObject, $ItemName = NULL) {
		$Array ['Response'] = $SuccessObject;
		$Array ['DateTime'] = array (
				'CurrentDateTime' => date ( 'Y-m-d H:i:s' )
		);
		if ($ItemName) {
			$Array [$ItemName] = $Data;
		} else {
			$Array ['Data'] = $Data;
		}
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	
	/**
	 * Function to Get Video Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 * @param INT $Limit        	
	 * @param INT $Size        	
	 * @return JSON
	 */
	public static function getVideosSuccessObjects($data, $successObject, $Limit = NULL, $Size = NULL,$season=NULL, $itemName = NULL) {
		$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
                 $array ['IsSeason'] = $season;
		if ($itemName) {
			$array [$itemName] = $data;
		} else {
			$array ['Videos'] = $data;
		}
               
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	
	//-------------------success object for home detail with otp banners and packages and Bucket-------------------
        
        public static function getBucketSectionsSuccesssObject($data, $successObject, $Limit = NULL, $Size = NULL, $Ad = NULL,$Otp=Null,$Pac=Null,$Buc=Null) {
		$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		$array ['Ad'] = $Ad;
		$array ['Tabs'] = $data;
                $array ['OtpBanners'] = $Otp;
                $array ['Packages'] = $Pac;
                $array ['Bucket'] = $Buc;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	/**
	 * Function to Get Categories Video Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 * @param INT $Limit        	
	 * @param INT $Size        	
	 * @return JSON
	 */
	public static function getCategoriesVideoSuccessObject($data, $successObject, $Limit = NULL, $Size = NULL, $itemName = NULL) {
		$array ['Response'] = $successObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		if ($itemName) {
			$array [$itemName] = $data;
		} else {
			$array ['Categories'] = $data;
		}
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	
	public static function getRelatedsVideoSuccessObjects($SuccessObject, $VideoObject = NULL, $RelatedVideosObject =  array()) {
		$Array ['Response'] = $SuccessObject;
		$Array ['Videos'] = $VideoObject;
		$Array ['Sections'] = $RelatedVideosObject;
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	
	
	/**
	 * Function to Get User Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 */
	public static function getUserPackagesSubscriptionSuccessObject($userArray, $userProfileArray, $userSubscriptionArray,$usersSubscriptionsArray,$userPackagesArray, $successObject) {
		$array ['Response'] = $successObject;
		$array ['User'] = $userArray;
		$array ['UserProfile'] = $userProfileArray;
		$array ['UserSubscription'] = $userSubscriptionArray;
		$array ['UserActiveSubscription'] = $usersSubscriptionsArray;
        $array ['UserPackages'] = $userPackagesArray;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	
	
	/**
	 * Function to Get User Success Response as JSON Object
	 *
	 * @param ARRAY $data        	
	 * @param ARRAY $successObject        	
	 */
	public static function getPackageUserSuccessObject($userArray, $userProfileArray, $userSubscriptionArray,$usersSubscriptionsArray,$userPackagesArray, $successObject) {
		$array ['Response'] = $successObject;
		$array ['User'] = $userArray;
		$array ['UserProfile'] = $userProfileArray;
		$array ['UserSubscription'] = $userSubscriptionArray;
		$array ['UserActiveSubscription'] = $usersSubscriptionsArray;
        $array ['UserPackages'] = $userPackagesArray;
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
}


