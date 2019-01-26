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
class PaymentSolutions extends Config {
    
//--------------------------------------make new payment------------------------------//
public static function makePaymentsTransactions(Request $request, Response $response) {
   
    
   // ---------- Log Request With Parameters
       /*
        $log = new Logger('makePaymentTransaction');
        $log->pushHandler(new StreamHandler('../log/makePaymentTransaction' . date("j.n.Y") . '.log', Logger::DEBUG));
        $log->pushHandler(new FirePHPHandler());
        $terms = count($request->getParsedBody());
        $queryStr .= 'UserIP = ' . General::getUserIP() . ' <-> ';
        if ($request->getParsedBody()) {
            foreach ($request->getParsedBody() as $field => $value) {
                $terms --;
                $queryStr .= $field . ' = ' . $value;
                if ($terms) {
                    $queryStr .= ' <-> ';
                }
            }
        }
        $log->info($queryStr);
       
        */
         // ---------- END
       
         
       
        
        // ---------- Creating And Setting Variables Against Request Parameters
        // TODO : Move To Parameters Class
        $Params['Version'] = filter_var(isset($request->getParsedBody()['Version']) ? $request->getParsedBody()['Version'] : NULL, FILTER_SANITIZE_STRING);
        $Params['Language'] = filter_var(isset($request->getParsedBody()['Language']) ? $request->getParsedBody()['Language'] : NULL, FILTER_SANITIZE_STRING);
        $Params['Platform'] = filter_var(isset($request->getParsedBody()['Platform']) ? $request->getParsedBody()['Platform'] : NULL, FILTER_SANITIZE_STRING);
        $Params['UserIP'] = General::getUserIP();
        $Params['ProductId'] = filter_var(isset($request->getParsedBody()['ProductId']) ? $request->getParsedBody()['ProductId'] : NULL, FILTER_SANITIZE_STRING);
        $Params['TransactionType'] = filter_var(isset($request->getParsedBody()['TransactionType']) ? $request->getParsedBody()['TransactionType'] : NULL, FILTER_SANITIZE_STRING);
        $Params['UserId'] = filter_var(isset($request->getParsedBody()['UserId']) ? $request->getParsedBody()['UserId'] : NULL, FILTER_SANITIZE_STRING);
        
        $Params['ReferenceId'] = filter_var(isset($request->getParsedBody()['ReferenceId']) ? $request->getParsedBody()['ReferenceId'] : NULL, FILTER_SANITIZE_STRING);
        
        if(isset($request->getParsedBody()['MobileNo']))
        {
            $Params['MobileNo'] = filter_var( $request->getParsedBody()['MobileNo'], FILTER_SANITIZE_STRING);
            $Params['MobileNo'] = ltrim($Params['MobileNo'],'0');
            $Params['MobileNo'] = ltrim($Params['MobileNo'],'+92');
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
                    "309"
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
                    "349"
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
                )
            );
            
