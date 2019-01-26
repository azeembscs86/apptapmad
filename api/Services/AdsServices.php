<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class To Handle All Services Related To Advertisements
 *
 * @author SAIF UD DIN
 *
 */
class AdsServices extends Config
{
    public static function getVastAd(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $AdID = $request->getAttribute('AdID');
        $results = null;

        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();

            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'androidvast':
                        case 'AndroidVast':
                        case 'ANDROIDVAST':
                        case 'iosvast':
                        case 'IosVast':
                        case 'IOSVAST':
                            $sql = <<<STR
							SELECT cl.ClientAgencyId AS AdvertisementAgencyId,
									cam.CampaignClientId AS AdvertisementClientId,
									cam.campaignid AS AdvertisementCampaignId,
									ads.AdvertisementId,
									ads.AdvertisementName,
									ads.AdvertisementUrl,
									ads.IsJavascriptTag,
									ads.AdvertisementJavascriptTag,
									ads.AdvertisementCallToActionUrl,
									ads.AdvertisementCallToActionImageUrl,
									ads.AdvertisementViewsDone,
									ads.AdvertisementTargetViews,
									ads.AdvertisementTypeId,
									ads.AdvertisementMinAdsPerDay,
									ads.AdvertisementCpmRate,
									ads.IsAllowSkipAd,
									ads.AdvertisementShowSkipAfter,
									ads.IsAllowOnNonPlayer,
									ads.AdvertisementVastURL,
									ads.IsVast

									FROM advertisement ads

									INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
									INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
									INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

									WHERE ads.AdvertisementId = :AdID
STR;

                            // echo $sql;
                            $bind = array(
                                ":AdID" => $AdID,
                            );
                            $results = $db->run($sql, $bind);

                            if ($results) {
                                if (!$results[0]['IsVast']) {
                                    Format::formatResponseData($results);
                                    $Version = 'V1';
                                    $IsChannel = '0';
                                    $PackageId = '0';
                                    $ChannelOrVODId = '0';
                                    $setImpression = 'http://app.tapmad.com/api/updateAdViewAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $IsChannel . '/' . $PackageId . '/' . $ChannelOrVODId . '/' . $results[0]['AdvertisementTypeId'] . '/0/tapmad TV/https/view';
                                    // $results [0] ['AdvertisementCallToActionUrl']
                                    $setClickTracking = 'http://app.tapmad.com/api/updateAdClickAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $ChannelOrVODId . '/' . $IsChannel . '/' . $PackageId . '/' . $results[0]['AdvertisementTypeId'] . '/' . $Platform . '/0/tapmad TV/https/click';
                                    // $results [0] ['AdvertisementUrl']

                                    return General::getXMLResponse($response->write('<?xml version="1.0" encoding="UTF-8"?>
	<VAST version="3.0"><Ad id="' . $results[0]['AdvertisementId'] . '"><InLine><AdSystem>PI TELEVISION Ads</AdSystem><AdTitle><![CDATA[' . $results[0]['AdvertisementName'] . ']]></AdTitle><Impression><![CDATA[' . $setImpression . ']]></Impression><Creatives><Creative><Linear skipoffset="00:00:10"><Duration>00:00:30</Duration><VideoClicks><ClickThrough><![CDATA[' . $setClickTracking . ']]></ClickThrough><ClickTracking><![CDATA[' . $setClickTracking . ']]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" height="100" width="100"><![CDATA[' . $results[0]['AdvertisementUrl'] . ']]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST>'));
                                } else {
                                    return General::getXMLResponse($response->write(file_get_contents($results[0]['AdvertisementVastURL'])));
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'), 'banner')));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }
    public static function getRamadanTiming(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $ResponseData = null;
        $results = null;

        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();

            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'android':
                        case 'Android':
                        case 'ANDROID':
                            $sql = <<<STR
							SELECT Date,
								TimeIftarHanfia,
								TimeIftarJafria,
								TimeSeharHanfia,
								TimeSeharJafria,
								TIME_FORMAT(TimeIftarHanfia, "%h:%i") AS IftarHanfia,
								TIME_FORMAT(TimeIftarJafria, "%h:%i") AS IftarJafria,
								TIME_FORMAT(TimeSeharHanfia, "%h:%i") AS SeharHanfia,
								TIME_FORMAT(TimeSeharJafria, "%h:%i") AS SeharJafria
							FROM ramadantiming
							WHERE ramadantiming.Date IN ( :Date1, :Date2 );
							ORDER BY ramadantiming.Date DESC
STR;

                            // echo $sql;
                            $datetime = new DateTime('tomorrow');
                            $bind = array(
                                ":Date1" => date("Y-m-d"),
                                ":Date2" => $datetime->format('Y-m-d'),
                            );
                            $results = $db->run($sql, $bind);

                            // print_r($results);

                            if ($results) {
                                if (time() <= strtotime($results[0]['TimeSeharHanfia'])) {
                                    $ResponseData['RamadanBanner'] = "http://www.tapmad.com/ads/PepsiSeharTime/?hanfiaTime=" . $results[0]['SeharHanfia'] . "&jafriaTime=" . $results[0]['SeharJafria'];
                                } else if (time() <= strtotime($results[0]['TimeIftarJafria'])) {
                                    $ResponseData['RamadanBanner'] = "http://www.tapmad.com/ads/PepsiIftarTime/?hanfiaTime=" . $results[0]['IftarHanfia'] . "&jafriaTime=" . $results[0]['IftarJafria'];
                                } else if (isset($results[1]['SeharHanfia']) && isset($results[1]['SeharJafria'])) {
                                    $ResponseData['RamadanBanner'] = "http://www.tapmad.com/ads/PepsiSeharTime/?hanfiaTime=" . $results[1]['SeharHanfia'] . "&jafriaTime=" . $results[1]['SeharJafria'];
                                }
                                $ResponseData['Response'] = Message::getMessage('M_DATA');
                                return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                            } else {
                                $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                            }
                            break;
                        default:
                            $ResponseData['Response'] = Message::getMessage('E_INVALID_PLATFORM');
                            return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                            break;
                    }
                    break;
                default:
                    $ResponseData['Response'] = Message::getMessage('E_INVALID_SERVICE_VERSION');
                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                    break;
            }
        } catch (PDOException $e) {
            $ResponseData['Response'] = Message::getPDOMessage($e);
            return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
        } finally {
            $results = null;
            $db = null;
        }
    }
    public static function getAdURL(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $AdType = $request->getAttribute('AdType');
        $Gender = $request->getAttribute('Gender');
        $Age = $request->getAttribute('Age');
        $results = null;

        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();

            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                            $sql = <<<STR
							SELECT CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
											ELSE cl.ClientAgencyId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
											ELSE cl.ClientAgencyId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementAgencyId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
											ELSE cam.CampaignClientId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
											ELSE cam.CampaignClientId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementClientId,

		                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
											ELSE cam.campaignid END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
											ELSE cam.campaignid END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCampaignId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
											ELSE ads.AdvertisementId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
											ELSE ads.AdvertisementId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
											ELSE ads.AdvertisementName END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
											ELSE ads.AdvertisementName END

										ELSE NULL END
									ELSE NULL END AS AdvertisementName,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
											ELSE ads.AdvertisementUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
											ELSE ads.AdvertisementUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
											ELSE ads.IsJavascriptTag END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
											ELSE ads.IsJavascriptTag END

										ELSE NULL END
									ELSE NULL END AS IsJavascriptTag,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
											ELSE ads.AdvertisementJavascriptTag END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
											ELSE ads.AdvertisementJavascriptTag END

										ELSE NULL END
									ELSE NULL END AS AdvertisementJavascriptTag,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCallToActionUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionImageUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionImageUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCallToActionImageUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
											ELSE ads.AdvertisementViewsDone END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
											ELSE ads.AdvertisementViewsDone END

										ELSE NULL END
									ELSE NULL END AS AdvertisementViewsDone,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
											ELSE ads.AdvertisementTargetViews END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
											ELSE ads.AdvertisementTargetViews END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTargetViews,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
											ELSE ads.AdvertisementTypeId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
											ELSE ads.AdvertisementTypeId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTypeId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
											ELSE ads.AdvertisementMinAdsPerDay END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
											ELSE ads.AdvertisementMinAdsPerDay END

										ELSE NULL END
									ELSE NULL END AS AdvertisementMinAdsPerDay,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
											ELSE IFNULL(dac.AdvertisementCount,0) END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
											ELSE IFNULL(dac.AdvertisementCount,0) END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTodayCount,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
											ELSE ads.AdvertisementCpmRate END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
											ELSE ads.AdvertisementCpmRate END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCpmRate,




									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
											ELSE ads.IsAllowSkipAd END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
											ELSE ads.IsAllowSkipAd END

										ELSE NULL END
									ELSE NULL END AS IsAllowSkipAd,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
											ELSE ads.AdvertisementShowSkipAfter END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
											ELSE ads.AdvertisementShowSkipAfter END

										ELSE NULL END
									ELSE NULL END AS AdvertisementShowSkipAfter,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
											ELSE ads.IsAllowOnNonPlayer END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
											ELSE ads.IsAllowOnNonPlayer END

										ELSE NULL END
									ELSE NULL END AS IsAllowOnNonPlayer,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
											ELSE ads.AdvertisementVastURL END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
											ELSE ads.AdvertisementVastURL END

										ELSE NULL END
									ELSE NULL END AS AdvertisementVastURL,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
											ELSE ads.IsVast END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
											ELSE ads.IsVast END

										ELSE NULL END
									ELSE NULL END AS IsVast,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
											ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
											ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

										ELSE NULL END
									ELSE NULL END AS RandomPriority

									FROM
									(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active,
									advertisement ads

		                            INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
		                            INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
		                            INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

		                            LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
									LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
									LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

		                        WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )
									AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
									AND ads.AdvertisementTypeId = :AdType
								ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC
								LIMIT 1
STR;

                            // echo $sql;
                            $bind = array(
                                ":Age" => $Age,
                                ":Gender" => $Gender,
                                ":AdType" => $AdType,
                            );
                            $results = $db->run($sql, $bind);

                            if ($results) {
                                Format::formatResponseData($results);
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 9) {
                                }
                                // if (isset ( $results [0] ['AdvertisementId'] ) && $results [0] ['AdvertisementId'] === 49) {
                                // $ch = curl_init ();
                                // curl_setopt ( $ch, CURLOPT_URL, "https://advertyze.feekit.com/tracking/view/549ec4e1d6f7e7f8ca2948d0e1c18d3b1d923ed534aaf370a27481193067a1b53355cfa1" );
                                // curl_setopt ( $ch, CURLOPT_HEADER, 0 );
                                // $dump = curl_exec ( $ch );
                                // curl_close ( $ch );
                                // //var_dump($dump);
                                // }
                                return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'), 'banner')));
                            }
                            break;

                        case 'Web':
                        case 'web':
                            $sql = <<<STR
							SELECT CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
											ELSE cl.ClientAgencyId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
											ELSE cl.ClientAgencyId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementAgencyId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
											ELSE cam.CampaignClientId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
											ELSE cam.CampaignClientId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementClientId,

		                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
											ELSE cam.campaignid END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
											ELSE cam.campaignid END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCampaignId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
											ELSE ads.AdvertisementId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
											ELSE ads.AdvertisementId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
											ELSE ads.AdvertisementName END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
											ELSE ads.AdvertisementName END

										ELSE NULL END
									ELSE NULL END AS AdvertisementName,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
											ELSE ads.AdvertisementUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
											ELSE ads.AdvertisementUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCallToActionUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionImageUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionImageUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCallToActionImageUrl,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
											ELSE ads.IsVast END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
											ELSE ads.IsVast END

										ELSE NULL END
									ELSE NULL END AS IsVast,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
											ELSE ads.AdvertisementVastURL END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
											ELSE ads.AdvertisementVastURL END

										ELSE NULL END
									ELSE NULL END AS AdvertisementVastURL,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
											ELSE ads.IsJavascriptTag END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
											ELSE ads.IsJavascriptTag END

										ELSE NULL END
									ELSE NULL END AS IsJavascriptTag,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
											ELSE ads.AdvertisementJavascriptTag END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
											ELSE ads.AdvertisementJavascriptTag END

										ELSE NULL END
									ELSE NULL END AS AdvertisementJavascriptTag,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
											ELSE ads.AdvertisementViewsDone END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
											ELSE ads.AdvertisementViewsDone END

										ELSE NULL END
									ELSE NULL END AS AdvertisementViewsDone,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
											ELSE ads.AdvertisementTargetViews END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
											ELSE ads.AdvertisementTargetViews END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTargetViews,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
											ELSE ads.AdvertisementTypeId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
											ELSE ads.AdvertisementTypeId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTypeId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
											ELSE ads.AdvertisementMinAdsPerDay END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
											ELSE ads.AdvertisementMinAdsPerDay END

										ELSE NULL END
									ELSE NULL END AS AdvertisementMinAdsPerDay,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
											ELSE IFNULL(dac.AdvertisementCount,0) END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
											ELSE IFNULL(dac.AdvertisementCount,0) END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTodayCount,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
											ELSE ads.AdvertisementCpmRate END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
											ELSE ads.AdvertisementCpmRate END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCpmRate,




									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
											ELSE ads.IsAllowSkipAd END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
											ELSE ads.IsAllowSkipAd END

										ELSE NULL END
									ELSE NULL END AS IsAllowSkipAd,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
											ELSE ads.AdvertisementShowSkipAfter END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
											ELSE ads.AdvertisementShowSkipAfter END

										ELSE NULL END
									ELSE NULL END AS AdvertisementShowSkipAfter,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
											ELSE ads.IsAllowOnNonPlayer END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
											ELSE ads.IsAllowOnNonPlayer END

										ELSE NULL END
									ELSE NULL END AS IsAllowOnNonPlayer,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
											ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
											ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

										ELSE NULL END
									ELSE NULL END AS RandomPriority

									FROM
									(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active,
									advertisement ads

		                            INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
		                            INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
		                            INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

		                            LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
									LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
									LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

		                        WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )
									AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
									AND ads.AdvertisementTypeId = :AdType
								ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC
									LIMIT 1
STR;

                            // echo $sql;
                            $bind = array(
                                ":Age" => $Age,
                                ":Gender" => $Gender,
                                ":AdType" => $AdType,
                            );
                            $results = $db->run($sql, $bind);

                            if ($results) {
                                Format::formatResponseData($results);

                                $AgencyId = $results[0]['AdvertisementAgencyId'];
                                $ClientId = $results[0]['AdvertisementClientId'];
                                $CampaignId = $results[0]['AdvertisementCampaignId'];
                                $AdId = $results[0]['AdvertisementId'];
                                $UserId = 0;
                                $UserIp = General::getUserIP();
                                $ChannelOrVODId = 0;
                                $IsChannel = 0;
                                $PackageId = 0;
                                $CurrentTime = date("Y-m-d H:i:s");
                                $DeviceType = $Platform;
                                $DeviceId = 0;
                                $Source = 'tapmad TV';
                                $VideoSourceUrl = 'httpsss';
                                $StatisticType = 'view';
                                $IsTempUser = 1;

                                AdsServices::localUpdateAdViewAndStats($AgencyId, $ClientId, $CampaignId, $AdId, $UserId, $UserIp, $ChannelOrVODId, $IsChannel, $PackageId, $AdType, $CurrentTime, $DeviceType, $DeviceId, $Source, $VideoSourceUrl, $StatisticType, $IsTempUser);

                                return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'), 'banner')));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v2':
                case 'V2':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                            $sql = <<<STR
							SELECT * FROM (
								SELECT CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
												ELSE cl.ClientAgencyId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
												ELSE cl.ClientAgencyId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementAgencyId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
												ELSE cam.CampaignClientId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
												ELSE cam.CampaignClientId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementClientId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
												ELSE cam.campaignid END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
												ELSE cam.campaignid END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCampaignId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
												ELSE ads.AdvertisementId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
												ELSE ads.AdvertisementId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
												ELSE ads.AdvertisementName END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
												ELSE ads.AdvertisementName END

											ELSE NULL END
										ELSE NULL END AS AdvertisementName,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
												ELSE ads.AdvertisementUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
												ELSE ads.AdvertisementUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
												ELSE ads.IsJavascriptTag END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
												ELSE ads.IsJavascriptTag END

											ELSE NULL END
										ELSE NULL END AS IsJavascriptTag,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												ELSE ads.AdvertisementJavascriptTag END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												ELSE ads.AdvertisementJavascriptTag END

											ELSE NULL END
										ELSE NULL END AS AdvertisementJavascriptTag,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCallToActionUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionImageUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionImageUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCallToActionImageUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
												ELSE ads.AdvertisementViewsDone END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
												ELSE ads.AdvertisementViewsDone END

											ELSE NULL END
										ELSE NULL END AS AdvertisementViewsDone,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
												ELSE ads.AdvertisementTargetViews END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
												ELSE ads.AdvertisementTargetViews END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTargetViews,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
												ELSE ads.AdvertisementTypeId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
												ELSE ads.AdvertisementTypeId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTypeId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												ELSE ads.AdvertisementMinAdsPerDay END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												ELSE ads.AdvertisementMinAdsPerDay END

											ELSE NULL END
										ELSE NULL END AS AdvertisementMinAdsPerDay,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												ELSE IFNULL(dac.AdvertisementCount,0) END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												ELSE IFNULL(dac.AdvertisementCount,0) END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTodayCount,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
												ELSE ads.AdvertisementCpmRate END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
												ELSE ads.AdvertisementCpmRate END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCpmRate,




										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
												ELSE ads.IsAllowSkipAd END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
												ELSE ads.IsAllowSkipAd END

											ELSE NULL END
										ELSE NULL END AS IsAllowSkipAd,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												ELSE ads.AdvertisementShowSkipAfter END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												ELSE ads.AdvertisementShowSkipAfter END

											ELSE NULL END
										ELSE NULL END AS AdvertisementShowSkipAfter,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												ELSE ads.IsAllowOnNonPlayer END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												ELSE ads.IsAllowOnNonPlayer END

											ELSE NULL END
										ELSE NULL END AS IsAllowOnNonPlayer,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
												ELSE ads.AdvertisementVastURL END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
												ELSE ads.AdvertisementVastURL END

											ELSE NULL END
										ELSE NULL END AS AdvertisementVastURL,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
												ELSE ads.IsVast END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
												ELSE ads.IsVast END

											ELSE NULL END
										ELSE NULL END AS IsVast,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

											ELSE NULL END
										ELSE NULL END AS RandomPriority

										FROM
										(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active,
										advertisement ads

										INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
										INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
										INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

										LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
										LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
										LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

									WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )
										AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
										AND ads.AdvertisementTypeId = :AdType
									ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC
									LIMIT 1

							) AS AdRow
							WHERE AdRow.AdvertisementAgencyId IS NOT NULL
								AND AdRow.AdvertisementClientId IS NOT NULL
								AND AdRow.AdvertisementCampaignId IS NOT NULL
								AND AdRow.AdvertisementId IS NOT NULL
STR;

                            // echo $sql;
                            $bind = array(
                                ":Age" => $Age,
                                ":Gender" => $Gender,
                                ":AdType" => $AdType,
                            );
                            $results = $db->run($sql, $bind);

                            if ($results) {
                                Format::formatResponseData($results);
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 9) {
                                    if (!$results[0]['IsVast']) {
                                        $results[0]['IsVast'] = true;
                                        $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                    }
                                }

                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 1) {
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));

                                    // $current_time = date ( 'h:i A' );
                                    // $sunrise = "8:00 am";
                                    // $sunset = "11:00 pm";
                                    // $date1 = DateTime::createFromFormat ( 'H:i a', $current_time );
                                    // $date2 = DateTime::createFromFormat ( 'H:i a', $sunrise );
                                    // $date3 = DateTime::createFromFormat ( 'H:i a', $sunset );
                                    // if ($date1 < $date2 || $date1 > $date3) {
                                    // $results [0] ['AdvertisementAgencyId'] = 4;
                                    // $results [0] ['AdvertisementClientId'] = 37;
                                    // $results [0] ['AdvertisementCampaignId'] = 33;
                                    // $results [0] ['AdvertisementId'] = 61;
                                    // $results [0] ['AdvertisementName'] = "Bonanza Pre Roll";
                                    // $results [0] ['AdvertisementUrl'] = "https://bs.serving-sys.com/Serving?cn=display&c=23&pl=VAST&pli=19956257&PluID=0&pos=4695&ord=[timestamp]&cim=1";
                                    // $results [0] ['AdvertisementCallToActionUrl'] = null;
                                    // $results [0] ['AdvertisementCallToActionImageUrl'] = null;
                                    // $results [0] ['IsVast'] = true;
                                    // $results [0] ['AdvertisementVastURL'] = "https://bs.serving-sys.com/Serving?cn=display&c=23&pl=VAST&pli=19956257&PluID=0&pos=4695&ord=[timestamp]&cim=1";
                                    // } else {
                                    // $results [0] ['AdvertisementAgencyId'] = 4;
                                    // $results [0] ['AdvertisementClientId'] = 37;
                                    // $results [0] ['AdvertisementCampaignId'] = 33;
                                    // $results [0] ['AdvertisementId'] = 61;
                                    // $results [0] ['AdvertisementName'] = "Bonanza Pre Roll";
                                    // $results [0] ['AdvertisementUrl'] = "https://bs.serving-sys.com/Serving?cn=display&c=23&pl=VAST&pli=19956257&PluID=0&pos=4695&ord=[timestamp]&cim=1";
                                    // $results [0] ['AdvertisementCallToActionUrl'] = null;
                                    // $results [0] ['AdvertisementCallToActionImageUrl'] = null;
                                    // $results [0] ['IsVast'] = true;
                                    // $results [0] ['AdvertisementVastURL'] = "https://bs.serving-sys.com/Serving?cn=display&c=23&pl=VAST&pli=19956257&PluID=0&pos=4695&ord=[timestamp]&cim=1";
                                    // }
                                }

