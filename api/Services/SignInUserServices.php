<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Respect\Validation\Validator as v;

/**
 * Class to Handle all Services Related to User
 *
 * @author SAIF UD DIN
 *
 */
class SignInUserServices extends Config
{
    public static $db;
    
    public function __construct()
    {
        self::$db = parent::getDataBase();
        return self::$db;        
        
    }
    
    
    
   
    
    
	public static function signUpORSignInUsingMobileNumber(Request $request, Response $response)
    {
        
        $Version = filter_var(isset($request->getParsedBody()['Version']) ? $request->getParsedBody()['Version'] : NULL, FILTER_SANITIZE_STRING);
        $Language = filter_var(isset($request->getParsedBody()['Language']) ? $request->getParsedBody()['Language'] : NULL, FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : NULL, FILTER_SANITIZE_STRING);
        // Users Table Data
        $user['UserUsername'] = ltrim(filter_var(isset($request->getParsedBody()['MobileNo']) ? $request->getParsedBody()['MobileNo'] : NULL, FILTER_SANITIZE_STRING), '0');
        $user['UserUsername'] = ltrim($user['UserUsername'], '+92');
        $user['UserSubscriptionAutoRenew'] = 1;

        $user['UserPassword'] = md5('TAPMAD999');

        $currentDate = new DateTime();
        $user['UserLastLoginAt'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserEmail'] = NULL;
        $user['UserToken'] = General::createGUID();
        // $user ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
        $user['UserDeviceId'] = filter_var(isset($request->getParsedBody()['DeviceID']) ? $request->getParsedBody()['DeviceID'] : NULL, FILTER_SANITIZE_STRING);
        $user['UserIsFree'] = '1';
        $user['UserIsActive'] = '1';
        $user['UserActivationCode'] = null;
        $user['UserIsPublisher'] = '0';
        $user['UserNetwork'] = 'other';
        $user['UserCountryCode'] = 'PK';
        $user['UserIPAddress'] = General::getUserIP();
        $user['UserTypeId'] = '0';
        $user['UserIsPassChanged'] = '0';

        // User Profiles Table Data
        $user['UserProfileFullName'] = "Anonymous";
        $user['UserProfileFirstName'] = NULL;
        $user['UserProfileLastName'] = NULL;
        $user['UserProfileMobile'] = filter_var(isset($request->getParsedBody()['MobileNo']) ? $request->getParsedBody()['MobileNo'] : NULL, FILTER_SANITIZE_STRING);
        // TODO: Implement Api Resolver
        $user['UserProfileCity'] = NULL;
        $user['UserProfileState'] = NULL;
        $user['UserProfileCountry'] = NULL;
        $user['UserProfileGender'] = NULL;
        $user['UserProfileDOB'] = NULL;
        $currentDate = new DateTime();
        $user['UserProfileRegistrationDate'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserProfilePlatform'] = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : NULL, FILTER_SANITIZE_STRING);
        $user['UserProfileRefCode'] = filter_var(isset($request->getParsedBody()['RefCode']) ? $request->getParsedBody()['RefCode'] : NULL, FILTER_SANITIZE_STRING);
        $user['UserProfileRefCode2'] = filter_var(isset($request->getParsedBody()['RefCode2']) ? $request->getParsedBody()['RefCode2'] : NULL, FILTER_SANITIZE_STRING);
        $user['UserProfilePicture'] = NULL;
        $user['UserProfileMobileNetwork'] = NULL;

        // User Subscriptions Table Data
        $user['UserSubscriptionIsTempUser'] = 0;
        $user['UserSubscriptionPackageId'] = 10;
        $user['UserSubscriptionStartDate'] = $currentDate->format('Y-m-d H:i:s');
        // $currentDate = $currentDate->modify ( '+7 day' );
        $currentDate = $currentDate->modify('-5 minutes');
        $user['UserSubscriptionExpiryDate'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserSubscriptionIsExpired'] = true;
        $user['UserSubscriptionTVExpiryDate'] = NULL;
        $user['UserSubscriptionMaxConcurrentConnections'] = 6;
        $user['UserSubscriptionAutoRenew'] = 0;
        $user['UserSubscriptionDetails'] = NULL;

        // --------- PARAMETERS VALIDATION
        $MobileNoValidator = v::Digit()->noWhitespace()->length(10, 10);
        if (! $MobileNoValidator->validate($user['UserUsername'])) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }

        $user['UserUsername'] = 'T' . $user['UserUsername'];

        try {
            
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    // print_r ( $user );
                    $bind = array(
                        ':Username' => $user['UserUsername']
                    );
                    if (self::$db->select('users', 'UserUsername = :Username', $bind)) {
                        return General::getResponse($response->write(SignInUserServices::localLogInUsingMobileNo($Version, $Language, $Platform, $user['UserUsername'])));
                    } else if (User::insertUserData(self::$db, $user) > 0) {
                        User::insertUserProfileData(self::$db, $user);
                        User::insertUserSubscriptionData(self::$db, $user);

                        $users[0] = $user;
                        Format::formatResponseData($users);
                        $user = $users[0];
                        SignInUserServices::addSubscriptionPackage($user['UserId']);
                        $userPackage = SignInUserServices::getUserPackagesArray($user);
                        Format::formatResponseData($userPackage);	
                        $userSubscriptions =SignInUserServices::getUserPackageSubscription($user);
                        Format::formatResponseData($userSubscriptions);	
                        // UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
                        return General::getResponse($response->write(SuccessObject::getUserPackagesSuccessObject(User::getUserArray($user), User::getUserProfileArray($user),User::getUserSubscriptionArray($results), $userSubscriptions, $userPackage,Message::getMessage('M_INSERT'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_INSERT'))));
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }
	
	public static function localLogInUsingMobileNo($Version, $Language, $Platform, $UserUsername)
    {		
        try {
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled                                    
                                       
                        // Updating User Last LogIn Time
                        $currentDate = new DateTime();
                        $update = array(
                            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                        );
                        // "UserToken" => General::createGUID()

                        
                    
                    $sql = <<<STR
						SELECT  UserId,
						UserUsername,
						UserChatId,
						UserPassword,
						UserPackageType,
						UserActivePackageType,
						UserPackageIsRecurring,
						UserTVPackageType,
						UserTVPackageIsRecurring,
						UserEmail,
						UserFacebookId,
						UserToken,
						UserDeviceId,
						UserIsFree,
						UserIsActive,
						UserActivationCode,
						UserIsPublisher,
						UserNetwork,
						UserLastLoginAt,
						UserCountryCode,
						UserIPAddress,
						UserTypeId,
						UserIsPassChanged
						FROM users
						WHERE UserUsername=:Username
                                    
STR;
                    $bind = array(
                        ":Username" => $UserUsername
                    );
                    // print_r ( $bind );
                $results = self::$db->run($sql, $bind);  
				Format::formatResponseData($results);
                if($results){        
					$bind = array(
						":Username" => $UserUsername
					);
					self::$db->update('users', $update, 'UserUsername=:Username', $bind);
					// To Get Object From Array
					$results = $results[0];//;
					 
					$userprofile=SignInUserServices::getUserProfileArray($results['UserId']);
					Format::formatResponseData($userprofile);
					$userpackagesubsription=SignInUserServices::getUsersPackagesSubscriptions($results['UserId']);
					Format::formatResponseData($userpackagesubsription);					
					$userPackage = SignInUserServices::getUserPackagesArray($results['UserId']);
					Format::formatResponseData($userPackage);	
					$userSubscriptions =SignInUserServices::getUserPackageSubscription($results['UserId']);
					Format::formatResponseData($userSubscriptions);	
					if($userSubscriptions){
						return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results),$userprofile,$userpackagesubsription,$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
					}else{
						return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), User::getUserProfileArray($results),$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
					}
				}else{
					return ErrorObject::getUserErrorObject(Message::getMessage('W_NO_CONTENT'));
				}                
				break;
                default:
                    return ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'));
                    break;
            }
        } catch (PDOException $e) {
            return ErrorObject::getUserErrorObject(Message::getPDOMessage($e));
        } finally {
            $db = null;
        }
    }
	
	public static function getUserProfileArray($results)
	{
		
		$sql = <<<STR
		SELECT  UserProfileUserId AS UserId,
        UserProfileFullName,
        UserProfileFirstName,
        UserProfileLastName,
        UserProfileMobile,
        UserProfileCity,
        UserProfileState,
        UserProfileCountry,
        UserProfileGender,
        UserProfileDOB,
        UserProfileRegistrationDate,
        UserProfilePlatform,
        UserProfileRefCode,
        UserProfileRefCode2,
        UserProfilePicture,
        UserProfileMobileNetwork					
		FROM userprofiles 
		WHERE UserProfileUserId=:UserId
STR;
		
		$bind = array(
			":UserId" => $results,
		);
		

		$results = self::$db->run($sql, $bind);		
		Format::formatResponseData($results);
		return $results[0];
	}
	
	public static function getUserPackagesArray($user)
	{
		
		$userPackagesArray;
		$sql = <<<STR
		SELECT PackageCode FROM userpackages
		WHERE userpackages.UserId=:UserId AND PackageCode!=0								
STR;
		$bind = array(
			":UserId" => $user
		);

        $userPackagesArray = self::$db->run($sql, $bind);		
		return $userPackagesArray;
	}
	
	
	
	//------------------------------get User Multiple Subscription---------------------------//
	public function getUserPackageSubscription($user)
	{		
		$results;
		$sql = <<<STR
			            SELECT 
						(CASE 
							WHEN userpackages.PackageCode="1007" THEN "Premium"
							WHEN userpackages.PackageCode="1005" THEN "Movies"
							WHEN userpackages.PackageCode="1009" THEN "Premium + Movie"
							ELSE NULL
						    END) AS PackageName, 
						userpackages.UserId AS UserId,
						userpackages.PackageCode As UserPackageType,
						userpackages.IsPackgeRecuring As IsExpiredPackage,
						usersubscriptions.UserSubscriptionIsTempUser,
						usersubscriptions.UserSubscriptionPackageId,
						usersubscriptions.UserSubscriptionStartDate,
						usersubscriptions.UserSubscriptionExpiryDate,
						usersubscriptions.UserSubscriptionTVExpiryDate,
						usersubscriptions.UserSubscriptionMaxConcurrentConnections,
						usersubscriptions.UserSubscriptionAutoRenew,usersubscriptions.UserSubscriptionDetails,
						IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
								FROM usersubscriptions
					            
								
								INNER JOIN userpackages
								ON usersubscriptions.UserSubscriptionId=userpackages.UserSubscriptionId
			                    
								WHERE usersubscriptions.UserSubscriptionUserId=:UserId
                                    AND usersubscriptions.UserSubscriptionIsTempUser=0 AND usersubscriptions.UserSubscriptionPackageId=10 AND userpackages.PackageCode!=0 
STR;
                    $bind = array(
                        ":UserId" => $user
                    );
                    // print_r ( $bind );
                    $results = self::$db->run($sql, $bind);
					return $results;
		
	}
	
	
	//------------------------------get User Multiple Subscription---------------------------//
	public function getUsersPackagesSubscriptions($user)
	{		
		$results;
		$sql = <<<STR
			SELECT 						
			usersubscriptions.UserSubscriptionIsTempUser,
			usersubscriptions.UserSubscriptionPackageId,
			usersubscriptions.UserSubscriptionStartDate,
			usersubscriptions.UserSubscriptionExpiryDate,
			usersubscriptions.UserSubscriptionTVExpiryDate,
			usersubscriptions.UserSubscriptionMaxConcurrentConnections,
			usersubscriptions.UserSubscriptionAutoRenew,usersubscriptions.UserSubscriptionDetails,
			IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
					FROM usersubscriptions
					
					
					WHERE usersubscriptions.UserSubscriptionUserId=:UserId
						AND usersubscriptions.UserSubscriptionIsTempUser=0 AND usersubscriptions.UserSubscriptionPackageId=10
STR;
		$bind = array(
			":UserId" => $user
		);
		// print_r ( $bind );
		$results = self::$db->run($sql, $bind);
		Format::formatResponseData($results);
		$packagesDetail =SignInUserServices::getUserPackageDetail($user);
		Format::formatResponseData($packagesDetail);
		//print_r($packagesDetail[0]);die;
		$results = array_merge($packagesDetail[0],$results[0]);
		return $results;
		
	}
	
	//------------------------add package null for free users-----------------------------------
	public static function getUserPackageDetail($UserId)
	{
		
		$bind = array(
			':UserId' => $UserId
		);
		$sql = <<<STR
			SELECT UserId AS UserId,
			PackageCode AS UserActivePackageType,
			(CASE 
				WHEN PackageCode="1007" THEN "Premium"
				WHEN PackageCode="1005" THEN "Movies"
				WHEN PackageCode="1009" THEN "Premium + Movie"
				ELSE NULL
				END) AS PackageName
			FROM userpackages
			WHERE UserId=:UserId    		        
STR;
		
		$result = self::$db->run($sql, $bind);
		Format::formatResponseData($result);
		return $result;
		
		
	}
	
	
	//------------------------add package null for free users-----------------------------------
	public static function addSubscriptionPackage($UserId)
	{
		
		$Subscription= UserServices::getSubscriptionByUserId($UserId);		
		
		$update = array(
            "PackageCode" => 0,
            "UserId" => $UserId,
            "UserSubscriptionId" => $Subscription[0]['UserSubscriptionId'],
        );

        self::$db->insert('userpackages', $update);
		
	}
	
	
	//-----------------------------get Subscription by User Id---------------------------------
	public static function getSubscriptionByUserId($UserId)
	{		
		$results;       
        $sql = <<<STR
    		        SELECT UserSubscriptionId				
                            FROM usersubscriptions 
                           WHERE UserSubscriptionUserId=:UserSubscriptionUserId AND UserSubscriptionIsTempUser=0 AND UserSubscriptionPackageId=10 Order by UserSubscriptionId DESC limit 1
STR;

        $bind = array(
            ":UserSubscriptionUserId" => $UserId,
        );

        $results = self::$db->run($sql, $bind);		
		
        Format::formatResponseData($results);
        return $results;
	}

	

    
	

	
	
	
	
	
	
	
}