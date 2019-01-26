<?php

// For Slim Request And Response Objects
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
// For Request Parameter's Validation
use Monolog\Logger;
// For Monolog Logger Use
use Respect\Validation\Validator as v;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class to Handle all Services Related to Payment Services
 *
 * @author SAIF UD DIN
 *
 */
class PaymentServices extends Config {

public static function makePaymentTransaction(Request $request, Response $response) {
        // ---------- Log Request With Parameters
    $log = new Logger('makePaymentTransaction');
    $log->pushHandler(new StreamHandler('../log/makePaymentTransaction' . date("j.n.Y") . '.log', Logger::DEBUG));
    $log->pushHandler(new FirePHPHandler());
    $terms = count($request->getParsedBody());
    $queryStr = 'UserIP = ' . General::getUserIP() . ' <-> ';
    if ($request->getParsedBody()) {
        foreach ($request->getParsedBody() as $field => $value) {
            $terms--;
            $queryStr .= $field . ' = ' . $value;
        if ($terms) {
                    $queryStr .= ' <-> ';
        }
    }
}
        $log->info($queryStr);
        // ---------- END
        // ---------- Creating And Setting Variables Against Request Parameters
        // TODO : Move To Parameters Class
        $Params['Version'] = filter_var(isset($request->getParsedBody()['Version']) ? $request->getParsedBody()['Version'] : null, FILTER_SANITIZE_STRING);
        $Params['Language'] = filter_var(isset($request->getParsedBody()['Language']) ? $request->getParsedBody()['Language'] : null, FILTER_SANITIZE_STRING);

        // ---------- Setting Configurations
        parent::setConfig($Params['Language']);
        // ---------- END

        $Params['Platform'] = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : null, FILTER_SANITIZE_STRING);
        $Params['UserIP'] = General::getUserIP();
        $Params['ProductId'] = filter_var(isset($request->getParsedBody()['ProductId']) ? $request->getParsedBody()['ProductId'] : null, FILTER_SANITIZE_STRING);
        $Params['TransactionType'] = filter_var(isset($request->getParsedBody()['TransactionType']) ? $request->getParsedBody()['TransactionType'] : null, FILTER_SANITIZE_STRING);
        $Params['UserId'] = filter_var(isset($request->getParsedBody()['UserId']) ? $request->getParsedBody()['UserId'] : null, FILTER_SANITIZE_STRING);

        $Params['ReferenceId'] = filter_var(isset($request->getParsedBody()['ReferenceId']) ? $request->getParsedBody()['ReferenceId'] : null, FILTER_SANITIZE_STRING);

        if (isset($request->getParsedBody()['MobileNo'])) {
            $Params['MobileNo'] = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
            $Params['MobileNo'] = ltrim($Params['MobileNo'], '0');
            $Params['MobileNo'] = ltrim($Params['MobileNo'], '+92');
        }

        if (isset($request->getParsedBody()['OperatorId'])) {
            $Params['OperatorId'] = filter_var($request->getParsedBody()['OperatorId'], FILTER_SANITIZE_STRING);
        } else {
            $OperatorPrefixes = array(
                'Mobilink' => array(
                    "300",
                    "301",
                    "302",
                    "303",
                    "304",
                    "305",
                    "306",
                    "307",
                    "308",
                    "309",
                ),
                'Telenor' => array(
                    "340",
                    "341",
                    "342",
                    "343",
                    "344",
                    "345",
                    "346",
                    "347",
                    "348",
                    "349",
                ),
                'Zong' => array(
                    "310",
                    "311",
                    "312",
                    "313",
                    "314",
                    "315",
                    "316",
                    "317",
                    "318",
                ),
            );

            if (in_array(substr($Params['MobileNo'], 0, 3), $OperatorPrefixes['Mobilink'])) {
                $Params['OperatorId'] = 100001;
            } else if (in_array(substr($Params['MobileNo'], 0, 3), $OperatorPrefixes['Telenor'])) {
                $Params['OperatorId'] = 100002;
            } else if (in_array(substr($Params['MobileNo'], 0, 3), $OperatorPrefixes['Zong'])) {
                $Params['OperatorId'] = 100003;
            } else {
                $log->info('E_NO_OPERATOR : OPERATOR NOT SUPPORTED');
                return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_OPERATOR'))));
            }
        }
        // ---------- END
        // ---------- Parameters Validations
        // TODO : Move To Validator Class
        $VersionValidator = v::Alnum()->noWhitespace()->length(2, 2);
        $PlatformValidator = v::Alpha()->noWhitespace()->length(3, 10);
        $ProductIdValidator = v::Digit()->noWhitespace()->length(4, 4);
        $TransactionTypeValidator = v::Digit()->noWhitespace()->length(1, 1);
        $UserIdValidator = v::Digit()->noWhitespace()->length(1, null);
        $OperatorIdValidator = v::Digit()->noWhitespace()->length(6, null);
        $MobileNoValidator = v::Digit()->noWhitespace()->length(10, 10);
        $ReferenceIdValidator = v::Alnum()->noWhitespace()->length(1, null);

        if (!$VersionValidator->validate($Params['Version'])) {
            $log->info('E_INVALID_PARAMS : Version');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (!$PlatformValidator->validate($Params['Platform'])) {
            $log->info('E_INVALID_PARAMS : Platform');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (!$ProductIdValidator->validate($Params['ProductId'])) {
            $log->info('E_INVALID_PARAMS : ProductId');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (!$TransactionTypeValidator->validate($Params['TransactionType'])) {
            $log->info('E_INVALID_PARAMS : TransactionType');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (!$UserIdValidator->validate($Params['UserId'])) {
            $log->info('E_INVALID_PARAMS : UserId');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (!$OperatorIdValidator->validate($Params['OperatorId'])) {
            $log->info('E_INVALID_PARAMS : OperatorId');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (isset($request->getParsedBody()['MobileNo']) && !$MobileNoValidator->validate($Params['MobileNo'])) {
            $log->info('E_INVALID_PARAMS : MobileNo');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (isset($request->getParsedBody()['ReferenceId']) && !$ReferenceIdValidator->validate($Params['ReferenceId'])) {
            $log->info('E_INVALID_PARAMS : ReferenceId');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        // ---------- END

        try {
            switch (strtolower($Params['Version'])) {
                case 'V1':
                case 'v1':
                    switch (strtolower($Params['Platform'])) {
                        case 'androidnew':
                            $db = parent::getDataBase();

                            $LogInsertArray = array(
                                "PaymentLogStatus" => 0,
                                "PaymentLogVersion" => $Params['Version'],
                                "PaymentLogPlatform" => $Params['Platform'],
                                "PaymentLogUserId" => $Params['UserId'],
                                "PaymentLogProductId" => $Params['ProductId'],
                                "PaymentLogTransactionType" => $Params['TransactionType'],
                                "PaymentLogOperatorId" => $Params['OperatorId'],
                                "PaymentLogMobileNo" => $Params['MobileNo'],
                                "PaymentLogReferenceId" => $Params['ReferenceId'],
                                "PaymentLogMessage" => null,
                                "PaymentLogIP" => General::getUserIP(),
                            );

                            $bind = array(
                                ":UserId" => $Params['UserId'],
                            );

                            $sql = <<<STR
    				SELECT *,IF ( TIMESTAMPDIFF(SECOND,NOW(), usersubscriptions.UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
                                    FROM users
    				INNER JOIN userprofiles ON userprofiles.UserProfileUserId = users.UserId
    			        INNER JOIN usersubscriptions ON usersubscriptions.UserSubscriptionUserId = users.UserId
                                AND UserSubscriptionIsTempUser=0
    			        WHERE UserId=:UserId
STR;
                            // print_r ( $bind );
                            $results = $db->run($sql, $bind);
                            // $results = $db->select("users", "UserId=:UserId", $bind);
                            //print_r($results);die;
                            if ($results) {
                                // If User Is Already Subscribed Then Don't Allow It To Make Another Transaction.
                                if (!$results[0]['UserSubscriptionIsExpired'] && PaymentServices::getActiveUserPackages($Params['UserId'], $Params['ProductId']) === 1) {
                                    $log->info('E_NO_PAYMENT : USER ALREADY SUBSCRIBED');

                                    $LogInsertArray['PaymentLogMessage'] = "USER ALREADY SUBSCRIBED";
                                    $db->insert("userpaymentlogs", $LogInsertArray);

                                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessage('You have already Subscribed.'))));
                                }

                                if ($Params['OperatorId'] == '100003') { // Zong
                                    $data = array(
                                        'productID' => urlencode($Params['ProductId']),
                                        'transactionType' => urlencode($Params['TransactionType']),
                                        'mobileNo' => urlencode($Params['MobileNo']),
                                        'userKey' => urlencode($Params['UserId']),
                                        'operatorID' => urlencode($Params['OperatorId']),
                                    );
                                    $data_string = json_encode($data);

                                    // TODO : Move To CURL Class
                                    $ch = curl_init('http://111.119.160.222:9991/dcb-integration/transaction/' . Config::$MerchantWebKey . '/WEB/make-payment');
                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Content-Type: application/json',
                                        'Content-Length: ' . strlen($data_string),
                                    ));

                                    $result = json_decode(curl_exec($ch), true);
                                    curl_close($ch);

                                    
                                    if ($result) {                                        
                                        if ($result['status'] === 1) {
                                            $log->info('M_PAYMENT : Zong - ' . $result['message']);
                                            $LogInsertArray['PaymentLogStatus'] = 1;
                                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                                            $db->insert("userpaymentlogs", $LogInsertArray);

                                            $results = $db->run($sql, $bind);
                                            $usersubscription = PaymentServices::getUserPackageSubscription($results);
                                            Format::formatResponseData($usersubscription);
                                            $userPackages = PaymentServices::getUserPackagesArray($results);
                                            $results = $results[0];
                                            return General::getResponse($response->write(SuccessObject::getPackageUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), $usersubscription, $userPackages, Message::getMessage('M_PAYMENT'))));
                                        } else {
                                            $log->info('E_NO_PAYMENT : ' . $result['message']);

                                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                                            $db->insert("userpaymentlogs", $LogInsertArray);

                                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessageAndCode($result['message'], $result['status']))));
                                        }
                                    } else {
                                        $log->info('E_NO_PAYMENT : NO RESPONSE FROM SIMPAISA');

                                        $LogInsertArray['PaymentLogMessage'] = "NO RESPONSE FROM SIMPAISA";
                                        $db->insert("userpaymentlogs", $LogInsertArray);

                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessage("NO RESPONSE FROM SIMPAISA."))));
                                    }
                                } else if ($Params['OperatorId'] == '100001') { // Mobilink
                                    $data = array(
                                        'productID' => urlencode($Params['ProductId']),
                                        'transactionType' => urlencode($Params['TransactionType']),
                                        'mobileNo' => urlencode($Params['MobileNo']),
                                        'userKey' => urlencode($Params['UserId']),
                                        'operatorID' => urlencode($Params['OperatorId']),
                                    );
                                    $data_string = json_encode($data);

                                    // TODO : Move To CURL Class
                                    $ch = curl_init('http://111.119.160.222:9991/dcb-integration/transaction/' . Config::$MerchantWebKey . '/WEB/make-payment');
                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Content-Type: application/json',
                                        'Content-Length: ' . strlen($data_string),
                                    ));