                                // if (isset ( $results [0] ['AdvertisementId'] ) && $results [0] ['AdvertisementId'] === 49) {
                                // $ch = curl_init ();
                                // curl_setopt ( $ch, CURLOPT_URL, "https://advertyze.feekit.com/tracking/view/549ec4e1d6f7e7f8ca2948d0e1c18d3b1d923ed534aaf370a27481193067a1b53355cfa1" );
                                // curl_setopt ( $ch, CURLOPT_HEADER, 0 );
                                // $dump = curl_exec ( $ch );
                                // curl_close ( $ch );
                                // //var_dump($dump);
                                // }
                                if (isset($results[0]['AdvertisementId']) && $results[0]['AdvertisementId'] === 17) {
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));
                                }
                                return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));
                            }
                            break;
                        case 'androidvast':
                        case 'AndroidVast':
                        case 'ANDROIDVAST':
                            $sql = <<<STR
							SELECT CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
											ELSE cl.ClientAgencyId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
											ELSE cl.ClientAgencyId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementAgencyId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
											ELSE cam.CampaignClientId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
											ELSE cam.CampaignClientId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementClientId,

		                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
											ELSE cam.campaignid END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
											ELSE cam.campaignid END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCampaignId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
											ELSE ads.AdvertisementId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
											ELSE ads.AdvertisementId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
											ELSE ads.AdvertisementName END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
											ELSE ads.AdvertisementName END

										ELSE NULL END
									ELSE NULL END AS AdvertisementName,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
											ELSE ads.AdvertisementUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
											ELSE ads.AdvertisementUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
											ELSE ads.IsJavascriptTag END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsJavascriptTag ELSE NULL END
											ELSE ads.IsJavascriptTag END

										ELSE NULL END
									ELSE NULL END AS IsJavascriptTag,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
											ELSE ads.AdvertisementJavascriptTag END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementJavascriptTag ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementJavascriptTag ELSE NULL END
											ELSE ads.AdvertisementJavascriptTag END

										ELSE NULL END
									ELSE NULL END AS AdvertisementJavascriptTag,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCallToActionUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionImageUrl END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
											ELSE ads.AdvertisementCallToActionImageUrl END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCallToActionImageUrl,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
											ELSE ads.AdvertisementViewsDone END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
											ELSE ads.AdvertisementViewsDone END

										ELSE NULL END
									ELSE NULL END AS AdvertisementViewsDone,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
											ELSE ads.AdvertisementTargetViews END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
											ELSE ads.AdvertisementTargetViews END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTargetViews,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
											ELSE ads.AdvertisementTypeId END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
											ELSE ads.AdvertisementTypeId END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTypeId,

									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
											ELSE ads.AdvertisementMinAdsPerDay END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
											ELSE ads.AdvertisementMinAdsPerDay END

										ELSE NULL END
									ELSE NULL END AS AdvertisementMinAdsPerDay,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
											ELSE IFNULL(dac.AdvertisementCount,0) END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
											ELSE IFNULL(dac.AdvertisementCount,0) END

										ELSE NULL END
									ELSE NULL END AS AdvertisementTodayCount,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
											ELSE ads.AdvertisementCpmRate END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
											ELSE ads.AdvertisementCpmRate END

										ELSE NULL END
									ELSE NULL END AS AdvertisementCpmRate,




									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
											ELSE ads.IsAllowSkipAd END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
											ELSE ads.IsAllowSkipAd END

										ELSE NULL END
									ELSE NULL END AS IsAllowSkipAd,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
											ELSE ads.AdvertisementShowSkipAfter END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
											ELSE ads.AdvertisementShowSkipAfter END

										ELSE NULL END
									ELSE NULL END AS AdvertisementShowSkipAfter,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
											ELSE ads.IsAllowOnNonPlayer END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
											ELSE ads.IsAllowOnNonPlayer END

										ELSE NULL END
									ELSE NULL END AS IsAllowOnNonPlayer,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
											ELSE ads.AdvertisementVastURL END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
											ELSE ads.AdvertisementVastURL END

										ELSE NULL END
									ELSE NULL END AS AdvertisementVastURL,



									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
											ELSE ads.IsVast END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
											ELSE ads.IsVast END

										ELSE NULL END
									ELSE NULL END AS IsVast,


									CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
										CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
											ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

										WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

											CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
												THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
													CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
													CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
											ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

										ELSE NULL END
									ELSE NULL END AS RandomPriority

									FROM
									(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active,
									advertisement ads

		                            INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
		                            INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
		                            INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

		                            LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
									LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
									LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

		                        WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )
									AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
									AND ads.AdvertisementTypeId = :AdType
								ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC
								LIMIT 1
