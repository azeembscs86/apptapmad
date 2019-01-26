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
class UserServices extends Config
{
	
    public static function signUpORSignInUsingACR(Request $request, Response $response)
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
        $user['UserProfileFullName'] = NULL;
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
                    if ($db->select('users', 'UserUsername = :Username OR UserACR = :UserACR', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogInUsingMobileNoAndACR($Version, $Language, $Platform, $user['UserUsername'])));
                    } else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);

                        // $users[0] = $user;
                        // Format::formatResponseData($users);
                        // $user = $users[0];
			// UserServices::addSubscriptionPackage($user['UserId']);
			// $userPackage = UserServices::getUserPackagesArray($user);
			// Format::formatResponseData($userPackage);	
			// $userSubscriptions =UserServices::getUserPackageSubscription($user);
                        // Format::formatResponseData($userSubscriptions);	
                        $users[0] = $user;
                        Format::formatResponseData($users);
                        $user = $users[0];
                        $bind = array(
                            ':Username' => $user['UserUsername'],
                            ':UserACR' => $user['UserACR']
                        );

                        $results=$db->select('users', 'UserUsername = :Username OR UserACR = :UserACR', $bind);
                        $results=$results[0];
                        UserServices::addSubscriptionPackage($results['UserId']);
			$userPackage = UserServices::getUserPackagesArray($results);
                        Format::formatResponseData($userPackage);	
			$userSubscriptions =UserServices::getUserPackageSubscription($results);
			Format::formatResponseData($userSubscriptions);
                        // UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
                        return General::getResponse($response->write(SuccessObject::getUserPackagesSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), User::getUserSubscriptionArray($user), UserServices::getUserPackagesArray($user),Message::getMessage('M_INSERT'))));
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

    public static function localLogInUsingMobileNoAndACR($Version, $Language, $Platform, $UserUsername)
    {
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $sql = <<<STR
			SELECT *,IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
                            FROM users
								
			INNER JOIN userprofiles
			    ON userprofiles.UserProfileUserId = users.UserId
			                   
			INNER JOIN usersubscriptions
                            ON usersubscriptions.UserSubscriptionUserId = users.UserId
								
			WHERE ( UserUsername=:Username OR UserACR = :UserACR )
                                    AND usersubscriptions.UserSubscriptionIsTempUser=0 AND usersubscriptions.UserSubscriptionPackageId=10 ORDER BY UserSubscriptionId DESC limit 0,1
STR;
                    // Password Encryption to Match Stored Password
                    // $salt = $results [0] ['UserSalt'];
                    // $saltedPW = $UserPassword . $salt;
                    // $hashedPW = hash ( 'sha256', $saltedPW );

                    $bind = array(
                        ":Username" => $UserUsername,
                        ":UserACR" => $UserUsername
                    );
                    // print_r ( $bind );
                    $results = $db->run($sql, $bind);
                    $pkgbind = array(
                            ':UserId' => $results[0]['UserId']
                    );
                    $sqlpkgquery = <<<STR
                    SELECT PackageCode FROM userpackages
                    WHERE UserId=:UserId    		        
STR;
                    $pkgs = $db->run($sqlpkgquery, $pkgbind);
                    $pkgarr = array();
                    for($c=0;$c<count($pkgs);$c++){
                            $pkg[]=$pkgs[$c]['PackageCode'];
                    }
                    $pkgarr['allinonePackageCode']=$pkg;
                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object
                    if ($results) {
                        Format::formatResponseData($results);
                        // Updating User Last LogIn Time
                        $currentDate = new DateTime();
                        $update = array(
                            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                        );
                        // "UserToken" => General::createGUID()

                        $bind = array(
                            ":Username" => $UserUsername,
                            ":UserACR" => $UserUsername
                        );
                        $db->update('users', $update, 'UserUsername=:Username OR UserACR = :UserACR', $bind);

                        // To Get Object From Array
                        $results = array_merge($results[0],$pkgarr);
                        // $results ['UserToken'] = $update ['UserToken'];
                        // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';

						

                        $userPackage = UserServices::getUserPackagesArray($results);
			Format::formatResponseData($userPackage);	
			$userSubscriptions =UserServices::getUserPackageSubscription($results);
			Format::formatResponseData($userSubscriptions);	
			if($userSubscriptions){
                            return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results),$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
			}else{
                            return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results),$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
			}
                    } else {
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
	
	
	
    public static function signUpORSignInUsingMobileNo(Request $request, Response $response)
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
        $user['UserProfileFullName'] = NULL;
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
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    // print_r ( $user );
                    $bind = array(
                        ':Username' => $user['UserUsername']
                    );
                    if ($db->select('users', 'UserUsername = :Username', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogInUsingMobileNo($Version, $Language, $Platform, $user['UserUsername'])));
                    } else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);

                        $users[0] = $user;
                        Format::formatResponseData($users);
                        $user = $users[0];
                        UserServices::addSubscriptionPackage($user['UserId']);
                        $userPackage = UserServices::getUserPackagesArray($user);
                        Format::formatResponseData($userPackage);	
                        $userSubscriptions =UserServices::getUserPackageSubscription($user);
                        Format::formatResponseData($userSubscriptions);	
                        // UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
                        return General::getResponse($response->write(SuccessObject::getUserPackagesSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), $userSubscriptions, $userPackage,Message::getMessage('M_INSERT'))));
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
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $sql = <<<STR
			            SELECT *,IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
								FROM users
								
			                    INNER JOIN userprofiles
			                    ON userprofiles.UserProfileUserId = users.UserId
			                    
					            INNER JOIN usersubscriptions
			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
								
								
			                    WHERE UserUsername=:Username
                                    AND usersubscriptions.UserSubscriptionIsTempUser=0 AND usersubscriptions.UserSubscriptionPackageId=10 ORDER BY UserSubscriptionId DESC limit 0,1
STR;
			
			
                    // Password Encryption to Match Stored Password
                    // $salt = $results [0] ['UserSalt'];
                    // $saltedPW = $UserPassword . $salt;
                    // $hashedPW = hash ( 'sha256', $saltedPW );

                    $bind = array(
                        ":Username" => $UserUsername
                    );
                    // print_r ( $bind );
                    $results = $db->run($sql, $bind);
					$pkgbind = array(
						':UserId' => $results[0]['UserId']
					);
					$sqlpkgquery = <<<STR
					SELECT PackageCode FROM userpackages
					WHERE UserId=:UserId    		        