                                    $result = json_decode(curl_exec($ch), true);
                                    curl_close($ch);

                                    // print_r($result);

                                    if ($result) {                                        
                                        if ($result['status'] === 1) {
                                            $log->info('M_PAYMENT : Mobilink - ' . $result['message']);
                                            $LogInsertArray['PaymentLogStatus'] = 1;
                                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                                            $db->insert("userpaymentlogs", $LogInsertArray);

                                            $results = $db->run($sql, $bind);
                                            $usersubscription = PaymentServices::getUserPackageSubscription($results);
                                            Format::formatResponseData($usersubscription);
                                            $userPackages = PaymentServices::getUserPackagesArray($results);
                                            $results = $results[0];
                                            return General::getResponse($response->write(SuccessObject::getPackageUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), $usersubscription, $userPackages, Message::getMessage('M_PAYMENT'))));
                                        } else {
                                            $log->info('E_NO_PAYMENT : ' . $result['message']);

                                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                                            $db->insert("userpaymentlogs", $LogInsertArray);

                                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessageAndCode($result['message'], $result['status']))));
                                        }
                                    } else {
                                        $log->info('E_NO_PAYMENT : NO RESPONSE FROM SIMPAISA');

                                        $LogInsertArray['PaymentLogMessage'] = "NO RESPONSE FROM SIMPAISA";
                                        $db->insert("userpaymentlogs", $LogInsertArray);

                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessage('NO RESPONSE FROM SIMPAISA.'))));
                                    }
                                } else if ($Params['OperatorId'] == '100002') { // Telenor
                                    $data = array(
                                        'productID' => urlencode($Params['ProductId']),
                                        'transactionType' => urlencode($Params['TransactionType']),
                                        'referenceID' => urlencode($Params['ReferenceId']),
                                        'userKey' => urlencode($Params['UserId']),
                                        'operatorID' => urlencode($Params['OperatorId']),
                                    );
                                    $data_string = json_encode($data);

                                    // TODO : Move To CURL Class
                                    $ch = curl_init('http://111.119.160.222:9991/dcb-integration/transaction/' . Config::$MerchantWebKey . '/WEB/make-payment');
                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Content-Type: application/json',
                                        'Content-Length: ' . strlen($data_string),
                                    ));

                                    $result = json_decode(curl_exec($ch), true);
                                    curl_close($ch);

                                    // print_r($result);

                                    if ($result) {                                       
                                        if ($result['status'] === 1) {
                                            $log->info('M_PAYMENT : Telenor - ' . $result['message']);
                                            $LogInsertArray['PaymentLogStatus'] = 1;
                                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                                            $db->insert("userpaymentlogs", $LogInsertArray);

                                            $results = $db->run($sql, $bind);
                                            $usersubscription = PaymentServices::getUserPackageSubscription($results);
                                            Format::formatResponseData($usersubscription);
                                            $userPackages = PaymentServices::getUserPackagesArray($results);
                                            $results = $results[0];
                                            return General::getResponse($response->write(SuccessObject::getPackageUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), $usersubscription, $userPackages, Message::getMessage('M_PAYMENT'))));
                                        } else {
                                            $log->info('E_NO_PAYMENT : ' . $result['message']);

                                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                                            $db->insert("userpaymentlogs", $LogInsertArray);

                                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessageAndCode($result['message'], $result['status']))));
                                        }
                                    } else {
                                        $log->info('E_NO_PAYMENT : NO RESPONSE FROM SIMPAISA');

                                        $LogInsertArray['PaymentLogMessage'] = "NO RESPONSE FROM SIMPAISA";
                                        $db->insert("userpaymentlogs", $LogInsertArray);

                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessage("NO RESPONSE FROM SIMPAISA"))));
                                    }
                                } else { // Not Set
                                    $log->info('E_NO_PAYMENT : INVALID OPERATOR');

                                    $LogInsertArray['PaymentLogMessage'] = "INVALID OPERATOR.";
                                    $db->insert("userpaymentlogs", $LogInsertArray);

                                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessage("INVALID OPERATOR."))));
                                }
                            } else {
                                $log->info('E_NO_PAYMENT : USER NOT FOUND');

                                $LogInsertArray['PaymentLogMessage'] = "USER NOT FOUND";
                                $db->insert("userpaymentlogs", $LogInsertArray);

                                return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getErrorMessage("USER NOT FOUND"))));
                            }
                            break;
                        default:
                            $log->info('E_INVALID_PLATFORM');
                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    $log->info('E_INVALID_SERVICE_VERSION');
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (Exception $e) {
            $log->info('PHPException');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } catch (PDOException $e) {
            $log->info('PDOException');
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function savePaymentTransaction(Request $request, Response $response) {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);

        $UserId = filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {
                                $insert = array(
                                    "UserPaymentVersion" => $Version,
                                    "UserPaymentPlatform" => $Platform,
                                    "UserPaymentUserName" => $UserId,
                                );
                                $result = $db->insert("userpayments", $insert) ? $db->lastInsertId() : false;
                                if ($result) {
                                    $resultArray['UserId'] = $UserId;
                                    $resultArray['TransKey'] = $result;
                                    return General::getResponse($response->write(SuccessObject::getPaymentSuccessObject($resultArray, Message::getMessage('M_INSERT'))));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_NO_INSERT'))));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_NO_INSERT'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function getUserPaymentHistory(Request $request, Response $response) {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);

        $UserId = filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);

                            if ($results) {
                                $bind = array(
                                    ":UserId" => $UserId,
                                );
                                $results = $db->select("userpayments", "UserPaymentUserName=:UserId", $bind);
                                if ($results) {
                                    Format::formatResponseData($results);
                                    return General::getResponse($response->write(SuccessObject::getPaymentSuccessObject($results, Message::getMessage('M_DATA'))));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_NO_DATA'))));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_NO_DATA'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getPaymentErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function unsubscribePaymentTransaction(Request $request, Response $response) {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);

        $UserId = filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {
                                if ($results[0]['UserPackageType'] === '1003') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1005') { // 1005: Tapmad TV Box has (for now) one monthly subscription only for Rs. 250
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context);
                                    if ($result === false) { /* Handle error */
                                    }
                                    print_r($result);
                                    if ($result != "Recursion Disabled Successfully") {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1006') { // 1006: Tapmad App/Web has daily subscription for Rs. 3
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1007') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1004') { // 1004: Tapmad App/Web has weekly subscription for Rs. 10
                                    $data = array(
                                        'UserID' => $UserId,
                                        'ProductID' => $results[0]['UserPackageType'],
                                        'MerchantID' => '1000004'
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data)
                                        )
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => NULL,
                                            "UserPackageIsRecurring" => NULL
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1005') { // 1005: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        'UserID' => $UserId,
                                        'ProductID' => $results[0]['UserPackageType'],
                                        'MerchantID' => '1000004'
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data)
                                        )
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => NULL,
                                            "UserPackageIsRecurring" => NULL
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1009') { // 1009: Tapmad App/Web has weekly subscription for Rs. 25
                                    $data = array(
                                        'UserID' => $UserId,
                                        'ProductID' => $results[0]['UserPackageType'],
                                        'MerchantID' => '1000004'
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data)
                                        )
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => NULL,
                                            "UserPackageIsRecurring" => NULL
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1008') { // 1008: Tapmad App/Web has monthly subscription for Rs. 50
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    //-------------------------------android use for unsubscription----------------------------------------------------//
    public static function unsubscribePaymentTransaction2(Request $request, Response $response) {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);

        $UserId = filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);

                            if ($results) {

                                if ($userpackage = $results[0]['UserPackageType'] === null) {
                                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_USER_SUBSCRIBED'))));
                                } else if ($results[0]['UserPackageType'] === '1003') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    // print_r ( $result );
                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1006') { // 1006: Tapmad App/Web has daily subscription for Rs. 3
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);


                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1007') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);

                                        $results = $db->select("users", "UserId=:UserId", $bind);

                                        $results = $results[0];

                                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1004') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);

                                        $results = $db->select("users", "UserId=:UserId", $bind);

                                        $results = $results[0];

                                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1005') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);

                                        $results = $db->select("users", "UserId=:UserId", $bind);

                                        $results = $results[0];

                                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1009') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);

                                        $results = $db->select("users", "UserId=:UserId", $bind);

                                        $results = $results[0];

                                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else if ($results[0]['UserPackageType'] === '1008') { // 1007: Tapmad App/Web has weekly subscription for Rs. 15
                                    $data = array(
                                        UserID => $UserId,
                                        ProductID => $results[0]['UserPackageType'],
                                        MerchantID => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    if ($result['responseCode'] === '0000') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);

                                        $results = $db->select("users", "UserId=:UserId", $bind);

                                        $results = $results[0];

                                        return General::getResponse($response->write(SuccessObject::getUserSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function getIncrementDateWithCurrent($subdays) {
        $timezone = date_default_timezone_get();
        $date = date_create($timezone);
        date_add($date, date_interval_create_from_date_string($subdays));

        return date_format($date, "Y-m-d H:i:s");
    }

    public static function responsePaymentTransactionTest(Request $request, Response $response) {
        $Version = "V1";
        $Language = "en";
        parent::setConfig($Language);
        $Platform = "dcb";

        $TransID = filter_var($request->getParsedBody()['TransID'], FILTER_SANITIZE_STRING);
        $UserId = filter_var($request->getParsedBody()['UserID'], FILTER_SANITIZE_STRING);
        $TransPackage = filter_var($request->getParsedBody()['TransPackageID'], FILTER_SANITIZE_STRING);
        $IsRecurring = filter_var($request->getParsedBody()['IsRecurring'], FILTER_SANITIZE_STRING);

        // Create a log channel
        $log = new Logger('responsePaymentTransactionTest');
        $log->pushHandler(new StreamHandler('../log/responsePaymentTransactionTest_' . date("j.n.Y") . '.log', Logger::WARNING));

        // Add records to the log
        $terms = count($request->getParsedBody());
        $queryStr = '';
        foreach ($request->getParsedBody() as $field => $value) {
            $terms--;
            $queryStr .= $field . ' = ' . $value;
            if ($terms) {
                $queryStr .= ' AND ';
            }
        }
        $queryStr .= ' AND UserIP = ' . General::getUserIP();
        $log->warning($queryStr);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Dcb':
                        case 'dcb':
                        case 'DCB':
                            $db = parent::getDataBase2();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {
                                $bind = array(
                                    ":UserProfileUserId" => $UserId,
                                );
                                $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);

                                $insert = array(
                                    "UserPaymentVersion" => $Version,
                                    "UserPaymentPlatform" => $Platform,
                                    "UserPaymentUserName" => $UserId,
                                    "UserPaymentPackageType" => $TransPackage,
                                    "UserPaymentStatus" => 0,
                                    "UserPaymentIsRecurring" => $IsRecurring,
                                    "UserPaymentTransactionId" => 0,
                                    "UserPaymentIP" => General::getUserIP(),
                                    "UserPaymentMobileNumber" => $results[0]['UserProfileMobile'],
                                    "UserPaymentOperatorID" => $results[0]['UserProfileMobileNetwork'],
                                );
                                $db->insert("userpayments", $insert);

                                return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function responsePaymentTransaction(Request $request, Response $response) {
        $Version = "V1";
        $Language = "en";
        parent::setConfig($Language);
        $Platform = "dcb";

        $TransID = filter_var($request->getParsedBody()['TransID'], FILTER_SANITIZE_STRING);
        $UserId = filter_var($request->getParsedBody()['UserID'], FILTER_SANITIZE_STRING);
        $TransPackage = filter_var($request->getParsedBody()['TransPackageID'], FILTER_SANITIZE_STRING);
        $IsRecurring = filter_var($request->getParsedBody()['IsRecurring'], FILTER_SANITIZE_STRING);

        // Create a log channel
        $log = new Logger('responsePaymentTransaction');
        $log->pushHandler(new StreamHandler('../log/responsePaymentTransaction_' . date("j.n.Y") . '.log', Logger::WARNING));

        // Add records to the log
        $terms = count($request->getParsedBody());
        $queryStr = '';
        foreach ($request->getParsedBody() as $field => $value) {
            $terms--;
            $queryStr .= $field . ' = ' . $value;
            if ($terms) {
                $queryStr .= ' AND ';
            }
        }
        $queryStr .= ' AND UserIP = ' . General::getUserIP();
        $log->warning($queryStr);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Dcb':
                        case 'dcb':
                        case 'DCB':
                            $db = parent::getDataBase2();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {
                                $bind = array(
                                    ":UserProfileUserId" => $UserId,
                                );
                                $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);

                                $insert = array(
                                    "UserPaymentVersion" => $Version,
                                    "UserPaymentPlatform" => $Platform,
                                    "UserPaymentUserName" => $UserId,
                                    "UserPaymentPackageType" => $TransPackage,
                                    "UserPaymentStatus" => 0,
                                    "UserPaymentIsRecurring" => $IsRecurring,
                                    "UserPaymentTransactionId" => 0,
                                    "UserPaymentIP" => General::getUserIP(),
                                    "UserPaymentMobileNumber" => $results[0]['UserProfileMobile'],
                                    "UserPaymentOperatorID" => $results[0]['UserProfileMobileNetwork'],
                                );
                                $db->insert("userpayments", $insert);

                                return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function failedPaymentTransaction(Request $request, Response $response) {

        $Version = "V1";
        $Language = "en";
        parent::setConfig($Language);
        $Platform = "dcb";
        $UserPaymentPackageType;
        $TransID = filter_var($request->getParsedBody()['transactionID'], FILTER_SANITIZE_STRING);
        $UserId = filter_var($request->getParsedBody()['userId'], FILTER_SANITIZE_STRING);
        $ProductID = filter_var($request->getParsedBody()['productID'], FILTER_SANITIZE_STRING);
        $IsRecurring = filter_var($request->getParsedBody()['isRecurring'], FILTER_SANITIZE_STRING);
        $Message = filter_var($request->getParsedBody()['message'], FILTER_SANITIZE_STRING);
        $MSISDN = filter_var($request->getParsedBody()['msisdn'], FILTER_SANITIZE_STRING);
        $OperatorID = filter_var($request->getParsedBody()['operatorID'], FILTER_SANITIZE_STRING);

        // Create a log channel
        $log = new Logger('failedPaymentTransaction');
        $log->pushHandler(new StreamHandler('../log/failedPaymentTransaction_' . date("j.n.Y") . '.log', Logger::WARNING));

        // Add records to the log
        $terms = count($request->getParsedBody());
        $queryStr = '';
        foreach ($request->getParsedBody() as $field => $value) {
            $terms--;
            $queryStr .= $field . ' = ' . $value;
            if ($terms) {
                $queryStr .= ' AND ';
            }
        }
        $queryStr .= ' AND UserIP = ' . General::getUserIP();
        $log->warning($queryStr);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Dcb':
                        case 'dcb':
                        case 'DCB':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );

                            if ($ProductID == "1007") {
                                $UserPaymentPackageType = "Premium";
                            }
                            if ($ProductID == "1005") {
                                $UserPaymentPackageType = "Movies";
                            }
                            if ($ProductID == "1009") {
                                $UserPaymentPackageType = "Premium + Movie";
                            }


                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {
                                $insert = array(
                                    "UserPaymentVersion" => $Version,
                                    "UserPaymentPlatform" => $Platform,
                                    "UserPaymentUserName" => $UserId,
                                    "UserPaymentPackageType" => $ProductID,
                                    "UserPaymentStatus" => 0,
                                    "UserPaymentIsRecurring" => $IsRecurring,
                                    "UserPaymentTransactionId" => $TransID,
                                    "UserPaymentIP" => General::getUserIP(),
                                    "UserPaymentMobileNumber" => $MSISDN,
                                    "UserPaymentOperatorID" => $OperatorID,
                                    "UserPaymentMessage" => $Message,
                                    "UserPaymentPackageName" => $UserPaymentPackageType,
                                );
                                $db->insert("userpayments", $insert);

                                //----------------------bucket on/off in below query-------------------------------
                                //if ($Message === "Transaction-Failed-Insufficient" && ($OperatorID === "100002" || $OperatorID === "100003")) {
                                //--------------------check bucket status in first condition------------------------------//
                                //$bucketUserPackages = PaymentServices::bucketUserPackages($UserId);
                                if ($Message === "Transaction-Failed-Insufficient" && PaymentServices::getBucketStatus() == 1) {


                                    if (PaymentServices::getIphoneBucket() === 1 && ($results[0]['UserPlatform'] === 'Ios')) {

                                        //--------------------condition for operators--------------------------//
                                        if (PaymentServices::getBucketOperators($OperatorID) === 1) {

                                            PaymentServices::updateBucketsUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message);
                                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                        } else {
                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                        }
                                    }
                                    //----------------------bucket checking for Android Platform---------------------//
                                    if (PaymentServices::getAndroidBucket() === 1 && ($results[0]['UserPlatform'] === 'Android')) {
                                        //--------------------condition for operators--------------------------//
                                        if (PaymentServices::getBucketOperators($OperatorID) === 1) {
                                            //-------------------Query for update Subscription----------------------//
                                            PaymentServices::updateBucketsUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message);
                                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                        } else {
                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                        }
                                    }
                                    //------------------------------------checking bucket for Ios Platform-----------------------------//

                                    if (PaymentServices::getWebBucket() === 1 && ($results[0]['UserPlatform'] === 'Web')) {
                                        // only IOS code
                                        if (PaymentServices::getBucketOperators($OperatorID) === 1) {
                                            PaymentServices::updateBucketsUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message);
                                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                        } else {
                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                        };
                                    }
                                } else if ($Message === "Subscription-Disabled") {

                                    //---------------- bucket on
                                    $update = array(
                                        "UserPackageType" => null,
                                        "UserPackageIsRecurring" => null,
                                    );
                                    $bind = array(
                                        ":UserId" => $UserId,
                                    );
                                    $db->update('users', $update, 'UserId=:UserId', $bind);
                                    $db->run($sql, $bind);
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    //--------------------------function for update User Subscription-----------------------------//
    public static function updateBucketUsersScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message) {

        $db = parent::getDataBase();
        $bucketUserPackages = PaymentServices::bucketUserPackages($UserId);
        if ($bucketUserPackages === 0 && $ProductID === "1007") {
            $sql = <<<STR
				UPDATE usersubscriptions
					SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(),INTERVAL 11 DAY)
				WHERE usersubscriptions.UserSubscriptionUserId = :UserId
				AND UserSubscriptionIsTempUser=0;
STR;
            $bind = array(
                ":UserId" => $UserId,
            );

            $db->run($sql, $bind);

            $insert = array(
                "TrialVersion" => $Version,
                "TrialPlatform" => $Platform,
                "TrialUserId" => $UserId,
                "TrialProductId" => $ProductID,
                "TrialIsRecurring" => $IsRecurring,
                "TrialTransactionId" => $TransID,
                "TrialIP" => General::getUserIP(),
                "TrialMobileNo" => $MSISDN,
                "TrialOperatorID" => $OperatorID,
                "TrialMessage" => $Message,
            );
            $db->insert("userpaymenttrials", $insert);

            $update = array(
                "UserId" => $UserId,
                "UserActivePackageType" => $ProductID
            );
            $bind = array(
                ":UserId" => $UserId
            );
            $db->update('users', $update, 'UserId=:UserId', $bind);
            $userPackages = PaymentServices::getUserPackages($UserId, $ProductID);
            $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
            PaymentServices::addSubscriptionUserNewPackages($UserId, $ProductID, $subscriptionId[0]['UserSubscriptionId']);
        } else if (PaymentServices::countUserPackages($UserId) === 1 && $ProductID === "1007") {

            $insert = array(
                "UserSubscriptionUserId" => $UserId,
                "UserSubscriptionIsTempUser" => 0,
                "UserSubscriptionPackageId" => 10,
                "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
                "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent("11 days"),
                "UserSubscriptionMaxConcurrentConnections" => 6,
                "UserSubscriptionAutoRenew" => 0,
                "UserSubscriptionDetails" => NULL,
            );
            $db->insert("usersubscriptions", $insert);

            $insert = array(
                "TrialVersion" => $Version,
                "TrialPlatform" => $Platform,
                "TrialUserId" => $UserId,
                "TrialProductId" => $ProductID,
                "TrialIsRecurring" => $IsRecurring,
                "TrialTransactionId" => $TransID,
                "TrialIP" => General::getUserIP(),
                "TrialMobileNo" => $MSISDN,
                "TrialOperatorID" => $OperatorID,
                "TrialMessage" => $Message,
            );
            $db->insert("userpaymenttrials", $insert);

            $update = array(
                "UserId" => $UserId,
                "UserActivePackageType" => $ProductID
            );
            $bind = array(
                ":UserId" => $UserId
            );
            $db->update('users', $update, 'UserId=:UserId', $bind);
            $userPackages = PaymentServices::getUserPackages($UserId, $ProductID);
            $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
            PaymentServices::addSubscriptionUserNewPackages($UserId, $ProductID, $subscriptionId[0]['UserSubscriptionId']);
        } else {
            return false;
        }
    }

    //--------------------------function for update User Subscription-----------------------------//
    public static function updateUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message) {

        $db = parent::getDataBase();
        $bucketUserPackages = PaymentServices::bucketUserPackages($UserId);
        if ($bucketUserPackages === 0 && $ProductID === "1007") {
            $sql = <<<STR
				UPDATE usersubscriptions
					SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(),INTERVAL 11 DAY)
				WHERE usersubscriptions.UserSubscriptionUserId = :UserId
				AND UserSubscriptionIsTempUser=0;
STR;
            $bind = array(
                ":UserId" => $UserId,
            );

            $db->run($sql, $bind);

            $insert = array(
                "TrialVersion" => $Version,
                "TrialPlatform" => $Platform,
                "TrialUserId" => $UserId,
                "TrialProductId" => $ProductID,
                "TrialIsRecurring" => $IsRecurring,
                "TrialTransactionId" => $TransID,
                "TrialIP" => General::getUserIP(),
                "TrialMobileNo" => $MSISDN,
                "TrialOperatorID" => $OperatorID,
                "TrialMessage" => $Message,
            );
            $db->insert("userpaymenttrials", $insert);

            $update = array(
                "UserId" => $UserId,
                "UserActivePackageType" => $ProductID
            );
            $bind = array(
                ":UserId" => $UserId
            );
            $db->update('users', $update, 'UserId=:UserId', $bind);
            $userPackages = PaymentServices::getUserPackages($UserId, $ProductID);
            $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
            PaymentServices::addSubscriptionUserNewPackages($UserId, $ProductID, $subscriptionId[0]['UserSubscriptionId']);
            echo "updated";
        } else {
            echo "Bucket Not On";
            exit;
        }
    }

    public static function updateUsersPackages($userPackages, $ProductID) {
        $db = parent::getDataBase();
        $update = array(
            "PackageCode" => $ProductID
        );
        $bind = array(
            ":UserPackageId" => $userPackages,
        );
        return $db->update('userpackages', $update, 'UserPackageId=:UserPackageId', $bind);
    }

    //--------------------------function for update User Subscription-----------------------------//
    public static function updateBucketsUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message) {


        $db = parent::getDataBase();
        $bucketUserPackages = PaymentServices::bucketUserPackages($UserId, $ProductCode = "0");

        if ($bucketUserPackages === 1 && $ProductID === "1007") {
            $sql = <<<STR
				UPDATE usersubscriptions
					SET 
                                            UserPackageCode=:UserPackageCode,
                                            usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(),INTERVAL 11 DAY)
				WHERE usersubscriptions.UserSubscriptionUserId = :UserId
				AND UserSubscriptionIsTempUser=0;
STR;
            $bind = array(
                ":UserId" => $UserId,
                ":UserPackageCode" => $ProductID,
            );

            $db->run($sql, $bind);

            $insert = array(
                "TrialVersion" => $Version,
                "TrialPlatform" => $Platform,
                "TrialUserId" => $UserId,
                "TrialProductId" => $ProductID,
                "TrialIsRecurring" => $IsRecurring,
                "TrialTransactionId" => $TransID,
                "TrialIP" => General::getUserIP(),
                "TrialMobileNo" => $MSISDN,
                "TrialOperatorID" => $OperatorID,
                "TrialMessage" => $Message,
            );
            $db->insert("userpaymenttrials", $insert);

            $update = array(
                "UserId" => $UserId,
                "UserActivePackageType" => $ProductID
            );
            $bind = array(
                ":UserId" => $UserId
            );
            $db->update('users', $update, 'UserId=:UserId', $bind);

            $userPackages = PaymentServices::getUserPackages($UserId, 0);
            PaymentServices::updateUsersPackages($userPackages, $ProductID);
        } else if ($ProductID === "1007") {
            $insert = array(
                "UserSubscriptionUserId" => $UserId,
                "UserSubscriptionIsTempUser" => 0,
                "UserSubscriptionPackageId" => 10,
                "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
                "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent("11 days"),
                "UserSubscriptionMaxConcurrentConnections" => 6,
                "UserSubscriptionAutoRenew" => 0,
                "UserSubscriptionDetails" => NULL,
                "UserPackageCode"=>$ProductID
            );
            $db->insert("usersubscriptions", $insert);

            $insert = array(
                "TrialVersion" => $Version,
                "TrialPlatform" => $Platform,
                "TrialUserId" => $UserId,
                "TrialProductId" => $ProductID,
                "TrialIsRecurring" => $IsRecurring,
                "TrialTransactionId" => $TransID,
                "TrialIP" => General::getUserIP(),
                "TrialMobileNo" => $MSISDN,
                "TrialOperatorID" => $OperatorID,
                "TrialMessage" => $Message,
            );
            $db->insert("userpaymenttrials", $insert);

            $update = array(
                "UserId" => $UserId,
                "UserActivePackageType" => $ProductID
            );
            $bind = array(
                ":UserId" => $UserId
            );
            $db->update('users', $update, 'UserId=:UserId', $bind);
            $userPackages = PaymentServices::getUserPackages($UserId, $ProductID);
            $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
            PaymentServices::addSubscriptionUserNewPackages($UserId, $ProductID, $subscriptionId[0]['UserSubscriptionId']);
        } else {
            return false;
        }
    }

    public static function failedPaymentTransactionTest(Request $request, Response $response) {

        $Version = "V1";
        $Language = "en";
        parent::setConfig($Language);
        $Platform = "dcb";
        $UserPaymentPackageType;
        $TransID = filter_var($request->getParsedBody()['transactionID'], FILTER_SANITIZE_STRING);
        $UserId = filter_var($request->getParsedBody()['userId'], FILTER_SANITIZE_STRING);
        $ProductID = filter_var($request->getParsedBody()['productID'], FILTER_SANITIZE_STRING);
        $IsRecurring = filter_var($request->getParsedBody()['isRecurring'], FILTER_SANITIZE_STRING);
        $Message = filter_var($request->getParsedBody()['message'], FILTER_SANITIZE_STRING);
        $MSISDN = filter_var($request->getParsedBody()['msisdn'], FILTER_SANITIZE_STRING);
        $OperatorID = filter_var($request->getParsedBody()['operatorID'], FILTER_SANITIZE_STRING);

        // Create a log channel
        $log = new Logger('failedPaymentTransaction');
        $log->pushHandler(new StreamHandler('../log/failedPaymentTransaction_' . date("j.n.Y") . '.log', Logger::WARNING));

        // Add records to the log
        $terms = count($request->getParsedBody());
        $queryStr = '';
        foreach ($request->getParsedBody() as $field => $value) {
            $terms--;
            $queryStr .= $field . ' = ' . $value;
            if ($terms) {
                $queryStr .= ' AND ';
            }
        }
        $queryStr .= ' AND UserIP = ' . General::getUserIP();
        $log->warning($queryStr);

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Dcb':
                        case 'dcb':
                        case 'DCB':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );

                            if ($ProductID == "1007") {
                                $UserPaymentPackageType = "Premium";
                            }
                            if ($ProductID == "1005") {
                                $UserPaymentPackageType = "Movies";
                            }
                            if ($ProductID == "1009") {
                                $UserPaymentPackageType = "Premium + Movie";
                            }


                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {
                                $insert = array(
                                    "UserPaymentVersion" => $Version,
                                    "UserPaymentPlatform" => $Platform,
                                    "UserPaymentUserName" => $UserId,
                                    "UserPaymentPackageType" => $ProductID,
                                    "UserPaymentStatus" => 0,
                                    "UserPaymentIsRecurring" => $IsRecurring,
                                    "UserPaymentTransactionId" => $TransID,
                                    "UserPaymentIP" => General::getUserIP(),
                                    "UserPaymentMobileNumber" => $MSISDN,
                                    "UserPaymentOperatorID" => $OperatorID,
                                    "UserPaymentMessage" => $Message,
                                    "UserPaymentPackageName" => $UserPaymentPackageType,
                                );
                                $db->insert("userpayments", $insert);

                                //----------------------bucket on/off in below query-------------------------------
                                //if ($Message === "Transaction-Failed-Insufficient" && ($OperatorID === "100002" || $OperatorID === "100003")) {
                                //--------------------check bucket status in first condition------------------------------//
                                //$bucketUserPackages = PaymentServices::bucketUserPackages($UserId);
                                if ($Message === "Transaction-Failed-Insufficient" && PaymentServices::getBucketStatus() == 1) {

                                    if (PaymentServices::getIphoneBucket() === 1 && ($results[0]['UserPlatform'] === 'Ios')) {

                                        //--------------------condition for operators--------------------------//
                                        if (PaymentServices::getBucketOperators($OperatorID) === 1) {

                                            PaymentServices::updateBucketsUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message);
                                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                        } else {
                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                        }
                                    }
                                    //----------------------bucket checking for Android Platform---------------------//
                                    if (PaymentServices::getAndroidBucket() === 1 && ($results[0]['UserPlatform'] === 'Android')) {
                                        //--------------------condition for operators--------------------------//
                                        if (PaymentServices::getBucketOperators($OperatorID) === 1) {
                                            //-------------------Query for update Subscription----------------------//
                                            PaymentServices::updateBucketsUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message);
                                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                        } else {
                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                        }
                                    }
                                    //------------------------------------checking bucket for Ios Platform-----------------------------//

                                    if (PaymentServices::getWebBucket() === 1 && ($results[0]['UserPlatform'] === 'Web')) {
                                        // only IOS code
                                        if (PaymentServices::getBucketOperators($OperatorID) === 1) {
                                            PaymentServices::updateBucketsUserScription($Version, $Platform, $UserId, $ProductID, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Message);
                                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                        } else {
                                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                        };
                                    }
                                } else if ($Message === "Subscription-Disabled") {

                                    //---------------- bucket on
                                    $update = array(
                                        "UserPackageType" => null,
                                        "UserPackageIsRecurring" => null,
                                    );
                                    $bind = array(
                                        ":UserId" => $UserId,
                                    );
                                    $db->update('users', $update, 'UserId=:UserId', $bind);
                                    $db->run($sql, $bind);
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_RECURSION_DISABLED'))));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                }
                            } else {

                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    //--------------------------------get Bucket Operators----------------------------------------//

    public static function getBucketOperators($operatorId) {
        $results;
        $db = parent::getDataBase();
        if ($operatorId == '100003') {
            $sql = <<<STR
    		        SELECT BucketStatus				
                            FROM dynamicbucket 
                           WHERE FIND_IN_SET("100003",OperatorId)
STR;
            $results = $db->run($sql);
            if ($results) {
                $results = 1;
                return $results;
            } else {
                $results = 0;
                return $results;
            }
        } else if ($operatorId == '100002') {
            $sql = <<<STR
    		        SELECT BucketStatus				
                            FROM dynamicbucket 
                           WHERE FIND_IN_SET("100002",OperatorId)
STR;
            $results = $db->run($sql);
            if ($results) {
                $results = 1;
                return $results;
            } else {
                $results = 0;
                return $results;
            }
        } else {
            $results = 0;
            return $results;
        }
    }

    //-----------------------------get Bucket Status---------------------------------------//	
    public static function getBucketStatus() {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT BucketStatus				
                            FROM dynamicbucket                            
STR;
        $results = $db->run($sql);

        if ($results[0]['BucketStatus'] == 1) {
            $results = 1;
            return $results;
        } else {
            $results = 0;
            return $results;
        }
    }

    //-----------------------------get Android Bucket Status---------------------------------------//

    public static function getAndroidBucket() {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT BucketStatus				
                            FROM dynamicbucket 
                           WHERE FIND_IN_SET("Android",Platform)
STR;
        $results = $db->run($sql);
        if ($results) {
            $results = 1;
            return $results;
        } else {
            $results = 0;
            return $results;
        }
    }

    //-----------------------------get iphone Bucket Status---------------------------------------//

    public static function getIphoneBucket() {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT BucketStatus				
                            FROM dynamicbucket 
                           WHERE FIND_IN_SET("Ios",Platform)
STR;
        $results = $db->run($sql);

        if ($results) {
            $results = 1;
            return $results;
        } else {
            $results = 0;
            return $results;
        }
    }

    //-----------------------------get iphone Bucket Status---------------------------------------//

    public static function getWebBucket() {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT BucketStatus				
                            FROM dynamicbucket 
                           WHERE FIND_IN_SET("Web",Platform)
STR;
        $results = $db->run($sql);

        if ($results) {
            $results = 1;
            return $results;
        } else {
            $results = 0;
            return $results;
        }
    }

    public static function getSubsctiptionByUserId($UserId) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
            SELECT UserSubscriptionId				
                FROM usersubscriptions 
            WHERE UserSubscriptionUserId=:UserId AND UserSubscriptionIsTempUser=0 AND UserSubscriptionPackageId=10 Order by UserSubscriptionId DESC limit 0,1
STR;

        $bind = array(
            ":UserId" => $UserId,
        );

        $results = $db->run($sql, $bind);
        if (!empty($results)) {
            return $results[0]['UserSubscriptionId'];
        } else {
            $results = 0;
            return $results;
        }
    }

    //----------------get user package---------------------------------//
    public function getUserPackageByUser($UserId, $usersubscriptionId, $TransPackage) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT UserPackageId				
                            FROM userpackages 
                           WHERE UserId=:UserId AND UserSubscriptionId=:UserSubscriptionId AND PackageCode=:PackageCode
