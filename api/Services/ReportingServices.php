<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class to Handle all Services Related to User
 *
 * @author SAIF UD DIN
 *        
 */
class ReportingServices extends Config
{

    public static function getTapmadStats(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        
        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Web':
                case 'web':
                case 'WEB':
                    $ResultArray = array();
                    $Sql = <<<STR
					SELECT COUNT(*) AS TotalRegisteredUsers
							FROM users
							WHERE UserId>208
STR;
                    $results = $db->run($Sql);
                    
                    $ResultArray['TotalRegisteredUsers'] = $results[0]['TotalRegisteredUsers'];
                    
                    $Sql = <<<STR
					SELECT COUNT(*) AS TotalPremiumUsers
							FROM users
							INNER JOIN userprofiles ON userprofiles.UserProfileUserId = users.UserId
							WHERE UserId>208
								 AND UserPackageType IS NOT NULL
STR;
                    $results = $db->run($Sql);
                    
                    $ResultArray['TotalPremiumUsers'] = $results[0]['TotalPremiumUsers'];
                    
                    $Sql = <<<STR
					SELECT COUNT(*) AS TotalUnsubscribedUsers
							FROM users
                            INNER JOIN userprofiles ON userprofiles.UserProfileUserId = users.UserId
							WHERE UserId>208 AND UserPackageType IS NULL
								AND UserId IN (SELECT UserPaymentUserName
												FROM userpayments
												GROUP BY UserPaymentUserName);
STR;
                    $results = $db->run($Sql);
                    
                    $ResultArray['TotalUnsubscribedUsers'] = $results[0]['TotalUnsubscribedUsers'];
                    
                    $Sql = <<<STR
					SELECT DATE(UserAddedDate) AS Date, COUNT(*) AS UserCount
							FROM users
							WHERE UserId>208
						    GROUP BY DATE(UserAddedDate);
STR;
                    $results = $db->run($Sql);
                    
                    $ResultArray['DailyRegisteredUsers'] = $results;
                    
                    $Sql = <<<STR
					SELECT DATE(UserAddedDate) AS Date, COUNT(*) AS UserCount
							FROM users
							WHERE UserId>208
								AND UserPackageType IS NOT NULL
						    GROUP BY DATE(UserAddedDate);
STR;
                    $results = $db->run($Sql);
                    
                    $ResultArray['DailyPremiumUsers'] = $results;
                    
                    return General::getResponse($response->write(json_encode($ResultArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
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

    public static function getCampaignStats(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $StartDate = $request->getAttribute('StartDate');
        $EndDate = $request->getAttribute('EndDate');
        
        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Web':
                case 'web':
                case 'WEB':
                    $ResultArray = array();
                    $Bind = array(
                        ':StartDate' => $StartDate,
                        ':EndDate' => $EndDate
                    );
                    $Sql = <<<STR
					SELECT DATE(UserPaymentStartDate) AS DATE, UserPaymentOperatorID AS OPERATOR, UserPaymentPackageType AS PACKAGE, COUNT(*) AS COUNT FROM winettv.userpayments
                    	WHERE UserPaymentUserName IN
                    			(SELECT CampaignDataUserId FROM winettv.campaigndata
                    					WHERE CampaignDataCampaignType IN ('38395','38395- Fifty')
                    						AND CampaignDataUserId IS NOT NULL
                                            AND CampaignDataAddedDate BETWEEN :StartDate AND :EndDate
                    					GROUP BY CampaignDataUserId)
                     		AND UserPaymentStatus = 1
                    		AND UserPaymentStartDate BETWEEN :StartDate AND :EndDate
                    	GROUP BY DATE(UserPaymentStartDate), UserPaymentOperatorID, UserPaymentPackageType
                        ORDER BY UserPaymentStartDate;
STR;
                    $results = $db->run($Sql, $Bind);
                    
                    $ResultArray['CampaignConversionsSuccess'] = $results;
                    
                    $Sql = <<<STR
					SELECT DATE(UserPaymentStartDate) AS DATE, UserPaymentOperatorID AS OPERATOR, UserPaymentPackageType AS PACKAGE, COUNT(*) AS COUNT FROM winettv.userpayments
                    	WHERE UserPaymentUserName IN
                    			(SELECT CampaignDataUserId FROM winettv.campaigndata
                    					WHERE CampaignDataCampaignType IN ('38395','38395- Fifty')
                    						AND CampaignDataUserId IS NOT NULL
                                            AND CampaignDataAddedDate BETWEEN :StartDate AND :EndDate
                    					GROUP BY CampaignDataUserId)
                     		AND UserPaymentStatus = 0
                    		AND UserPaymentStartDate BETWEEN :StartDate AND :EndDate
                    	GROUP BY DATE(UserPaymentStartDate), UserPaymentOperatorID, UserPaymentPackageType
                        ORDER BY UserPaymentStartDate;
STR;
                    $results = $db->run($Sql, $Bind);
                    
                    $ResultArray['CampaignConversionsFailure'] = $results;
                    
                    return General::getResponse($response->write(json_encode($ResultArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
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
    
    public static function getCampaignTxIDs(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $StartDate = $request->getAttribute('StartDate');
        $EndDate = $request->getAttribute('EndDate');
        
        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Web':
                case 'web':
                case 'WEB':
                    $ResultArray = array();
                    $Bind = array(
                        ':StartDate' => $StartDate,
                        ':EndDate' => $EndDate
                    );
                    $Sql = <<<STR
                    SELECT CampaignDataCampaignType AS CAMPAIGN,CampaignDataAddedDate AS DATE, CampaignDataOperator AS OPERATOR, CampaignDataCol9 AS TXID FROM winettv.campaigndata
					WHERE CampaignDataCampaignType IN ('Hate_Story_3', 'Forrest')
                        AND CampaignDataCol9 IS NOT NULL
                        AND CampaignDataAddedDate BETWEEN :StartDate AND :EndDate
					GROUP BY CampaignDataUserId
                    ORDER BY CampaignDataAddedDate DESC;
STR;
                    $results = $db->run($Sql, $Bind);
                    
                    $ResultArray['CampaignTxIDs'] = $results;
                    
                    return General::getResponse($response->write(json_encode($ResultArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
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
    
    public static function getBucketDetails(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $StartDate = $request->getAttribute('StartDate');
        $EndDate = $request->getAttribute('EndDate');
        
        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Web':
                case 'web':
                case 'WEB':
                    $ResultArray = array();
                    $Bind = array(
                        ':StartDate' => $StartDate,
                        ':EndDate' => $EndDate
                    );
                    $Sql = <<<STR
                    SELECT SUM(IF(UserPaymentStatus=2,1,0)) AS QueueBucket,
                    	SUM(IF(UserPaymentStatus=0,1,0)) AS FailedBucket,
                    	SUM(IF(UserPaymentStatus=1,1,0)) AS SuccessBucket
                    FROM (
                    	SELECT * FROM (
                            SELECT userpaymenttrials.*, userpayments.UserPaymentStatus FROM userpaymenttrials
                    		INNER JOIN userpayments ON userpayments.UserPaymentUserName = userpaymenttrials.TrialUserId
                    			AND DATE(userpayments.UserPaymentStartDate) > DATE(:StartDate)
                                AND UserPaymentStatus = 1
                    		WHERE TrialAddedDate BETWEEN :StartDate AND :EndDate
                    		GROUP BY userpaymenttrials.TrialUserId

                            UNION ALL

                    		SELECT userpaymenttrials.*, userpayments.UserPaymentStatus FROM userpaymenttrials
                    		INNER JOIN userpayments ON userpayments.UserPaymentUserName = userpaymenttrials.TrialUserId
                    			AND DATE(userpayments.UserPaymentStartDate) > DATE(:StartDate)
                                AND UserPaymentStatus = 0
                    		WHERE TrialAddedDate BETWEEN :StartDate AND :EndDate
                    		GROUP BY userpaymenttrials.TrialUserId
                    
                    		UNION ALL
                    
                    		SELECT userpaymenttrials.*, 2 AS UserPaymentStatus FROM userpaymenttrials
                    		WHERE TrialAddedDate BETWEEN :StartDate AND :EndDate
                    		GROUP BY userpaymenttrials.TrialUserId
                    	) AS innertable
                        group by innertable.TrialId
                    ) as outertable
STR;
                    $results = $db->run($Sql, $Bind);
                    
                    $ResultArray['TrialBucketDetails'] = $results;
                    
                    return General::getResponse($response->write(json_encode($ResultArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
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
}