            if (in_array(substr($Params['MobileNo'], 0, 3), $OperatorPrefixes['Mobilink'])) {
                $Params['OperatorId'] = 100001;
            } else if (in_array(substr($Params['MobileNo'], 0, 3), $OperatorPrefixes['Telenor'])) {
                $Params['OperatorId'] = 100002;
            } else if (in_array(substr($Params['MobileNo'], 0, 3), $OperatorPrefixes['Zong'])) {
                $Params['OperatorId'] = 100003;
            }
        }
        // ---------- END
        
        // ---------- Setting Configurations
        parent::setConfig($Params['Language']);
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
        
        if (! $VersionValidator->validate($Params['Version'])) {
            $log->info('E_INVALID_PARAMS : Version');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (! $PlatformValidator->validate($Params['Platform'])) {
            $log->info('E_INVALID_PARAMS : Platform');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (! $ProductIdValidator->validate($Params['ProductId'])) {
            $log->info('E_INVALID_PARAMS : ProductId');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (! $TransactionTypeValidator->validate($Params['TransactionType'])) {
            $log->info('E_INVALID_PARAMS : TransactionType');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (! $UserIdValidator->validate($Params['UserId'])) {
            $log->info('E_INVALID_PARAMS : UserId');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (! $OperatorIdValidator->validate($Params['OperatorId'])) {
            $log->info('E_INVALID_PARAMS : OperatorId');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (isset($request->getParsedBody()['MobileNo']) && ! $MobileNoValidator->validate($Params['MobileNo'])) {
            $log->info('E_INVALID_PARAMS : MobileNo');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
        }
        if (isset($request->getParsedBody()['ReferenceId']) && ! $ReferenceIdValidator->validate($Params['ReferenceId'])) {
            $log->info('E_INVALID_PARAMS : ReferenceId');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PARAMS'))));
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
                                "PaymentLogIP" => General::getUserIP()
                            );
                            
                            $bind = array(
                                ":UserId" => $Params['UserId'],
                                ":UserPackageCode" => $Params['ProductId']
                            );
                            
                            $sql = <<<STR
                                SELECT UserPackageCode,IF ( TIMESTAMPDIFF(SECOND,NOW(),UserSubscriptionExpiryDate) > 0 , 0, 1 ) AS UserSubscriptionIsExpired
                                            FROM usernsubscriptions
                                    WHERE UserSubscriptionUserId=:UserId AND UserPackageCode =:UserPackageCode AND UserSubscriptionIsTempUser=0
STR;
                             
                            $results = $db->run($sql, $bind);                            
                            
                            
                            // $results = $db->select("users", "UserId=:UserId", $bind);
                            
                           
                                // If User Is Already Subscribed Then Don't Allow It To Make Another Transaction.
                    if (!$results[0]['UserSubscriptionIsExpired'] && $results[0]['UserPackageCode']===$Params['ProductId']) {
                        echo "Already Exists";
                        exit;
                        $log->info('E_NO_PAYMENT : USER ALREADY SUBSCRIBED');
                        $LogInsertArray['PaymentLogMessage'] = "USER ALREADY SUBSCRIBED";
                        $db->insert("userpaymentlogs", $LogInsertArray);
                        return General::getResponse($response->write(ErrorObject::getSingUserErrorObject(Message::getErrorMessage('You have already Subscribed.'))));
                    }
                    else{
                        echo "Not Found";
                        exit;
                        if($Params['OperatorId'] == '100001'){
                            $operatorName="Mobilink";
                        }else if($Params['OperatorId'] == '100002')
                        {
                           $operatorName="Telenor";
                        }else if($Params['OperatorId'] == '100003'){
                         $operatorName="Zong";
                        }else{
                          // Not Set
                        $log->info('E_NO_PAYMENT : INVALID OPERATOR');

                        $LogInsertArray['PaymentLogMessage'] = "INVALID OPERATOR";
                        $db->insert("userpaymentlogs", $LogInsertArray);

                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_PAYMENT'))));
                    }                                    
                               
                                    //--------------------make data for make payment-------------------------------------//                                 
                if($Params['OperatorId'] == '100002'){
                        $data = array(
                            'productID' => urlencode($Params['ProductId']),
                            'transactionType' => urlencode($Params['TransactionType']),
                            'referenceID' => urlencode($Params['ReferenceId']),
                            'userKey' => urlencode($Params['UserId']),
                            'operatorID' => urlencode($Params['OperatorId']),
                            );
                    }else{
                        $data = array(
                            'productID' => urlencode($Params['ProductId']),
                            'transactionType' => urlencode($Params['TransactionType']),
                            'mobileNo' => urlencode($Params['MobileNo']),
                            'userKey' => urlencode($Params['UserId']),
                            'operatorID' => urlencode($Params['OperatorId']),
                       );
                    }   
                    $data_string = json_encode($data);             
                    //-------------------------------call simpaisa DCB for Payment----------------------//
                    $ch = curl_init('http://111.119.160.222:9991/dcb-integration/transaction/' . Config::$MerchantSdkKey . '/SDK/make-payment');
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data_string)
                        ));
                                    
                        $result = json_decode(curl_exec($ch), true);
                        curl_close($ch);
    //--------------------------check sim paisa result--------------------------//                    
                    if ($result) {                                        
                        if ($result['status'] === 1) {                            
                            $log->info('M_PAYMENT : '.$operatorName. $result['message']);

                            $LogInsertArray['PaymentLogStatus'] = 1;
                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                            $db->insert("userpaymentlogs", $LogInsertArray);                           
                            //$results = $db->run($sql, $bind);
                            //$results = $results[0];
                            return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_PAYMENT'))));
                        } else {
                            $log->info('E_NO_PAYMENT : ' . $result['message']);

                            $LogInsertArray['PaymentLogMessage'] = $result['message'];
                            $db->insert("userpaymentlogs", $LogInsertArray);
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getErrorMessageAndCode($result['message'], $result['status']))));
                        }
                    } else {
                        $log->info('E_NO_PAYMENT : NO RESPONSE FROM SIMPAISA');
                        
                        $LogInsertArray['PaymentLogMessage'] = "NO RESPONSE FROM SIMPAISA";
                        $db->insert("userpaymentlogs", $LogInsertArray);
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_PAYMENT'))));
                    }        

                    }
                    break;
                default:
                    $log->info('E_INVALID_PLATFORM');
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                    break;
            }
                    break;
                default:
                    $log->info('E_INVALID_SERVICE_VERSION');
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (Exception $e) {
            $log->info('PHPException');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } catch (PDOException $e) {
            $log->info('PDOException');
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = NULL;
            $db = NULL;
        } 
    
    
    
    
    
}    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}