STR;

        $bind = array(
            ":UserId" => $UserId,
            ":UserSubscriptionId" => $usersubscriptionId,
            ":PackageCode" => $TransPackage,
        );

        //sizeof($cars);
        $results = $db->run($sql, $bind);
        if (!empty($results)) {
            $results = 1;
            return $results;
        } else {
            $results = 0;
            return $results;
        }
    }

    //--------------------------update user Packages---------------------------------------------------
    public static function updatesUsersActivPackages1($UserId, $PackageCode, $UserPackageId, $SubscriptionId) {

        $db = parent::getDataBase();
        $update = array(
            "UserId" => $UserId,
            "PackageCode" => $PackageCode,
            "UserSubscriptionId" => $SubscriptionId,
        );
        $bind = array(
            ":UserPackageId" => $UserPackageId,
        );
        return $db->update('userpackages', $update, 'UserPackageId=:UserPackageId', $bind);
    }

    public static function successfulPaymentTransactionTest(Request $request, Response $response) {
        $Version = "V1";
        $Language = "en";
        parent::setConfig($Language);
        $Platform = "dcb";

        $TransID = filter_var($request->getParsedBody()['transactionID'], FILTER_SANITIZE_STRING);
        $UserId = filter_var($request->getParsedBody()['userId'], FILTER_SANITIZE_STRING);
        $TransPackage = filter_var($request->getParsedBody()['productID'], FILTER_SANITIZE_STRING);
        $IsRecurring = filter_var($request->getParsedBody()['isRecurring'], FILTER_SANITIZE_STRING);
        $Status = filter_var($request->getParsedBody()['message'], FILTER_SANITIZE_STRING);
        $MSISDN = filter_var($request->getParsedBody()['msisdn'], FILTER_SANITIZE_STRING);
        $OperatorID = filter_var($request->getParsedBody()['operatorID'], FILTER_SANITIZE_STRING);

        // Create a log channel
        $log = new Logger('successfulPaymentTransaction');
        $log->pushHandler(new StreamHandler('../log/successfulPaymentTransaction_' . date("j.n.Y") . '.log', Logger::WARNING));

        // Add records to the log
        $terms = count($request->getParsedBody());
        $queryStr = '';
        foreach ($request->getParsedBody() as $field => $value) {
            $terms--;
            $queryStr .= $field . ' = ' . $value;
            if ($terms) {
                $queryStr .= ' AND ';
            }
        }
        $queryStr .= ' AND UserIP = ' . General::getUserIP();
        $log->warning($queryStr);
        //PaymentServices::checkIp();

        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Dcb':
                        case 'dcb':
                        case 'DCB':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {
                                if ($TransPackage === '1007') {
                                    $subscriptionDays = 18;
                                    $UserPaymentPackageName = "Premium";
                                } else if ($TransPackage === '1005') {
                                    $subscriptionDays = 18;
                                    $UserPaymentPackageName = "Movies";
                                } else if ($TransPackage === '1009') {
                                    $subscriptionDays = 18;
                                    $UserPaymentPackageName = "Premium + Movie";
                                } else if ($TransPackage === '1008') {
                                    $TransPackage = '1009';
                                    $subscriptionDays = 18;
                                    $UserPaymentPackageName = "Premium + Movie";
                                } else if ($TransPackage === '1003') {
                                    $TransPackage = '1009';
                                    $subscriptionDays = 21;
                                    $UserPaymentPackageName = "Premium + Movie";
                                } else if ($TransPackage === '1004') {
                                    $TransPackage = '1009';
                                    $subscriptionDays = 18;
                                    $UserPaymentPackageName = "Premium + Movie";
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                }

                                $usersubscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                $userpackage = PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackage);

                                if ($userpackage == 1) {
                                    PaymentServices::updateSubsciptions($subscriptionDays, $UserId, $TransPackage, $db);
                                } else if (PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, "0") === 1) {
                                    PaymentServices::updatreUserSubscriptions($UserId, $TransPackage, $subscriptionDays, $db);
                                } else {
                                    PaymentServices::insertUserSubscriptions($UserId, $subscriptionDays, $TransPackage, $db);
                                }
                                PaymentServices:: insertPaymentAndUpdateUserSystem($UserId, $Version, $Platform, $TransPackage, $UserPaymentStatus = 1, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Status, $UserPaymentPackageName, $db);

                                return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function checkIp() {
        if (!IPHelpers::checkPaymentIP()) {
            $log->warning('IP Blocked');
            return;
        }
    }

    public static function updateSubsciptions($dayIntervals, $UserId, $TransPackage, $db) {
        $subdays = date('Y-m-d H:i:s', time() + (86400 * $dayIntervals)); // increase subscription days 
        $sql = <<<STR
										UPDATE usersubscriptions
										SET usersubscriptions.UserSubscriptionExpiryDate = :totalDays
										WHERE usersubscriptions.UserSubscriptionUserId = :UserId
										AND UserSubscriptionIsTempUser=0;
STR;
        $bind = array(
            ":UserId" => $UserId,
            ":totalDays" => $subdays,
        );
        $db->run($sql, $bind);
        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
        PaymentServices::updatesUsersActivPackages1($UserId, $TransPackage, $userPackages, $subscriptionId);
    }

    public static function updatreUserSubscriptions($UserId, $TransPackage, $subscriptionDays, $db) {
        $subdays = date('Y-m-d H:i:s', time() + (86400 * $subscriptionDays)); // increase subscription days 
        $sql = <<<STR
                                        UPDATE usersubscriptions
                                        SET usersubscriptions.UserSubscriptionExpiryDate = :totalDays
										WHERE usersubscriptions.UserSubscriptionUserId = :UserId
										AND UserSubscriptionIsTempUser=0;
STR;
        $bind = array(
            ":UserId" => $UserId,
            ":totalDays" => $subdays,
        );

        $db->run($sql, $bind);
        $userPackages = PaymentServices::getUserPackages($UserId, "0");
        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
        PaymentServices::updatesUsersActivPackages1($UserId, $TransPackage, $userPackages, $subscriptionId);
    }

    public static function insertUserSubscriptions($UserId, $IncrementDays, $TransPackage, $db) {
        $insert = array(
            "UserSubscriptionUserId" => $UserId,
            "UserSubscriptionIsTempUser" => 0,
            "UserSubscriptionPackageId" => 10,
            "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
            "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent($IncrementDays . " days"),
            "UserSubscriptionMaxConcurrentConnections" => 6,
            "UserSubscriptionAutoRenew" => 0,
            "UserSubscriptionDetails" => NULL,
        );
        $db->insert("usersubscriptions", $insert);
        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
        $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
        PaymentServices::addSubscriptionUserNewPackages($UserId, $TransPackage, $subscriptionId[0]['UserSubscriptionId']);
    }

    public static function insertPaymentAndUpdateUserSystem($UserId, $Version, $Platform, $TransPackage, $UserPaymentStatus, $IsRecurring, $TransID, $MSISDN, $OperatorID, $Status, $UserPaymentPackageName, $db) {
        $bind = array(
            ":UserProfileUserId" => $UserId,
        );
        $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);


        $insert = array(
            "UserPaymentVersion" => $Version,
            "UserPaymentPlatform" => $Platform,
            "UserPaymentUserName" => $UserId,
            "UserPaymentPackageType" => $TransPackage,
            "UserPaymentStatus" => $UserPaymentStatus,
            "UserPaymentIsRecurring" => $IsRecurring,
            "UserPaymentTransactionId" => $TransID,
            "UserPaymentIP" => General::getUserIP(),
            "UserPaymentMobileNumber" => $MSISDN,
            "UserPaymentOperatorID" => $OperatorID,
            "UserPaymentMessage" => $Status,
            "UserPaymentPackageName" => $UserPaymentPackageName,
        );
        $db->insert("userpayments", $insert);

        $update = array(
            "UserIsFree" => 0,
            "UserPackageType" => $TransPackage,
            "UserActivePackageType" => $TransPackage,
            "UserPackageIsRecurring" => $IsRecurring,
        );
        $bind = array(
            ":UserId" => $UserId,
        );
        $db->update('users', $update, 'UserId=:UserId', $bind);
        //$log->warning('M_UPDATE');
        // return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
    }

    public static function getUserActiveSubscriptions($UserId) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT count(UserSubscriptionId) as UserSubscriptionId		
                            FROM usersubscriptions 
                           WHERE UserSubscriptionUserId=:UserSubscriptionUserId AND UserSubscriptionIsTempUser=1
