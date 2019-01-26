<?php
/**
 * Class To Handle User Related Helpers
 * 
 * @author SAIF UD DIN
 *
 */
class NewUser {
	/**
	 * Function To Get User Array
	 *
	 * @param STRING[] $results        	
	 * @return STRING[]
	 */
	public static function getUserArray($results) {
		$userArray = array ();
		$userArray ['UsersId'] = isset ( $results ['UsersId'] ) ? $results ['UsersId'] : NULL;
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
		$userProfileArray ['UserProfileMobile'] = isset ( $results ['UserProfileMobile'] ) ? $results ['UserProfileMobile'] : '92123456789';
		$userProfileArray ['UserProfileGender'] = isset ( $results ['UserProfileGender'] ) ? $results ['UserProfileGender'] : 'Male';
		$userProfileArray ['UserProfileDOB'] = isset ( $results ['UserProfileDOB'] ) ? $results ['UserProfileDOB'] : '1986-02-23';
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
		$userSubscriptionArray ['UserSubscriptionStartDate'] = isset ( $results ['UserSubscriptionStartDate'] ) ? $results ['UserSubscriptionStartDate'] : NULL;
		$userSubscriptionArray ['UserSubscriptionExpiryDate'] = isset ( $results ['UserSubscriptionExpiryDate'] ) ? $results ['UserSubscriptionExpiryDate'] : NULL;
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
                                "UsersId" => isset ( $user ['UsersId'] ) ? $user ['UsersId'] : null,
				"UserFacebookId" => isset ( $user ['UserFacebookId'] ) ? $user ['UserFacebookId'] : null,
				"UserUsername" => isset ( $user ['UserUsername'] ) ? $user ['UserUsername'] : null,
				"UserPassword" => isset ( $user ['UserPassword'] ) ? $user ['UserPassword'] : null,
                                 "UserLastLoginAt" => isset ( $user ['UserLastLoginAt'] ) ? $user ['UserLastLoginAt'] : null,
				"UserSalt" => isset ( $user ['UserSalt'] ) ? $user ['UserSalt'] : null,
				"UserEmail" => isset ( $user ['UserEmail'] ) ? $user ['UserEmail'] : null,
				"UserToken" => isset ( $user ['UserToken'] ) ? $user ['UserToken'] : null,
				"UserDeviceId" => isset ( $user ['UserDeviceId'] ) ? $user ['UserDeviceId'] : null,
				"UserIsFree" => isset ( $user ['UserIsFree'] ) ? $user ['UserIsFree'] : null,
				"UserIsActive" => isset ( $user ['UserIsActive'] ) ? $user ['UserIsActive'] : null,
				"UserActivationCode" => isset ( $user ['UserActivationCode'] ) ? $user ['UserActivationCode'] : null,
				"UserIsPublisher" => isset ( $user ['UserIsPublisher'] ) ? $user ['UserIsPublisher'] : null,
				"UserNetwork" => isset ( $user ['UserNetwork'] ) ? $user ['UserNetwork'] : null,
				"UserToken" => isset ( $user ['UserToken'] ) ? $user ['UserToken'] : null,
				"UserCountryCode" => isset ( $user ['UserCountryCode'] ) ? $user ['UserCountryCode'] : null,
				"UserIPAddress" => isset ( $user ['UserIPAddress'] ) ? $user ['UserIPAddress'] : null,
				"UserTypeId" => isset ( $user ['UserTypeId'] ) ? $user ['UserTypeId'] : null,
				"UserPlatform" => 'Android',
				"UserIsPassChanged" => isset ( $user ['UserIsPassChanged'] ) ? $user ['UserIsPassChanged'] : null 
		);
		
		$rowsAffected = ( int ) $db->insert ( "newusers", $insert );
		
		if ($rowsAffected > 0)
			$user ['UsersId'] = $db->lastInsertId ();
		
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
				"UserProfileFullName" => isset ( $user ['UserProfileFullName'] ) ? $user ['UserProfileFullName'] : null,
				"UserProfileFirstName" => isset ( $user ['UserProfileFirstName'] ) ? $user ['UserProfileFirstName'] : null,
				"UserProfileLastName" => isset ( $user ['UserProfileLastName'] ) ? $user ['UserProfileLastName'] : null,
				"UserProfileMobile" => isset ( $user ['UserProfileMobile'] ) ? $user ['UserProfileMobile'] : null,
				"UserProfileCity" => isset ( $user ['UserProfileCity'] ) ? $user ['UserProfileCity'] : null,
				"UserProfileState" => isset ( $user ['UserProfileState'] ) ? $user ['UserProfileState'] : null,
				"UserProfileCountry" => isset ( $user ['UserProfileCountry'] ) ? $user ['UserProfileCountry'] : null,
				"UserProfileGender" => isset ( $user ['UserProfileGender'] ) ? $user ['UserProfileGender'] : null,
				"UserProfileDOB" => isset ( $user ['UserProfileDOB'] ) ? $user ['UserProfileDOB'] : null,
				"UserProfileRegistrationDate" => isset ( $user ['UserProfileRegistrationDate'] ) ? $user ['UserProfileRegistrationDate'] : null,
				"UserProfilePlatform" => isset ( $user ['UserProfilePlatform'] ) ? $user ['UserProfilePlatform'] : null,
				"UserProfileRefCode" => isset ( $user ['UserProfileRefCode'] ) ? $user ['UserProfileRefCode'] : null,
				"UserProfileRefCode2" => isset ( $user ['UserProfileRefCode2'] ) ? $user ['UserProfileRefCode2'] : null,
				"UserProfilePicture" => isset ( $user ['UserProfilePicture'] ) ? $user ['UserProfilePicture'] : null
		);
		// print_r ( $insert );
		return ( int ) $db->insert ( "usernprofiles", $insert );
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
		
		return ( int ) $db->insert ( "usernsubscriptions", $insert );
	}
	
	
	public static function getUserPackagesArray($results) {
		$userPackagesArray = array ();
		$userPackagesArray ['PackageCode'] = isset ( $results ['PackageCode'] ) ? $results ['PackageCode'] : NULL;
		return $userSubscriptionArray;
	}
}