STR;
					$pkgs = $db->run($sqlpkgquery, $pkgbind);
					$pkgarr = array();
					for($c=0;$c<count($pkgs);$c++){
						$pkg[]=$pkgs[$c]['PackageCode'];
					}
					$pkgarr['allinonePackageCode']=$pkg;
                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object
                    if ($results) {
                        Format::formatResponseData($results); 
                        // Updating User Last LogIn Time
                        $currentDate = new DateTime();
                        $update = array(
                            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                        );
                        // "UserToken" => General::createGUID()

                        $bind = array(
                            ":Username" => $UserUsername
                        );
                        $db->update('users', $update, 'UserUsername=:Username', $bind);

                        // To Get Object From Array
                        $results = array_merge($results[0],$pkgarr);
                        // $results ['UserToken'] = $update ['UserToken'];
                        // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
						$userPackage = UserServices::getUserPackagesArray($results);
						Format::formatResponseData($userPackage);	
						$userSubscriptions =UserServices::getUserPackageSubscription($results);
						Format::formatResponseData($userSubscriptions);	
						if($userSubscriptions){
							return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results),$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
						}else{
							
							return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results),$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
						}
					} else {
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

	
	/*signUpORSignInUsingMobileNum for testing*/
	public static function signUpORSignInUsingMobileNum(Request $request, Response $response)
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
        $user['UserProfileFullName'] = NULL;
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
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    // print_r ( $user );
                    $bind = array(
                        ':Username' => $user['UserUsername']
                    );
                    if ($db->select('users', 'UserUsername = :Username', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogInUsingMobileNum($Version, $Language, $Platform, $user['UserUsername'])));
                    } else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);

                        $users[0] = $user;
                        Format::formatResponseData($users);
                        $user = $users[0];
                        UserServices::addSubscriptionPackage($user['UserId']);
                        $userPackage = UserServices::getUserPackagesArray($user);
                        Format::formatResponseData($userPackage);	
                        $userSubscriptions =UserServices::getUserPackageSubscription($user);
                        Format::formatResponseData($userSubscriptions);	
                        // UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
                        return General::getResponse($response->write(SuccessObject::getUserPackagesSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), $userSubscriptions, $userPackage,Message::getMessage('M_INSERT'))));
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
	
	public static function localLogInUsingMobileNum($Version, $Language, $Platform, $UserUsername)
    {
        try {
            $db = parent::getDataBase();
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
			SELECT UserId,UserUsername,UserPackageType,UserIsFree,UserIsActive,UserCountryCode,UserIPAddress
                            FROM users
                                WHERE UserUsername=:Username
                                    
STR;
                    $bind = array(
                        ":Username" => $UserUsername
                    );
                    // print_r ( $bind );
                $results = $db->run($sql, $bind);    
                if($results){        
                  $bind = array(
                            ":Username" => $UserUsername
                        );
                        $db->update('users', $update, 'UserUsername=:Username', $bind);
                        // To Get Object From Array
                $results = $results[0];//;
                $userprofile=UserServices::getUserProfileArray($results);
                $userPackage = UserServices::getUserPackagesArraynum($results);
                Format::formatResponseData($userPackage);	
                $userSubscriptions =UserServices::getUserPackageSubscriptionnum($results);
                Format::formatResponseData($userSubscriptions);	
                if($userSubscriptions){
                    return SuccessObject::getUsersPackagesSuccessObjects(User::getUserArray($results), $userprofile,$userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
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
		$db = parent::getDataBase();
		$sql = <<<STR
		SELECT UserProfileUserId AS UserId,UserProfileFullName,UserProfileMobile,UserProfileGender,UserProfileDOB,UserProfileRegistrationDate				
			FROM userprofiles 
		WHERE UserProfileUserId=:UserId limit 1,1
STR;

		
		$bind = array(
			":UserId" => $results['UserId'],
		);

		$results = $db->run($sql, $bind);
		Format::formatResponseData($results);
		return $results[0];
	}
	
	public static function getUserPackagesArraynum($user)
	{
		$db = parent::getDataBase();    
		$sql = <<<STR
		SELECT UserPackageCode AS PackageCode FROM usersnsubscriptions
			WHERE UserSubscriptionUserId=:UserId AND UserPackageCode IS NOT NULL							
STR;
		$bind = array(
		   ":UserId" => $user['UserId']
		);
		$userPackagesArray = $db->run($sql, $bind);		
		return $userPackagesArray;
	}
	
	public function getUserPackageSubscriptionnum($user)
	{		
		$db = parent::getDataBase();
		$results;
		$sql = <<<STR
			SELECT 
				(CASE 
					WHEN UserPackageCode="1007" THEN "Premium"
					WHEN UserPackageCode="1005" THEN "Movies"
					WHEN UserPackageCode="1009" THEN "Premium + Movie"
					ELSE NULL
					END) AS PackageName, 
					UserSubscriptionUserId AS UserId,
					UserPackageCode As UserPackageType,
					UserSubscriptionStartDate,
					UserSubscriptionExpiryDate,
					isUnsubscribe As IsExpiredPackage,
					IF(DATEDIFF(UserSubscriptionExpiryDate,UserSubscriptionStartDate) = 11, 1, 0) AS IsBucketUser

					FROM usersnsubscriptions                            

					WHERE UserSubscriptionUserId=:UserId
				   AND UserPackageCode IS NOT NULL AND UserSubscriptionIsTempUser=0 AND UserSubscriptionPackageId=10
STR;
		$bind = array(
			":UserId" => $user['UserId']
		);
		// print_r ( $bind );
		$results = $db->run($sql, $bind);
		Format::formatResponseData($results);
		return $results;
	}
	
	
	
	
    
	

    public static function saveUserActivity(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        // Users Table Data
        $UserActivity['UserActivityType'] = isset($request->getParsedBody()['Type']) ? filter_var($request->getParsedBody()['Type'], FILTER_SANITIZE_STRING) : NULL;
        $UserActivity['UserActivityUserId'] = isset($request->getParsedBody()['UserId']) ? filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING) : NULL;
        $UserActivity['UserActivityIsTempUser'] = isset($request->getParsedBody()['IsTempUser']) ? filter_var($request->getParsedBody()['IsTempUser'], FILTER_SANITIZE_STRING) : NULL;
        $UserActivity['UserActivityIPAddress'] = General::getUserIP();
        $UserActivity['UserActivityLatitude'] = isset($request->getParsedBody()['Latitude']) ? filter_var($request->getParsedBody()['Latitude'], FILTER_SANITIZE_STRING) : NULL;
        $UserActivity['UserActivityLongitude'] = isset($request->getParsedBody()['Longitude']) ? filter_var($request->getParsedBody()['Longitude'], FILTER_SANITIZE_STRING) : NULL;
        $UserActivity['UserActivityDeviceId'] = isset($request->getParsedBody()['DeviceId']) ? filter_var($request->getParsedBody()['DeviceId'], FILTER_SANITIZE_STRING) : NULL;
        $UserActivity['UserActivityPlatform'] = $request->getAttribute('Platform');

        if (General::IsNullOrEmptyString($UserActivity['UserActivityType']) || General::IsNullOrEmptyString($UserActivity['UserActivityUserId']) || General::IsNullOrEmptyString($UserActivity['UserActivityIsTempUser'])) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }

        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Android':
                case 'android':
                case 'ANDROID':
                    $insert = array(
                        "UserActivityType" => $UserActivity['UserActivityType'],
                        "UserActivityUserId" => $UserActivity['UserActivityUserId'],
                        "UserActivityIsTempUser" => $UserActivity['UserActivityIsTempUser'],
                        "UserActivityIPAddress" => $UserActivity['UserActivityIPAddress'],
                        "UserActivityLatitude" => $UserActivity['UserActivityLatitude'],
                        "UserActivityLongitude" => $UserActivity['UserActivityLongitude'],
                        "UserActivityDeviceId" => $UserActivity['UserActivityDeviceId'],
                        "UserActivityPlatform" => $UserActivity['UserActivityPlatform']
                    );

                    if ((int) $db->insert("useractivity", $insert)) {
                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_INSERT'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_INSERT'))));
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    private function localSaveUserActivity($Version, $Language, $Platform, $Type, $UserId, $IsTempUser, $Latitude, $Longitude, $DeviceId)
    {
        parent::setConfig($Language);
        // Users Table Data
        $UserActivity['UserActivityType'] = $Type;
        $UserActivity['UserActivityUserId'] = $UserId;
        $UserActivity['UserActivityIsTempUser'] = $IsTempUser;
        $UserActivity['UserActivityIPAddress'] = General::getUserIP();
        $UserActivity['UserActivityLatitude'] = $Latitude;
        $UserActivity['UserActivityLongitude'] = $Longitude;
        $UserActivity['UserActivityDeviceId'] = $DeviceId;
        $UserActivity['UserActivityPlatform'] = $Platform;

        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Android':
                case 'android':
                case 'ANDROID':
                    $insert = array(
                        "UserActivityType" => $UserActivity['UserActivityType'],
                        "UserActivityUserId" => $UserActivity['UserActivityUserId'],
                        "UserActivityIsTempUser" => $UserActivity['UserActivityIsTempUser'],
                        "UserActivityIPAddress" => $UserActivity['UserActivityIPAddress'],
                        "UserActivityLatitude" => $UserActivity['UserActivityLatitude'],
                        "UserActivityLongitude" => $UserActivity['UserActivityLongitude'],
                        "UserActivityDeviceId" => $UserActivity['UserActivityDeviceId'],
                        "UserActivityPlatform" => $UserActivity['UserActivityPlatform']
                    );

                    if ((int) $db->insert("useractivity", $insert)) {
                        return SuccessObject::getSuccessObject(Message::getMessage('M_INSERT'));
                    } else {
                        return ErrorObject::getErrorObject(Message::getMessage('E_NO_INSERT'));
                    }
                    break;
                default:
                    return ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'));
                    break;
            }
        } catch (PDOException $e) {
            return ErrorObject::getErrorObject(Message::getPDOMessage($e));
        } finally {
            $db = null;
        }
    }

    public static function getAllUsersAgainstMobileNo(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        // Users Table Data
        $MobileNo = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    $sql = <<<STR
						SELECT users.UserId,users.UserUsername,IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
								FROM users
			
			                    INNER JOIN userprofiles
			                    ON userprofiles.UserProfileUserId = users.UserId
			
					            INNER JOIN usersubscriptions
			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
			
			                    WHERE UserProfileMobile=:UserProfileMobile
                                    AND UserSubscriptionIsTempUser=0
STR;
                    // Password Encryption to Match Stored Password
                    // $salt = $results [0] ['UserSalt'];
                    // $saltedPW = $UserPassword . $salt;
                    // $hashedPW = hash ( 'sha256', $saltedPW );

                    $bind = array(
                        ':UserProfileMobile' => $MobileNo
                    );
                    // print_r ( $bind );
                    $results = $db->run($sql, $bind);

                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object
                    if ($results) {
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($results, Message::getMessage('M_DATA'), NULL, NULL, 'Users')));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('W_NO_CONTENT'))));
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

    public static function getUserIdAgainstUsername(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        // Users Table Data
        $Username = filter_var($request->getAttribute('Username'), FILTER_SANITIZE_STRING);

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    $sql = <<<STR
						SELECT users.UserId,users.UserUsername AS Username
								FROM users
		
			                    INNER JOIN userprofiles ON userprofiles.UserProfileUserId = users.UserId
		
					            INNER JOIN usersubscriptions ON usersubscriptions.UserSubscriptionUserId = users.UserId
		
			                    WHERE UserUsername=:UserUsername
                                    AND UserSubscriptionIsTempUser=0
STR;
                    // Password Encryption to Match Stored Password
                    // $salt = $results [0] ['UserSalt'];
                    // $saltedPW = $UserPassword . $salt;
                    // $hashedPW = hash ( 'sha256', $saltedPW );

                    $bind = array(
                        ':UserUsername' => $Username
                    );
                    // print_r ( $bind );
                    $results = $db->run($sql, $bind);

                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object
                    if ($results) {
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($results, Message::getMessage('M_DATA'), NULL, NULL, 'Users')));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('W_NO_CONTENT'), NULL, NULL, 'Users')));
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    public static function saveOrUpdateDeviceInfo(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');

        $DeviceInfo['DeviceID'] = $request->getAttribute('DeviceID');
        $DeviceInfo['DeviceMAC'] = $request->getAttribute('DeviceMAC');
        $DeviceInfo['DeviceToken'] = $request->getAttribute('DeviceToken');
        $DeviceInfo['DeviceTokenNew'] = $request->getAttribute('DeviceToken');
        $DeviceInfo['DeviceOS'] = $request->getAttribute('Platform');
        $DeviceInfo['DeviceBrand'] = $request->getAttribute('DeviceBrand');
        $DeviceInfo['DeviceName'] = $request->getAttribute('DeviceName');
        $DeviceInfo['DeviceModel'] = $request->getAttribute('DeviceModel');
        $DeviceInfo['DeviceManufacturer'] = $request->getAttribute('DeviceManufacturer');
        $DeviceInfo['DeviceProduct'] = $request->getAttribute('DeviceProduct');

        try {
            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":DeviceID" => $DeviceInfo['DeviceID'],
                                ":DeviceMAC" => $DeviceInfo['DeviceMAC']
                            );
                            $results = $db->select("deviceinformation", "DeviceID=:DeviceID AND DeviceMAC=:DeviceMAC", $bind);

                            // If Device ID Plus MAC Address Exists Then it will Proceed
                            // Else it will Add a New Entry
                            if ($results) {
                                if ($results[0]['DeviceToken'] === $DeviceInfo['DeviceToken']) {
                                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_NO_UPDATE_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                } else {
                                    $update = array(
                                        "DeviceToken" => $DeviceInfo['DeviceToken']
                                    );
                                    $bind = array(
                                        ":DeviceID" => $DeviceInfo['DeviceID'],
                                        ":DeviceMAC" => $DeviceInfo['DeviceMAC']
                                    );
                                    if ($db->update("deviceinformation", $update, "DeviceID =:DeviceID AND DeviceMAC =:DeviceMAC", $bind)) {
                                        return General::getResponse($response->write(SuccessObject::getGeneralSuccessObject2(Message::getMessage('M_UPDATE_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_NO_UPDATE_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                    }
                                }
                            } else {
                                if (General::insertDeviceInfo($db, $DeviceInfo)) {
                                    return General::getResponse($response->write(SuccessObject::getGeneralSuccessObject2(Message::getMessage('M_INSERT_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_NO_INSERT_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                }
                            }
                            break;
                        case 'AndroidNew':
                        case 'androidnew':
                        case 'ANDROIDNEW':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":DeviceID" => $DeviceInfo['DeviceID'],
                                ":DeviceMAC" => $DeviceInfo['DeviceMAC']
                            );
                            $results = $db->select("deviceinformation", "DeviceID=:DeviceID AND DeviceMAC=:DeviceMAC", $bind);

                            // If Device ID Plus MAC Address Exists Then it will Proceed
                            // Else it will Add a New Entry
                            if ($results) {
                                if ($results[0]['DeviceTokenNew'] === $DeviceInfo['DeviceTokenNew']) {
                                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_NO_UPDATE_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                } else {
                                    $update = array(
                                        "DeviceTokenNew" => $DeviceInfo['DeviceTokenNew']
                                    );
                                    $bind = array(
                                        ":DeviceID" => $DeviceInfo['DeviceID'],
                                        ":DeviceMAC" => $DeviceInfo['DeviceMAC']
                                    );
                                    if ($db->update("deviceinformation", $update, "DeviceID =:DeviceID AND DeviceMAC =:DeviceMAC", $bind)) {
                                        return General::getResponse($response->write(SuccessObject::getGeneralSuccessObject2(Message::getMessage('M_UPDATE_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_NO_UPDATE_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                    }
                                }
                            } else {
                                $DeviceInfo['DeviceOS'] = 'android';
                                if (General::insertNewDeviceInfo($db, $DeviceInfo)) {
                                    return General::getResponse($response->write(SuccessObject::getGeneralSuccessObject2(Message::getMessage('M_INSERT_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_NO_INSERT_DEVICE_INFO'), $DeviceInfo, 'DeviceInfo')));
                                }
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(array(
                        'In Process.'
                    ), $DeviceInfo, 'DeviceInfo')));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), $DeviceInfo, 'DeviceInfo')));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    public static function updateTrialPeriod(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $DeviceID = $request->getAttribute('DeviceID');
        $MACAddress = $request->getAttribute('MACAddress');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    $bind = array(
                        ":TempUserDeviceId" => $DeviceID,
                        ":TempUserDeviceMac" => $MACAddress
                    );
                    $results = $db->select("tempusers", "TempUserDeviceId=:TempUserDeviceId AND TempUserDeviceMac=:TempUserDeviceMac", $bind);

                    // If Device ID Plus MAC Address Exists Then it will Proceed
                    // Else it will Add a New Temp User Entry
                    if ($results) {
                        $results = $results[0];
                        $results['TempUserTrialUsed'] = (int) $results['TempUserTrialUsed'];
                        $results['TempUserTrialUsed'] += 5;

                        $update = array(
                            "TempUserTrialUsed" => $results['TempUserTrialUsed']
                        );
                        $bind = array(
                            ":TempUserDeviceId" => $results["TempUserDeviceId"],
                            ":TempUserDeviceMac" => $results["TempUserDeviceMac"]
                        );

                        $db->update("tempusers", $update, "TempUserDeviceId =:TempUserDeviceId AND TempUserDeviceMac =:TempUserDeviceMac", $bind);
                        return General::getResponse($response->write(SuccessObject::getTempUserSuccessObject($results, Message::getMessage('M_TRIAL_UPDATED'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getMessage('W_NO_CONTENT'))));
                    }
                    break;
                case 'v2':
                case 'V2':
                    $bind = array(
                        ":TempUserDeviceId" => $DeviceID,
                        ":TempUserDeviceMac" => $MACAddress
                    );
                    $results = $db->select("tempusers", "TempUserDeviceId=:TempUserDeviceId AND TempUserDeviceMac=:TempUserDeviceMac", $bind);

                    // If Device ID Plus MAC Address Exists Then it will Proceed
                    // Else it will Add a New Temp User Entry
                    if ($results) {
                        $results = $results[0];
                        $results['TempUserId'] = (int) $results['TempUserId'];
                        $results['TempUserIsRestricted'] = (int) $results['TempUserIsRestricted'];
                        $results['TempUserTrialUsed'] = (int) $results['TempUserTrialUsed'];

                        if ($results["TempUserTrialUsed"] < Config::$trialPeriodLimit) {
                            $results['TempUserTrialUsed'] += 5;
                            if ($results["TempUserTrialUsed"] >= Config::$trialPeriodLimit) {
                                $results['TempUserIsRestricted'] = 1;
                                $update = array(
                                    "TempUserTrialUsed" => $results['TempUserTrialUsed'],
                                    "TempUserIsRestricted" => $results['TempUserIsRestricted']
                                );
                                $bind = array(
                                    ":TempUserDeviceId" => $results["TempUserDeviceId"],
                                    ":TempUserDeviceMac" => $results["TempUserDeviceMac"]
                                );
                                $db->update("tempusers", $update, "TempUserDeviceId =:TempUserDeviceId AND TempUserDeviceMac =:TempUserDeviceMac", $bind);
                                return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getMessage('E_RESTRICT_USER'), $results)));
                            } else {
                                $update = array(
                                    "TempUserTrialUsed" => $results['TempUserTrialUsed']
                                );
                                $bind = array(
                                    ":TempUserDeviceId" => $results["TempUserDeviceId"],
                                    ":TempUserDeviceMac" => $results["TempUserDeviceMac"]
                                );
                                $db->update("tempusers", $update, "TempUserDeviceId =:TempUserDeviceId AND TempUserDeviceMac =:TempUserDeviceMac", $bind);
                                return General::getResponse($response->write(SuccessObject::getTempUserSuccessObject($results, Message::getMessage('M_TRIAL_UPDATED'))));
                            }
                        } else {
                            return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getMessage('E_RESTRICT_USER'), $results)));
                        }
                    } else {
                        return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getMessage('W_NO_CONTENT'))));
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Add/Update User Information in tempusers
     *
     * @param Request $request
     * @param Response $response
     */
    public static function registerTempUser(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');

        // $headers = $response->getHeaders ();
        // $tempUser ["AllowedToken"] = $headers ['AllowedToken'] [0];
        $tempUser["AllowedToken"] = NULL;

        $tempUser["TempUserDeviceId"] = $request->getAttribute('DeviceID');
        $tempUser["TempUserDeviceMac"] = $request->getAttribute('MACAddress');
        $tempUser["TempUserToken"] = General::createGUID('temp');
        // $tempUser ["TempUserToken"] = General::createNewToken ( 24, 'tmp' );
        // $tempUser ["TempUserToken"] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
        $tempUser["TempUserIPAddress"] = General::getUserIP();
        $currentDate = new DateTime();
        $tempUser["TempUserFirstVisitAt"] = $currentDate->format('Y-m-d H:i:s');
        $tempUser["TempUserLastVisitAt"] = $currentDate->format('Y-m-d H:i:s');

        try {
            $db = parent::getDataBase();
            $bind = array(
                ":TempUserDeviceId" => $tempUser["TempUserDeviceId"],
                ":TempUserDeviceMac" => $tempUser["TempUserDeviceMac"]
            );
            $results = $db->select("tempusers", "TempUserDeviceId=:TempUserDeviceId AND TempUserDeviceMac=:TempUserDeviceMac", $bind);

            // If Device ID Plus MAC Address Exists Then it will Proceed
            // Else it will Add a New Temp User Entry
            if ($results) {
                Format::formatResponseData($results);
                $results = $results[0];
                $results['AllowedToken'] = $tempUser["AllowedToken"];
                // If we have to Restrict the Unregistered User from Watching Further Content then Proceed
                // Else just Update User Last Visit Time
                switch ($Version) {
                    case 'v2':
                    case 'V2':
                        // When System Has To Restrict The User.
                        if ($results["TempUserIsRestricted"]) {
                            return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getMessage('E_RESTRICT_USER'), $results)));
                        } else {
                            $update = array(
                                "TempUserIsRestricted" => 1
                            );
                            $bind = array(
                                ":TempUserDeviceId" => $tempUser["TempUserDeviceId"],
                                ":TempUserDeviceMac" => $tempUser["TempUserDeviceMac"]
                            );
                            $db->update("tempusers", $update, "TempUserDeviceId =:TempUserDeviceId AND TempUserDeviceMac =:TempUserDeviceMac", $bind);
                            return General::getResponse($response->write(SuccessObject::getTempUserSuccessObject($results, Message::getMessage('M_RESTRICT_TEMP_USER'))));
                        }
                        break;
                    case 'v1':
                    case 'V1':
                        // When Restriction Is Not Applicable.
                        $update = array(
                            "TempUserLastVisitAt" => $tempUser["TempUserLastVisitAt"],
                            "TempUserToken" => $tempUser["TempUserToken"]
                        );
                        $bind = array(
                            ":TempUserDeviceId" => $tempUser["TempUserDeviceId"],
                            ":TempUserDeviceMac" => $tempUser["TempUserDeviceMac"]
                        );
                        $db->update("tempusers", $update, "TempUserDeviceId =:TempUserDeviceId AND TempUserDeviceMac =:TempUserDeviceMac", $bind);
                        $results['TempUserLastVisitAt'] = $tempUser['TempUserLastVisitAt'];
                        $results['TempUserToken'] = $tempUser['TempUserToken'];
                        $currentDate = new DateTime();
                        $currentDate = $currentDate->modify('+1 day');
                        $results['UserSubscriptionExpiryDate'] = $currentDate->format('Y-m-d H:i:s');

                        $update = array(
                            "UserSubscriptionExpiryDate" => $results['UserSubscriptionExpiryDate']
                        );
                        $bind = array(
                            ":UserSubscriptionUserId" => $results['TempUserId']
                        );

                        $db->update("usersubscriptions", $update, "UserSubscriptionUserId =:UserSubscriptionUserId AND UserSubscriptionIsTempUser = 1", $bind);
                        return General::getResponse($response->write(SuccessObject::getTempUserSuccessObject($results, Message::getMessage('M_UPDATE_TEMP_USER'))));
                        break;
                    case 'v3':
                    case 'V3':
                        return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(array(
                            'In Process.'
                        ))));
                        break;
                    default:
                        return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                        break;
                }
            } else {
                $user['UserSubscriptionIsTempUser'] = 1;
                $user['UserSubscriptionPackageId'] = 7;
                $user['UserSubscriptionStartDate'] = $currentDate->format('Y-m-d H:i:s');
                $currentDate = $currentDate->modify('+1 day');
                $user['UserSubscriptionExpiryDate'] = $currentDate->format('Y-m-d H:i:s');
                $user['UserSubscriptionMaxConcurrentConnections'] = 5;
                $user['UserSubscriptionAutoRenew'] = '0';
                $user['UserSubscriptionDetails'] = NULL;
                $user['UserId'] = User::insertTempUserData($db, $tempUser);
                User::insertUserSubscriptionData($db, $user);
                return General::getResponse($response->write(SuccessObject::getTempUserSuccessObject($tempUser, Message::getMessage('M_INSERT_TEMP_USER'))));
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Send Push Notification
     *
     * @param Request $request
     * @param Response $response
     */
    public static function sendPushNotifications(Request $request, Response $response)
    {
        // In Process
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');

        // Users Table Data
        $UserProfile['UserUsername'] = $request->getAttribute('Username');
        $UserProfile['UserProfileFirstName'] = $request->getAttribute('FirstName');
        $UserProfile['UserProfileLastName'] = $request->getAttribute('LastName');
        $UserProfile['UserProfileCity'] = $request->getAttribute('City');
        $UserProfile['UserProfileState'] = $request->getAttribute('State');
        $UserProfile['UserProfileCountry'] = $request->getAttribute('Country');
        $UserProfile['UserProfileGender'] = $request->getAttribute('Gender');
        $UserProfile['UserProfileDOB'] = $request->getAttribute('DOB');

        try {
            $db = parent::getDataBase();
            // print_r ( $UserProfile );
            switch ($Version) {
                case 'v1':
                case 'V1':
                    return General::getResponse($response->write(SuccessObject::getGeneralSuccessObject2(Message::getMessage('M_UPDATE'), $UserProfile, 'UserProfile')));
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(array(
                        'In Process.'
                    ), $UserProfile, 'UserProfile')));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), $UserProfile, 'UserProfile')));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Edit User Profile Info
     *
     * @param Request $request
     * @param Response $response
     */
    public static function editUserProfile(Request $request, Response $response)
    {
        $Version = isset($request->getParsedBody()['Version']) ? filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING) : NULL;
        $Language = isset($request->getParsedBody()['Language']) ? filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING) : NULL;
        parent::setConfig($Language);
        $Platform = isset($request->getParsedBody()['Platform']) ? filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING) : NULL;

        // Users Table Data
        $UserProfile['UserId'] = isset($request->getParsedBody()['UserId']) ? filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserChatId'] = isset($request->getParsedBody()['UserChatId']) ? filter_var($request->getParsedBody()['UserChatId'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserProfileMobile'] = isset($request->getParsedBody()['MobileNo']) ? filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserProfileFullName'] = isset($request->getParsedBody()['FullName']) ? filter_var($request->getParsedBody()['FullName'], FILTER_SANITIZE_STRING) : NULL;
        // $UserProfile ['UserProfileFirstName'] = $request->getParsedBody() ['FirstName'];
        // $UserProfile ['UserProfileLastName'] = $request->getParsedBody() ['LastName'];
        // $UserProfile ['UserProfileCity'] = $request->getParsedBody() ['City'];
        // $UserProfile ['UserProfileState'] = $request->getParsedBody() ['State'];
        // $UserProfile ['UserProfileCountry'] = $request->getParsedBody() ['Country'];
        $UserProfile['UserProfileGender'] = isset($request->getParsedBody()['Gender']) ? filter_var($request->getParsedBody()['Gender'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserProfileDOB'] = isset($request->getParsedBody()['DOB']) ? filter_var($request->getParsedBody()['DOB'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserProfileMobileNetwork'] = isset($request->getParsedBody()['MobileNetwork']) ? filter_var($request->getParsedBody()['MobileNetwork'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserProfileRefCode'] = isset($request->getParsedBody()['RefCode']) ? filter_var($request->getParsedBody()['RefCode'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserProfileRefCode2'] = isset($request->getParsedBody()['RefCode2']) ? filter_var($request->getParsedBody()['RefCode2'], FILTER_SANITIZE_STRING) : NULL;
        $UserProfile['UserProfilePicture'] = isset($request->getParsedBody()['ProfilePicture']) ? filter_var($request->getParsedBody()['ProfilePicture'], FILTER_SANITIZE_STRING) : NULL;

        try {
            $db = parent::getDataBase();
            // print_r ( $UserProfile );
            switch ($Version) {
                case 'v1':
                case 'V1':
                    if (isset($request->getParsedBody()['UserChatId'])) {
                        $Bind = array(
                            ':UserChatId' => $UserProfile['UserChatId']
                        );

                        $Results = $db->select('users', 'UserChatId = :UserChatId', $Bind);
                        if ($Results) {
                            return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_ALREADY_CHAT_ID'), $UserProfile, 'UserProfile')));
                        }
                    }

                    $Bind = array(
                        ':UserId' => $UserProfile['UserId']
                    );

                    $Results = $db->select('users', 'UserId = :UserId', $Bind);
                    if ($Results) {
                        // print_r($_FILES ['ProfilePicture']);
                        if (isset($_FILES['ProfilePicture'])) {
                            try {

                                // Undefined | Multiple Files | $_FILES Corruption Attack
                                // If this request falls under any of them, treat it invalid.
                                if (! isset($_FILES['ProfilePicture']['error']) || is_array($_FILES['ProfilePicture']['error'])) {
                                    throw new RuntimeException('Invalid parameters.');
                                }

                                // Check $_FILES['upfile']['error'] value.
                                switch ($_FILES['ProfilePicture']['error']) {
                                    case UPLOAD_ERR_OK:
                                        break;
                                    case UPLOAD_ERR_NO_FILE:
                                        // throw new RuntimeException ( 'No file sent.' );
                                        break;
                                    case UPLOAD_ERR_INI_SIZE:
                                    case UPLOAD_ERR_FORM_SIZE:
                                        throw new RuntimeException('Exceeded filesize limit.');
                                        break;
                                    default:
                                        throw new RuntimeException('Server Not Responding. Please Try Later.');
                                        break;
                                }

                                // You should also check filesize here.

                                if ($_FILES['ProfilePicture']['size'] > 2097152) {
                                    throw new RuntimeException('Exceeded File Size Limit.');
                                }

                                // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                                // Check MIME Type by yourself.
                                if (false === $ext = array_search(image_type_to_mime_type(exif_imagetype($_FILES['ProfilePicture']['tmp_name'])), array(
                                        'jpg' => 'image/jpeg',
                                        'png' => 'image/png',
                                        'gif' => 'image/gif'
                                    ), true)) {
                                    throw new RuntimeException('Invalid File Format.');
                                }

                                // You should name it uniquely.
                                // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                                // On this example, obtain safe unique name from its binary data.
                                $UniqueID = uniqid('img-' . date('Ymd') . '-');
                                // $DestinationPath = getcwd().DIRECTORY_SEPARATOR;
                                $DestinationPath = '../pics/profilepics/';
                                $FileName = filter_var($UniqueID . basename($_FILES["ProfilePicture"]["name"]), FILTER_SANITIZE_STRING);
                                $TargetPath = $DestinationPath . $FileName;


 
							if (move_uploaded_file($_FILES['ProfilePicture']['tmp_name'], $TargetPath) === false) {
                                    throw new RuntimeException('Failed To Move Uploaded File.');
                                } else {
									
                                    $UserProfile['UserProfilePicture'] = 'http://app.tapmad.com/pics/profilepics/' . $FileName;
                                }
                            } catch (RuntimeException $e) {
                                return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getPDOMessage($e), $UserProfile, 'UserProfile')));
                            }
                        }

                        $UpdateUserProfile = array(
                            isset($UserProfile['UserProfileFullName']) && $UserProfile['UserProfileFullName'] != NULL ? 'UserProfileFullName' : NULL => $UserProfile['UserProfileFullName'],
                            isset($UserProfile['UserProfileMobile']) && $UserProfile['UserProfileMobile'] != NULL ? 'UserProfileMobile' : NULL => $UserProfile['UserProfileMobile'],
                            isset($UserProfile['UserProfileGender']) && $UserProfile['UserProfileGender'] != NULL ? 'UserProfileGender' : NULL => $UserProfile['UserProfileGender'],
                            isset($UserProfile['UserProfileDOB']) && $UserProfile['UserProfileDOB'] != NULL ? 'UserProfileDOB' : NULL => $UserProfile['UserProfileDOB'],
                            isset($UserProfile['UserProfilePicture']) && $UserProfile['UserProfilePicture'] != NULL ? 'UserProfilePicture' : NULL => $UserProfile['UserProfilePicture'],
                            isset($UserProfile['UserProfileMobileNetwork']) && $UserProfile['UserProfileMobileNetwork'] != NULL ? 'UserProfileMobileNetwork' : NULL => $UserProfile['UserProfileMobileNetwork'],
                            isset($UserProfile['UserProfileRefCode']) && $UserProfile['UserProfileRefCode'] != NULL ? 'UserProfileRefCode' : NULL => $UserProfile['UserProfileRefCode'],
                            isset($UserProfile['UserProfileRefCode2']) && $UserProfile['UserProfileRefCode2'] != NULL ? 'UserProfileRefCode2' : NULL => $UserProfile['UserProfileRefCode2']
                        );
                        $BindUserProfile = array(
                            ":UserProfileUserId" => $Results[0]['UserId']
                        );
                        $UpdateUser = array(
                            isset($UserProfile['UserChatId']) && $UserProfile['UserChatId'] != NULL ? 'UserChatId' : NULL => $UserProfile['UserChatId']
                        );
                        $BindUser = array(
                            ":UserId" => $Results[0]['UserId']
                        );

                        if ($db->update('userprofiles', $UpdateUserProfile, 'UserProfileUserId=:UserProfileUserId', $BindUserProfile) || $db->update('users', $UpdateUser, 'UserId=:UserId', $BindUser)) {
                            return General::getResponse($response->write(SuccessObject::getGeneralSuccessObject2(Message::getMessage('M_UPDATE'), $UserProfile, 'UserProfile')));
                        } else {
                            return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_NO_UPDATE'), $UserProfile, 'UserProfile')));
                        }
                    } else {
                        return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('W_NO_CONTENT'), $UserProfile, 'UserProfile')));
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), $UserProfile, 'UserProfile')));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getGeneralErrorObject(Message::getPDOMessage($e), $UserProfile, 'UserProfile')));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Register User
     *
     * @param Request $request
     * @param Response $response
     */
    public static function signUp(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        // Users Table Data
        $user['UserUsername'] = $request->getAttribute('Username');
        $user['UserSubscriptionAutoRenew'] = $request->getAttribute('IsRecurring');

        // Password Encryption
        // Generate a random salt to use for this account
        // $salt = bin2hex ( mcrypt_create_iv ( 32, MCRYPT_DEV_URANDOM ) ) );
        // $saltedPW = $request->getAttribute ( 'Password' ) . $salt;
        // $hashedPW = hash ( 'sha256', $saltedPW );
        // $user ['UserPassword'] = $hashedPW;
        // $user ['UserSalt'] = $salt;

        $user['UserPassword'] = md5($request->getAttribute('Password'));

        $currentDate = new DateTime();
        $user['UserLastLoginAt'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserEmail'] = NULL;
        $user['UserToken'] = General::createGUID();
        // $user ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
        $user['UserIsFree'] = '1';
        $user['UserIsActive'] = '1';
        $user['UserActivationCode'] = General::createNumericToken(7);
        $user['UserIsPublisher'] = '0';
        $user['UserNetwork'] = 'other';
        $user['UserCountryCode'] = 'PK';
        $user['UserIPAddress'] = General::getUserIP();
        $user['UserTypeId'] = '0';
        $user['UserIsPassChanged'] = '0';

        // User Profiles Table Data
        $user['UserProfileFullName'] = NULL;
        $user['UserProfileFirstName'] = NULL;
        $user['UserProfileLastName'] = NULL;
        $user['UserProfileMobile'] = $request->getAttribute('MobileNo');
        // TODO: Implement Api Resolver
        $user['UserProfileCity'] = NULL;
        $user['UserProfileState'] = NULL;
        $user['UserProfileCountry'] = NULL;
        $user['UserProfileGender'] = NULL;
        $user['UserProfileDOB'] = NULL;
        $currentDate = new DateTime();
        $user['UserProfileRegistrationDate'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserProfilePlatform'] = $request->getAttribute('Platform');
        $user['UserProfileRefCode'] = NULL;
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
        $user['UserSubscriptionIsExpired'] = false;

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    // print_r ( $user );
                    $bind = array(
                        ':Username' => $user['UserUsername']
                    );
                    if ($db->select('users', 'UserUsername = :Username', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogIn($Version, $Language, $Platform, $user['UserUsername'], $user['UserPassword'])));
                    } else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);

                        $user['UserId'] = (int) $user['UserId'];
                        $user['UserIsFree'] = (int) $user['UserIsFree'];
                        $user['UserIsActive'] = (int) $user['UserIsActive'];
                        $user['UserIsPublisher'] = (int) $user['UserIsPublisher'];
                        $user['UserTypeId'] = (int) $user['UserTypeId'];
                        $user['UserIsPassChanged'] = (int) $user['UserIsPassChanged'];
                        $user['UserActivationCode'] = NULL;
                        // UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), User::getUserSubscriptionArray($user), Message::getMessage('M_INSERT'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_INSERT'))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(array(
                        'In Process.'
                    ))));
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

    public static function signUpPOST(Request $request, Response $response)
    {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);
        // Users Table Data
        $user['UserUsername'] = filter_var($request->getParsedBody()['Username'], FILTER_SANITIZE_STRING);
        $user['UserSubscriptionAutoRenew'] = filter_var($request->getParsedBody()['IsRecurring'], FILTER_SANITIZE_STRING);

        // Password Encryption
        // Generate a random salt to use for this account
        // $salt = bin2hex ( mcrypt_create_iv ( 32, MCRYPT_DEV_URANDOM ) ) );
        // $saltedPW = $request->getAttribute ( 'Password' ) . $salt;
        // $hashedPW = hash ( 'sha256', $saltedPW );
        // $user ['UserPassword'] = $hashedPW;
        // $user ['UserSalt'] = $salt;

        $user['UserPassword'] = md5($request->getParsedBody()['Password']);

        $currentDate = new DateTime();
        $user['UserLastLoginAt'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserEmail'] = NULL;
        $user['UserToken'] = General::createGUID();
        // $user ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
        $user['UserDeviceId'] = filter_var($request->getParsedBody()['DeviceID'], FILTER_SANITIZE_STRING);
        $user['UserIsFree'] = '1';
        $user['UserIsActive'] = '1';
        $user['UserActivationCode'] = General::createNumericToken(7);
        $user['UserIsPublisher'] = '0';
        $user['UserNetwork'] = 'other';
        $user['UserCountryCode'] = 'PK';
        $user['UserIPAddress'] = General::getUserIP();
        $user['UserTypeId'] = '0';
        $user['UserIsPassChanged'] = '0';

        // User Profiles Table Data
        $user['UserProfileFullName'] = NULL;
        $user['UserProfileFirstName'] = NULL;
        $user['UserProfileLastName'] = NULL;
        $user['UserProfileMobile'] = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
        // TODO: Implement Api Resolver
        $user['UserProfileCity'] = NULL;
        $user['UserProfileState'] = NULL;
        $user['UserProfileCountry'] = NULL;
        $user['UserProfileGender'] = NULL;
        $user['UserProfileDOB'] = NULL;
        $currentDate = new DateTime();
        $user['UserProfileRegistrationDate'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserProfilePlatform'] = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);
        $user['UserProfileRefCode'] = filter_var($request->getParsedBody()['RefCode'], FILTER_SANITIZE_STRING);
        $user['UserProfileRefCode2'] = filter_var($request->getParsedBody()['RefCode2'], FILTER_SANITIZE_STRING);
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

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    // print_r ( $user );
                    $bind = array(
                        ':Username' => $user['UserUsername']
                    );
                    $bind2 = array(
                        ':UserDeviceId' => $user['UserDeviceId']
                    );
                    if ($db->select('users', 'UserUsername = :Username', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogIn($Version, $Language, $Platform, $user['UserUsername'], $user['UserPassword'])));
                    } else if ($db->select('users', 'UserDeviceId = :UserDeviceId', $bind2)) {
                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_DEVICE_EXIST'))));
                    } else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);

                        $user['UserId'] = (int) $user['UserId'];
                        $user['UserIsFree'] = (int) $user['UserIsFree'];
                        $user['UserIsActive'] = (int) $user['UserIsActive'];
                        $user['UserIsPublisher'] = (int) $user['UserIsPublisher'];
                        $user['UserTypeId'] = (int) $user['UserTypeId'];
                        $user['UserIsPassChanged'] = (int) $user['UserIsPassChanged'];
                        $user['UserActivationCode'] = NULL;
                        // UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), User::getUserSubscriptionArray($user), Message::getMessage('M_INSERT'))));
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

    public static function saveUserMobileNo(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        // Users Table Data
        $MobileNo = $request->getAttribute('MobileNo');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    $bind = array(
                        ':UserMobileNo' => $MobileNo
                    );
                    if ($db->select('usermobileno', 'UserMobileNo = :UserMobileNo', $bind)) {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_INSERT'))));
                    } else {
                        $insert = array(
                            "UserMobileNo" => $MobileNo
                        );
                        $db->insert("usermobileno", $insert);
                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_INSERT'))));
                    }
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    public static function localLogIn($Version, $Language, $Platform, $UserUsername, $UserPassword)
    {
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $bind = array(
                        ":Username" => $UserUsername
                    );

                    $results = $db->select('users', 'UserUsername=:Username', $bind);
                    // If Result is Returned then Verify Password
                    // Else Return Error Message Object
                    if ($results) {

                        $sql = <<<STR
			            SELECT *, IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
								
								FROM users
			
			                    INNER JOIN userprofiles
			                    ON userprofiles.UserProfileUserId = users.UserId
			
					            INNER JOIN usersubscriptions
			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
			
			                    WHERE UserUsername=:Username
                                    AND UserPassword=:Password
                                    AND UserSubscriptionIsTempUser=0
STR;
                        // Password Encryption to Match Stored Password
                        // $salt = $results [0] ['UserSalt'];
                        // $saltedPW = $UserPassword . $salt;
                        // $hashedPW = hash ( 'sha256', $saltedPW );

                        $bind = array(
                            ":Username" => $UserUsername,
                            ":Password" => $UserPassword
                        );
                        // print_r ( $bind );
                        $results = $db->run($sql, $bind);

                        // If Result is Returned then Return User Information
                        // Else Return Error Message Object
                        if ($results) {
                            Format::formatResponseData($results);
                            // Updating User Last LogIn Time
                            $currentDate = new DateTime();
                            $update = array(
                                "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                            );
                            // "UserToken" => General::createGUID()

                            $bind = array(
                                ":Username" => $UserUsername
                            );
                            $db->update('users', $update, 'UserUsername=:Username', $bind);

                            // To Get Object From Array
                            $results = $results[0];
                            // $results ['UserToken'] = $update ['UserToken'];
                            // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';

                            return SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_LOGIN_SIGNUP'));
                        } else {
                            return ErrorObject::getUserErrorObject(Message::getMessage('E_WRONG_PASS_SIGNUP'));
                        }
                    } else {
                        return ErrorObject::getUserErrorObject(Message::getMessage('E_NO_LOGIN'));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return ErrorObject::getUserErrorObject(array(
                        'In Process.'
                    ));
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

    
	
	
	
	

    /**
     * Function to Register User Using Facebook
     *
     * @param Request $request
     * @param Response $response
     */
    public static function signUpUsingFacebook(Request $request, Response $response)
    {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);

        // Users Table Data
        $user['UserFacebookId'] = filter_var($request->getParsedBody()['FacebookId'], FILTER_SANITIZE_STRING);
        $user['UserUsername'] = filter_var($request->getParsedBody()['FacebookId'], FILTER_SANITIZE_STRING);
        $user['UserPassword'] = filter_var($request->getParsedBody()['FacebookId'], FILTER_SANITIZE_STRING);
        $user['UserEmail'] = filter_var($request->getParsedBody()['Email'], FILTER_SANITIZE_STRING);
        $user['UserToken'] = General::createGUID();
        // $user ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
        $user['UserDeviceId'] = filter_var($request->getParsedBody()['DeviceID'], FILTER_SANITIZE_STRING);
        $user['UserIsFree'] = '1';
        $user['UserIsActive'] = '1';
        $user['UserIsPublisher'] = '0';
        $user['UserNetwork'] = 'other';
        $user['UserCountryCode'] = 'PK';
        $user['UserIPAddress'] = General::getUserIP();
        $user['UserTypeId'] = '0';
        $user['UserIsPassChanged'] = '0';

        // User Profiles Table Data
        $user['UserProfileFullName'] = filter_var($request->getParsedBody()['FullName'], FILTER_SANITIZE_STRING);
        $user['UserProfileFirstName'] = filter_var($request->getParsedBody()['FirstName'], FILTER_SANITIZE_STRING);
        $user['UserProfileLastName'] = filter_var($request->getParsedBody()['LastName'], FILTER_SANITIZE_STRING);
        $user['UserProfileMobile'] = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
        ;
        // TODO: Implement Api Resolver
        $user['UserProfileCity'] = NULL;
        $user['UserProfileState'] = NULL;
        $user['UserProfileCountry'] = NULL;
        $user['UserProfileGender'] = filter_var($request->getParsedBody()['Gender'], FILTER_SANITIZE_STRING);
        $user['UserProfileDOB'] = filter_var($request->getParsedBody()['DOB'], FILTER_SANITIZE_STRING);
        $user['UserProfileDOB'] = $user['UserProfileDOB'] != "null" ? Format::getDOBFromAge($user['UserProfileDOB']) : NULL;
        $currentDate = new DateTime();
        $user['UserProfileRegistrationDate'] = $currentDate->format('Y-m-d H:i:s');
        $user['UserProfilePlatform'] = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);
        $user['UserProfileRefCode'] = filter_var($request->getParsedBody()['RefCode'], FILTER_SANITIZE_STRING);
        $user['UserProfileRefCode2'] = filter_var($request->getParsedBody()['RefCode2'], FILTER_SANITIZE_STRING);
        $user['UserProfilePicture'] = filter_var($request->getParsedBody()['ProfilePicture'], FILTER_SANITIZE_STRING);
        $user['UserProfileMobileNetwork'] = filter_var($request->getParsedBody()['MobileNetwork'], FILTER_SANITIZE_STRING);

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

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    // print_r ( $user );
                    $bind = array(
                        ':UserFacebookId' => $user['UserFacebookId']
                    );
                    if ($db->select('users', 'UserFacebookId = :UserFacebookId', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogInUsingFacebook($Version, $Language, $Platform, $user['UserFacebookId'])));
                    } else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);

                        Format::formatResponseData($user);
                        return General::getResponse($response->write(UserServices::localLogInUsingFacebook($Version, $Language, $Platform, $user['UserFacebookId'])));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_INSERT'))));
                    }
                    break;
                case 'v2':
                case 'V2':
                    // print_r ( $user );
                    $bind = array(
                        ':UserFacebookId' => $user['UserFacebookId']
                    );
                    $bind2 = array(
                        ':UserDeviceId' => $user['UserDeviceId']
                    );
                    if ($db->select('users', 'UserFacebookId = :UserFacebookId', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogInUsingFacebook($Version, $Language, $Platform, $user['UserFacebookId'])));
                    } // else if ($db->select ( 'users', 'UserDeviceId = :UserDeviceId', $bind2 )) {
                    // return General::getResponse ( $response->write ( ErrorObject::getUserErrorObject ( Message::getMessage ( 'E_DEVICE_EXIST' ) ) ) );
                    // }
                    else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);

                        Format::formatResponseData($user);
                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), User::getUserSubscriptionArray($user), Message::getMessage('M_LOGIN_FB'))));
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

    /**
     * To LogIn Using Facebook ID
     *
     * @param Request $request
     * @param Response $response
     */
    public static function localLogInUsingFacebook($Version, $Language, $Platform, $FacebookId)
    {
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $sql = <<<STR
					SELECT *, IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired FROM users
					   
					        INNER JOIN userprofiles
					        ON userprofiles.UserProfileUserId = users.UserId
					   
							INNER JOIN usersubscriptions
					        ON usersubscriptions.UserSubscriptionUserId = users.UserId
				
					        WHERE users.UserFacebookId=:FacebookId
									AND UserSubscriptionIsTempUser=0
STR;
                    $bind = array(
                        ":FacebookId" => $FacebookId
                    );

                    $results = $db->run($sql, $bind);
                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object with Empty User Information
                    if ($results) {
                        Format::formatResponseData($results);
                        // Updating User Last LogIn Time
                        $currentDate = new DateTime();
                        $update = array(
                            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                        );
                        // "UserToken" => General::createGUID()

                        $bind = array(
                            ":UserFacebookId" => $FacebookId
                        );
                        $db->update('users', $update, 'UserFacebookId = :UserFacebookId', $bind);

                        // To Get Object From Array
                        $results = $results[0];
                        // $results ['UserToken'] = $update ['UserToken'];
                        // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';

                        return SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_LOGIN_FB'));
                    } else {
                        return ErrorObject::getUserErrorObject(Message::getMessage('E_WRONG_FB'));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Enabled

                    $sql = <<<STR
					SELECT *, IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired FROM users
					   
					        INNER JOIN userprofiles
					        ON userprofiles.UserProfileUserId = users.UserId
					   
							INNER JOIN usersubscriptions
					        ON usersubscriptions.UserSubscriptionUserId = users.UserId
				
					        WHERE users.UserFacebookId=:FacebookId
									AND UserSubscriptionIsTempUser=0
STR;
                    $bind = array(
                        ":FacebookId" => $FacebookId
                    );

                    $results = $db->run($sql, $bind);
                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object with Empty User Information
                    if ($results) {
                        Format::formatResponseData($results);
                        // Updating User Last LogIn Time
                        $currentDate = new DateTime();
                        $update = array(
                            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                        );
                        // "UserToken" => General::createGUID()

                        $bind = array(
                            ":UserFacebookId" => $FacebookId
                        );
                        $db->update('users', $update, 'UserFacebookId = :UserFacebookId', $bind);

                        // To Get Object From Array
                        $results = $results[0];
                        // $results ['UserToken'] = $update ['UserToken'];
                        // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';

                        return SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_LOGIN_FB_SIGNUP'));
                    } else {
                        return ErrorObject::getUserErrorObject(Message::getMessage('E_WRONG_FB_SIGNUP'));
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

    /**
     * To LogIn Using Facebook ID
     *
     * @param Request $request
     * @param Response $response
     */
    public static function logInUsingFacebook(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $FacebookId = $request->getAttribute('FacebookId');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $sql = <<<STR
					SELECT *, IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired FROM users
							
							INNER JOIN userprofiles
					        ON userprofiles.UserProfileUserId = users.UserId
					   
							INNER JOIN usersubscriptions
					        ON usersubscriptions.UserSubscriptionUserId = users.UserId
				
					        WHERE users.UserFacebookId=:FacebookId
									AND UserSubscriptionIsTempUser=0
STR;
                    $bind = array(
                        ":FacebookId" => $FacebookId
                    );

                    $results = $db->run($sql, $bind);
                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object with Empty User Information
                    if ($results) {
                        Format::formatResponseData($results);
                        // Updating User Last LogIn Time
                        $currentDate = new DateTime();
                        $update = array(
                            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                        );
                        // "UserToken" => General::createGUID()

                        $bind = array(
                            ":UserFacebookId" => $FacebookId
                        );
                        $db->update('users', $update, 'UserFacebookId=:UserFacebookId', $bind);

                        // To Get Object From Array
                        $results = $results[0];
                        // $results ['UserToken'] = $update ['UserToken'];
                        // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';

                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_LOGIN_FB'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_WRONG_FB'))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(array(
                        'In Process.'
                    ))));
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

    /**
     * To LogIn Using Username And Password
     *
     * @param Request $request
     * @param Response $response
     */
    public static function logIn(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $UserUsername = $request->getAttribute('Username');
        $UserPassword = $request->getAttribute('Password');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $bind = array(
                        ":Username" => $UserUsername
                    );

                    $results = $db->select('users', 'UserUsername=:Username', $bind);
                    // If Result is Returned then Verify Password
                    // Else Return Error Message Object
                    if ($results) {

                        $sql = <<<STR
						SELECT *, IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired FROM users
						
						        INNER JOIN userprofiles
						        ON userprofiles.UserProfileUserId = users.UserId
						
								INNER JOIN usersubscriptions
						        ON usersubscriptions.UserSubscriptionUserId = users.UserId
						
						        WHERE UserUsername=:Username
			                        AND UserPassword=:Password
			                        AND UserSubscriptionIsTempUser=0
STR;
                        // Password Encryption to Match Stored Password
                        // $salt = $results [0] ['UserSalt'];
                        // $saltedPW = $UserPassword . $salt;
                        // $hashedPW = hash ( 'sha256', $saltedPW );

                        $bind = array(
                            ":Username" => $UserUsername,
                            ":Password" => md5($UserPassword)
                        );
                        // print_r ( $bind );
                        $results = $db->run($sql, $bind);

                        // If Result is Returned then Return User Information
                        // Else Return Error Message Object
                        if ($results) {
                            Format::formatResponseData($results);
                            // Updating User Last LogIn Time
                            $currentDate = new DateTime();
                            $update = array(
                                "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                            );
                            // "UserToken" => General::createGUID()

                            $bind = array(
                                ":Username" => $UserUsername
                            );
                            $db->update('users', $update, 'UserUsername=:Username', $bind);

                            // To Get Object From Array
                            $results = $results[0];
                            // $results ['UserToken'] = $update ['UserToken'];
                            // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';

                            return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_LOGIN'))));
                        } else {
                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_WRONG_PASS'))));
                        }
                    } else {
                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_LOGIN'))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(array(
                        'In Process.'
                    ))));
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

    public static function logInPOST(Request $request, Response $response)
    {
        $Version = filter_var(isset($request->getParsedBody()['Version']) ? $request->getParsedBody()['Version'] : NULL, FILTER_SANITIZE_STRING);
        $Language = filter_var(isset($request->getParsedBody()['Language']) ? $request->getParsedBody()['Language'] : NULL, FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : NULL, FILTER_SANITIZE_STRING);
        $UserUsername = filter_var(isset($request->getParsedBody()['Username']) ? $request->getParsedBody()['Username'] : NULL, FILTER_SANITIZE_STRING);
        $UserPassword = filter_var(isset($request->getParsedBody()['Password']) ? $request->getParsedBody()['Password'] : NULL, FILTER_SANITIZE_STRING);

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $bind = array(
                        ":Username" => $UserUsername
                    );

                    $results = $db->select('users', 'UserUsername=:Username', $bind);
                    // If Result is Returned then Verify Password
                    // Else Return Error Message Object
                    if ($results) {

                        $sql = <<<STR
						SELECT *, IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired FROM users
						
						        INNER JOIN userprofiles
						        ON userprofiles.UserProfileUserId = users.UserId
						        
								INNER JOIN usersubscriptions
						        ON usersubscriptions.UserSubscriptionUserId = users.UserId
						        
						        WHERE UserUsername=:Username
			                        AND UserPassword=:Password
			                        AND UserSubscriptionIsTempUser=0
STR;
                        // Password Encryption to Match Stored Password
                        // $salt = $results [0] ['UserSalt'];
                        // $saltedPW = $UserPassword . $salt;
                        // $hashedPW = hash ( 'sha256', $saltedPW );

                        $bind = array(
                            ":Username" => $UserUsername,
                            ":Password" => md5($UserPassword)
                        );
                        // print_r ( $bind );
                        $results = $db->run($sql, $bind);

                        // If Result is Returned then Return User Information
                        // Else Return Error Message Object
                        if ($results) {
                            Format::formatResponseData($results);
                            // Updating User Last LogIn Time
                            $currentDate = new DateTime();
                            $update = array(
                                "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                            );
                            // "UserToken" => General::createGUID()

                            $bind = array(
                                ":Username" => $UserUsername
                            );
                            $db->update('users', $update, 'UserUsername=:Username', $bind);

                            // To Get Object From Array
                            $results = $results[0];
                            // $results ['UserToken'] = $update ['UserToken'];
                            // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';

                            return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_LOGIN'))));
                        } else {
                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_WRONG_PASS'))));
                        }
                    } else {
                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_LOGIN'))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(array(
                        'In Process.'
                    ))));
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

    public static function validateUser(Request $request, Response $response)
    {
        $Version = filter_var($request->getAttribute('Version'), FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getAttribute('Language'), FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getAttribute('Platform'), FILTER_SANITIZE_STRING);
        $UserUsername = filter_var($request->getParsedBody()['username'], FILTER_SANITIZE_STRING);
        $UserPassword = filter_var($request->getParsedBody()['token'], FILTER_SANITIZE_STRING);

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':

                    $sql = <<<STR
					SELECT * FROM users
		
					        INNER JOIN userprofiles
					        ON userprofiles.UserProfileUserId = users.UserId
	
							INNER JOIN usersubscriptions
					        ON usersubscriptions.UserSubscriptionUserId = users.UserId
	
					        WHERE UserUsername=:Username
		                        AND UserSubscriptionIsTempUser=0
STR;

                    $bind = array(
                        ":Username" => $UserUsername
                    );
                    $results = $db->run($sql, $bind);

                    if ($results) {
                        return General::getResponse($response->write('true'));
                    } else {
                        $sql = <<<STR
						SELECT * FROM users
							
						        INNER JOIN userprofiles
						        ON userprofiles.UserProfileUserId = users.UserId
							
								INNER JOIN usersubscriptions
						        ON usersubscriptions.UserSubscriptionUserId = users.UserId
							
						        WHERE UserChatId=:UserChatId
			                        AND UserSubscriptionIsTempUser=0
STR;

                        $bind = array(
                            ":UserChatId" => $UserUsername
                        );
                        $results = $db->run($sql, $bind);

                        if ($results) {
                            return General::getResponse($response->write('true'));
                        } else {
                            return General::getResponse($response->write('false'));
                        }
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

    public static function getUserSubscription(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $UserId = $request->getAttribute('UserId');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $bind = array(
                        ":UserSubscriptionUserId" => $UserId
                    );

                    $results = $db->select('usersubscriptions', 'UserSubscriptionUserId=:UserSubscriptionUserId AND UserSubscriptionIsTempUser=0', $bind);
                    // If Result is Returned then Verify Password
                    // Else Return Error Message Object
                    if ($results) {
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getUserSubscriptionSuccessObject($results[0], Message::getMessage('M_DATA'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getUserSubscriptionErrorObject(Message::getMessage('W_NO_CONTENT'))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getUserSubscriptionErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getUserSubscriptionErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserSubscriptionErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Handle Forget Password Request Using Email
     *
     * @param Request $request
     * @param Response $response
     */
    public static function forgetPasswordEmail(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $Username = $request->getAttribute('Username');

        $tempPassword = General::createNewToken();

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $bind = array(
                        ":UserUsername" => $Username
                    );
                    $results = $db->select("users", "UserUsername=:UserUsername", $bind);

                    if ($results) {
                        $mail = new PHPMailer();

                        // $mail->SMTPDebug = 3; // Enable verbose debug output
                        $mail->isSMTP(); // Set mailer to use SMTP
                        $mail->Host = 'smtpout.secureserver.net'; // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true; // Enable SMTP authentication
                        $mail->Username = 'support@pitelevision.com'; // SMTP username
                        $mail->Password = 'sup&3450'; // SMTP password
                        // $mail->SMTPSecure = 'tls'; // Enable TLS encryption, ssl also accepted
                        $mail->Port = 25; // TCP port to connect to

                        $mail->From = 'support@pitelevision.com';
                        $mail->FromName = 'PI Television Support';
                        $mail->addAddress($results[0]['UserEmail']);
                        // Add a recipient //ccbl2@pk.wi-tribe.com
                        // $mail->addAddress('ellen@example.com'); // Name is optional
                        // $mail->addReplyTo('info@example.com', 'Information');
                        // $mail->addCC('jp@pipakistan.com');
                        // $mail->addCC('yassir.pasha@gmail.com');
                        // $mail->addCC ( 'saifuddin.ba@gmail.com' );
                        // $mail->addCC('support@pitelevision.com');

                        // $mail->addBCC('bcc@example.com');

                        // $mail->addAttachment('/var/tmp/file.tar.gz'); // Add attachments
                        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // Optional name
                        $mail->isHTML(true); // Set email format to HTML

                        $mail->Subject = 'Tapmad TV Password for ' . $results[0]['UserUsername'];
                        $mail->Body = "<b>Dear Customer,</b><br/><br/>
						Your temporary Tapmad TV password is <b>$tempPassword</b><br/><br/>
						<b>Thank You</b><br/>
						PI Televison Network Support";
                        $mail->AltBody = "<b>Dear Customer,</b><br/><br/>
						Your temporary Tapmad TV password is <b>$tempPassword</b><br/><br/>
						<b>Thank You</b><br/>
						PI Televison Network Support";

                        if (! $mail->send()) {
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_MAIL_NOTSENT'))));
                        }
                        $update = array(
                            "UserPassword" => md5($tempPassword)
                        );
                        $bind = array(
                            ":UserUsername" => $Username
                        );
                        $db->update("users", $update, "UserUsername =:UserUsername", $bind);
                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_MAIL_SENT'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_USER'))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Handle Forget Password Request Using Mobile
     *
     * @param Request $request
     * @param Response $response
     */
    public static function forgetPasswordMobile(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $MobileNo = $request->getAttribute('MobileNo');

        $TempPassword = General::createNumericToken(9);

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    $Sql = <<<STR
					SELECT userprofiles.UserProfileMobile
						FROM userprofiles
						INNER JOIN users ON users.UserId = userprofiles.UserProfileUserId
				
					WHERE users.UserUsername=:UserUsername
STR;
                    $Bind = array(
                        ":UserUsername" => $MobileNo
                    );
                    $Results = $db->run($Sql, $Bind);

                    if ($Results) {
                        /*
                         * $URL = 'http://cbs.zong.com.pk/reachcwsv2/corporatesms.svc?wsdl';
                         * $Client = new SoapClient ( $URL, array (
                         * 'trace' => 1,
                         * 'exception' => 0
                         * ) );
                         *
                         * $Result = $Client->GetAccountSummary ( array (
                         * 'obj_GetAccountSummary' => array (
                         * 'loginId' => '923161123285',
                         * //'loginPassword' => 'hjg456t28'
                         * 'loginPassword' => '123'
                         * )
                         * ) );
                         *
                         * $Result = $Client->QuickSMS ( array (
                         * 'obj_QuickSMS' => array (
                         * 'loginId' => '923161123285',
                         * 'loginPassword' => '123',
                         * 'Destination' => '92' . ltrim ( $Results [0] ['UserUsername'], '0' ),
                         * 'Mask' => 'tapmad TV',
                         * 'Message' => "Dear User,\n\nYour temporary password for tapmad TV is : " . $TempPassword . ".\n\ntapmad TV",
                         * 'UniCode' => '0',
                         * 'ShortCodePrefered' => 'n'
                         * )
                         * ) );
                         */
                        // Your Account SID and Auth Token from twilio.com/console
                        $sid = 'AC5a629f87dce71f0d1c7323f801fcf749';
                        $token = 'c5b22451e785535e89e71a68eedb4a4e';
                        $client = new Client($sid, $token);

                        // Use the client to do fun stuff like send text messages!
                        $Result = new stdClass();
                        try {
                            $client->messages->create(
                            // the number you'd like to send the message to
                                '+92' . ltrim($Results[0]['UserProfileMobile'], '0'), [
                                // the body of the text message you'd like to send
                                "body" => "Dear User,\n\nYour temporary password for tapmad TV is : " . $TempPassword . ".\n\ntapmad TV",
                                // A Twilio phone number you purchased at twilio.com/console
                                "from" => '+12314361240'
                            ]);
                            // On US phone numbers, you could send an image as well!
                            // 'mediaUrl' => $imageUrl

                            $Result->QuickSMSResult = 'Message sent to ' . $Results[0]['UserProfileMobile'];
                        } catch (TwilioException $e) {
                            $Result->QuickSMSResult = 'Could not send SMS notification.' . ' Twilio replied with: ' . $e;
                        }

                        $update = array(
                            "UserPassword" => md5($TempPassword)
                        );
                        $bind = array(
                            ":UserUsername" => $MobileNo
                        );
                        $db->update("users", $update, "UserUsername =:UserUsername", $bind);
                        // echo '<pre>';
                        // print_r ( $Result );
                        if (strpos($Result->QuickSMSResult, 'Message sent to') !== false) {
                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getSMSSuccessMessage($Result))));
                        } else {
                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getSMSErrorMessage($Result))));
                        }
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_USER'))));
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Reset Password Request
     *
     * @param Request $request
     * @param Response $response
     */
    public static function resetPassword(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $Array['UserUsername'] = $request->getAttribute('MobileNo');
        $Array['CurrentPassword'] = $request->getAttribute('CurrentPassword');
        $Array['NewPassword'] = $request->getAttribute('NewPassword');
        $Array['ConfirmPassword'] = $request->getAttribute('ConfirmPassword');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $bind = array(
                        ":UserUsername" => $Array['UserUsername'],
                        ":Password" => md5($Array['CurrentPassword'])
                    );

                    // Check Old Password Provided By User Matches The Password Stored In Database
                    $results = $db->select('users', 'UserUsername=:UserUsername AND UserPassword=:Password', $bind);
                    if ($results) {
                        if ($Array['CurrentPassword'] != $Array['NewPassword']) {
                            if ($Array['NewPassword'] === $Array['ConfirmPassword']) {
                                $update = array(
                                    "UserPassword" => md5($Array['NewPassword'])
                                );
                                $bind = array(
                                    ":UserUsername" => $Array['UserUsername']
                                );
                                $db->update("users", $update, "UserUsername=:UserUsername", $bind);
                                return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_PASS_CHANGED'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_PASS_MISMATCH'))));
                            }
                        } else {
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_SAME_PASS'))));
                        }
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_WRONG_CURRENT_PASS'))));
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Send Activate Code To User
     *
     * @param Request $request
     * @param Response $response
     */
    public static function sendActivationCodeMobile(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $Array['Mobile'] = $request->getAttribute('Mobile');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                        case 'Web':
                        case 'web':
                        case 'WEB':
                        case 'Tv':
                        case 'tv':
                        case 'TV':
                        case 'Ios':
                        case 'ios':
                        case 'IOS':
                            $bind = array(
                                ":UserUsername" => $Array['Mobile']
                            );

                            $Results = $db->select('users', 'UserUsername=:UserUsername AND UserIsActive=0', $bind);

                            if ($Results) {
                                // Activation Code $results [0] ['UserActivationCode'])

                                // Your Account SID and Auth Token from twilio.com/console
                                $sid = 'AC5a629f87dce71f0d1c7323f801fcf749';
                                $token = 'c5b22451e785535e89e71a68eedb4a4e';
                                $client = new Client($sid, $token);

                                // Use the client to do fun stuff like send text messages!
                                try {
                                    $client->messages->create(
                                    // the number you'd like to send the message to
                                        '+92' . ltrim($Results[0]['UserUsername'], '0'), [
                                        // the body of the text message you'd like to send
                                        "body" => "Dear User,\n\nYou've successfully registered for tapmad TV. Your verification code is : " . $Results[0]['UserActivationCode'] . ".\n\ntapmad TV",
                                        // A Twilio phone number you purchased at twilio.com/console
                                        "from" => '+12314361240'
                                    ]);
                                    // On US phone numbers, you could send an image as well!
                                    // 'mediaUrl' => $imageUrl

                                    $Result = new stdClass();
                                    $Result->QuickSMSResult = 'Message sent to ' . $Results[0]['UserUsername'];
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getSMSSuccessMessage($Result))));
                                } catch (TwilioException $e) {
                                    $Result = new stdClass();
                                    $Result->QuickSMSResult = 'Could not send SMS notification.' . ' Twilio replied with: ' . $e;
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getSMSErrorMessage($Result))));
                                }

                                /*
                                 * $URL = 'http://cbs.zong.com.pk/reachcwsv2/corporatesms.svc?wsdl';
                                 * $Client = new SoapClient ( $URL, array (
                                 * 'trace' => 1,
                                 * 'exception' => 0
                                 * ) );
                                 *
                                 * $Result = $Client->GetAccountSummary ( array (
                                 * 'obj_GetAccountSummary' => array (
                                 * 'loginId' => '923161123285',
                                 * //'loginPassword' => 'hjg456t28'
                                 * 'loginPassword' => '123'
                                 * )
                                 * ) );
                                 *
                                 * $Result = $Client->QuickSMS ( array (
                                 * 'obj_QuickSMS' => array (
                                 * 'loginId' => '923161123285',
                                 * 'loginPassword' => '123',
                                 * 'Destination' => '92'. ltrim($Results [0]['UserUsername'], '0'),
                                 * 'Mask' => 'tapmad TV',
                                 * 'Message' => "Dear User,\n\nYou've successfully registered for tapmad TV. Your verification code is : " .$Results [0] ['UserActivationCode']. ".\n\ntapmad TV",
                                 * 'UniCode' => '0',
                                 * 'ShortCodePrefered' => 'n'
                                 * )
                                 * ) );
                                 */
                                // echo '<pre>';
                                // print_r ( $Result );
                                /*
                                 * if (strpos ( $Result->QuickSMSResult, 'Submitted Successfully' ) !== false) {
                                 * return General::getResponse ( $response->write ( SuccessObject::getSuccessObject ( Message::getSMSSuccessMessage ( $Result ) ) ) );
                                 * } else {
                                 * return General::getResponse ( $response->write ( SuccessObject::getSuccessObject ( Message::getSMSErrorMessage ( $Result ) ) ) );
                                 * }
                                 */
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_ALREADY_ACTIVATED'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    public static function localSendActivationCodeMobile($Version, $Language, $Platform, $MobileNo)
    {
        $Array['Mobile'] = $MobileNo;

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                        case 'Web':
                        case 'web':
                        case 'WEB':
                        case 'Tv':
                        case 'tv':
                        case 'TV':
                        case 'Ios':
                        case 'ios':
                        case 'IOS':
                            $bind = array(
                                ":UserUsername" => $Array['Mobile']
                            );

                            $Results = $db->select('users', 'UserUsername=:UserUsername AND UserIsActive=0', $bind);

                            if ($Results) {
                                // Activation Code $results [0] ['UserActivationCode'])

                                // Your Account SID and Auth Token from twilio.com/console
                                $sid = 'AC5a629f87dce71f0d1c7323f801fcf749';
                                $token = 'c5b22451e785535e89e71a68eedb4a4e';
                                $client = new Client($sid, $token);

                                // Use the client to do fun stuff like send text messages!
                                try {
                                    $client->messages->create(
                                    // the number you'd like to send the message to
                                        '+92' . ltrim($Results[0]['UserUsername'], '0'), [
                                        // the body of the text message you'd like to send
                                        "body" => "Dear User,\n\nYou've successfully registered for tapmad TV. Your verification code is : " . $Results[0]['UserActivationCode'] . ".\n\ntapmad TV",
                                        // A Twilio phone number you purchased at twilio.com/console
                                        "from" => '+12314361240'
                                    ]);
                                    // On US phone numbers, you could send an image as well!
                                    // 'mediaUrl' => $imageUrl
                                } catch (TwilioException $e) {}

                                /*
                                 * $URL = 'http://cbs.zong.com.pk/reachcwsv2/corporatesms.svc?wsdl';
                                 * $Client = new SoapClient ( $URL, array (
                                 * 'trace' => 1,
                                 * 'exception' => 0
                                 * ) );
                                 *
                                 * $Result = $Client->GetAccountSummary ( array (
                                 * 'obj_GetAccountSummary' => array (
                                 * 'loginId' => '923161123285',
                                 * //'loginPassword' => 'hjg456t28'
                                 * 'loginPassword' => '123'
                                 * )
                                 * ) );
                                 *
                                 * $Result = $Client->QuickSMS ( array (
                                 * 'obj_QuickSMS' => array (
                                 * 'loginId' => '923161123285',
                                 * 'loginPassword' => '123',
                                 * 'Destination' => '92'. ltrim($Results [0]['UserUsername'], '0'),
                                 * 'Mask' => 'tapmad TV',
                                 * 'Message' => "Dear User,\n\nYou've successfully registered for tapmad TV. Your verification code is : " .$Results [0] ['UserActivationCode']. ".\n\ntapmad TV",
                                 * 'UniCode' => '0',
                                 * 'ShortCodePrefered' => 'n'
                                 * )
                                 * ) );
                                 */
                                // echo '<pre>';
                                // print_r ( $Result );
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    /**
     * Function to Activate User Account
     *
     * @param Request $request
     * @param Response $response
     */
    public static function activateUserAccount(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $Array['Mobile'] = $request->getAttribute('Mobile');
        $Array['ActivationCode'] = $request->getAttribute('ActivationCode');

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                        case 'Web':
                        case 'web':
                        case 'WEB':
                        case 'Tv':
                        case 'tv':
                        case 'TV':
                            $bind = array(
                                ":UserUsername" => $Array['Mobile']
                            );

                            $results = $db->select('users', 'UserUsername=:UserUsername AND UserIsActive=0', $bind);

                            if ($results) {
                                if ($Array['ActivationCode'] === $results[0]['UserActivationCode']) {
                                    $currentDate = new DateTime();
                                    $expiryDate = new DateTime();
                                    $expiryDate = $expiryDate->modify('+30 day');
                                    $update = array(
                                        "UserIsActive" => 1,
                                        "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s'),
                                        "UserToken" => General::createNewToken(24)
                                    );
                                    $bind = array(
                                        ":UserUsername" => $Array['Mobile']
                                    );
                                    $db->update("users", $update, "UserUsername=:UserUsername", $bind);

                                    $update = array(
                                        "UserSubscriptionPackageId" => 10,
                                        "UserSubscriptionStartDate" => $currentDate->format('Y-m-d H:i:s'),
                                        "UserSubscriptionExpiryDate" => $expiryDate->format('Y-m-d H:i:s'),
                                        "UserSubscriptionMaxConcurrentConnections" => 3,
                                        "UserSubscriptionDetails" => NULL
                                    );
                                    $bind = array(
                                        ":UserSubscriptionUserId" => $results[0]['UserId'],
                                        ":UserSubscriptionIsTempUser" => 0
                                    );
                                    $db->update("usersubscriptions", $update, "UserSubscriptionUserId=:UserSubscriptionUserId AND UserSubscriptionIsTempUser=:UserSubscriptionIsTempUser", $bind);

                                    $sql = <<<STR
			                        SELECT users.UserId,
					                        users.UserUsername,
					                        users.UserPassword,
					                        users.UserEmail,
					                        users.UserFacebookId,
			                                users.UserToken,
			                                users.UserIsFree,
			                                users.UserIsActive,
			                                users.UserIsPublisher,
			                                users.UserNetwork,
					                        users.UserLastLoginAt,
			                                users.UserCountryCode,
			                                users.UserIPAddress,
			                                users.UserTypeId,
			                                users.UserIsPassChanged,
	
					                        userprofiles.UserProfileFirstName,
			                                userprofiles.UserProfileLastName,
			                                userprofiles.UserProfileMobile,
			                                userprofiles.UserProfileCity,
			                                userprofiles.UserProfileState,
			                                userprofiles.UserProfileCountry,
			                                userprofiles.UserProfileGender,
			                                userprofiles.UserProfileDOB,
			                                userprofiles.UserProfileRegistrationDate,
			                                userprofiles.UserProfilePlatform,
			                                userprofiles.UserProfileRefCode,
	
					                        usersubscriptions.UserSubscriptionIsTempUser,
                                            usersubscriptions.UserSubscriptionPackageId,
			                                usersubscriptions.UserSubscriptionStartDate,
			                                usersubscriptions.UserSubscriptionExpiryDate,
			                                usersubscriptions.UserSubscriptionMaxConcurrentConnections,
			                                usersubscriptions.UserSubscriptionAutoRenew,
			                                usersubscriptions.UserSubscriptionDetails
			
					                        FROM users
			
			                                INNER JOIN userprofiles
			                                ON userprofiles.UserProfileUserId = users.UserId
			
					                        INNER JOIN usersubscriptions
			                                ON usersubscriptions.UserSubscriptionUserId = users.UserId
			
			                                WHERE UserUsername=:Username
                                                AND UserSubscriptionIsTempUser=:UserSubscriptionIsTempUser
STR;

                                    $bind = array(
                                        ":Username" => $Array['Mobile'],
                                        ":UserSubscriptionIsTempUser" => 0
                                    );
                                    // print_r ( $bind );
                                    $results = $db->run($sql, $bind);

                                    Format::formatResponseData($results);

                                    return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results[0]), User::getUserProfileArray($results[0]), User::getUserSubscriptionArray($results[0]), Message::getMessage('M_ACOUNT_ACTIVATED'))));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_CODE_MISMATCH'))));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_ALREADY_ACTIVATED'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }
    /**
     * Function to sendOTP User Account
     *
     * @param Request $request
     * @param Response $response
     */
    public static function sendOTP(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $Mobile_No = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
        $MobileNo = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
        $MobileNo = ltrim($MobileNo, '0');
        $MobileNo = ltrim($MobileNo, '+92');
        $MobileNo = ltrim($MobileNo, '0092');
        $MobileNoValidator = v::Digit()->noWhitespace()->length(10, 10);

        if($MobileNo!=null && $MobileNo!='') {
            if (Format::mobileNOformat($Mobile_No) === 1) {
                if ($MobileNoValidator->validate($MobileNo)) {
                    try {
                        $db = parent::getDataBase();
                        switch ($Version) {
                            case 'v1':
                            case 'V1': 
                                /*$Sql = <<<STR
                                SELECT count(UserOtpMobileNo) as otpcount FROM `userotp` WHERE UserOtpMobileNo=:UserOtpMobileNo AND UserOtpAddedDate>= DATE_SUB(NOW(),INTERVAL 4 MINUTE);
STR;
                                $Bind = array(
                                    ":UserOtpMobileNo" => trim($MobileNo)
                                );
                                $Results = $db->run($Sql, $Bind);
                                if (!empty($Results)) {
                                    if ($Results[0]['otpcount'] > 2) {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_LIMIT_REACHED'))));
                                    } else {*/
                                        $digits = 4;
                                        $randCode = str_pad(rand(0, pow(10, $digits) - 1), $digits, '1', STR_PAD_LEFT);
                                        $insert = array(
                                            "UserOtpMobileNo" => trim($MobileNo),
                                            "UserOtpCode" => $randCode
                                        );
                                        $uSql = <<<STR
                                        update userotp set `UserOtpCodeIsVerified`=1 WHERE `UserOtpMobileNo`=:UserOtpMobileNo ORDER by `UserOtpId` DESC LIMIT 1;
STR;
                                        $Bind = array(
                                            ":UserOtpMobileNo" => trim($MobileNo)
                                        );
                                        $Results = $db->run($uSql, $Bind);
                                        if (User::insertuserOTP($db, $insert) == 1) {
                                            $url = "https://global.solutionsinfini.com/api/v4/?api_key=Ad3a9e97d2e42e148db285f09c8225dfe&method=sms&message=Tapmad TV Code : $randCode.  is link ko click kar ke OTP code ko Tasdeeq karen: http://www.tapmad.com/$MobileNo/$randCode &to=92" . trim($MobileNo) . "&sender=TapmadTV";
                                            $headers = array(
                                                'Accept: application/json',
                                                'Content-Type: application/json',
                                            );
                                            try {
                                                $r = Curl::Get($url, $headers);
                                                $someArray = json_decode($r, true);
                                                //print_r($someArray);
                                                if($someArray['status']==='H601'){
                                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('E_OTP_CODE_MESSAGE_NOT_SEND'))));
                                                }else{
                                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_OTP_SEND'))));
                                                }
                                            } catch (Exception $e) {
                                                echo 'Could not send SMS notification.' . ' replied with: ' . $e;
                                            }
                                        } else {
                                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_INSERT'))));
                                        }
                                    /*}
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_RECORD_NOT_FOUND'))));
                                }*/
                                break;
                            case 'v2':
                            case 'V2':
                                return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                                    'In Process.'
                                ))));
                                break;
                            default:
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                                break;
                        }
                    } catch (PDOException $e) {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
                    } finally {
                        $db = null;
                    }
                } else {
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_MOBILE_NUMBER'))));
                }
            }else{
                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_MOBILE_NUMBER'))));
            }
        }
        else{
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_MISSING_PARAMETER'))));
        }
    }

    /**
     * Function to verifyOTP User Account
     *
     * @param Request $request
     * @param Response $response
     */
    public static function verifyOTP(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $Mobile_No = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
        $MobileNo = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
        $MobileNo = ltrim($MobileNo, '0');
        $MobileNo = ltrim($MobileNo, '+92');
        $MobileNo = ltrim($MobileNo, '0092');
        $MobileNoValidator = v::Digit()->noWhitespace()->length(10, 10);
        $otpCodeValidator = v::Digit()->noWhitespace()->length(4, 4);
        $otpCode = filter_var($request->getParsedBody()['otpCode'], FILTER_SANITIZE_STRING);

        if($MobileNo!=null && $MobileNo!=''){
            if (Format::mobileNOformat($Mobile_No) == 1) {
                if($otpCode!=null && $otpCode!=''){
                    if ($MobileNoValidator->validate($MobileNo)) {
                        if ($otpCodeValidator->validate($otpCode)) {
                            try {
                                $db = parent::getDataBase();
                                switch ($Version) {
                                    case 'v1':
                                    case 'V1':
                                        $Sql = <<<STR
                                        SELECT * FROM `userotp` WHERE UserOtpMobileNo=:UserOtpMobileNo ORDER BY UserOtpId DESC LIMIT 1;
STR;
                                        $Bind = array(
                                            ":UserOtpMobileNo" => trim($MobileNo)
                                        );
                                        $Results = $db->run($Sql, $Bind);
                                        //print_r($Results);die();
                                        if (!empty($Results)) {
                                            $previousMinute = explode(':', $Results[0]['UserOtpAddedDate']);
                                            $newtime=date("i")-$previousMinute[1];
                                            //echo date("i") .' '.$previousMinute[1];die();
                                            if($newtime>4)
                                            {
                                                $Sql = <<<STR
                                                update userotp set `UserOtpCodeIsVerified`=1 WHERE `UserOtpMobileNo`=:UserOtpMobileNo ORDER by `UserOtpId` DESC LIMIT 1;
STR;
                                                $Bind = array(
                                                    ":UserOtpMobileNo" => trim($MobileNo)
                                                );
                                                $Results = $db->run($Sql, $Bind);
                                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_OTP_EXPIRE_AFTER_TWO_MINUTES'))));
                                            }else{
                                                $Sql = <<<STR
                                                SELECT UserOtpCodeIsVerified, UserOtpHits FROM `userotp` WHERE UserOtpMobileNo=:UserOtpMobileNo AND UserOtpCode=:otpCode AND UserOtpAddedDate>= DATE_SUB(NOW(),INTERVAL 4 MINUTE) ORDER BY UserOtpId DESC LIMIT 1;
STR;
                                                $Bind = array(
                                                    ":UserOtpMobileNo" => trim($MobileNo),
                                                    ":otpCode" => trim($otpCode)
                                                );
                                                $Results = $db->run($Sql, $Bind);
                                                //print_r($Results);die();
                                                if (!empty($Results)) {
                                                    if ($Results[0]['UserOtpCodeIsVerified'] == 1) {
                                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_OTP_EXPIRE'))));
                                                    } else if ($Results[0]['UserOtpHits'] > 2) {
                                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_LIMIT_REACHED'))));
                                                    } else {
                                                        $Sql = <<<STR
                                                    update userotp set `UserOtpCodeIsVerified`=1 WHERE `UserOtpMobileNo`=:UserOtpMobileNo AND UserOtpCode=:otpCode ORDER by `UserOtpId` DESC LIMIT 1;
STR;
                                                        $Bind = array(
                                                            ":UserOtpMobileNo" => trim($MobileNo),
                                                            ":otpCode" => trim($otpCode)
                                                        );
                                                        $Results = $db->run($Sql, $Bind);
                                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('M_OTP_VERIFIED'))));
                                                    }
                                                }else {
                                                    $Sql = <<<STR
                                                    SELECT UserOtpCodeIsVerified, UserOtpHits FROM `userotp` WHERE UserOtpMobileNo=:UserOtpMobileNo ORDER BY UserOtpId DESC LIMIT 1;
STR;
                                                    $Bind = array(
                                                        ":UserOtpMobileNo" => trim($MobileNo)
                                                    );
                                                    $Results = $db->run($Sql, $Bind);
                                                    //print_r($Results);die();
                                                    if (!empty($Results)) {
                                                        if ($Results[0]['UserOtpHits'] > 2) {
                                                            $Sql = <<<STR
                                                            update userotp set `UserOtpCodeIsVerified`=1 WHERE `UserOtpMobileNo`=:UserOtpMobileNo ORDER by `UserOtpId` DESC LIMIT 1;
STR;
                                                            $Bind = array(
                                                                ":UserOtpMobileNo" => trim($MobileNo)
                                                            );
                                                            $Results = $db->run($Sql, $Bind);
                                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_LIMIT_REACHED'))));
                                                        } else {
                                                            $Sql = <<<STR
                                                            update userotp set `UserOtpHits`=`UserOtpHits`+1 WHERE `UserOtpMobileNo`=:UserOtpMobileNo ORDER by `UserOtpId` DESC LIMIT 1;
STR;
                                                            $Bind = array(
                                                                ":UserOtpMobileNo" => trim($MobileNo)
                                                            );
                                                            $Results = $db->run($Sql, $Bind);
                                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_CODE_MISMATCH'))));
                                                        }
                                                    }else{
                                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_OTP_EXPIRE'))));
                                                    }
                                                }
                                            }
                                        }else{
                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INCORRECT_OR_MISSMATCHED_MOBILE_NO'))));
                                        }
                                    case 'v2':
                                    case 'V2':
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                                            'In Process.'
                                        ))));
                                        break;
                                    default:
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                                        break;
                                }
                            } catch (PDOException $e) {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
                            } finally {
                                $db = null;
                            }
                        }else{
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_OTP_CODE'))));
                        }
                    }else{
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_MOBILE_NUMBER'))));
                    }
                }else{
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_MISSING_PARAMETER'))));
                }
            }else{
                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_MOBILE_NUMBER'))));
            }
        }else{
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_MISSING_PARAMETER'))));
        }
    }
	
	
	
	public static function getAllUsersByUserId(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        // Users Table Data
        $UserId = filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING);

        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    $sql = <<<STR
						SELECT users.UserId,users.UserUsername,IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
								FROM users
			
			                    INNER JOIN userprofiles
			                    ON userprofiles.UserProfileUserId = users.UserId
			
					            INNER JOIN usersubscriptions
			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
			
			                    WHERE UserId=:UserId
                                    AND UserSubscriptionIsTempUser=0