STR;

        $bind = array(
            ":UserSubscriptionUserId" => $UserId,
        );
        $results = $db->run($sql, $bind);
        if ($results[0]['UserSubscriptionId'] == 1) {
            $results = 1;
            return $results;
        }
        if ($results[0]['UserSubscriptionId'] == 0) {
            $results = 0;
            return $results;
        }
        if ($results[0]['UserSubscriptionId'] == 2) {
            $results = 2;
            return $results;
        }
    }

    public static function successfulPaymentTransaction(Request $request, Response $response) {
        $Version = "V1";
        $Language = "en";
        parent::setConfig($Language);
        $Platform = "dcb";

        $TransID = filter_var($request->getParsedBody()['transactionID'], FILTER_SANITIZE_STRING);
        $UserId = filter_var($request->getParsedBody()['userId'], FILTER_SANITIZE_STRING);
        $TransPackage = filter_var($request->getParsedBody()['productID'], FILTER_SANITIZE_STRING);
        $IsRecurring = filter_var($request->getParsedBody()['isRecurring'], FILTER_SANITIZE_STRING);
        $Status = filter_var($request->getParsedBody()['message'], FILTER_SANITIZE_STRING);
        $MSISDN = filter_var($request->getParsedBody()['msisdn'], FILTER_SANITIZE_STRING);
        $OperatorID = filter_var($request->getParsedBody()['operatorID'], FILTER_SANITIZE_STRING);

        // Create a log channel
        $log = new Logger('successfulPaymentTransaction');
        $log->pushHandler(new StreamHandler('../log/successfulPaymentTransaction_' . date("j.n.Y") . '.log', Logger::WARNING));

        // Add records to the log
        $terms = count($request->getParsedBody());
        $queryStr = '';
        foreach ($request->getParsedBody() as $field => $value) {
            $terms--;
            $queryStr .= $field . ' = ' . $value;
            if ($terms) {
                $queryStr .= ' AND ';
            }
        }
        $queryStr .= ' AND UserIP = ' . General::getUserIP();
        $log->warning($queryStr);


        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Dcb':
                        case 'dcb':
                        case 'DCB':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);
                            if ($results) {

                                if ($TransPackage === '1007') {
                                    if (!IPHelpers::checkPaymentIP()) {
                                        $log->warning('IP Blocked');
                                        return;
                                    }
                                    $usersubscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                    $userpackage = PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackage);
                                    if ($userpackage === 1) {
                                        $sql = <<<STR
                                           UPDATE usersubscriptions
                                            SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 18 DAY),
                                            UserPackageCode=:UserPackageCode
					                        WHERE usersubscriptions.UserSubscriptionUserId = :UserId
					                        AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1007", $userPackages, $subscriptionId);
                                    } else if (PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackages = "0") === 1) {
                                        $sql = <<<STR
                                        UPDATE usersubscriptions
					                    SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 18 DAY),
                                        UserPackageCode=:UserPackageCode
                                        WHERE usersubscriptions.UserSubscriptionUserId = :UserId
                                        
                                        AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackages = "0");
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1007", $userPackages, $subscriptionId);
                                    } else {
                                        $insert = array(
                                            "UserSubscriptionUserId" => $UserId,
                                            "UserSubscriptionIsTempUser" => 0,
                                            "UserSubscriptionPackageId" => 10,
                                            "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
                                            "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent("18 days"),
                                            "UserSubscriptionMaxConcurrentConnections" => 6,
                                            "UserSubscriptionAutoRenew" => 0,
                                            "UserSubscriptionDetails" => NULL,
                                            "UserPackageCode"=>$TransPackage,
                                        );
                                        $db->insert("usersubscriptions", $insert);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
                                        $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
                                        PaymentServices::addSubscriptionUserNewPackages($UserId, $TransPackage, $subscriptionId[0]['UserSubscriptionId']);
                                    }

                                    $bind = array(
                                        ":UserProfileUserId" => $UserId,
                                    );
                                    $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);


                                    $insert = array(
                                        "UserPaymentVersion" => $Version,
                                        "UserPaymentPlatform" => $Platform,
                                        "UserPaymentUserName" => $UserId,
                                        "UserPaymentPackageType" => $TransPackage,
                                        "UserPaymentStatus" => 1,
                                        "UserPaymentIsRecurring" => $IsRecurring,
                                        "UserPaymentTransactionId" => $TransID,
                                        "UserPaymentIP" => General::getUserIP(),
                                        "UserPaymentMobileNumber" => $MSISDN,
                                        "UserPaymentOperatorID" => $OperatorID,
                                        "UserPaymentMessage" => $Status,
                                        "UserPaymentPackageName" => "Premium",
                                    );
                                    $db->insert("userpayments", $insert);

                                    $update = array(
                                        "UserIsFree" => 0,
                                        "UserPackageType" => $TransPackage,
                                        "UserActivePackageType" => $TransPackage,
                                        "UserPackageIsRecurring" => $IsRecurring,
                                    );
                                    $bind = array(
                                        ":UserId" => $UserId,
                                    );
                                    $db->update('users', $update, 'UserId=:UserId', $bind);
                                    $log->warning('M_UPDATE');
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                } else if ($TransPackage === '1009') { // 1009: Tapmad App/Web has weekly subscription for Rs. 15
                                    if (!IPHelpers::checkPaymentIP()) {
                                        $log->warning('IP Blocked');
                                        return;
                                    }
                                    $usersubscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                    $userpackage = PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackage);
                                    if ($userpackage === 1) {
                                        $sql = <<<STR
                                            UPDATE usersubscriptions
                                            SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 21 DAY)
                                            , UserPackageCode=:UserPackageCode
                                            WHERE usersubscriptions.UserSubscriptionUserId = :UserId

                                            AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1009", $userPackages, $subscriptionId);
                                    } else if (PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackages = "0") === 1) {
                                        $sql = <<<STR
                                            UPDATE usersubscriptions
                                            SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 21 DAY)
                                            , UserPackageCode=:UserPackageCode
                                            WHERE usersubscriptions.UserSubscriptionUserId = :UserId
                                            AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackages = "0");
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1009", $userPackages, $subscriptionId);
                                    } else {
                                        $insert = array(
                                            "UserSubscriptionUserId" => $UserId,
                                            "UserSubscriptionIsTempUser" => 0,
                                            "UserSubscriptionPackageId" => 10,
                                            "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
                                            "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent("21 days"),
                                            "UserSubscriptionMaxConcurrentConnections" => 6,
                                            "UserSubscriptionAutoRenew" => 0,
                                            "UserSubscriptionDetails" => NULL,
                                            "UserPackageCode"=>$TransPackage,

                                        );
                                        $db->insert("usersubscriptions", $insert);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
                                        $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
                                        PaymentServices::addSubscriptionUserNewPackages($UserId, $TransPackage, $subscriptionId[0]['UserSubscriptionId']);
                                    }

                                    $bind = array(
                                        ":UserProfileUserId" => $UserId,
                                    );
                                    $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);


                                    $insert = array(
                                        "UserPaymentVersion" => $Version,
                                        "UserPaymentPlatform" => $Platform,
                                        "UserPaymentUserName" => $UserId,
                                        "UserPaymentPackageType" => $TransPackage,
                                        "UserPaymentStatus" => 1,
                                        "UserPaymentIsRecurring" => $IsRecurring,
                                        "UserPaymentTransactionId" => $TransID,
                                        "UserPaymentIP" => General::getUserIP(),
                                        "UserPaymentMobileNumber" => $MSISDN,
                                        "UserPaymentOperatorID" => $OperatorID,
                                        "UserPaymentMessage" => $Status,
                                        "UserPaymentPackageName" => "Premium + Movie",
                                    );
                                    $db->insert("userpayments", $insert);

                                    $update = array(
                                        "UserIsFree" => 0,
                                        "UserPackageType" => $TransPackage,
                                        "UserActivePackageType" => $TransPackage,
                                        "UserPackageIsRecurring" => $IsRecurring,
                                    );
                                    $bind = array(
                                        ":UserId" => $UserId,
                                    );
                                    $db->update('users', $update, 'UserId=:UserId', $bind);
                                    $log->warning('M_UPDATE');
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                } else if ($TransPackage === '1005') {
                                    if (!IPHelpers::checkPaymentIP()) {
                                        $log->warning('IP Blocked');
                                        return;
                                    }
                                    $usersubscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                    $userpackage = PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackage);
                                    if ($userpackage === 1) {
                                        $sql = <<<STR
                                            UPDATE usersubscriptions
                                            SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 15 DAY)
                                            , UserPackageCode=:UserPackageCode
                                            WHERE usersubscriptions.UserSubscriptionUserId = :UserId
                                            AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1005", $userPackages, $subscriptionId);
                                    } else if (PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackages = "0") === 1) {
                                        $sql = <<<STR
                                            UPDATE usersubscriptions
                                            SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 15 DAY)
                                            , UserPackageCode=:UserPackageCode
                                            WHERE usersubscriptions.UserSubscriptionUserId = :UserId
                                            AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackages = "0");
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1005", $userPackages, $subscriptionId);
                                    } else {

                                        $insert = array(
                                            "UserSubscriptionUserId" => $UserId,
                                            "UserSubscriptionIsTempUser" => 0,
                                            "UserSubscriptionPackageId" => 10,
                                            "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
                                            "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent("15 days"),
                                            "UserSubscriptionMaxConcurrentConnections" => 6,
                                            "UserSubscriptionAutoRenew" => 0,
                                            "UserSubscriptionDetails" => NULL,
                                            "UserPackageCode"=>$TransPackage,
                                        );
                                        $db->insert("usersubscriptions", $insert);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
                                        $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
                                        PaymentServices::addSubscriptionUserNewPackages($UserId, $TransPackage, $subscriptionId[0]['UserSubscriptionId']);
                                    }

                                    $bind = array(
                                        ":UserProfileUserId" => $UserId,
                                    );
                                    $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);


                                    $insert = array(
                                        "UserPaymentVersion" => $Version,
                                        "UserPaymentPlatform" => $Platform,
                                        "UserPaymentUserName" => $UserId,
                                        "UserPaymentPackageType" => $TransPackage,
                                        "UserPaymentStatus" => 1,
                                        "UserPaymentIsRecurring" => $IsRecurring,
                                        "UserPaymentTransactionId" => $TransID,
                                        "UserPaymentIP" => General::getUserIP(),
                                        "UserPaymentMobileNumber" => $MSISDN,
                                        "UserPaymentOperatorID" => $OperatorID,
                                        "UserPaymentMessage" => $Status,
                                        "UserPaymentPackageName" => "Movies",
                                    );
                                    $db->insert("userpayments", $insert);

                                    $update = array(
                                        "UserIsFree" => 0,
                                        "UserPackageType" => $TransPackage,
                                        "UserActivePackageType" => $TransPackage,
                                        "UserPackageIsRecurring" => $IsRecurring,
                                    );
                                    $bind = array(
                                        ":UserId" => $UserId,
                                    );
                                    $db->update('users', $update, 'UserId=:UserId', $bind);
                                    $log->warning('M_UPDATE');
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                } else if ($TransPackage === '1008') { // 1008: Tapmad App/Web has monthly subscription for Rs. 50
                                    if (!IPHelpers::checkPaymentIP()) {
                                        $log->warning('IP Blocked');
                                        return;
                                    }
                                    $usersubscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                    $userpackage = PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackage);

                                    if ($userpackage == 1) {
                                        $sql = <<<STR
                                            UPDATE usersubscriptions
                                            SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 21 DAY)
                                            , UserPackageCode=:UserPackageCode
                                            WHERE usersubscriptions.UserSubscriptionUserId = :UserId
                                            AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage = "1009");
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1009", $userPackages, $subscriptionId);
                                    } else {
                                        $insert = array(
                                            "UserSubscriptionUserId" => $UserId,
                                            "UserSubscriptionIsTempUser" => 0,
                                            "UserSubscriptionPackageId" => 10,
                                            "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
                                            "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent("21 days"),
                                            "UserSubscriptionMaxConcurrentConnections" => 6,
                                            "UserSubscriptionAutoRenew" => 0,
                                            "UserSubscriptionDetails" => NULL,
                                            "UserPackageCode"=>$TransPackage,
                                        );
                                        $db->insert("usersubscriptions", $insert);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage);
                                        $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
                                        PaymentServices::addSubscriptionUserNewPackages($UserId, $TransPackage, $subscriptionId[0]['UserSubscriptionId']);
                                    }

                                    $bind = array(
                                        ":UserProfileUserId" => $UserId,
                                    );
                                    $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);


                                    $insert = array(
                                        "UserPaymentVersion" => $Version,
                                        "UserPaymentPlatform" => $Platform,
                                        "UserPaymentUserName" => $UserId,
                                        "UserPaymentPackageType" => $TransPackage,
                                        "UserPaymentStatus" => 1,
                                        "UserPaymentIsRecurring" => $IsRecurring,
                                        "UserPaymentTransactionId" => $TransID,
                                        "UserPaymentIP" => General::getUserIP(),
                                        "UserPaymentMobileNumber" => $MSISDN,
                                        "UserPaymentOperatorID" => $OperatorID,
                                        "UserPaymentMessage" => $Status,
                                        "UserPaymentPackageName" => "Premium + Movie",
                                    );
                                    $db->insert("userpayments", $insert);

                                    $update = array(
                                        "UserIsFree" => 0,
                                        "UserPackageType" => $TransPackage,
                                        "UserActivePackageType" => $TransPackage,
                                        "UserPackageIsRecurring" => $IsRecurring,
                                    );
                                    $bind = array(
                                        ":UserId" => $UserId,
                                    );
                                    $db->update('users', $update, 'UserId=:UserId', $bind);
                                    $log->warning('M_UPDATE');
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                } else if ($TransPackage === '1003') { // 1008: Tapmad App/Web has monthly subscription for Rs. 50
                                    if (!IPHelpers::checkPaymentIP()) {
                                        $log->warning('IP Blocked');
                                        return;
                                    }
                                    $usersubscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                    $userpackage = PaymentServices::getUserPackageByUser($UserId, $usersubscriptionId, $TransPackage);

                                    if ($userpackage == 1) {
                                        $sql = <<<STR
                                        UPDATE usersubscriptions
                                        SET usersubscriptions.UserSubscriptionExpiryDate = DATE_ADD(NOW(), INTERVAL 21 DAY)
                                        , UserPackageCode=:UserPackageCode
                                        WHERE usersubscriptions.UserSubscriptionUserId = :UserId
                                         AND UserSubscriptionIsTempUser=0;
STR;
                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":UserPackageCode"=> $TransPackage
                                        );

                                        $db->run($sql, $bind);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage = "1009");
                                        $subscriptionId = PaymentServices::getSubsctiptionByUserId($UserId);
                                        PaymentServices::updatesUsersActivPackages1($UserId, "1009", $userPackages, $subscriptionId);
                                    } else {
                                        $insert = array(
                                            "UserSubscriptionUserId" => $UserId,
                                            "UserSubscriptionIsTempUser" => 0,
                                            "UserSubscriptionPackageId" => 10,
                                            "UserSubscriptionStartDate" => date('Y-m-d H:i:s'),
                                            "UserSubscriptionExpiryDate" => PaymentServices::getIncrementDateWithCurrent("21 days"),
                                            "UserSubscriptionMaxConcurrentConnections" => 6,
                                            "UserSubscriptionAutoRenew" => 0,
                                            "UserSubscriptionDetails" => NULL,
                                            "UserPackageCode"=>$TransPackage,

                                        );
                                        $db->insert("usersubscriptions", $insert);
                                        $userPackages = PaymentServices::getUserPackages($UserId, $TransPackage = "1009");
                                        $subscriptionId = PaymentServices::getUserSubsciptionId($UserId);
                                        PaymentServices::addSubscriptionUserNewPackages($UserId, $TransPackage = "1009", $subscriptionId[0]['UserSubscriptionId']);
                                    }

                                    $bind = array(
                                        ":UserProfileUserId" => $UserId,
                                    );
                                    $results = $db->select("userprofiles", "UserProfileUserId=:UserProfileUserId", $bind);


                                    $insert = array(
                                        "UserPaymentVersion" => $Version,
                                        "UserPaymentPlatform" => $Platform,
                                        "UserPaymentUserName" => $UserId,
                                        "UserPaymentPackageType" => $TransPackage,
                                        "UserPaymentStatus" => 1,
                                        "UserPaymentIsRecurring" => $IsRecurring,
                                        "UserPaymentTransactionId" => $TransID,
                                        "UserPaymentIP" => General::getUserIP(),
                                        "UserPaymentMobileNumber" => $MSISDN,
                                        "UserPaymentOperatorID" => $OperatorID,
                                        "UserPaymentMessage" => $Status,
                                        "UserPaymentPackageName" => "Premium + Movie",
                                    );
                                    $db->insert("userpayments", $insert);

                                    $update = array(
                                        "UserIsFree" => 0,
                                        "UserPackageType" => $TransPackage,
                                        "UserActivePackageType" => $TransPackage,
                                        "UserPackageIsRecurring" => $IsRecurring,
                                    );
                                    $bind = array(
                                        ":UserId" => $UserId,
                                    );
                                    $db->update('users', $update, 'UserId=:UserId', $bind);
                                    $log->warning('M_UPDATE');
                                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                }
                                return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    //---------------------------------------unsubscribe with user package-----------------------------------
    //-------------------------------android use for unsubscription----------------------------------------------------//
    public static function unsubscribePackagePaymentTransaction(Request $request, Response $response) {



        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);

        $UserId = filter_var($request->getParsedBody()['UserId'], FILTER_SANITIZE_STRING);

        $PackageCode = filter_var($request->getParsedBody()['ProductId'], FILTER_SANITIZE_STRING);


        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserId" => $UserId,
                            );
                            $results = $db->select("users", "UserId=:UserId", $bind);


                            if ($results) {

                                //	echo PaymentServices::getActiveUserPackages($UserId,$PackageCode);
                                //	exit;
                                $bind = array(
                                    ":UserId" => $UserId,
                                    ":IsPackgeRecuring" => 1,
                                );
                                $UserPackagesArray = $db->select("userpackages", "UserId=:UserId AND IsPackgeRecuring=:IsPackgeRecuring", $bind);
                                // if ($userpackage = $results[0]['UserPackageType'] === null) {
                                if ($userpackage = $results[0]['UserPackageType'] === null && (!$UserPackagesArray)) {
                                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_USER_SUBSCRIBED'))));
                                }
                                if ($results[0]['UserPackageType'] === $PackageCode || ($UserPackagesArray)) {
                                    // 1007: Tapmad App/Web has weekly subscription for Rs. 15

                                    $data = array(
                                        'UserID' => $UserId,
                                        'ProductID' => $PackageCode,
                                        'MerchantID' => '1000004',
                                    );
                                    // use key 'http' even if you send the request to https://...
                                    $options = array(
                                        'http' => array(
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data),
                                        ),
                                    );
                                    $context = stream_context_create($options);
                                    $result = json_decode(file_get_contents('http://111.119.160.222:9991/dcb-integration/recursion/' . Config::$MerchantWebKey . '/WEB/unsubscribe', false, $context), true);

                                    if ($result['responseCode'] === '0000' || $result['responseCode'] === '0001') {
                                        $update = array(
                                            "UserPackageType" => null,
                                            "UserPackageIsRecurring" => null,
                                        );
                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );
                                        $db->update('users', $update, 'UserId=:UserId', $bind);
                                        // userActivePackages

                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":PackageCode" => $PackageCode,
                                        );

                                        $update = array(
                                            'IsPackgeRecuring' => 0,
                                        );
                                        $db->update('userpackages', $update, 'UserId=:UserId AND PackageCode=:PackageCode', $bind);

                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":PackageCode" => $PackageCode,
                                        );
                                        //

                                        $bind = array(
                                            ":UserId" => $UserId,
                                            ":IsPackgeRecuring" => 1,
                                        );
                                        $packageArray = $db->select("userpackages", "UserId=:UserId AND IsPackgeRecuring=:IsPackgeRecuring", $bind);

                                        if ($packageArray) {
                                            $UserPackageType = $packageArray[0]['PackageCode'];



                                            $update = array(
                                                "UserPackageType" => $UserPackageType,
                                                "UserActivePackageType" => $UserPackageType,
                                                'UserPackageIsRecurring' => 1,
                                            );
                                            $bind = array(
                                                ":UserId" => $UserId,
                                            );
                                            $db->update('users', $update, 'UserId=:UserId', $bind);
                                        }

                                        $bind = array(
                                            ":UserId" => $UserId,
                                        );

                                        $results = $db->select("users", "UserId=:UserId", $bind);
                                        $usersubscription = PaymentServices::getUserPackageSubscription($results);
                                        Format::formatResponseData($usersubscription);
                                        $userPackages = PaymentServices::getUserPackagesArray($results);
                                        $results = $results[0];

                                        Format::formatResponseData($userPackages);
                                        return General::getResponse($response->write(SuccessObject::getUserPackagesSubscriptionSuccessObject(User::getUserArray($results), User::getUserProfileArray($results), User::getUserSubscriptionArray($results), $usersubscription, $userPackages, Message::getMessage('M_RECURSION_DISABLED'))));
                                    } else if ($result['responseCode'] === '0001') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_USER'))));
                                    } else if ($result['responseCode'] === '0002') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PRODUCT'))));
                                    } else if ($result['responseCode'] === '0003') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
                                    } else if ($result['responseCode'] === '0004') {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_EXCEPTION_FORBIDDEN'))));
                                    } else {
                                        return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                    }
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_NO_UPDATE'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function deletePaymentUser(Request $request, Response $response) {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);

        $Mobile = filter_var($request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);

        $user['UserUsername'] = ltrim(filter_var(isset($request->getParsedBody()['MobileNo']) ? $request->getParsedBody()['MobileNo'] : NULL, FILTER_SANITIZE_STRING), '0');
        $Mobile = ltrim($user['UserUsername'], '+92');
        $Mobile = 'T' . $Mobile;
        // $db = parent::getDataBase();
        try {
            switch ($Version) {
                case 'V1':
                case 'v1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $db = parent::getDataBase();
                            $bind = array(
                                ":UserUsername" => $Mobile,
                            );
                            $results = $db->select("users", "UserUsername=:UserUsername", $bind);

                            if ($results) {
                                $UserId = $results[0]['UserId'];


                                // Delete user from userpackages table 
                                $bind = array(
                                    ":UserId" => $UserId,
                                );
                                $sql = <<<STR
                                    DELETE FROM userpackages  WHERE UserId=:UserId
STR;
                                $results = $db->run($sql, $bind); //sql 
                                // Delete User from usersubscriptions table  

                                $sql = <<<STR
                                    DELETE FROM usersubscriptions  WHERE UserSubscriptionUserId=:UserId
STR;
                                $results = $db->run($sql, $bind);


                                //Delete user from users table.
                                $sql = <<<STR
                                    DELETE FROM users  WHERE userId=:UserId
STR;
                                $results = $db->run($sql, $bind);
                                return General::getResponse($response->write(SuccessObject::returnMessage('User deleted successfully')));
                                // return "User deleted successfully";
                            } else {
                                return General::getResponse($response->write(SuccessObject::returnMessage('User not found')));
                            }
                    }
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        }
    }

    //----------------------------add new user Packages--------------------------------------------
    public static function addUserNewPackages($UserId, $PackageCode) {
        $db = parent::getDataBase();
        $update = array(
            "PackageCode" => $PackageCode,
            "UserId" => $UserId,
            "UserSubscriptionId" => NULL,
        );
        return $db->insert('userpackages', $update);
    }

    //--------------------------update user Packages---------------------------------------------------
    public static function updateUserPackages($PackageCode, $UserId, $UserPackageId) {
        $db = parent::getDataBase();
        $update = array(
            "PackageCode" => $PackageCode,
            "UserId" => $UserId,
        );
        $bind = array(
            ":UserPackageId" => $UserPackageId,
        );
        return $db->update('userpackages', $update, 'UserPackageId=:UserPackageId', $bind);
    }

    //--------------------------update user Packages---------------------------------------------------
    public static function updateUserActivPackages($UserId, $PackageCode, $UserPackageId, $SubscriptionId) {
        $db = parent::getDataBase();
        $update = array(
            "UserId" => $UserId,
            "PackageCode" => $PackageCode,
            "UserSubscriptionId" => $SubscriptionId,
        );
        $bind = array(
            ":UserPackageId" => $UserPackageId,
        );
        return $db->update('userpackages', $update, 'UserPackageId=:UserPackageId', $bind);
    }

    //----------------------get User Packages for Make Payment-------------------------//
    public static function getsActivesUserPackages($userId, $packageCode) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
					SELECT userpackages.UserPackageId AS UserPackageId ,usersubscriptions.UserSubscriptionId AS UserSubscriptionId 			
						FROM userpackages
					INNER JOIN usersubscriptions ON usersubscriptions.UserSubscriptionId=userpackages.UserSubscriptionId
					WHERE userpackages.UserId=:UserId AND userpackages.PackageCode=:PackageCode     		        
