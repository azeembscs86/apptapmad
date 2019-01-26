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
class UsersService extends Config
{
    public static $_connection;
	public  function __construct() {
        $this->_connection =  parent::getDataBase();    
   }
    public static function getInstance(){
      
      if($this->_connection){
         echo "Connected";
      }else {
         echo "DisConnected";
      }

   }
  


    

   public static function signUpORSignInUsingMobileNoNew(Request $request, Response $response)
     {
      UsersService::getInstance();
        
     }
//         $Version = filter_var(isset($request->getParsedBody()['Version']) ? $request->getParsedBody()['Version'] : NULL, FILTER_SANITIZE_STRING);
//         $Language = filter_var(isset($request->getParsedBody()['Language']) ? $request->getParsedBody()['Language'] : NULL, FILTER_SANITIZE_STRING);
//         parent::setConfig($Language);
//         $Platform = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : NULL, FILTER_SANITIZE_STRING);
//         // Users Table Data
//         $user['UserUsername'] = ltrim(filter_var(isset($request->getParsedBody()['MobileNo']) ? $request->getParsedBody()['MobileNo'] : NULL, FILTER_SANITIZE_STRING), '0');
//         $user['UserUsername'] = ltrim($user['UserUsername'], '+92');
//         $user['UserSubscriptionAutoRenew'] = 1;

//         $user['UserPassword'] = md5('TAPMAD999');

//         $currentDate = new DateTime();
//         $user['UserLastLoginAt'] = $currentDate->format('Y-m-d H:i:s');
//         $user['UserEmail'] = NULL;
//         $user['UserToken'] = General::createGUID();
//         // $user ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
//         $user['UserDeviceId'] = filter_var(isset($request->getParsedBody()['DeviceID']) ? $request->getParsedBody()['DeviceID'] : NULL, FILTER_SANITIZE_STRING);
//         $user['UserIsFree'] = '1';
//         $user['UserIsActive'] = '1';
//         $user['UserActivationCode'] = null;
//         $user['UserIsPublisher'] = '0';
//         $user['UserNetwork'] = 'other';
//         $user['UserCountryCode'] = 'PK';
//         $user['UserIPAddress'] = General::getUserIP();
//         $user['UserTypeId'] = '0';
//         $user['UserIsPassChanged'] = '0';

//         // User Profiles Table Data
//         $user['UserProfileFullName'] = NULL;
//         $user['UserProfileFirstName'] = NULL;
//         $user['UserProfileLastName'] = NULL;
//         $user['UserProfileMobile'] = filter_var(isset($request->getParsedBody()['MobileNo']) ? $request->getParsedBody()['MobileNo'] : NULL, FILTER_SANITIZE_STRING);
//         // TODO: Implement Api Resolver
//         $user['UserProfileCity'] = NULL;
//         $user['UserProfileState'] = NULL;
//         $user['UserProfileCountry'] = NULL;
//         $user['UserProfileGender'] = NULL;
//         $user['UserProfileDOB'] = NULL;
//         $currentDate = new DateTime();
//         $user['UserProfileRegistrationDate'] = $currentDate->format('Y-m-d H:i:s');
//         $user['UserProfilePlatform'] = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : NULL, FILTER_SANITIZE_STRING);
//         $user['UserProfileRefCode'] = filter_var(isset($request->getParsedBody()['RefCode']) ? $request->getParsedBody()['RefCode'] : NULL, FILTER_SANITIZE_STRING);
//         $user['UserProfileRefCode2'] = filter_var(isset($request->getParsedBody()['RefCode2']) ? $request->getParsedBody()['RefCode2'] : NULL, FILTER_SANITIZE_STRING);
//         $user['UserProfilePicture'] = NULL;
//         $user['UserProfileMobileNetwork'] = NULL;

//         // User Subscriptions Table Data
//         $user['UserSubscriptionIsTempUser'] = 0;
//         $user['UserSubscriptionPackageId'] = 10;
//         $user['UserSubscriptionStartDate'] = $currentDate->format('Y-m-d H:i:s');
//         // $currentDate = $currentDate->modify ( '+7 day' );
//         $currentDate = $currentDate->modify('-5 minutes');
//         $user['UserSubscriptionExpiryDate'] = $currentDate->format('Y-m-d H:i:s');
//         $user['UserSubscriptionIsExpired'] = true;
//         $user['UserSubscriptionTVExpiryDate'] = NULL;
//         $user['UserSubscriptionMaxConcurrentConnections'] = 6;
//         $user['UserSubscriptionAutoRenew'] = 0;
//         $user['UserSubscriptionDetails'] = NULL;

//         // --------- PARAMETERS VALIDATION
//         $MobileNoValidator = v::Digit()->noWhitespace()->length(10, 10);
//         if (! $MobileNoValidator->validate($user['UserUsername'])) {
//             return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
//         }

//         $user['UserUsername'] = 'T' . $user['UserUsername'];

//         try {
           
//             switch ($Version) {
//                 case 'v1':
//                 case 'V1': // Local/International Filter Enabled
//                     // print_r ( $user );
//                     $bind = array(
//                         ':Username' => $user['UserUsername']
//                     );
//                     if ($db->select('users', 'UserUsername = :Username', $bind)) {
//                         return General::getResponse($response->write(UsersService::localLogInUsingMobileNo($Version, $Language, $Platform, $user['UserUsername'])));
//                     } else if (User::insertUserData($this->$db, $user) > 0) {
//                         User::insertUserProfileData($this->$db, $user);
//                         User::insertUserSubscriptionData($this->$db, $user);

//                         $users[0] = $user;
//                         Format::formatResponseData($users);
//                         $user = $users[0];
//                         UsersService::addSubscriptionPackage($this->$db,$user['UserId']);
//                         $userPackage = UsersService::getUserPackagesArray($this->$db,$user);
//                         Format::formatResponseData($userPackage);	
//                         $userSubscriptions =UsersService::getUserPackageSubscription($this->$db,$user);
//                         Format::formatResponseData($userSubscriptions);	
//                         // UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
//                         return General::getResponse($response->write(SuccessObject::getUserPackagesSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), $userSubscriptions, $userPackage,Message::getMessage('M_INSERT'))));
//                     } else {
//                         return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_INSERT'))));
//                     }
//                     break;
//                 default:
//                     return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
//                     break;
//             }
//         } catch (PDOException $e) {
//             return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
//         } finally {
//             $db = null;
//         }
//     }

//     public static function localLogInUsingMobileNo($Version, $Language, $Platform, $UserUsername)
//     {
//         try {
           
//             switch ($Version) {
//                 case 'v1':
//                 case 'V1': // Local/International Filter Enabled

//                     $sql = <<<STR
// 			            SELECT *,IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
// 								FROM users
								
// 			                    INNER JOIN userprofiles
// 			                    ON userprofiles.UserProfileUserId = users.UserId
			                    
// 					            INNER JOIN usersubscriptions
// 			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
								
// 								INNER JOIN userpackages
// 								ON users.UserId = userpackages.UserId AND usersubscriptions.UserSubscriptionId=userpackages.UserSubscriptionId
			                    
// 			                    WHERE UserUsername=:Username
//                                     AND usersubscriptions.UserSubscriptionIsTempUser=0 AND usersubscriptions.UserSubscriptionPackageId=10
// STR;
			
			
//                     // Password Encryption to Match Stored Password
//                     // $salt = $results [0] ['UserSalt'];
//                     // $saltedPW = $UserPassword . $salt;
//                     // $hashedPW = hash ( 'sha256', $saltedPW );

//                     $bind = array(
//                         ":Username" => $UserUsername
//                     );
//                     // print_r ( $bind );
//                     $results = $db->run($sql, $bind);
// 					$pkgbind = array(
// 						':UserId' => $results[0]['UserId']
// 					);
// 					$sqlpkgquery = <<<STR
// 					SELECT PackageCode FROM userpackages
// 					WHERE UserId=:UserId    		        
// STR;
// 					$pkgs = $this->$db->run($sqlpkgquery, $pkgbind);
// 					$pkgarr = array();
// 					for($c=0;$c<count($pkgs);$c++){
// 						$pkg[]=$pkgs[$c]['PackageCode'];
// 					}
// 					$pkgarr['allinonePackageCode']=$pkg;
//                     // If Result is Returned then Return User Information
//                     // Else Return Error Message Object
//                     if ($results) {
//                         Format::formatResponseData($results); 
//                         // Updating User Last LogIn Time
//                         $currentDate = new DateTime();
//                         $update = array(
//                             "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
//                         );
//                         // "UserToken" => General::createGUID()

//                         $bind = array(
//                             ":Username" => $UserUsername
//                         );
//                         $this->$db->update('users', $update, 'UserUsername=:Username', $bind);

//                         // To Get Object From Array
//                         $results = array_merge($results[0],$pkgarr);
//                         // $results ['UserToken'] = $update ['UserToken'];
//                         // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
// 						$userPackage = UsersService::getUserPackagesArray($this->$db,$results);
// 						Format::formatResponseData($userPackage);	
// 						$userSubscriptions =UsersService::getUserPackageSubscription($this->$db,$results);
// 						Format::formatResponseData($userSubscriptions);	
// 						if($userSubscriptions){
// 							return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results),$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
// 						}else{
							
// 							return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results),$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
// 						}
// 					} else {
//                         return ErrorObject::getUserErrorObject(Message::getMessage('W_NO_CONTENT'));
//                     }
//                     break;
//                 default:
//                     return ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'));
//                     break;
//             }
//         } catch (PDOException $e) {
//             return ErrorObject::getUserErrorObject(Message::getPDOMessage($e));
//         } finally {
//             $db = null;
//         }
//     }

//     public static function addSubscriptionPackage(&$db,$UserId)
// 	{
// 	//	$db = parent::getDataBase();
// 		$Subscription= UsersService::getSubscriptionByUserId($db,$UserId);		
		
// 		$update = array(
//             "PackageCode" => 0,
//             "UserId" => $UserId,
//             "UserSubscriptionId" => $Subscription[0]['UserSubscriptionId'],
//         );

//         $db->insert('userpackages', $update);
		
//     }
//     public static function getSubscriptionByUserId(&$db,$UserId)
// 	{		
// 		$results;
//      //   $db = parent::getDataBase();
//         $sql = <<<STR
//     		        SELECT UserSubscriptionId				
//                             FROM usersubscriptions 
//                            WHERE UserSubscriptionUserId=:UserSubscriptionUserId AND UserSubscriptionIsTempUser=0 AND UserSubscriptionPackageId=10 Order by UserSubscriptionId DESC limit 1
// STR;

//         $bind = array(
//             ":UserSubscriptionUserId" => $UserId,
//         );

//         $results = $db->run($sql, $bind);		
		
//         Format::formatResponseData($results);
//         return $results;
// 	}
    
//     public static function getUserPackagesArray(&$db,$user)
// 	{
// 		//$db = parent::getDataBase();
// 		$userPackagesArray;
// 		$sql = <<<STR
// 					SELECT PackageCode FROM userpackages
// 					        WHERE userpackages.UserId=:UserId AND PackageCode!=0								
// STR;
//                     $bind = array(
//                         ":UserId" => $user['UserId']
//                     );

//         $userPackagesArray = $db->run($sql, $bind);		
// 		return $userPackagesArray;
//     }
    
//     public function getUserPackageSubscription(&$db,$user)
// 	{		
// 	//	$db = parent::getDataBase();
// 		$results;
// 		$sql = <<<STR
// 			            SELECT 
// 						(CASE 
// 							WHEN userpackages.PackageCode="1007" THEN "Premium"
// 							WHEN userpackages.PackageCode="1005" THEN "Movies"
// 							WHEN userpackages.PackageCode="1009" THEN "Premium + Movie"
// 							ELSE NULL
// 						    END) AS PackageName, 
// 						users.UserId,
// 						userpackages.PackageCode As UserPackageType,
// 						userpackages.IsPackgeRecuring As IsExpiredPackage,
// 						usersubscriptions.UserSubscriptionIsTempUser,
// 						usersubscriptions.UserSubscriptionPackageId,
// 						usersubscriptions.UserSubscriptionStartDate,
// 						usersubscriptions.UserSubscriptionExpiryDate,
// 						usersubscriptions.UserSubscriptionTVExpiryDate,
// 						usersubscriptions.UserSubscriptionMaxConcurrentConnections,
// 						usersubscriptions.UserSubscriptionAutoRenew,usersubscriptions.UserSubscriptionDetails,
// 						IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
// 								FROM users
								
// 					            INNER JOIN usersubscriptions
// 			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
								
// 								INNER JOIN userpackages
// 								ON users.UserId = userpackages.UserId AND usersubscriptions.UserSubscriptionId=userpackages.UserSubscriptionId
			                    
// 								WHERE users.UserId=:UserId
//                                     AND usersubscriptions.UserSubscriptionIsTempUser=0 AND userpackages.PackageCode!=0 AND usersubscriptions.UserSubscriptionPackageId=10
// STR;
//                     $bind = array(
//                         ":UserId" => $user['UserId']
//                     );
//                     // print_r ( $bind );
//                     $results = $db->run($sql, $bind);
// 					return $results;
		
// 	}
	
}