STR;

                            // echo $sql;
                            $bind = array(
                                ":Age" => $Age,
                                ":Gender" => $Gender,
                                ":AdType" => $AdType,
                            );
                            $results = $db->run($sql, $bind);

                            if ($results) {
                                Format::formatResponseData($results);
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 9) {
                                    if (!$results[0]['IsVast']) {
                                        $Version = 'V1';
                                        $IsChannel = '0';
                                        $PackageId = '0';
                                        $ChannelOrVODId = '0';

                                        $setImpression = 'http://api.tapmad.com/api/updateAdViewAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $IsChannel . '/' . $PackageId . '/' . $ChannelOrVODId . '/' . $results[0]['AdvertisementTypeId'] . '/0/tapmad TV/https/view';
                                        // $results [0] ['AdvertisementCallToActionUrl']
                                        $setClickTracking = 'http://api.tapmad.com/api/updateAdClickAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $ChannelOrVODId . '/' . $IsChannel . '/' . $PackageId . '/' . $results[0]['AdvertisementTypeId'] . '/' . $Platform . '/0/tapmad TV/https/click';
                                        // $results [0] ['AdvertisementUrl']

                                        return General::getXMLResponse($response->write('<?xml version="1.0" encoding="UTF-8"?>
<VAST version="3.0"><Ad id="' . $results[0]['AdvertisementId'] . '"><InLine><AdSystem>PI TELEVISION Ads</AdSystem><AdTitle><![CDATA[' . $results[0]['AdvertisementName'] . ']]></AdTitle><Impression><![CDATA[' . $setImpression . ']]></Impression><Creatives><Creative><Linear skipoffset="00:00:10"><Duration>00:00:30</Duration><VideoClicks><ClickThrough><![CDATA[' . $results[0]['AdvertisementCallToActionUrl'] . ']]></ClickThrough><ClickTracking><![CDATA[' . $setClickTracking . ']]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" height="100" width="100"><![CDATA[' . $results[0]['AdvertisementUrl'] . ']]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST>'));
                                    } else {
                                        return General::getXMLResponse($response->write(file_get_contents($results[0]['AdvertisementVastURL'])));
                                    }
                                }
                            } else {
                                return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'), 'banner')));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }
    private static function localUpdateAdViewAndStats($AgencyId, $ClientId, $CampaignId, $AdId, $UserId, $UserIp, $ChannelOrVODId, $IsChannel, $PackageId, $AdType, $CurrentTime, $DeviceType, $DeviceId, $Source, $VideoSourceUrl, $StatisticType, $IsTempUser)
    {
        $Result = null;
        try {
            $db = parent::getDataBase();
            // Inserting Data Of View Into AdvertisementDetail
            $sql = <<<STR
					INSERT INTO advertisementdetail
							(AdvertisementDetailAgencyId, AdvertisementDetailClientId, AdvertisementDetailCampaignId, AdvertisementDetailAdvertisementId,
							AdvertisementDetailUserId, AdvertisementDetailUserIp, AdvertisementDetailChannelOrVODId, isChannel, AdvertisementDetailPackageId,
							AdvertisementDetailAdvertisementType, AdvertisementDetailTime, AdvertisementDetailDeviceType,
							AdvertisementDetailDeviceId, AdvertisementDetailSource, AdvertisementDetailVideoSourceUrl, AdvertisementDetailType, IsTempUser)
					VALUES(:AgencyId,:ClientId,:CampaignId,:AdId,
							:UserId,:UserIp,:ChannelOrVODId,:IsChannel,:PackageId,
							:AdType,:CurrentTime,:DeviceType,
							:DeviceId,:Source,:VideoSourceUrl,:StatisticType, :IsTempUser)
STR;
            $bind = array(
                ":AgencyId" => $AgencyId,
                ":ClientId" => $ClientId,
                ":CampaignId" => $CampaignId,
                ":AdId" => $AdId,
                ":UserId" => $UserId,
                ":UserIp" => $UserIp,
                ":ChannelOrVODId" => $ChannelOrVODId,
                ":IsChannel" => $IsChannel,
                ":PackageId" => $PackageId,
                ":AdType" => $AdType,
                ":CurrentTime" => $CurrentTime,
                ":DeviceType" => $DeviceType,
                ":DeviceId" => $DeviceId,
                ":Source" => $Source,
                ":VideoSourceUrl" => $VideoSourceUrl,
                ":StatisticType" => $StatisticType,
                ":IsTempUser" => $IsTempUser,
            );
            // $Result ['AdvertisementDetailInsert'] = $db->run ( $sql, $bind );
            $Result['AdvertisementDetailInsert'] = 0;

            // Updating Channel OR VOD Viewdone
            if ($IsChannel) {
                $sql = <<<STR
						UPDATE channels
						SET channels.ChannelTotalViews    =  channels.ChannelTotalViews + 1
						WHERE channels.ChannelId = :ChannelOrVODId
STR;
            } else {
                $sql = <<<STR
						UPDATE videoondemand
						SET videoondemand.VideoOnDemandTotalViews =  videoondemand.VideoOnDemandTotalViews + 1
						WHERE videoondemand.VideoOnDemandId = :ChannelOrVODId
STR;
            }

            $bind = array(
                ":ChannelOrVODId" => $ChannelOrVODId,
            );
            $Result['ChannelOrVODViewsDoneUpdate'] = $db->run($sql, $bind);
            // $Result ['ChannelOrVODViewsDoneUpdate'] = 0;

            // Updating Advertisement Viewsdone
            $sql = <<<STR
					UPDATE advertisement
					SET advertisement.AdvertisementViewsDone = advertisement.AdvertisementViewsDone + 1
					WHERE advertisement.AdvertisementId = :AdId AND advertisement.AdvertisementCampaignId = :CampaignId
STR;
            $bind = array(
                ":AdId" => $AdId,
                ":CampaignId" => $CampaignId,
            );
            // $Result ['AdvertisementViewsDoneUpdate'] = $db->run ( $sql, $bind );
            $Result['AdvertisementViewsDoneUpdate'] = 0;

            // Updating Advertisement Daily Ad Count
            $Result['AdvertisementDailyCountUpdate'] = 0;
            $CacheCountLimit = 10;
            $Id = 'AdDailyCount' . $CampaignId . $AdId;

            if (AdsServices::cacheAdCount($Id) >= $CacheCountLimit) {
                // Update Query
                $sql = <<<STR
				INSERT INTO advertisementdailycount (AdvertisementId, AdvertisementCountDate, AdvertisementCount)
						VALUES( :AdId, CURRENT_DATE(), 10 )
						ON DUPLICATE KEY UPDATE AdvertisementCount = AdvertisementCount + 10
STR;
                $bind = array(
                    ":AdId" => $AdId,
                );
                $Result['AdvertisementDailyCountUpdate'] = $db->run($sql, $bind);
                AdsServices::resetCacheAdCount($Id);
            }

            // Updating Advertisement's Campaign Balance
            $sql = <<<STR
					UPDATE campaign,advertisement
							SET campaign.CampaignBalance = campaign.CampaignBalance - (advertisement.AdvertisementCpmRate/1000)
							WHERE campaign.CampaignId = advertisement.AdvertisementCampaignId AND advertisement.AdvertisementId = :AdId
STR;
            $bind = array(
                ":AdId" => $AdId,
            );
            // $Result ['CampaignBalanceUpdate'] = $db->run ( $sql, $bind );
            $Result['CampaignBalanceUpdate'] = 0;

            // Updating Campaign's Static OR Dynamic Advertisement Balance
            $sql = <<<STR
					SELECT advertisementtype.AdvertisementTypeId
							FROM advertisementtype
							WHERE advertisementtype.AdvertisementTypeCategory='dynamic'
STR;
            $AdTypes = $db->run($sql);
            if (in_array($AdType, $AdTypes)) {
                $sql = <<<STR
						UPDATE campaign,advertisement
								SET campaign.CampaignDynamicAdBalance = campaign.CampaignDynamicAdBalance - (advertisement.AdvertisementCpmRate/1000)
								WHERE campaign.CampaignId = advertisement.AdvertisementCampaignId AND advertisement.AdvertisementId = :AdId
STR;
                $bind = array(
                    ":AdId" => $AdId,
                );
                // $Result ['CampaignDynamicBalanceUpdate'] = $db->run ( $sql, $bind );
                $Result['CampaignDynamicBalanceUpdate'] = 0;
            } else {
                $sql = <<<STR
						UPDATE campaign,advertisement
						SET campaign.CampaignStaticAdBalance = campaign.CampaignStaticAdBalance - (advertisement.AdvertisementCpmRate/1000)
						WHERE campaign.CampaignId = advertisement.AdvertisementCampaignId AND advertisement.AdvertisementId = :AdId
STR;
                $bind = array(
                    ":AdId" => $AdId,
                );
                // $Result ['CampaignStaticBalanceUpdate'] = $db->run ( $sql, $bind );
                $Result['CampaignStaticBalanceUpdate'] = 0;
            }

            return SuccessObject::getVideoSuccessObject($Result, Message::getMessage('M_DATA'), null, null, 'Advertisement');
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $Result = null;
            $db = null;
        }
    }
    public static function getVODOrChannelURLWithAd(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $IsChannel = $request->getAttribute('IsChannel');
        $AdType = $request->getAttribute('AdType');
        $PackageId = $request->getAttribute('PackageId');
        $ChannelOrVODId = $request->getAttribute('ChannelOrVODId');
        $Gender = $request->getAttribute('Gender');
        $Age = $request->getAttribute('Age');
        $results = null;

        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();

            switch ($Version) {
                case 'v1':
                case 'V1':
                    if ($IsChannel) {
                        $sql = <<<STR
                        SELECT * FROM (
					SELECT ch.ChannelIOSStreamUrl AS VideoStreamUrl,
							ch.ChannelIOSStreamUrlLow AS VideoStreamUrlLow,
							ch.ChannelStreamUrlH265 AS VideoStreamUrlHD,

                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
									ELSE cl.ClientAgencyId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
									ELSE cl.ClientAgencyId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementAgencyId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
									ELSE cam.CampaignClientId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
									ELSE cam.CampaignClientId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementClientId,

                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
									ELSE cam.campaignid END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
									ELSE cam.campaignid END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCampaignId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
									ELSE ads.AdvertisementId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
									ELSE ads.AdvertisementId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
									ELSE ads.AdvertisementName END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
									ELSE ads.AdvertisementName END

								ELSE NULL END
							ELSE NULL END AS AdvertisementName,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
									ELSE ads.AdvertisementUrl END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
									ELSE ads.AdvertisementUrl END

								ELSE NULL END
							ELSE NULL END AS AdvertisementUrl,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionUrl END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionUrl END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCallToActionUrl,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionImageUrl END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionImageUrl END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCallToActionImageUrl,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
									ELSE ads.AdvertisementViewsDone END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
									ELSE ads.AdvertisementViewsDone END

								ELSE NULL END
							ELSE NULL END AS AdvertisementViewsDone,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
									ELSE ads.AdvertisementTargetViews END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
									ELSE ads.AdvertisementTargetViews END

								ELSE NULL END
							ELSE NULL END AS AdvertisementTargetViews,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
									ELSE ads.AdvertisementTypeId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
									ELSE ads.AdvertisementTypeId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementTypeId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
									ELSE ads.AdvertisementMinAdsPerDay END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
									ELSE ads.AdvertisementMinAdsPerDay END

								ELSE NULL END
							ELSE NULL END AS AdvertisementMinAdsPerDay,


							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
									ELSE IFNULL(dac.AdvertisementCount,0) END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
									ELSE IFNULL(dac.AdvertisementCount,0) END

								ELSE NULL END
							ELSE NULL END AS AdvertisementTodayCount,


							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
									ELSE ads.AdvertisementCpmRate END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
									ELSE ads.AdvertisementCpmRate END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCpmRate,




							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
									ELSE ads.IsAllowSkipAd END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
									ELSE ads.IsAllowSkipAd END

								ELSE NULL END
							ELSE NULL END AS IsAllowSkipAd,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
									ELSE ads.AdvertisementShowSkipAfter END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
									ELSE ads.AdvertisementShowSkipAfter END

								ELSE NULL END
							ELSE NULL END AS AdvertisementShowSkipAfter,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
									ELSE ads.IsAllowOnNonPlayer END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
									ELSE ads.IsAllowOnNonPlayer END

								ELSE NULL END
							ELSE NULL END AS IsAllowOnNonPlayer,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
									ELSE ads.AdvertisementVastURL END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
									ELSE ads.AdvertisementVastURL END

								ELSE NULL END
							ELSE NULL END AS AdvertisementVastURL,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
									ELSE ads.IsVast END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
									ELSE ads.IsVast END

								ELSE NULL END
							ELSE NULL END AS IsVast,


							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
									ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
									ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

								ELSE NULL END
							ELSE NULL END AS RandomPriority

							FROM
							(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active,
							channels ch

                            INNER JOIN packagechannels pkgch ON pkgch.channelid = ch.channelid
                            INNER JOIN advertisementinpackage aip ON aip.packageid = pkgch.PackageId
                            INNER JOIN advertisement ads ON ads.AdvertisementId = aip.AdvertisementId
                            INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
                            INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
                            INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

                            LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
							LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
							LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

                        WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )
							AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
							AND ch.channelid = :ChannelOrVODId
							AND pkgch.packageid = :PackageId
							AND ads.AdvertisementTypeId = :AdType
						ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC LIMIT 1

                        ) AS AdRow
							WHERE AdRow.AdvertisementAgencyId IS NOT NULL
								AND AdRow.AdvertisementClientId IS NOT NULL
								AND AdRow.AdvertisementCampaignId IS NOT NULL
								AND AdRow.AdvertisementId IS NOT NULL
STR;
                    } else {
                        $sql = <<<STR
						SELECT * FROM (
						SELECT
							vod.VideoOnDemandHDVideo AS VideoStreamUrl,

							vod.VideoOnDemandSDVideo AS VideoStreamUrlLow,

							vod.VideoOnDemandH265Video AS VideoStreamUrlH265,

                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
									ELSE cl.ClientAgencyId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
									ELSE cl.ClientAgencyId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementAgencyId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
									ELSE cam.CampaignClientId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
									ELSE cam.CampaignClientId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementClientId,

                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
									ELSE cam.campaignid END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
									ELSE cam.campaignid END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCampaignId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
									ELSE ads.AdvertisementId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
									ELSE ads.AdvertisementId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
									ELSE ads.AdvertisementName END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
									ELSE ads.AdvertisementName END

								ELSE NULL END
							ELSE NULL END AS AdvertisementName,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
									ELSE ads.AdvertisementUrl END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
									ELSE ads.AdvertisementUrl END

								ELSE NULL END
							ELSE NULL END AS AdvertisementUrl,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionUrl END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionUrl END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCallToActionUrl,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionImageUrl END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
									ELSE ads.AdvertisementCallToActionImageUrl END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCallToActionImageUrl,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
									ELSE ads.AdvertisementViewsDone END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
									ELSE ads.AdvertisementViewsDone END

								ELSE NULL END
							ELSE NULL END AS AdvertisementViewsDone,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
									ELSE ads.AdvertisementTargetViews END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
									ELSE ads.AdvertisementTargetViews END

								ELSE NULL END
							ELSE NULL END AS AdvertisementTargetViews,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
									ELSE ads.AdvertisementTypeId END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
									ELSE ads.AdvertisementTypeId END

								ELSE NULL END
							ELSE NULL END AS AdvertisementTypeId,

							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
									ELSE ads.AdvertisementMinAdsPerDay END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
									ELSE ads.AdvertisementMinAdsPerDay END

								ELSE NULL END
							ELSE NULL END AS AdvertisementMinAdsPerDay,


							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
									ELSE IFNULL(dac.AdvertisementCount,0) END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
									ELSE IFNULL(dac.AdvertisementCount,0) END

								ELSE NULL END
							ELSE NULL END AS AdvertisementTodayCount,


							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
									ELSE ads.AdvertisementCpmRate END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
									ELSE ads.AdvertisementCpmRate END

								ELSE NULL END
							ELSE NULL END AS AdvertisementCpmRate,




							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
									ELSE ads.IsAllowSkipAd END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
									ELSE ads.IsAllowSkipAd END

								ELSE NULL END
							ELSE NULL END AS IsAllowSkipAd,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
									ELSE ads.AdvertisementShowSkipAfter END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
									ELSE ads.AdvertisementShowSkipAfter END

								ELSE NULL END
							ELSE NULL END AS AdvertisementShowSkipAfter,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
									ELSE ads.IsAllowOnNonPlayer END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
									ELSE ads.IsAllowOnNonPlayer END

								ELSE NULL END
							ELSE NULL END AS IsAllowOnNonPlayer,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
									ELSE ads.AdvertisementVastURL END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
									ELSE ads.AdvertisementVastURL END

								ELSE NULL END
							ELSE NULL END AS AdvertisementVastURL,



							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
									ELSE ads.IsVast END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
									ELSE ads.IsVast END

								ELSE NULL END
							ELSE NULL END AS IsVast,


							CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
								CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
									ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

								WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

									CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
										THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
											CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
										WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
											CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
									ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

								ELSE NULL END
							ELSE NULL END AS RandomPriority

							FROM
							(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active

                            INNER JOIN videoondemand vod ON vod.VideoOnDemandId = :ChannelOrVODId
                            INNER JOIN advertisement ads ON ads.AdvertisementTypeId = :AdType
                            INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
								AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
                            INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
                            INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

                            LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
							LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
							LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

                        WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )

						ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC
                        LIMIT 1

						) AS AdRow
							WHERE AdRow.AdvertisementAgencyId IS NOT NULL
								AND AdRow.AdvertisementClientId IS NOT NULL
								AND AdRow.AdvertisementCampaignId IS NOT NULL
								AND AdRow.AdvertisementId IS NOT NULL
STR;
                    }

                    // echo $sql;
                    $bind = array(
                        ":Age" => $Age,
                        ":Gender" => $Gender,
                        ":ChannelOrVODId" => $ChannelOrVODId,
                        ":PackageId" => $PackageId,
                        ":AdType" => $AdType,
                    );
                    $results = $db->run($sql, $bind);

                    if ($results) {
                        Format::formatResponseData($results);
                        switch ($Platform) {
                            case 'Android':
                            case 'android':

                                if (!$results[0]['IsVast']) {
                                    $results[0]['IsVast'] = true;
                                    $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                }
                                return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'), 3)));
                                break;
                            case 'androidvast':
                            case 'AndroidVast':
                            case 'ANDROIDVAST':
                                $setImpression = 'http://api.tapmad.com/api/updateAdViewAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $IsChannel . '/' . $PackageId . '/' . $ChannelOrVODId . '/' . $results[0]['AdvertisementTypeId'] . '/0/tapmad TV/https/view';
                                // $results [0] ['AdvertisementCallToActionUrl']
                                $setClickTracking = 'http://api.tapmad.com/api/updateAdClickAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $ChannelOrVODId . '/' . $IsChannel . '/' . $PackageId . '/' . $results[0]['AdvertisementTypeId'] . '/' . $Platform . '/0/tapmad TV/https/click';
                                // $results [0] ['AdvertisementUrl']

                                return General::getXMLResponse($response->write('<?xml version="1.0" encoding="UTF-8"?>
<VAST version="3.0"><Ad id="' . $results[0]['AdvertisementId'] . '"><InLine><AdSystem>PI TELEVISION Ads</AdSystem><AdTitle><![CDATA[' . $results[0]['AdvertisementName'] . ']]></AdTitle><Impression><![CDATA[' . $setImpression . ']]></Impression><Creatives><Creative><Linear skipoffset="00:00:10"><Duration>00:00:30</Duration><VideoClicks><ClickThrough><![CDATA[' . $results[0]['AdvertisementCallToActionUrl'] . ']]></ClickThrough><ClickTracking><![CDATA[' . $setClickTracking . ']]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" height="100" width="100"><![CDATA[' . $results[0]['AdvertisementUrl'] . ']]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST>'));
                                break;
                            case 'TV':
                            case 'tv':
                                // $results [0] ['AdvertisementAgencyId'] = null;
                                // $results [0] ['AdvertisementClientId'] = null;
                                // $results [0] ['AdvertisementCampaignId'] = null;
                                // $results [0] ['AdvertisementId'] = null;
                                // $results [0] ['AdvertisementName'] = null;
                                // $results [0] ['AdvertisementUrl'] = null;
                                // $results [0] ['AdvertisementCallToActionUrl'] = null;
                                // $results [0] ['AdvertisementCallToActionImageUrl'] = null;
                                // $results [0] ['AdvertisementViewsDone'] = null;
                                // $results [0] ['AdvertisementTargetViews'] = null;
                                // $results [0] ['AdvertisementTypeId'] = null;
                                // $results [0] ['AdvertisementMinAdsPerDay'] = null;
                                // $results [0] ['AdvertisementTodayCount'] = null;
                                // $results [0] ['AdvertisementCpmRate'] = null;
                                // $results [0] ['IsAllowSkipAd'] = null;
                                // $results [0] ['AdvertisementShowSkipAfter'] = null;
                                // $results [0] ['IsAllowOnNonPlayer'] = null;
                                // $results [0] ['AdvertisementVastURL'] = null;
                                // $results [0] ['IsVast'] = null;
                                // $results [0] ['RandomPriority'] = null;
                                return General::getResponse($response->write(SuccessObject::getEmptyAdSuccessObject($results[0], Message::getMessage('M_DATA'), 3)));
                                break;
                            case 'Web':
                            case 'web':
                                // $document = \Sokil\Vast\Document::create('3.0');
                                // $IsChannel $AdType $PackageId $ChannelOrVODId $Gender $Age
                                // insert Ad section
                                // $ad1 = $document->createInLineAdSection()
                                // ->setId($results[0]['AdvertisementId'])
                                // ->setAdSystem('PI TELEVISION Ads')
                                // ->setAdTitle($results[0]['AdvertisementName'])
                                // /updateAdViewAndStats/{Version} /{Language} /{Platform} /{AgencyId} /{ClientId} /{CampaignId} /{AdId} /{UserId}/{IsTempUser}/{IsChannel}/{PackageId}/{ChannelOrVODId}/{AdType} /{DeviceId}/{Source}/{VideoSourceUrl}/{StatisticType}'
                                // ->setImpression('http://tapmad.com/PitvBackend/api/updateAdViewAndStats/'.$Version.'/'.$Language.'/'.$Platform.'/'.$results[0]['AdvertisementAgencyId'].'/'.$results[0]['AdvertisementClientId'].'/'.$results[0]['AdvertisementCampaignId'].'/'.$results[0]['AdvertisementCampaignId'].'/0/1/'.$IsChannel.'/'.$PackageId.'/'.$ChannelOrVODId.'/'.$results[0]['AdvertisementTypeId'].'/0/tapmad TV/https/view');

                                // create creative for ad section
                                // $ad1->createLinearCreative()
                                // ->setDuration(10)
                                // ->setVideoClicksClickThrough($results[0]['AdvertisementCallToActionUrl'])
                                // '/updateAdClickAndStats/{Version}/{Language}/{Platform}/{AgencyId}/{ClientId}/{CampaignId}/{AdId}/{UserId}/{IsTempUser}/{ChannelOrVODId}/{IsChannel}/{PackageId}/{AdType}/{DeviceType}/{DeviceId}/{Source}/{VideoSourceUrl}/{StatisticType}'
                                // /'.$Version.'/'.$Language.'/'.$Platform.'/'.$results[0]['AdvertisementAgencyId'].'/'.$results[0]['AdvertisementClientId'].'/'.$results[0]['AdvertisementCampaignId'].'/'.$results[0]['AdvertisementCampaignId'].'/0/1/1/'.$PackageId.'/'.$ChannelOrVODId.'/'.$results[0]['AdvertisementTypeId'].'/0/tapmad TV/https/view
                                // ->addVideoClicksClickTracking('http://tapmad.com/PitvBackend/api/updateAdClickAndStats/'.$Version.'/'.$Language.'/'.$Platform.'/'.$results[0]['AdvertisementAgencyId'].'/'.$results[0]['AdvertisementClientId'].'/'.$results[0]['AdvertisementCampaignId'].'/'.$results[0]['AdvertisementCampaignId'].'/0/1/'.$ChannelOrVODId.'/'.$IsChannel.'/'.$PackageId.'/'.$results[0]['AdvertisementTypeId'].'/'.$Platform.'/0/tapmad TV/https/click')
                                // ->addVideoClicksCustomClick('http://ad.server.com/videoclicks/customclick')
                                // ->addTrackingEvent('start', 'http://ad.server.com/trackingevent/start')
                                // ->addTrackingEvent('pause', 'http://ad.server.com/trackingevent/stop')
                                // ->createMediaFile()
                                // ->setProgressiveDelivery()
                                // ->setType('video/mp4')
                                // ->setHeight(100)
                                // ->setWidth(100)
                                // ->setUrl($results[0]['AdvertisementUrl']);

                                // get dom document
                                // $domDocument = $document->toDomDocument();

                                // get XML string
                                // echo $document;

                                // $results [0] ['AdvertisementId']
                                // 'PI TELEVISION Ads'
                                // $results [0] ['AdvertisementName']

                                if ($results[0]['AdvertisementId'] === 52) {
                                    echo file_get_contents($results[0]['AdvertisementUrl']);
                                } else {
                                    if ($results[0]['IsVast']) {
                                        echo file_get_contents($results[0]['AdvertisementVastURL']);

                                        $AgencyId = $results[0]['AdvertisementAgencyId'];
                                        $ClientId = $results[0]['AdvertisementClientId'];
                                        $CampaignId = $results[0]['AdvertisementCampaignId'];
                                        $AdId = $results[0]['AdvertisementId'];
                                        $UserId = 0;
                                        $UserIp = General::getUserIP();
                                        $CurrentTime = date("Y-m-d H:i:s");
                                        $DeviceType = $Platform;
                                        $DeviceId = 0;
                                        $Source = 'tapmad TV';
                                        $VideoSourceUrl = 'httpsss';
                                        $StatisticType = 'view';
                                        $IsTempUser = 1;

                                        AdsServices::localUpdateAdViewAndStats($AgencyId, $ClientId, $CampaignId, $AdId, $UserId, $UserIp, $ChannelOrVODId, $IsChannel, $PackageId, $AdType, $CurrentTime, $DeviceType, $DeviceId, $Source, $VideoSourceUrl, $StatisticType, $IsTempUser);
                                    } else {
                                        $setImpression = 'http://tapmad.com/PitvBackend/api/updateAdViewAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $IsChannel . '/' . $PackageId . '/' . $ChannelOrVODId . '/' . $results[0]['AdvertisementTypeId'] . '/0/tapmad TV/https/view';
                                        // $results [0] ['AdvertisementCallToActionUrl']
                                        $setClickTracking = 'http://tapmad.com/PitvBackend/api/updateAdClickAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $ChannelOrVODId . '/' . $IsChannel . '/' . $PackageId . '/' . $results[0]['AdvertisementTypeId'] . '/' . $Platform . '/0/tapmad TV/https/click';
                                        // $results [0] ['AdvertisementUrl']

                                        echo '<?xml version="1.0" encoding="UTF-8"?>
<VAST version="3.0"><Ad id="' . $results[0]['AdvertisementId'] . '"><InLine><AdSystem>PI TELEVISION Ads</AdSystem><AdTitle><![CDATA[' . $results[0]['AdvertisementName'] . ']]></AdTitle><Impression><![CDATA[' . $setImpression . ']]></Impression><Creatives><Creative><Linear skipoffset="00:00:10"><Duration>00:00:30</Duration><VideoClicks><ClickThrough><![CDATA[' . $results[0]['AdvertisementCallToActionUrl'] . ']]></ClickThrough><ClickTracking><![CDATA[' . $setClickTracking . ']]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" height="100" width="100"><![CDATA[' . $results[0]['AdvertisementUrl'] . ']]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST>';
                                    }
                                }

                                // $results[0]['IsVast'] = true;
                                // $results[0]['AdvertisementVastURL'] = simplexml_load_string($document);
                                // return General::getResponse ( $response->write ( SuccessObject::getAddSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ), 3 ) ) );
                                break;
                            case 'Ios':
                            case 'ios':
                                return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'), 3)));
                                break;
                            default:
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                break;
                        }
                    } else {
                        if ($IsChannel) {
                            $sql = <<<STR
							SELECT ch.ChannelIOSStreamUrl AS VideoStreamUrl,
									ch.ChannelIOSStreamUrlLow AS VideoStreamUrlLow,
									ch.ChannelStreamUrlH265 AS VideoStreamUrlHD,
									NULL AS AdvertisementAgencyId,
									NULL AS AdvertisementClientId,
									NULL AS AdvertisementCampaignId,
									NULL AS AdvertisementId,
									NULL AS AdvertisementName,
									NULL AS AdvertisementUrl,
									NULL AS AdvertisementCallToActionUrl,
									NULL AS AdvertisementCallToActionImageUrl,
									NULL AS AdvertisementViewsDone,
									NULL AS AdvertisementTargetViews,
									NULL AS AdvertisementTypeId,
									NULL AS AdvertisementMinAdsPerDay,
									NULL AS AdvertisementTodayCount,
									NULL AS AdvertisementCpmRate,
									NULL AS IsAllowSkipAd,
									NULL AS AdvertisementShowSkipAfter,
									NULL AS IsAllowOnNonPlayer,
									NULL AS AdvertisementVastURL,
									NULL AS IsVast,
									NULL AS RandomPriority

								FROM channels ch

	                        	WHERE ch.channelid = :ChannelOrVODId
STR;
                        } else {
                            $sql = <<<STR
							SELECT vod.VideoOnDemandHDVideo AS VideoStreamUrl,
									vod.VideoOnDemandSDVideo AS VideoStreamUrlLow,
									vod.VideoOnDemandH265Video AS VideoStreamUrlHD,
									NULL AS AdvertisementAgencyId,
									NULL AS AdvertisementClientId,
									NULL AS AdvertisementCampaignId,
									NULL AS AdvertisementId,
									NULL AS AdvertisementName,
									NULL AS AdvertisementUrl,
									NULL AS AdvertisementCallToActionUrl,
									NULL AS AdvertisementCallToActionImageUrl,
									NULL AS AdvertisementViewsDone,
									NULL AS AdvertisementTargetViews,
									NULL AS AdvertisementTypeId,
									NULL AS AdvertisementMinAdsPerDay,
									NULL AS AdvertisementTodayCount,
									NULL AS AdvertisementCpmRate,
									NULL AS IsAllowSkipAd,
									NULL AS AdvertisementShowSkipAfter,
									NULL AS IsAllowOnNonPlayer,
									NULL AS AdvertisementVastURL,
									NULL AS IsVast,
									NULL AS RandomPriority

									FROM videoondemand vod
									WHERE vod.VideoOnDemandId = :ChannelOrVODId

STR;
                        }

                        // echo $sql;
                        $bind = array(
                            ":ChannelOrVODId" => $ChannelOrVODId,
                        );
                        $results = $db->run($sql, $bind);

                        switch ($Platform) {
                            case 'Android':
                            case 'android':
                                if ($results) {
                                    Format::formatResponseData($results);
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('M_DATA'), $results[0])));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));
                                }
                                break;
                            case 'TV':
                            case 'tv':
                                if ($results) {
                                    Format::formatResponseData($results);
                                    return General::getResponse($response->write(SuccessObject::getEmptyAdSuccessObject($results[0], Message::getMessage('M_DATA'), 3)));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));
                                }
                                break;
                            case 'Web':
                            case 'web':
                                if ($results) {
                                    Format::formatResponseData($results);
                                    return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'), 3)));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));
                                }
                                break;
                            case 'Ios':
                            case 'ios':
                                if ($results) {
                                    Format::formatResponseData($results);
                                    return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'), 3)));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));
                                }
                                break;
                            default:
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                break;
                        }
                    }
                    break;
                case 'v2':
                case 'V2':
                    include_once '../geoip/geoip.php';
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';

                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                        case 'androidvast':
                        case 'AndroidVast':
                        case 'ANDROIDVAST':
                            if ($IsChannel) {
                                $sql = <<<STR
								SELECT * FROM (
										SELECT ch.ChannelStreamUrlLQ AS VideoStreamUrlLQ,
										ch.ChannelStreamUrlMQ AS VideoStreamUrlMQ,
										ch.ChannelStreamUrlHQ AS VideoStreamUrlHQ,
										ch.ChannelStreamUrlHD AS VideoStreamUrlHD,
										ch.ChannelChatGroupId AS VideoChatGroupId,
                                        ch.ChannelShowFakeLogo AS ShowFakeLogo,

			                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
												ELSE cl.ClientAgencyId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
												ELSE cl.ClientAgencyId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementAgencyId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
												ELSE cam.CampaignClientId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
												ELSE cam.CampaignClientId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementClientId,

			                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
												ELSE cam.campaignid END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
												ELSE cam.campaignid END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCampaignId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
												ELSE ads.AdvertisementId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
												ELSE ads.AdvertisementId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
												ELSE ads.AdvertisementName END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
												ELSE ads.AdvertisementName END

											ELSE NULL END
										ELSE NULL END AS AdvertisementName,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
												ELSE ads.AdvertisementUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
												ELSE ads.AdvertisementUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCallToActionUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionImageUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionImageUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCallToActionImageUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
												ELSE ads.AdvertisementViewsDone END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
												ELSE ads.AdvertisementViewsDone END

											ELSE NULL END
										ELSE NULL END AS AdvertisementViewsDone,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
												ELSE ads.AdvertisementTargetViews END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
												ELSE ads.AdvertisementTargetViews END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTargetViews,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
												ELSE ads.AdvertisementTypeId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
												ELSE ads.AdvertisementTypeId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTypeId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												ELSE ads.AdvertisementMinAdsPerDay END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												ELSE ads.AdvertisementMinAdsPerDay END

											ELSE NULL END
										ELSE NULL END AS AdvertisementMinAdsPerDay,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												ELSE IFNULL(dac.AdvertisementCount,0) END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												ELSE IFNULL(dac.AdvertisementCount,0) END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTodayCount,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
												ELSE ads.AdvertisementCpmRate END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
												ELSE ads.AdvertisementCpmRate END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCpmRate,




										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
												ELSE ads.IsAllowSkipAd END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
												ELSE ads.IsAllowSkipAd END

											ELSE NULL END
										ELSE NULL END AS IsAllowSkipAd,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												ELSE ads.AdvertisementShowSkipAfter END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												ELSE ads.AdvertisementShowSkipAfter END

											ELSE NULL END
										ELSE NULL END AS AdvertisementShowSkipAfter,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												ELSE ads.IsAllowOnNonPlayer END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												ELSE ads.IsAllowOnNonPlayer END

											ELSE NULL END
										ELSE NULL END AS IsAllowOnNonPlayer,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
												ELSE ads.AdvertisementVastURL END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
												ELSE ads.AdvertisementVastURL END

											ELSE NULL END
										ELSE NULL END AS AdvertisementVastURL,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
												ELSE ads.IsVast END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
												ELSE ads.IsVast END

											ELSE NULL END
										ELSE NULL END AS IsVast,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

											ELSE NULL END
										ELSE NULL END AS RandomPriority

										FROM
										(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active,
										channels ch

			                            INNER JOIN packagechannels pkgch ON pkgch.channelid = ch.channelid
			                            INNER JOIN advertisementinpackage aip ON aip.packageid = pkgch.PackageId
			                            INNER JOIN advertisement ads ON ads.AdvertisementId = aip.AdvertisementId
			                            INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
			                            INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
			                            INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

			                            LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
										LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
										LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

			                        WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )
										AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
										AND ch.channelid = :ChannelOrVODId
										AND pkgch.packageid = :PackageId
										AND ads.AdvertisementTypeId = :AdType
										AND ads.AdvertisementViewType IN (0,2)
										AND
											CASE
				                            WHEN :CountryCode != 'PK' AND ch.ChannelIsAllowedInternationally = 1
											THEN
												CASE
				                                WHEN ch.ChannelAllowCountryCodeList=0 THEN (ch.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
												WHEN ch.ChannelAllowCountryCodeList=1 THEN (ch.ChannelCountryCodeList LIKE :CountryCodePattern)
				                                ELSE 1 END
											WHEN :CountryCode != 'PK' AND ch.ChannelIsAllowedInternationally = 0
											THEN 0
				                            ELSE 1 END
									ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC LIMIT 1

	                        ) AS AdRow
								WHERE AdRow.AdvertisementAgencyId IS NOT NULL
									AND AdRow.AdvertisementClientId IS NOT NULL
									AND AdRow.AdvertisementCampaignId IS NOT NULL
									AND AdRow.AdvertisementId IS NOT NULL
STR;
                            } else {
                                $sql = <<<STR
								SELECT * FROM (
										SELECT vod.VideoOnDemandLQVideo AS VideoStreamUrlLQ,

										vod.VideoOnDemandMQVideo AS VideoStreamUrlMQ,

										vod.VideoOnDemandHQVideo AS VideoStreamUrlHQ,

										null AS VideoStreamUrlHD,

										null AS VideoChatGroupId,

                                        false AS ShowFakeLogo,

			                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
												ELSE cl.ClientAgencyId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cl.ClientAgencyId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cl.ClientAgencyId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cl.ClientAgencyId ELSE NULL END
												ELSE cl.ClientAgencyId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementAgencyId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
												ELSE cam.CampaignClientId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.CampaignClientId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.CampaignClientId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.CampaignClientId ELSE NULL END
												ELSE cam.CampaignClientId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementClientId,

			                            CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
												ELSE cam.campaignid END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN cam.campaignid ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN cam.campaignid ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN cam.campaignid ELSE NULL END
												ELSE cam.campaignid END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCampaignId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
												ELSE ads.AdvertisementId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementId ELSE NULL END
												ELSE ads.AdvertisementId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
												ELSE ads.AdvertisementName END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementName ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementName ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementName ELSE NULL END
												ELSE ads.AdvertisementName END

											ELSE NULL END
										ELSE NULL END AS AdvertisementName,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
												ELSE ads.AdvertisementUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementUrl ELSE NULL END
												ELSE ads.AdvertisementUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCallToActionUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionImageUrl END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCallToActionImageUrl ELSE NULL END
												ELSE ads.AdvertisementCallToActionImageUrl END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCallToActionImageUrl,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
												ELSE ads.AdvertisementViewsDone END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementViewsDone ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementViewsDone ELSE NULL END
												ELSE ads.AdvertisementViewsDone END

											ELSE NULL END
										ELSE NULL END AS AdvertisementViewsDone,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
												ELSE ads.AdvertisementTargetViews END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTargetViews ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTargetViews ELSE NULL END
												ELSE ads.AdvertisementTargetViews END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTargetViews,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
												ELSE ads.AdvertisementTypeId END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementTypeId ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementTypeId ELSE NULL END
												ELSE ads.AdvertisementTypeId END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTypeId,

										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												ELSE ads.AdvertisementMinAdsPerDay END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementMinAdsPerDay ELSE NULL END
												ELSE ads.AdvertisementMinAdsPerDay END

											ELSE NULL END
										ELSE NULL END AS AdvertisementMinAdsPerDay,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												ELSE IFNULL(dac.AdvertisementCount,0) END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN IFNULL(dac.AdvertisementCount,0) ELSE NULL END
												ELSE IFNULL(dac.AdvertisementCount,0) END

											ELSE NULL END
										ELSE NULL END AS AdvertisementTodayCount,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
												ELSE ads.AdvertisementCpmRate END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementCpmRate ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementCpmRate ELSE NULL END
												ELSE ads.AdvertisementCpmRate END

											ELSE NULL END
										ELSE NULL END AS AdvertisementCpmRate,




										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
												ELSE ads.IsAllowSkipAd END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowSkipAd ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowSkipAd ELSE NULL END
												ELSE ads.IsAllowSkipAd END

											ELSE NULL END
										ELSE NULL END AS IsAllowSkipAd,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												ELSE ads.AdvertisementShowSkipAfter END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementShowSkipAfter ELSE NULL END
												ELSE ads.AdvertisementShowSkipAfter END

											ELSE NULL END
										ELSE NULL END AS AdvertisementShowSkipAfter,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												ELSE ads.IsAllowOnNonPlayer END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsAllowOnNonPlayer ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsAllowOnNonPlayer ELSE NULL END
												ELSE ads.IsAllowOnNonPlayer END

											ELSE NULL END
										ELSE NULL END AS IsAllowOnNonPlayer,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
												ELSE ads.AdvertisementVastURL END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.AdvertisementVastURL ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.AdvertisementVastURL ELSE NULL END
												ELSE ads.AdvertisementVastURL END

											ELSE NULL END
										ELSE NULL END AS AdvertisementVastURL,



										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
												ELSE ads.IsVast END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN ads.IsVast ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN ads.IsVast ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN ads.IsVast ELSE NULL END
												ELSE ads.IsVast END

											ELSE NULL END
										ELSE NULL END AS IsVast,


										CASE WHEN NOW() BETWEEN cam.CampaignStartTime AND cam.CampaignEndTime AND cam.campaignbalance > 0 AND ads.AdvertisementViewsDone < ads.AdvertisementTargetViews THEN
											CASE WHEN cam.CampaignDynamicAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'dynamic') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

											WHEN cam.CampaignStaticAdBalance > 0 AND ads.AdvertisementTypeId IN (SELECT AdvertisementTypeId FROM advertisementtype WHERE AdvertisementTypeCategory = 'static') THEN

												CASE WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 AND dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL)
													THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0 AND TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) ) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN (ads.AdvertisementMinAdsPerDay > 0 AND ads.AdvertisementMaxAdsPerDay > 0 ) THEN
														CASE WHEN ( (ads.AdvertisementMaxAdsPerDay - IFNULL(dac.AdvertisementCount,0)) > 0) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
													WHEN ( dp.DayPartingStartTime IS NOT NULL AND dp.DayPartingEndTime IS NOT NULL ) THEN
														CASE WHEN TIME(NOW()) BETWEEN TIME(dp.DayPartingStartTime) AND TIME(dp.DayPartingEndTime) THEN (FLOOR( 1 + RAND( ) * active.totalActive )) ELSE NULL END
												ELSE (FLOOR( 1 + RAND( ) * active.totalActive )) END

											ELSE NULL END
										ELSE NULL END AS RandomPriority

										FROM
										(SELECT IF(COUNT(*)=0,100,COUNT(*)) AS totalActive FROM advertisementdailycount da,advertisement ad WHERE da.AdvertisementId = ad.AdvertisementId AND ad.AdvertisementTypeId = :AdType AND da.AdvertisementCountDate = CURRENT_DATE()) AS active

			                            INNER JOIN videoondemand vod ON vod.VideoOnDemandId = :ChannelOrVODId
			                            INNER JOIN advertisement ads ON ads.AdvertisementTypeId = :AdType
											AND ads.AdvertisementViewType IN (0,2)
			                            INNER JOIN campaign cam ON cam.CampaignId=ads.AdvertisementCampaignId
											AND (cam.CampaignGender='All' OR cam.CampaignGender= :Gender)
			                            INNER JOIN client cl ON cl.ClientId = cam.CampaignClientId
			                            INNER JOIN agency ag ON ag.id = cl.ClientAgencyId

			                            LEFT JOIN advertisementdayparting dp ON dp.DayPartingAdvertisementId=ads.AdvertisementId
										LEFT JOIN advertisementagetarget aget ON aget.AgeTargetAdvertisementId=ads.AdvertisementId
										LEFT JOIN advertisementdailycount dac ON dac.AdvertisementId = ads.AdvertisementId AND CURRENT_DATE() = dac.AdvertisementCountdate

			                        WHERE ( :Age BETWEEN aget.AgeTargetStartingAge AND aget.AgeTargetEndingAge )
										AND
											CASE
					                        WHEN :CountryCode != 'PK'
											THEN
												vod.VideoOnDemandCategoryId
												 IN (SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
														WHERE CASE
																WHEN videoondemandcategories.VideoOnDemandCategoryparentId = 0
														        THEN videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
																ELSE videoondemandcategories.VideoOnDemandCategoryparentId
																	 IN (
																		SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
																		WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
																			AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
																		)
																END
														GROUP BY videoondemandcategories.VideoOnDemandCategoryId)
											ELSE 1 END

									ORDER BY RandomPriority DESC,AdvertisementCpmRate DESC, (AdvertisementMinAdsPerDay-AdvertisementTodayCount) DESC, (AdvertisementTargetViews - AdvertisementViewsDone) DESC
			                        LIMIT 1

							) AS AdRow
								WHERE AdRow.AdvertisementAgencyId IS NOT NULL
									AND AdRow.AdvertisementClientId IS NOT NULL
									AND AdRow.AdvertisementCampaignId IS NOT NULL
									AND AdRow.AdvertisementId IS NOT NULL