STR;

        $bind = array(
            ":UserId" => $userId,
            ":PackageCode" => $packageCode,
        );

        $results = $db->run($sql, $bind);
        if ($results[0]['UserPackageId'] != Null) {
            return $results;
        } else {
            $results = 0;
            return $results;
        }
    }

    //---------------------------------get User Subscription Id by User Id ------------------------------------
    public static function getUserSubsciptionId($userId) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT UserSubscriptionId				
                            FROM usersubscriptions 
                           WHERE UserSubscriptionUserId=:UserId AND UserSubscriptionIsTempUser=0 Order by UserSubscriptionId DESC limit 0,1
STR;

        $bind = array(
            ":UserId" => $userId,
        );

        $results = $db->run($sql, $bind);
        Format::formatResponseData($results);
        return $results;
    }

    //---------------------------------get User Subscription Id by User Id ------------------------------------
    public static function getUserSubsciptionIds($userId) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT UserSubscriptionId				
                            FROM usersubscriptions 
                           WHERE UserSubscriptionUserId=:UserId AND UserSubscriptionIsTempUser=0 Order by UserSubscriptionId ASC limit 1
STR;

        $bind = array(
            ":UserId" => $userId,
        );

        $results = $db->run($sql, $bind);
        Format::formatResponseData($results);
        return $results;
    }

    //---------------------------------get User Subscription Id by User Id ------------------------------------
    public static function getUserSubsciptionDates($userId) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT UserSubscriptionStartDate,UserSubscriptionExpiryDate				
                            FROM usersubscriptions 
                           WHERE UserSubscriptionUserId=:UserId AND UserSubscriptionIsTempUser=0 AND UserSubscriptionPackageId=10 Order by UserSubscriptionId ASC limit 1