STR;
                    // Password Encryption to Match Stored Password
                    // $salt = $results [0] ['UserSalt'];
                    // $saltedPW = $UserPassword . $salt;
                    // $hashedPW = hash ( 'sha256', $saltedPW );

                    $bind = array(
                        ':UserId' => $UserId
                    );
                    // print_r ( $bind );
                    $results = $db->run($sql, $bind);

                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object
                    if ($results) {
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($results, Message::getMessage('M_DATA'), NULL, NULL, 'Users')));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('W_NO_CONTENT'))));
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
	
	
	public static function signUpORSignInUsingMobileNo1(Request $request, Response $response)
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
        $user['UserProfileFullName'] = NULL;
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
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    // print_r ( $user );
                    $bind = array(
                        ':Username' => $user['UserUsername']
                    );
                    if ($db->select('users', 'UserUsername = :Username', $bind)) {
                        return General::getResponse($response->write(UserServices::localLogInUsingMobileNo1($Version, $Language, $Platform, $user['UserUsername'])));
                    } else if (User::insertUserData($db, $user) > 0) {
                        User::insertUserProfileData($db, $user);
                        User::insertUserSubscriptionData($db, $user);
						

                        $users[0] = $user;
                        Format::formatResponseData($users);
                        $user = $users[0];
						UserServices::addSubscriptionPackage($user['UserId']);
						// UserServices::localSendActivationCodeMobile ( $Version, $Language, $Platform, $user ['UserUsername'] );
                        return General::getResponse($response->write(SuccessObject::getUserPackagesSuccessObject(User::getUserArray($user), User::getUserProfileArray($user), User::getUserSubscriptionArray($user), User::getUserPackagesArray($user),Message::getMessage('M_INSERT'))));
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
	
	
	
	public static function localLogInUsingMobileNo1($Version, $Language, $Platform, $UserUsername)
    {
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled

                    $sql = <<<STR
			            SELECT *,IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
								FROM users
								
			                    INNER JOIN userprofiles
			                    ON userprofiles.UserProfileUserId = users.UserId
			                    
					            INNER JOIN usersubscriptions
			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
			                    
			                    WHERE UserUsername=:Username
                                    AND UserSubscriptionIsTempUser=0
STR;
                    // Password Encryption to Match Stored Password
                    // $salt = $results [0] ['UserSalt'];
                    // $saltedPW = $UserPassword . $salt;
                    // $hashedPW = hash ( 'sha256', $saltedPW );

                    $bind = array(
                        ":Username" => $UserUsername
                    );
                    // print_r ( $bind );
                    $results = $db->run($sql, $bind);

                    // If Result is Returned then Return User Information
                    // Else Return Error Message Object
                    if ($results) {
                        Format::formatResponseData($results);
                        // Updating User Last LogIn Time
                        $currentDate = new DateTime();
                        $update = array(
                            "UserLastLoginAt" => $currentDate->format('Y-m-d H:i:s')
                        );
                        // "UserToken" => General::createGUID()

                        $bind = array(
                            ":Username" => $UserUsername
                        );
                        $db->update('users', $update, 'UserUsername=:Username', $bind);

                        // To Get Object From Array
                        $results = $results[0];
                        // $results ['UserToken'] = $update ['UserToken'];
                        // $results ['UserToken'] = 'a6f452ec3293d7fb72c5b677257b20ectmp';
						$userPackage = UserServices::getUserPackagesArray($results);
						Format::formatResponseData($userPackage);	
						$userSubscriptions =UserServices::getUserPackageSubscription($results);
						Format::formatResponseData($userSubscriptions);	
						if($userSubscriptions){
						return SuccessObject::getUserPackagesSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), $userSubscriptions,$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
						}else{
							return SuccessObject::getUserPackagesSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results),$userPackage, Message::getMessage('M_LOGIN_SIGNUP'));
						}
					} else {
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
	
	
	public static function getUserPackagesArray($user)
	{
		$db = parent::getDataBase();
		$userPackagesArray;
		$sql = <<<STR
					SELECT PackageCode FROM userpackages
					        WHERE userpackages.UserId=:UserId AND PackageCode!=0								
STR;
                    $bind = array(
                        ":UserId" => $user['UserId']
                    );

        $userPackagesArray = $db->run($sql, $bind);		
		return $userPackagesArray;
	}
	
	//------------------------------get User Multiple Subscription---------------------------//
	public function getUserPackageSubscription($user)
	{		
		$db = parent::getDataBase();
		$results;
		$sql = <<<STR
			            SELECT 
						(CASE 
							WHEN userpackages.PackageCode="1007" THEN "Premium"
							WHEN userpackages.PackageCode="1005" THEN "Movies"
							WHEN userpackages.PackageCode="1009" THEN "Premium + Movie"
							ELSE NULL
						    END) AS PackageName, 
						users.UserId,
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
								FROM users
								
					            INNER JOIN usersubscriptions
			                    ON usersubscriptions.UserSubscriptionUserId = users.UserId
								
								INNER JOIN userpackages
								ON users.UserId = userpackages.UserId AND usersubscriptions.UserSubscriptionId=userpackages.UserSubscriptionId
			                    
								WHERE users.UserId=:UserId
                                    AND usersubscriptions.UserSubscriptionIsTempUser=0 AND userpackages.PackageCode!=0 AND usersubscriptions.UserSubscriptionPackageId=10
STR;
                    $bind = array(
                        ":UserId" => $user['UserId']
                    );
                    // print_r ( $bind );
                    $results = $db->run($sql, $bind);
					return $results;
		
	}
	
	
	
	//------------------------add package null for free users-----------------------------------
	public static function addSubscriptionPackage($UserId)
	{
		$db = parent::getDataBase();
		$Subscription= UserServices::getSubscriptionByUserId($UserId);		
		
		$update = array(
            "PackageCode" => 0,
            "UserId" => $UserId,
            "UserSubscriptionId" => $Subscription[0]['UserSubscriptionId'],
        );

        $db->insert('userpackages', $update);
		
	}
	
	
	//-----------------------------get Subscription by User Id---------------------------------
	public static function getSubscriptionByUserId($UserId)
	{		
		$results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT UserSubscriptionId				
                            FROM usersubscriptions 
                           WHERE UserSubscriptionUserId=:UserSubscriptionUserId AND UserSubscriptionIsTempUser=0 AND UserSubscriptionPackageId=10 Order by UserSubscriptionId DESC limit 1
STR;

        $bind = array(
            ":UserSubscriptionUserId" => $UserId,
        );

        $results = $db->run($sql, $bind);		
		
        Format::formatResponseData($results);
        return $results;
	}

	
	
	
}