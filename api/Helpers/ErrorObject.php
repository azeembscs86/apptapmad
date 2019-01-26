<?php
/**
 * Class To Handle All Error Response Objects
 * 
 * @author SAIF UD DIN
 *
 */
class ErrorObject {
	public static function getPaymentErrorObject($ErrorObject) {
		$Array ['Response'] = $ErrorObject;
		$Array ['Transaction'] = NULL;
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getUserSubscriptionErrorObject($ErrorObject) {
		$Array ['Response'] = $ErrorObject;
		$Array ['UserSubscription'] = NULL;
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getAdErrorObject($ErrorObject, $Video=NULL) {
		$Array ['Response'] = $ErrorObject;
        if($Video)
        {
            $Array ['Video'] = array_slice ( $Video, 0, 5 );
        }
        else
        {
		    $Array ['Video'] = NULL;
        }
		$Array ['Ad'] = NULL;
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function to Get Video Error Response as JSON Object
	 *
	 * @param ARRAY $errorObject        	
	 * @param INT $Limit        	
	 * @param INT $Size        	
	 * @return JSON
	 */
	public static function getVideoErrorObject($errorObject, $Limit = NULL, $Size = NULL, $itemName = NULL) {
		$array ['Response'] = $errorObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		if ($itemName) {
			$array [$itemName] = Array ();
		} else {
			$array ['Videos'] = Array ();
		}
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getTVVideoErrorObject($ErrorObject, $Categories = NULL, $Channels = NULL) {
		$ResponseArray ['Response'] = $ErrorObject;
		$ResponseArray ['DateTime'] = array (
				'CurrentDateTime' => date ( 'Y-m-d H:i:s' ) 
		);
		$ResponseArray ['Categories'] = $Categories;
		$ResponseArray ['Videos'] = $Channels;
		return json_encode ( $ResponseArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function to Get Section And Detail Error Response as JSON Object
	 *
	 * @param ARRAY $errorObject        	
	 * @param INT $Limit        	
	 * @param INT $Size        	
	 * @return JSON
	 */
	public static function getSectionErrorObject($errorObject, $Limit = NULL, $Size = NULL) {
		$array ['Response'] = $errorObject;
		if ($Limit) {
			$array ['Limit'] = $Limit;
			$array ['Size'] = $Size;
		}
		$array ['Tabs'] = Array ();
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	public static function getGeneralErrorObject($ErrorObject, $DataArray = NULL, $ItemName = NULL, $Limit = NULL, $Size = NULL) {
		$Array ['Response'] = $ErrorObject;
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
	/**
	 * Function to Get User Error Response as JSON Object
	 *
	 * @param ARRAY $errorObject        	
	 */
	public static function getUserErrorObject($errorObject) {
		$array ['Response'] = $errorObject;
		$array ['User'] = Array (
				'UserId' => NULL,
				'UserUsername' => NULL,
				'UserPassword' => NULL,
				'UserEmail' => NULL,
				'UserFacebookId' => NULL,
				'UserToken' => NULL,
				'UserIsFree' => NULL,
				'UserIsActive' => NULL,
				'UserIsPublisher' => NULL,
				'UserNetwork' => NULL,
				'UserLastLoginAt' => NULL,
				'UserCountryCode' => NULL,
				'UserIPAddress' => NULL,
				'UserTypeId' => NULL,
				'UserIsPassChanged' => NULL 
		);
		$array ['UserProfile'] = Array (
				'UserId' => NULL,
				'UserProfileFirstName' => NULL,
				'UserProfileLastName' => NULL,
				'UserProfileMobile' => NULL,
				'UserProfileCity' => NULL,
				'UserProfileState' => NULL,
				'UserProfileCountry' => NULL,
				'UserProfileGender' => NULL,
				'UserProfileDOB' => NULL,
				'UserProfileRegistrationDate' => NULL,
				'UserProfilePlatform' => NULL,
				'UserProfilRefCode' => NULL 
		);
		$array ['UserSubscription'] = Array (
				'UserId' => NULL,
				'UserSubscriptionPackageId' => NULL,
				'UserSubscriptionStartDate' => NULL,
				'UserSubscriptionExpiryDate' => NULL,
				'UserSubscriptionMaxConcurrentConnections' => NULL,
				'UserSubscriptionAutoRenew' => NULL,
				'UserSubscriptionDetails' => NULL 
		);
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function To Get Simple Error Object
	 *
	 * @param ARRAY $errorObject        	
	 * @return JSON
	 */
	public static function getErrorObject($errorObject) {
		return json_encode ( $errorObject, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function to Get Temp User Error Response as JSON Object
	 *
	 * @param ARRAY $errorObject        	
	 * @param ARRAY $tempUser        	
	 */
	public static function getTempUserErrorObject($errorObject, $tempUser = NULL) {
		$array ['Response'] = $errorObject;
		if ($tempUser) {
			$array ['TempUser'] = $tempUser;
		} else {
			$array ['TempUser'] = Array (
					'TempUserDeviceId' => NULL,
					'TempUserDeviceMac' => NULL,
					'TempUserToken' => NULL,
					'TempUserIPAddress' => NULL,
					'TempUserFirstVisitAt' => NULL,
					'TempUserLastVisitAt' => NULL,
					'TempUserIsRestricted' => NULL,
					'TempUserTrialUsed' => NULL 
			);
		}
		return json_encode ( $array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
	/**
	 * Function to Get App Settings Error Response as JSON Object
	 *
	 * @param ARRAY $ErrorObject        	
	 * @param ARRAY $AppSettings        	
	 * @return JSON
	 */
	public static function getAppSettingsErrorObject($ErrorObject, $AppSettings = NULL) {
		$Array ['Response'] = $ErrorObject;
		$Array ['DateTime'] = array (
				'CurrentDateTime' => date ( 'Y-m-d H:i:s' )
		);
		if ($AppSettings) {
			$Array ['AppSettings'] = $AppSettings;
		} else {
			$Array ['AppSettings'] = Array (
					'AppSettingId' => NULL,
					'AppSettingPlatform' => NULL,
					'AppSettingVersion' => NULL,
					'AppSettingIsServiceFree' => NULL,
					'AppSettingIsPremiumContentFree' => NULL,
					'AppSettingShowAdMobAds' => NULL,
					'AppSettingShowAdspirationAds' => NULL,
					'AppSettingShowBannerAd' => NULL,
					'AppSettingShowPreRoll' => NULL,
					'AppSettingShowMidRoll' => NULL,
					'AppSettingShowPostRoll' => NULL,
					'AppSettingShowBannerOnPlayer' => NULL,
					'AppSettingShowInterstitial' => NULL,
					'AppSettingInterstitialRefreshRate' => NULL,
					'AppSettingIsAccountVerificationNeeded' => NULL,
					'AppSettingIsRegisterationNeeded' => NULL 
			);
		}
		return json_encode ( $Array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	}
}