STR;

        $bind = array(
            ":UserId" => $userId,
        );

        $results = $db->run($sql, $bind);
        Format::formatResponseData($results);
        return $results;
    }

    public static function addSubscriptionUserNewPackages($UserId, $PackageCode, $SubscriptionId) {
        $db = parent::getDataBase();
        $update = array(
            "PackageCode" => $PackageCode,
            "UserId" => $UserId,
            "UserSubscriptionId" => $SubscriptionId,
        );

        return $db->insert('userpackages', $update);
    }

    public static function countUserPackages($UserId) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT count(UserPackageId) as UserPackageId		
                            FROM userpackages 
                           WHERE UserId=:UserId
STR;

        $bind = array(
            ":UserId" => $UserId,
        );
        $results = $db->run($sql, $bind);
        //exit;
        if ($results[0]['UserPackageId'] == 0) {
            $results = 0;
            return $results;
        }
        if ($results[0]['UserPackageId'] == 1) {
            $results = 1;
            return $results;
        }
        if ($results[0]['UserPackageId'] == 2) {
            $results = 2;
            return $results;
        }


        //echo  $results[0]['UserPackages'];	        
    }

    public static function countUsersPackages($UserId, $PackageCode) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT count(UserPackageId) as UserPackageId		
                            FROM userpackages 
                           WHERE UserId=:UserId AND PackageCode=:PackageCode