STR;
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }

                    // echo $sql;
                    $bind = array();
                    if ($IsChannel) {
                        $bind = array(
                            ":Age" => $Age,
                            ":Gender" => $Gender,
                            ":ChannelOrVODId" => $ChannelOrVODId,
                            ":PackageId" => $PackageId,
                            ":AdType" => $AdType,
                            ":CountryCode" => $CountryCode,
                            ":CountryCodePattern" => "%$CountryCode%",
                        );
                    } else {
                        $bind = array(
                            ":Age" => $Age,
                            ":Gender" => $Gender,
                            ":ChannelOrVODId" => $ChannelOrVODId,
                            ":PackageId" => $PackageId,
                            ":AdType" => $AdType,
                            ":CountryCode" => $CountryCode,
                        );
                    }
                    $results = $db->run($sql, $bind);

                    if ($results) {
                        Format::formatResponseData($results);
                        switch ($Platform) {
                            case 'Android':
                            case 'android':

                                if (!$results[0]['IsVast']) {
                                    $results[0]['IsVast'] = true;
                                    $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                }

                                $Midrolls = null;
                                if ($IsChannel) {
                                    $Midrolls = array();
                                } else {
                                    $Sql = <<<STR
        							SELECT ads.AdvertisementId AS AdvertisementId,
        									ads.AdvertisementName AS AdvertisementName,
        									ads.IsVast AS IsVast,
        									ads.AdvertisementVastURL AS AdvertisementVastURL,
        									ads.AdvertisementTypeId AS AdvertisementTypeId,
        									ads.AdvertisementCpmRate AS AdvertisementCpmRate

                                            FROM advertisement ads
                                            WHERE ads.AdvertisementTypeId = 2
                                                AND ads.AdvertisementIsActive = 1
        								    ORDER BY AdvertisementCpmRate DESC
STR;
                                    $Midrolls = $db->run($Sql);
                                    Format::formatResponseData($Midrolls);
                                }
                                /*
                                if(!$results [0] ['IsVast'])
                                {
                                $setImpression = 'http://tapmad.com/PitvBackend/api/updateAdViewAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results [0] ['AdvertisementAgencyId'] . '/' . $results [0] ['AdvertisementClientId'] . '/' . $results [0] ['AdvertisementCampaignId'] . '/' . $results [0] ['AdvertisementId'] . '/0/1/' . $IsChannel . '/' . $PackageId . '/' . $ChannelOrVODId . '/' . $results [0] ['AdvertisementTypeId'] . '/0/tapmad TV/https/view';
                                // $results [0] ['AdvertisementCallToActionUrl']
                                $setClickTracking = 'http://tapmad.com/PitvBackend/api/updateAdClickAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results [0] ['AdvertisementAgencyId'] . '/' . $results [0] ['AdvertisementClientId'] . '/' . $results [0] ['AdvertisementCampaignId'] . '/' . $results [0] ['AdvertisementId'] . '/0/1/' . $ChannelOrVODId . '/' . $IsChannel . '/' . $PackageId . '/' . $results [0] ['AdvertisementTypeId'] . '/' . $Platform . '/0/tapmad TV/https/click';
                                // $results [0] ['AdvertisementUrl']

                                $results [0] ['AdvertisementVastURL'] = '<?xml version="1.0" encoding="UTF-8"?>
                                <VAST version="3.0"><Ad id="' . $results [0] ['AdvertisementId'] . '"><InLine><AdSystem>PI TELEVISION Ads</AdSystem><AdTitle><![CDATA[' . $results [0] ['AdvertisementName'] . ']]></AdTitle><Impression><![CDATA[' . $setImpression . ']]></Impression><Creatives><Creative><Linear skipoffset="00:00:10"><Duration>00:00:30</Duration><VideoClicks><ClickThrough><![CDATA[' . $results [0] ['AdvertisementCallToActionUrl'] . ']]></ClickThrough><ClickTracking><![CDATA[' . $setClickTracking . ']]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" height="100" width="100"><![CDATA[' . $results [0] ['AdvertisementUrl'] . ']]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST>';
                                $results [0] ['IsVast'] = true;
                                }
                                 */
                                return General::getResponse($response->write(SuccessObject::getAddSuccessObject($results[0], Message::getMessage('M_DATA'), 6, $Midrolls)));
                                break;
                            case 'androidvast':
                            case 'AndroidVast':
                            case 'ANDROIDVAST':
                                $setImpression = 'http://api.tapmad.com/api/updateAdViewAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $IsChannel . '/' . $PackageId . '/' . $ChannelOrVODId . '/' . $results[0]['AdvertisementTypeId'] . '/0/tapmad TV/https/view';
                                // $results [0] ['AdvertisementCallToActionUrl']
                                $setClickTracking = 'http://api.tapmad.com/api/updateAdClickAndStats/' . $Version . '/' . $Language . '/' . $Platform . '/' . $results[0]['AdvertisementAgencyId'] . '/' . $results[0]['AdvertisementClientId'] . '/' . $results[0]['AdvertisementCampaignId'] . '/' . $results[0]['AdvertisementId'] . '/0/1/' . $ChannelOrVODId . '/' . $IsChannel . '/' . $PackageId . '/' . $results[0]['AdvertisementTypeId'] . '/' . $Platform . '/0/tapmad TV/https/click';
                                // $results [0] ['AdvertisementUrl']

                                return General::getXMLResponse($response->write('<?xml version="1.0" encoding="UTF-8"?>
<VAST version="3.0"><Ad id="' . $results[0]['AdvertisementId'] . '"><InLine><AdSystem>PI TELEVISION Ads</AdSystem><AdTitle><![CDATA[' . $results[0]['AdvertisementName'] . ']]></AdTitle><Impression><![CDATA[' . $setImpression . ']]></Impression><Creatives><Creative><Linear skipoffset="00:00:10"><Duration>00:00:30</Duration><VideoClicks><ClickThrough><![CDATA[' . $results[0]['AdvertisementCallToActionUrl'] . ']]></ClickThrough><ClickTracking><![CDATA[' . $setClickTracking . ']]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" height="100" width="100"><![CDATA[' . $results[0]['AdvertisementUrl'] . ']]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST>'));
                                break;
                            default:
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                break;
                        }
                    } else {
                        switch ($Platform) {
                            case 'Android':
                            case 'android':
                                if ($IsChannel) {
                                    $sql = <<<STR
							SELECT ch.ChannelStreamUrlLQ AS VideoStreamUrlLQ,
									ch.ChannelStreamUrlMQ AS VideoStreamUrlMQ,
									ch.ChannelStreamUrlHQ AS VideoStreamUrlHQ,
									ch.ChannelStreamUrlHD AS VideoStreamUrlHD,
									ch.ChannelChatGroupId AS VideoChatGroupId,
                                    ch.ChannelShowFakeLogo AS ShowFakeLogo,
									NULL AS AdvertisementAgencyId,
									NULL AS AdvertisementClientId,
									NULL AS AdvertisementCampaignId,
									NULL AS AdvertisementId,
									NULL AS AdvertisementName,
									NULL AS AdvertisementUrl,
									NULL AS AdvertisementCallToActionUrl,
									NULL AS AdvertisementCallToActionImageUrl,
									NULL AS AdvertisementViewsDone,
									NULL AS AdvertisementTargetViews,
									NULL AS AdvertisementTypeId,
									NULL AS AdvertisementMinAdsPerDay,
									NULL AS AdvertisementTodayCount,
									NULL AS AdvertisementCpmRate,
									NULL AS IsAllowSkipAd,
									NULL AS AdvertisementShowSkipAfter,
									NULL AS IsAllowOnNonPlayer,
									NULL AS AdvertisementVastURL,
									NULL AS IsVast,
									NULL AS RandomPriority

								FROM channels ch

	                        	WHERE ch.channelid = :ChannelOrVODId
									AND
										CASE
			                            WHEN :CountryCode != 'PK' AND ch.ChannelIsAllowedInternationally = 1
										THEN
											CASE
			                                WHEN ch.ChannelAllowCountryCodeList=0 THEN (ch.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
											WHEN ch.ChannelAllowCountryCodeList=1 THEN (ch.ChannelCountryCodeList LIKE :CountryCodePattern)
			                                ELSE 1 END
										WHEN :CountryCode != 'PK' AND ch.ChannelIsAllowedInternationally = 0
										THEN 0
			                            ELSE 1 END
STR;
                                } else {
                                    $sql = <<<STR
							SELECT vod.VideoOnDemandLQVideo AS VideoStreamUrlLQ,
									vod.VideoOnDemandMQVideo AS VideoStreamUrlMQ,
									vod.VideoOnDemandHQVideo AS VideoStreamUrlHQ,
									null AS VideoStreamUrlHD,
									null AS VideoChatGroupId,
                                    false AS ShowFakeLogo,
									NULL AS AdvertisementAgencyId,
									NULL AS AdvertisementClientId,
									NULL AS AdvertisementCampaignId,
									NULL AS AdvertisementId,
									NULL AS AdvertisementName,
									NULL AS AdvertisementUrl,
									NULL AS AdvertisementCallToActionUrl,
									NULL AS AdvertisementCallToActionImageUrl,
									NULL AS AdvertisementViewsDone,
									NULL AS AdvertisementTargetViews,
									NULL AS AdvertisementTypeId,
									NULL AS AdvertisementMinAdsPerDay,
									NULL AS AdvertisementTodayCount,
									NULL AS AdvertisementCpmRate,
									NULL AS IsAllowSkipAd,
									NULL AS AdvertisementShowSkipAfter,
									NULL AS IsAllowOnNonPlayer,
									NULL AS AdvertisementVastURL,
									NULL AS IsVast,
									NULL AS RandomPriority

									FROM videoondemand vod
									WHERE vod.VideoOnDemandId = :ChannelOrVODId
										AND
											CASE
					                        WHEN :CountryCode != 'PK'
											THEN
												vod.VideoOnDemandCategoryId
												 IN (SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
														WHERE CASE
																WHEN videoondemandcategories.VideoOnDemandCategoryparentId = 0
														        THEN videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
																ELSE videoondemandcategories.VideoOnDemandCategoryparentId
																	 IN (
																		SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
																		WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
																			AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
																		)
																END
														GROUP BY videoondemandcategories.VideoOnDemandCategoryId)
											ELSE 1 END

STR;
                                }

                                // echo $sql;
                                $bind = array();
                                if ($IsChannel) {
                                    $bind = array(
                                        ":ChannelOrVODId" => $ChannelOrVODId,
                                        ":CountryCode" => $CountryCode,
                                        ":CountryCodePattern" => "%$CountryCode%",
                                    );
                                } else {
                                    $bind = array(
                                        ":ChannelOrVODId" => $ChannelOrVODId,
                                        ":AdType" => $AdType,
                                        ":CountryCode" => $CountryCode,
                                    );
                                }
                                $results = $db->run($sql, $bind);

                                if ($results) {
                                    Format::formatResponseData($results);
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('M_DATA'), $results[0])));
                                } else {
                                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('W_NO_CONTENT'))));
                                }
                                break;
                            default:
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                break;
                        }
                    }
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getAdErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }
    public static function updateAdViewAndStats(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $AgencyId = $request->getAttribute('AgencyId');
        $ClientId = $request->getAttribute('ClientId');
        $CampaignId = $request->getAttribute('CampaignId');
        $AdId = $request->getAttribute('AdId');
        $UserId = $request->getAttribute('UserId');
        $UserIp = General::getUserIP();
        $ChannelOrVODId = $request->getAttribute('ChannelOrVODId');
        $IsChannel = $request->getAttribute('IsChannel');
        $PackageId = $request->getAttribute('PackageId');
        $AdType = $request->getAttribute('AdType');
        $CurrentTime = date("Y-m-d H:i:s");
        $DeviceType = $request->getAttribute('Platform');
        $DeviceId = $request->getAttribute('DeviceId');
        $Source = $request->getAttribute('Source');
        $VideoSourceUrl = $request->getAttribute('VideoSourceUrl');
        $StatisticType = $request->getAttribute('StatisticType');
        $IsTempUser = $request->getAttribute('IsTempUser');
        $Result = null;

        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    // Inserting Data Of View Into AdvertisementDetail
                    $sql = <<<STR
					INSERT INTO advertisementdetail
							(AdvertisementDetailAgencyId, AdvertisementDetailClientId, AdvertisementDetailCampaignId, AdvertisementDetailAdvertisementId,
							AdvertisementDetailUserId, AdvertisementDetailUserIp, AdvertisementDetailChannelOrVODId, isChannel, AdvertisementDetailPackageId,
							AdvertisementDetailAdvertisementType, AdvertisementDetailTime, AdvertisementDetailDeviceType,
							AdvertisementDetailDeviceId, AdvertisementDetailSource, AdvertisementDetailVideoSourceUrl, AdvertisementDetailType, IsTempUser)
					VALUES(:AgencyId,:ClientId,:CampaignId,:AdId,
							:UserId,:UserIp,:ChannelOrVODId,:IsChannel,:PackageId,
							:AdType,:CurrentTime,:DeviceType,
							:DeviceId,:Source,:VideoSourceUrl,:StatisticType, :IsTempUser)
STR;
                    $bind = array(
                        ":AgencyId" => $AgencyId,
                        ":ClientId" => $ClientId,
                        ":CampaignId" => $CampaignId,
                        ":AdId" => $AdId,
                        ":UserId" => $UserId,
                        ":UserIp" => $UserIp,
                        ":ChannelOrVODId" => $ChannelOrVODId,
                        ":IsChannel" => $IsChannel,
                        ":PackageId" => $PackageId,
                        ":AdType" => $AdType,
                        ":CurrentTime" => $CurrentTime,
                        ":DeviceType" => $DeviceType,
                        ":DeviceId" => $DeviceId,
                        ":Source" => $Source,
                        ":VideoSourceUrl" => $VideoSourceUrl,
                        ":StatisticType" => $StatisticType,
                        ":IsTempUser" => $IsTempUser,
                    );
                    // $Result ['AdvertisementDetailInsert'] = $db->run ( $sql, $bind );
                    $Result['AdvertisementDetailInsert'] = 0;

                    // Updating Channel OR VOD Viewdone
                    if ($IsChannel) {
                        $sql = <<<STR
						UPDATE channels
						SET channels.ChannelTotalViews    =  channels.ChannelTotalViews + 1
						WHERE channels.ChannelId = :ChannelOrVODId
STR;
                    } else {
                        $sql = <<<STR
						UPDATE videoondemand
						SET videoondemand.VideoOnDemandTotalViews =  videoondemand.VideoOnDemandTotalViews + 1
						WHERE videoondemand.VideoOnDemandId = :ChannelOrVODId
STR;
                    }

                    $bind = array(
                        ":ChannelOrVODId" => $ChannelOrVODId,
                    );
                    $Result['ChannelOrVODViewsDoneUpdate'] = $db->run($sql, $bind);
                    // $Result ['ChannelOrVODViewsDoneUpdate'] = 0;

                    // Updating Advertisement Viewsdone
                    $sql = <<<STR
					UPDATE advertisement
					SET advertisement.AdvertisementViewsDone = advertisement.AdvertisementViewsDone + 1
					WHERE advertisement.AdvertisementId = :AdId AND advertisement.AdvertisementCampaignId = :CampaignId
STR;
                    $bind = array(
                        ":AdId" => $AdId,
                        ":CampaignId" => $CampaignId,
                    );
                    $Result['AdvertisementViewsDoneUpdate'] = $db->run($sql, $bind);
                    // $Result ['AdvertisementViewsDoneUpdate'] = 0;

                    // Updating Advertisement Daily Ad Count
                    $Result['AdvertisementDailyCountUpdate'] = 0;
                    $CacheCountLimit = 10;
                    $Id = 'AdDailyCount' . $CampaignId . $AdId;

                    if (AdsServices::cacheAdCount($Id) >= $CacheCountLimit) {
                        // Update Query
                        $sql = <<<STR
						INSERT INTO advertisementdailycount (AdvertisementId, AdvertisementCountDate, AdvertisementCount)
								VALUES( :AdId, CURRENT_DATE(), 10 )
								ON DUPLICATE KEY UPDATE AdvertisementCount = AdvertisementCount + 10
STR;
                        $bind = array(
                            ":AdId" => $AdId,
                        );
                        $Result['AdvertisementDailyCountUpdate'] = $db->run($sql, $bind);
                        AdsServices::resetCacheAdCount($Id);
                    }

                    // Updating Advertisement's Campaign Balance
                    $sql = <<<STR
					UPDATE campaign,advertisement
							SET campaign.CampaignBalance = campaign.CampaignBalance - (advertisement.AdvertisementCpmRate/1000)
							WHERE campaign.CampaignId = advertisement.AdvertisementCampaignId AND advertisement.AdvertisementId = :AdId
STR;
                    $bind = array(
                        ":AdId" => $AdId,
                    );
                    // $Result ['CampaignBalanceUpdate'] = $db->run ( $sql, $bind );
                    $Result['CampaignBalanceUpdate'] = 0;

                    // Updating Campaign's Static OR Dynamic Advertisement Balance
                    $sql = <<<STR
					SELECT advertisementtype.AdvertisementTypeId
							FROM advertisementtype
							WHERE advertisementtype.AdvertisementTypeCategory='dynamic'
STR;
                    $AdTypes = $db->run($sql);
                    if (in_array($AdType, array_column($AdTypes, 'AdvertisementTypeId'))) {
                        $sql = <<<STR
						UPDATE campaign,advertisement
								SET campaign.CampaignDynamicAdBalance = campaign.CampaignDynamicAdBalance - (advertisement.AdvertisementCpmRate/1000)
								WHERE campaign.CampaignId = advertisement.AdvertisementCampaignId AND advertisement.AdvertisementId = :AdId
STR;
                        $bind = array(
                            ":AdId" => $AdId,
                        );
                        // $Result ['CampaignDynamicBalanceUpdate'] = $db->run ( $sql, $bind );
                        $Result['CampaignDynamicBalanceUpdate'] = 0;
                    } else {
                        $sql = <<<STR
						UPDATE campaign,advertisement
						SET campaign.CampaignStaticAdBalance = campaign.CampaignStaticAdBalance - (advertisement.AdvertisementCpmRate/1000)
						WHERE campaign.CampaignId = advertisement.AdvertisementCampaignId AND advertisement.AdvertisementId = :AdId
STR;
                        $bind = array(
                            ":AdId" => $AdId,
                        );
                        // $Result ['CampaignStaticBalanceUpdate'] = $db->run ( $sql, $bind );
                        $Result['CampaignStaticBalanceUpdate'] = 0;
                    }

                    return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($Result, Message::getMessage('M_DATA'), null, null, 'Advertisement')));
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject('In Process.', null, null, 'Advertisement')));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), null, null, 'Advertisement')));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $Result = null;
            $db = null;
        }
    }
    public static function updateAdClickAndStats(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $AgencyId = $request->getAttribute('AgencyId');
        $ClientId = $request->getAttribute('ClientId');
        $CampaignId = $request->getAttribute('CampaignId');
        $AdId = $request->getAttribute('AdId');
        $UserId = $request->getAttribute('UserId');
        $UserIp = General::getUserIP();
        $ChannelOrVODId = $request->getAttribute('ChannelOrVODId');
        $IsChannel = $request->getAttribute('IsChannel');
        $PackageId = $request->getAttribute('PackageId');
        $AdType = $request->getAttribute('AdType');
        $CurrentTime = date("Y-m-d H:i:s");
        $DeviceType = $request->getAttribute('DeviceType');
        $DeviceId = $request->getAttribute('DeviceId');
        $Source = $request->getAttribute('Source');
        $VideoSourceUrl = $request->getAttribute('VideoSourceUrl');
        $StatisticType = $request->getAttribute('StatisticType');
        $IsTempUser = $request->getAttribute('IsTempUser');
        $Result = null;

        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    // Inserting Data Of Click Into AdvertisementDetail
                    $sql = <<<STR
					INSERT INTO advertisementdetail
							(AdvertisementDetailAgencyId, AdvertisementDetailClientId, AdvertisementDetailCampaignId, AdvertisementDetailAdvertisementId,
							AdvertisementDetailUserId, AdvertisementDetailUserIp, AdvertisementDetailChannelOrVODId, isChannel, AdvertisementDetailPackageId,
							AdvertisementDetailAdvertisementType, AdvertisementDetailTime, AdvertisementDetailDeviceType,
							AdvertisementDetailDeviceId, AdvertisementDetailSource, AdvertisementDetailVideoSourceUrl, AdvertisementDetailType, IsTempUser)
					VALUES(:AgencyId,:ClientId,:CampaignId,:AdId,
							:UserId,:UserIp,:ChannelOrVODId,:IsChannel,:PackageId,
							:AdType,:CurrentTime,:DeviceType,
							:DeviceId,:Source,:VideoSourceUrl,:StatisticType, :IsTempUser)
STR;
                    $bind = array(
                        ":AgencyId" => $AgencyId,
                        ":ClientId" => $ClientId,
                        ":CampaignId" => $CampaignId,
                        ":AdId" => $AdId,
                        ":UserId" => $UserId,
                        ":UserIp" => $UserIp,
                        ":ChannelOrVODId" => $ChannelOrVODId,
                        ":IsChannel" => $IsChannel,
                        ":PackageId" => $PackageId,
                        ":AdType" => $AdType,
                        ":CurrentTime" => $CurrentTime,
                        ":DeviceType" => $DeviceType,
                        ":DeviceId" => $DeviceId,
                        ":Source" => $Source,
                        ":VideoSourceUrl" => $VideoSourceUrl,
                        ":StatisticType" => $StatisticType,
                        ":IsTempUser" => $IsTempUser,
                    );
                    // echo $sql;
                    // $Result ['AdvertisementDetailInsert'] = $db->run ( $sql, $bind );
                    $Result['AdvertisementDetailInsert'] = 0;

                    // Updating Advertisement's Call To Action Count
                    $sql = <<<STR
					UPDATE advertisement
							SET advertisement.AdvertisementCallToActionCount = advertisement.AdvertisementCallToActionCount + 1
							WHERE advertisement.AdvertisementId = :AdId
STR;
                    $bind = array(
                        ":AdId" => $AdId,
                    );
                    $Result['CallToActionCountUpdate'] = $db->run($sql, $bind);

                    // Get Advertisement's Call To Action URL
                    $sql = <<<STR
					SELECT advertisement.AdvertisementCallToActionUrl
							FROM advertisement
							WHERE advertisement.AdvertisementId = :AdId
STR;
                    $bind = array(
                        ":AdId" => $AdId,
                    );

                    $Results = $db->run($sql, $bind);
                    $Result['AdvertisementCallToActionUrl'] = null;
                    if ($Results) {
                        $Result['AdvertisementCallToActionUrl'] = $Results[0]['AdvertisementCallToActionUrl'];
                        header('Location: ' . $Results[0]['AdvertisementCallToActionUrl']);
                    }

                    return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($Result, Message::getMessage('M_DATA'), null, null, 'Advertisement')));
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject('In Process.', null, null, 'Advertisement')));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), null, null, 'Advertisement')));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $Result = null;
            $db = null;
        }
    }
    /*
     * Function To Update Cache Count
     */
    public static function cacheAdCount($Id)
    {
        wincache_ucache_set($Id, AdsServices::getCacheAdCount($Id) + 1);
        // echo wincache_ucache_get($Id);
        return wincache_ucache_get($Id);
    }

    /*
     * Function To Get Cache Count
     */
    public static function getCacheAdCount($Id)
    {
        return (int) wincache_ucache_get($Id);
    }

    /*
     * Function To Reset Cache Count
     */
    public static function resetCacheAdCount($Id)
    {
        wincache_ucache_set($Id, 0);
        return wincache_ucache_get($Id);
    }
}
