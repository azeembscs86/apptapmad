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
		$userArray ['UserChatId'] = isset ( $results ['UserChatId'] ) ? $results ['UserChatId'] : NULL;
		$userArray ['UserPassword'] = '0';
		$userArray ['UserPackageType'] = isset ( $results ['UserPackageType'] ) ? $results ['UserPackageType'] : NULL;
		$userArray ['UserActivePackageType'] = isset ( $results ['UserActivePackageType'] ) ? $results ['UserActivePackageType'] : NULL;
		$userArray ['AllInOne']=(isset ( $results ['allinonePackageCode']) && in_array("1009", $results['allinonePackageCode'])) ? true : false;
		$userArray ['AllAndOne']=(isset ( $results ['allinonePackageCode']) && in_array("1009", $results['allinonePackageCode'])) ? "Yes" : "No";
		$userArray ['UserPackageIsRecurring'] = isset ( $results ['UserPackageIsRecurring'] ) ? $results ['UserPackageIsRecurring'] : NULL;
		$userArray ['UserTVPackageType'] = isset ( $results ['UserTVPackageType'] ) ? $results ['UserTVPackageType'] : NULL;
		$userArray ['UserTVPackageIsRecurring'] = isset ( $results ['UserTVPackageIsRecurring'] ) ? $results ['UserTVPackageIsRecurring'] : NULL;
		$userArray ['UserEmail'] = isset ( $results ['UserEmail'] ) ? $results ['UserEmail'] : NULL;
		$userArray ['UserFacebookId'] = isset ( $results ['UserFacebookId'] ) ? $results ['UserFacebookId'] : NULL;
		$userArray ['UserToken'] = isset ( $results ['UserToken'] ) ? $results ['UserToken'] : NULL;
		$userArray ['UserDeviceId'] = isset ( $results ['UserDeviceId'] ) ? $results ['UserDeviceId'] : NULL;
		$userArray ['UserIsFree'] = isset ( $results ['UserIsFree'] ) ? $results ['UserIsFree'] : NULL;
		$userArray ['UserIsActive'] = isset ( $results ['UserIsActive'] ) ? $results ['UserIsActive'] : NULL;
		$userArray ['UserActivationCode'] = isset ( $results ['UserActivationCode'] ) ? $results ['UserActivationCode'] : NULL;
		$userArray ['UserIsPublisher'] = isset ( $results ['UserIsPublisher'] ) ? $results ['UserIsPublisher'] : NULL;
		$userArray ['UserNetwork'] = isset ( $results ['UserNetwork'] ) ? $results ['UserNetwork'] : NULL;
		$userArray ['UserLastLoginAt'] = isset ( $results ['UserLastLoginAt'] ) ? $results ['UserLastLoginAt'] : NULL;
		$userArray ['UserCountryCode'] = isset ( $results ['UserCountryCode'] ) ? $results ['UserCountryCode'] : NULL;
		$userArray ['UserIPAddress'] = isset ( $results ['UserIPAddress'] ) ? $results ['UserIPAddress'] : NULL;
		$userArray ['UserTypeId'] = isset ( $results ['UserTypeId'] ) ? $results ['UserTypeId'] : NULL;
		$userArray ['UserIsPassChanged'] = isset ( $results ['UserIsPassChanged'] ) ? $results ['UserIsPassChanged'] : NULL;
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
		$userProfileArray ['UserId'] = isset ( $results ['UserId'] ) ? $results ['UserId'] : NULL;
		$userProfileArray ['UserProfileFullName'] = isset ( $results ['UserProfileFullName'] ) ? $results ['UserProfileFullName'] : 'Anonymous';
		$userProfileArray ['UserProfileFirstName'] = isset ( $results ['UserProfileFirstName'] ) ? $results ['UserProfileFirstName'] : NULL;
		$userProfileArray ['UserProfileLastName'] = isset ( $results ['UserProfileLastName'] ) ? $results ['UserProfileLastName'] : NULL;
		$userProfileArray ['UserProfileMobile'] = isset ( $results ['UserProfileMobile'] ) ? $results ['UserProfileMobile'] : NULL;
		$userProfileArray ['UserProfileCity'] = isset ( $results ['UserProfileCity'] ) ? $results ['UserProfileCity'] : NULL;
		$userProfileArray ['UserProfileState'] = isset ( $results ['UserProfileState'] ) ? $results ['UserProfileState'] : NULL;
		$userProfileArray ['UserProfileCountry'] = isset ( $results ['UserProfileCountry'] ) ? $results ['UserProfileCountry'] : NULL;
		$userProfileArray ['UserProfileGender'] = isset ( $results ['UserProfileGender'] ) ? $results ['UserProfileGender'] : NULL;
		$userProfileArray ['UserProfileDOB'] = isset ( $results ['UserProfileDOB'] ) ? $results ['UserProfileDOB'] : NULL;
		$userProfileArray ['UserProfileRegistrationDate'] = isset ( $results ['UserProfileRegistrationDate'] ) ? $results ['UserProfileRegistrationDate'] : NULL;
		$userProfileArray ['UserProfilePlatform'] = isset ( $results ['UserProfilePlatform'] ) ? $results ['UserProfilePlatform'] : NULL;
		$userProfileArray ['UserProfileRefCode'] = isset ( $results ['UserProfileRefCode'] ) ? $results ['UserProfileRefCode'] : NULL;
		$userProfileArray ['UserProfileRefCode2'] = isset ( $results ['UserProfileRefCode2'] ) ? $results ['UserProfileRefCode2'] : NULL;
		$userProfileArray ['UserProfilePicture'] = isset ( $results ['UserProfilePicture'] ) ? $results ['UserProfilePicture'] : NULL;
		$userProfileArray ['UserProfileMobileNetwork'] = isset ( $results ['UserProfileMobileNetwork'] ) ? $results ['UserProfileMobileNetwork'] : NULL;
		return $userProfileArray;
	}
	/**
	 * Function To Get User Subscription Array
	 *
	 * @param STRING[] $results        	
	 * @return STRING[]
	 */
	public static function getUserSubscriptionArray($results) {
		if(isset ( $results ['UserActivePackageType'] ) && $results ['UserActivePackageType'] ==1009){
			$PackageName = "Premium + Movie";
		}else if(isset ( $results ['UserActivePackageType'] ) && $results ['UserActivePackageType'] ==1007){
			$PackageName = "Premium";
		}else if(isset ( $results ['UserActivePackageType'] ) && $results ['UserActivePackageType'] ==1005){
			$PackageName = "Movies";
		}else{
			$PackageName = NULL;
		}
		
		$userSubscriptionArray = array ();
		$userSubscriptionArray ['UserId'] = isset ( $results ['UserId'] ) ? $results ['UserId'] : NULL;
		$userSubscriptionArray ['UserActivePackageType'] = isset ( $results ['UserActivePackageType'] ) ? $results ['UserActivePackageType'] : NULL;
		$userSubscriptionArray ['PackageName'] = $PackageName;
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