STR;

        $bind = array(
            ":UserId" => $UserId,
            ":PackageCode" => $PackageCode,
        );
        $results = $db->run($sql, $bind);
        //exit;
        if ($results[0]['UserPackageId'] == 0) {
            $results = 0;
            return $results;
        }
        if ($results[0]['UserPackageId'] == 1) {
            $results = 1;
            return $results;
        }
        if ($results[0]['UserPackageId'] == 2) {
            $results = 2;
            return $results;
        }


        //echo  $results[0]['UserPackages'];	        
    }

//----------------------get User Packages for Make Payment-------------------------//
    public static function getActiveUserPackages($userId, $packageCode) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT UserPackageId				
                            FROM userpackages 
                           WHERE UserId=:UserId AND PackageCode=:PackageCode
STR;

        $bind = array(
            ":UserId" => $userId,
            ":PackageCode" => $packageCode,
        );

        $results = $db->run($sql, $bind);
        if ($results) {
            $results = 1;
            return $results;
        } else {
            $results = 0;
            return $results;
        }
    }

//-----------------------------get User Packages--------------------------------------------
    public static function getUserPackages($userId, $PackageCode) {
        $results;
        $db = parent::getDataBase();
        $sql = <<<STR
    		        SELECT UserPackageId				
                            FROM userpackages 
                           WHERE UserId=:UserId AND PackageCode=:PackageCode
STR;

        $bind = array(
            ":UserId" => $userId,
            ":PackageCode" => $PackageCode,
        );

        //sizeof($cars);
        $results = $db->run($sql, $bind);
        if (!empty($results)) {
            return $results[0]['UserPackageId'];
        } else {
            $results = 0;
            return $results;
        }
    }

