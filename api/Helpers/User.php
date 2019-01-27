<?php
/**
 * Class To Handle User Related Helpers
 * 
 * @author SAIF UD DIN
 *
 */
class User {
	/**
	 * Function To Get User Array
	 *
	 * @param STRING[] $results        	
	 * @return STRING[]
	 */
	public static function getUserArray($results) {
		$userArray = array ();
		$userArray ['UserId'] = isset ( $results ['UserId'] ) ? $results ['UserId'] : NULL;
		$userArray ['UserUsername'] = isset ( $results ['UserUsername'] ) ? $results ['UserUsername'] : NULL;		
		$userArray ['UserIsFree'] = isset ( $results ['UserIsFree'] ) ? $results ['UserIsFree'] : NULL;
		$userArray ['UserIsActive'] = isset ( $results ['UserIsActive'] ) ? $results ['UserIsActive'] : NULL;
		$userArray ['UserCountryCode'] = isset ( $results ['UserCountryCode'] ) ? $results ['UserCountryCode'] : NULL;		
		return $userArray;
	}
	/** 
	 * Function To Get User Profile Array
	 *
	 * @param STRING[] $results        	
	 * @return STRING[]
	 */
	public static function getUserProfileArray($results) {
		$userProfileArray = array ();
		$userProfileArray ['UserProfileFullName'] = isset ( $results ['UserProfileFullName'] ) ? $results ['UserProfileFullName'] : 'Anonymous';
		$userProfileArray ['UserProfileMobile'] = isset ( $results ['UserProfileMobile'] ) ? $results ['UserProfileMobile'] : NULL;
		$userProfileArray ['UserProfileGender'] = isset ( $results ['UserProfileGender'] ) ? $results ['UserProfileGender'] : NULL;
		$userProfileArray ['UserProfileDOB'] = isset ( $results ['UserProfileDOB'] ) ? $results ['UserProfileDOB'] : NULL;
		$userProfileArray ['UserProfilePicture'] = isset ( $results ['UserProfilePicture'] ) ? $results ['UserProfilePicture'] : NULL;
		return $userProfileArray;
	}
	/**
	 * Function To Get User Subscription Array
	 *
	 * @param STRING[] $results        	
	 * @return STRING[]
	 */
	public static function getUserSubscriptionArray($results) {		
		
		$userSubscriptionArray = array ();
		$userSubscriptionArray ['UserId'] = isset ( $results ['UserId'] ) ? $results ['UserId'] : NULL;
		$userSubscriptionArray ['UserActivePackageType'] = isset ( $results ['UserActivePackageType'] ) ? $results ['UserActivePackageType'] : NULL;
		$userSubscriptionArray ['UserSubscriptionIsTempUser'] = isset ( $results ['UserSubscriptionIsTempUser'] ) ? $results ['UserSubscriptionIsTempUser'] : NULL;
		$userSubscriptionArray ['UserSubscriptionPackageId'] = isset ( $results ['UserSubscriptionPackageId'] ) ? $results ['UserSubscriptionPackageId'] : NULL;
		$userSubscriptionArray ['UserSubscriptionStartDate'] = isset ( $results ['UserSubscriptionStartDate'] ) ? $results ['UserSubscriptionStartDate'] : NULL;
		$userSubscriptionArray ['UserSubscriptionExpiryDate'] = isset ( $results ['UserSubscriptionExpiryDate'] ) ? $results ['UserSubscriptionExpiryDate'] : NULL;
		$userSubscriptionArray ['UserSubscriptionTVExpiryDate'] = isset ( $results ['UserSubscriptionTVExpiryDate'] ) ? $results ['UserSubscriptionTVExpiryDate'] : NULL;
		$userSubscriptionArray ['UserSubscriptionMaxConcurrentConnections'] = isset ( $results ['UserSubscriptionMaxConcurrentConnections'] ) ? $results ['UserSubscriptionMaxConcurrentConnections'] : NULL;
		$userSubscriptionArray ['UserSubscriptionAutoRenew'] = isset ( $results ['UserSubscriptionAutoRenew'] ) ? $results ['UserSubscriptionAutoRenew'] : NULL;
		$userSubscriptionArray ['UserSubscriptionDetails'] = isset ( $results ['UserSubscriptionDetails'] ) ? $results ['UserSubscriptionDetails'] : NULL;
		$userSubscriptionArray ['UserSubscriptionIsExpired'] = isset ( $results ['UserSubscriptionIsExpired'] ) ? $results ['UserSubscriptionIsExpired'] : NULL;
		return $userSubscriptionArray;
	}
	/**
	 * Function to Insert Record in TempUser Table
	 *
	 * @param String $deviceId        	
	 * @param String $token        	
	 * @param String $ipAddress        	
	 * @param DateTime $firstVisit        	
	 * @param DateTime $lastVisit        	
	 */
	public static function insertTempUserData(&$db, $tempUser) {
		$insert = array (
				"TempUserDeviceId" => $tempUser ["TempUserDeviceId"],
				"TempUserDeviceMac" => $tempUser ["TempUserDeviceMac"],
				"TempUserToken" => $tempUser ["TempUserToken"],
				"TempUserIPAddress" => $tempUser ["TempUserIPAddress"],
				"TempUserFirstVisitAt" => $tempUser ["TempUserFirstVisitAt"],
				"TempUserLastVisitAt" => $tempUser ["TempUserLastVisitAt"] 
		);
		return $db->insert ( "tempusers", $insert ) ? $db->lastInsertId () : FALSE;
	}
	/**
	 * Function to Insert User Data in User Table
	 *
	 * @param pdodb $db        	
	 * @param STRING[] $user        	
	 * @return INT
	 */
	public static function insertUserData(&$db, &$user) {
		$insert = array (				
				"UserUsername" => isset ( $user ['UserUsername'] ) ? $user ['UserUsername'] : null,
				"UserPassword" => isset ( $user ['UserPassword'] ) ? $user ['UserPassword'] : null,                               						
				"UserToken" => isset ( $user ['UserToken'] ) ? $user ['UserToken'] : null,				
				"UserIsFree" => isset ( $user ['UserIsFree'] ) ? $user ['UserIsFree'] : null,
				"UserIsActive" => isset ( $user ['UserIsActive'] ) ? $user ['UserIsActive'] : null,
				"UserNetwork" => isset ( $user ['UserNetwork'] ) ? $user ['UserNetwork'] : 'other',				
				"UserCountryCode" => isset ( $user ['UserCountryCode'] ) ? $user ['UserCountryCode'] : null,
				"UserIPAddress" => isset ( $user ['UserIPAddress'] ) ? $user ['UserIPAddress'] : null,				
				"UserPlatform" => 'Android'
				
		);
		
		$rowsAffected = ( int ) $db->insert ( "users", $insert );
		
		if ($rowsAffected > 0)
			$user ['UserId'] = $db->lastInsertId ();
		
		return $rowsAffected;
	}
	/**
	 * Function to Insert User Data in User Profiles Table
	 *
	 * @param pdodb $db        	
	 * @param STRING[] $user        	
	 */
	public static function insertUserProfileData(&$db, $user) {
		$insert = array (
				"UserProfileUserId" => isset ( $user ['UserId'] ) ? $user ['UserId'] : null,
				"UserProfileFullName" => isset ( $user ['UserProfileFullName'] ) ? $user ['UserProfileFullName'] : 'Anonymous',
				"UserProfileMobile" => isset ( $user ['UserProfileMobile'] ) ? $user ['UserProfileMobile'] : null,
				"UserProfileCountry" => isset ( $user ['UserProfileCountry'] ) ? $user ['UserProfileCountry'] : null,
				"UserProfileDOB" => isset ( $user ['UserProfileDOB'] ) ? $user ['UserProfileDOB'] : '1986-02-23',
				"UserProfilePlatform" => isset ( $user ['UserProfilePlatform'] ) ? $user ['UserProfilePlatform'] : 'Android',
				"UserProfilePicture" => isset ( $user ['UserProfilePicture'] ) ? $user ['UserProfilePicture'] : null
		);
		// print_r ( $insert );
		return ( int ) $db->insert ( "userprofiles", $insert );
	}
	 /**
     * Function to Insert User Data in User OTP
     *
     * @param pdodb $db
     * @param STRING[] $user
     */
    public static function insertuserOTP(&$db, $data) {
        $insert = array(
            "UserOtpMobileNo" => $data['UserOtpMobileNo'],
            "UserOtpCode" => $data['UserOtpCode']
        );
        return ( int ) $db->insert ( "userotp", $insert );
    }
	/**
	 * Function to Insert User Data in User Subscription Table
	 *
	 * @param pdodb $db        	
	 * @param STRING[] $user        	
	 * @return INT
	 */
	public static function insertUserSubscriptionData(&$db, $user) {
		$insert = array (
				"UserSubscriptionUserId" => isset ( $user ['UserId'] ) ? $user ['UserId'] : null,
				"UserSubscriptionIsTempUser" => isset ( $user ['UserSubscriptionIsTempUser'] ) ? $user ['UserSubscriptionIsTempUser'] : null,
				"UserSubscriptionPackageId" => isset ( $user ['UserSubscriptionPackageId'] ) ? $user ['UserSubscriptionPackageId'] : null,
				"UserSubscriptionStartDate" => isset ( $user ['UserSubscriptionStartDate'] ) ? $user ['UserSubscriptionStartDate'] : null,
				"UserSubscriptionExpiryDate" => isset ( $user ['UserSubscriptionExpiryDate'] ) ? $user ['UserSubscriptionExpiryDate'] : null,
				"UserSubscriptionMaxConcurrentConnections" => isset ( $user ['UserSubscriptionMaxConcurrentConnections'] ) ? $user ['UserSubscriptionMaxConcurrentConnections'] : null,
				"UserSubscriptionAutoRenew" => isset ( $user ['UserSubscriptionAutoRenew'] ) ? $user ['UserSubscriptionAutoRenew'] : null,
				"UserSubscriptionDetails" => isset ( $user ['UserSubscriptionDetails'] ) ? $user ['UserSubscriptionDetails'] : null 
		);
		
		return ( int ) $db->insert ( "usersubscriptions", $insert );
	}
	
	
	public static function getUserPackagesArray($results) {
		$userPackagesArray = array ();
		$userPackagesArray ['PackageCode'] = isset ( $results ['PackageCode'] ) ? $results ['PackageCode'] : NULL;
		return $userSubscriptionArray;
	}
}