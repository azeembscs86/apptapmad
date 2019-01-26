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
//app
class NewSignInService extends Config
{
    public static function NewsignUpORSignInUsingMobileNo(Request $request, Response $response)
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
            $user['UserProfileGender'] = 'Male';
            $user['UserProfileDOB'] = '1986-02-23';
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
                $db = parent::getDataBase();
                switch ($Version) {
                    case 'v1':
                    case 'V1': // Local/International Filter Enabled
                        // print_r ( $user );
                        $bind = array(
                            ':Username' => $user['UserUsername']
                        );
                        
                        $sql = <<<STR
                            SELECT  UserId,
                                    UserUsername,
                                    UserIsFree,
                                    UserIsActive,
                                    UserCountryCode
                            FROM newusers
                            WHERE UserUsername=:Username
                                        
STR;
                        
                       $results = $db->run($sql,$bind);
                               
                        if ($results) {                        
                            return General::getResponse($response->write(NewSignInService::localLogInUsingMobileNo($results,$db)));
                        } else if (NewUser::insertUserData($db, $user) > 0) {
                            NewUser::insertUserProfileData($db, $user);
                            //User::insertUserSubscriptionData($db, $user); 
    
                            $users[0] = $user;
                            Format::formatResponseData($users);
                            $user = $users[0];
                            return General::getResponse($response->write(SuccessObject::getSingleUsersPackagesSuccessObjects(NewUser::getUserArray($user),NewUser::getUserProfileArray($user),null,Message::getMessage('M_INSERT'))));
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
        
    public static function localLogInUsingMobileNo($results,$db)
    {   
        try {
        $results = $results[0];//;		
        $currentDate = new DateTime();
        $update = array(
            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
        );
        $bind = array(
        ":Username" => $results['UserUsername']
        );
        $db->update('newusers', $update, 'UserUsername=:Username', $bind);
        
        // To Get Object From Array   			
        $userSubscriptions =NewSignInService::getUserPackageSubscription($results['UserId'],$db);
    // print_r( $userSubscriptions);
       
        Format::formatResponseData($userSubscriptions);	
        
        $userprofile=NewSignInService::getUserProfileArray($results['UserId'],$db);   
       // print_r($userSubscriptions);
        //exit;
        return SuccessObject::getSingleUsersPackagesSuccessObjects(User::getUserArray($results),$userprofile,$userSubscriptions, Message::getMessage('M_LOGIN_SIGNUP'));
        } catch (PDOException $e) {
                return ErrorObject::getUserErrorObject(Message::getPDOMessage($e));
        } finally {
                $db = null;
        }
    }
        
    
    //------------------------------------sign/SignUp Using ACR Code--------------------------------------//
    public static function NewsignUpORSignInUsingACR(Request $request, Response $response)
    {
            $Version = filter_var(isset($request->getParsedBody()['Version']) ? $request->getParsedBody()['Version'] : NULL, FILTER_SANITIZE_STRING);
            $Language = filter_var(isset($request->getParsedBody()['Language']) ? $request->getParsedBody()['Language'] : NULL, FILTER_SANITIZE_STRING);
            parent::setConfig($Language);
            $Platform = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : NULL, FILTER_SANITIZE_STRING);
            // Users Table Data
            $user['UserUsername'] = filter_var(isset($request->getParsedBody()['ACR']) ? $request->getParsedBody()['ACR'] : NULL, FILTER_SANITIZE_STRING);
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
            $user['UserProfileFullName'] = 'NULL';
            $user['UserProfileFirstName'] = NULL;
            $user['UserProfileLastName'] = NULL;
            $user['UserProfileMobile'] = filter_var(isset($request->getParsedBody()['MobileNo']) ? $request->getParsedBody()['MobileNo'] : NULL, FILTER_SANITIZE_STRING);
            // TODO: Implement Api Resolver
            $user['UserProfileCity'] = NULL;
            $user['UserProfileState'] = NULL;
            $user['UserProfileCountry'] = 'PK';
            $user['UserProfileGender'] = 'Male';
            $user['UserProfileDOB'] = '1986-02-23';
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
            $ACRValidator = v::Alnum()->noWhitespace()->length(15, 15);
            if (! $ACRValidator->validate($user['UserUsername'])) {
                return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
            }
    
            $user['UserUsername'] = 'TA' . $user['UserUsername'] . 'CR';
            $user['UserACR'] = $user['UserUsername'];
    
            try {
                $db = parent::getDataBase();
                switch ($Version) {
                    case 'v1':
                    case 'V1': // Local/International Filter Enabled
                        // print_r ( $user );
                        $bind = array(
                            ':Username' => $user['UserUsername'],
                            ':UserACR' => $user['UserACR']
                        );
                        $sql = <<<STR
                            SELECT  UserId AS UserId,
                            UserUsername,
                            UserIsFree,
                            UserIsActive
                            FROM newusers
                            WHERE UserUsername = :Username OR UserACR = :UserACR
                                        
STR;
                        
                       $results = $db->run($sql,$bind);                                    
                        if ($results) {                        
                            return General::getResponse($response->write(NewSignInService::localLogInUsingMobileNoAndACR($results,$db)));
                        }else if (NewUser::insertUserData($db, $user) > 0) {
                            NewUser::insertUserProfileData($db, $user);
                            $users[0] = $user;
                            Format::formatResponseData($users);
                            $user = $users[0];
                            return General::getResponse($response->write(SuccessObject::getSingleUsersPackagesSuccessObjects(User::getUserArray($user),null,null,Message::getMessage('M_INSERT'))));
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
    
    
    public static function localLogInUsingMobileNoAndACR($results,$db)
    {
        try {
        $results = $results[0];//;		
        $currentDate = new DateTime();
        $update = array(
            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
        );
        $bind = array(
                ":Username" => $results['UserUsername'],
                ":UserACR" => $results['UserUsername']
        );
        
        $db->update('newusers', $update, 'UserUsername=:Username OR UserACR = :UserACR', $bind);
        // To Get Object From Array   			
        $userSubscriptions =NewSignInService::getUserPackageSubscription($results['UserId'],$db);
        Format::formatResponseData($userSubscriptions);	
        $userprofile=NewSignInService::getUserProfileArray($results['UserId'],$db);    
        return SuccessObject::getSingleUsersPackagesSuccessObjects(NewUser::getUserArray($results),$userprofile,$userSubscriptions, Message::getMessage('M_LOGIN_SIGNUP'));
        } catch (PDOException $e) {
                return ErrorObject::getUserErrorObject(Message::getPDOMessage($e));
        } finally {
                $db = null;
        }
    }
    
    
    
    
    public static function getUserProfileArray($results,$db)
    {		
            $sql = <<<STR
            SELECT    UserProfileFullName,
                      UserProfileMobile,
                      UserProfileGender,
                      UserProfileDOB,
                      UserProfilePicture
                       
            FROM usernprofiles 
            WHERE UserProfileUserId=:UserId
STR;
            
            $bind = array(
                ":UserId" => $results,
            );
            
    
            $results = $db->run($sql, $bind);
            Format::formatResponseData($results);
            return $results[0];
    }
    
        
    //------------------------------get User Multiple Subscription---------------------------//
    public function getUserPackageSubscription($user,$db)
    {
            
            $results;
            $sql = <<<STR
                    SELECT     
                        
                        UserPackageCode As UserPackageType,                        
                        UserSubscriptionStartDate,
                        UserSubscriptionExpiryDate,
                        IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) As UserSubscriptionIsExpired
                        FROM usernsubscriptions                            
    
                        WHERE UserSubscriptionUserId=:UserId
                       AND UserSubscriptionIsTempUser=0 AND UserSubscriptionPackageId=10 AND UserPackageCode IS NOT NULL
STR;
                        
                        $bind = array(
                            ":UserId" => $user
                        );
                        // print_r ( $bind );
                        $results = $db->run($sql, $bind);					
                        return $results;
            
        }
}