//------------------------------get User Multiple Subscription---------------------------//
    public function getUserPackageSubscription($user) {
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
                AND usersubscriptions.UserSubscriptionIsTempUser=0 AND userpackages.PackageCode!=0
STR;
        $bind = array(
            ":UserId" => $user[0]['UserId']
        );
        // print_r ( $bind );
        $results = $db->run($sql, $bind);
        return $results;
    }

    public static function getUserPackagesArray($user) {
        $db = parent::getDataBase();
        $userPackagesArray;
        $sql = <<<STR
        SELECT PackageCode FROM userpackages
        WHERE userpackages.UserId=:UserId AND userpackages.PackageCode!=0									
STR;
        $bind = array(
            ":UserId" => $user[0]['UserId']
        );
        $userPackagesArray = $db->run($sql, $bind);
        return $userPackagesArray;
    }

    public static function bucketUserPackages($UserId, $packageCode = "0") {
        $db = parent::getDataBase();
        $bucketUserPackages;
        $sqlquery = <<<STR
        SELECT UserPackageId FROM userpackages
        WHERE UserId=:UserId AND PackageCode=:PackageCode								
STR;
        $bind = array(
            ":UserId" => $UserId,
            ":PackageCode" => $packageCode
        );
        $bucketUserPackages = $db->run($sqlquery, $bind);
        if (!empty($bucketUserPackages)) {
            $bucketUserPackages = 1;
            return $bucketUserPackages;
        } else {
            $bucketUserPackages = 0;
            return $bucketUserPackages;
        }
        //return $bucketUserPackages;
    }

}
