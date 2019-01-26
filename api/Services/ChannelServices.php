<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
/**
 * Class to Handle all Services Related to Channels for ANDROID
 *
 * @author SAIF UD DIN
 *        
 */

class ChannelServices extends Config {
	public static function updateChannelOrVODViews(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		$Platform = $request->getAttribute ( 'Platform' );
		
		$IsChannel = $request->getAttribute ( 'IsChannel' );
		$ChannelOrVODId = $request->getAttribute ( 'ChannelOrVODId' );
		$results = NULL;
		
		try {
			parent::setConfig ( $Language );
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
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
					
					$bind = array (
							":ChannelOrVODId" => $ChannelOrVODId 
					);
					if ($db->run ( $sql, $bind )) {
						return General::getResponse ( $response->write ( SuccessObject::getSuccessObject ( Message::getMessage ( 'M_RECORD_UPDATE' ) ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getErrorObject ( Message::getMessage ( 'E_RECORD_UPDATE' ) ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getErrorObject ( array (
							'In Process.' 
					) ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getChannelOrVODUrl(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$ChannelOrVODId = $request->getAttribute ( 'ChannelOrVODId' );
		$IsChannel = $request->getAttribute ( 'IsChannel' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					if ($IsChannel === '1') {
						switch ($Platform) {
							case 'Android' :
							case 'android' :
								$sql = <<<STR
								SELECT channels.ChannelIOSStreamUrl AS VideoStreamUrl,
										channels.ChannelIOSStreamUrlLow AS VideoStreamUrlLow,
										channels.ChannelStreamUrlH265 AS VideoStreamUrlHD,
										channels.ChannelName as Name,
										channels.ChannelDescription as Description,
										channels.ChannelTotalViews as TotalViews
										FROM channels
	
										WHERE channels.ChannelIsOnline=1
											AND channels.channelId =:ChannelId
STR;


								break;
							case 'Web' :
							case 'web' :
								$sql = <<<STR
								SELECT true AS IsVideoChannel,
										'Channel' AS VideoType,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
										IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewChannelThumbnailPath,
                                        channels.ChannelIOSStreamUrlLow AS VideoStreamUrlLow,
                                        channels.ChannelIOSStreamUrl AS VideoStreamUrl,
										null AS VideoStreamUrlMD,
										channels.ChannelStreamUrlH265 AS VideoStreamUrlHD,
										channels.ChannelId AS VideoEntityId,
										channels.ChannelName as VideoName,
										channels.ChannelDescription as Description,
										channels.ChannelTotalViews as TotalViews
										FROM channels
					
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
			
										WHERE channels.ChannelIsOnline=1
											AND channels.channelId =:ChannelId
STR;
								break;
							default :
								return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
								break;
						}
						// echo $sql;
						$bind = array (
								':ChannelId' => $ChannelOrVODId,
								':ImagesDomainName' => Config::$imagesDomainName 
						);

						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Video' ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Video' ) ) );
						}
					} else {
						switch ($Platform) {
							case 'Android' :
							case 'android' :
								$sql = <<<STR
								SELECT videoondemand.VideoOnDemandHDVideo AS VideoStreamUrl,
										videoondemand.VideoOnDemandSDVideo AS VideoStreamUrlLow,
										videoondemand.VideoOnDemandH265Video AS VideoStreamUrlHD,
										videoondemand.VideoOnDemandTitle as Name,
										videoondemand.VideoOnDemandDescription as Description,
										videoondemand.VideoOnDemandTotalViews as TotalViews
	
										FROM videoondemand
	
										WHERE videoondemand.VideoOnDemandIsOnline=1
											AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
											AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
											AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
											AND videoondemand.VideoOnDemandId = :VideoOnDemandId
STR;
								break;
							case 'Web' :
							case 'web' :
								$sql = <<<STR
								SELECT false AS IsVideoChannel,
										IF (videoondemandcategories.VideoOnDemandCategoryId IN (3,6,8),'Movie','VOD') AS VideoType,
										videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
										IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
										IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
                                        IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
										videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
										videoondemand.VideoOnDemandSDVideo AS VideoStreamUrlLow,
										videoondemand.VideoOnDemandLQVideo AS VideoStreamUrl,
                                        videoondemand.VideoOnDemandMQVideo AS VideoStreamUrlMD,
										videoondemand.VideoOnDemandHQVideo AS VideoStreamUrlHD,
										videoondemand.VideoOnDemandId AS VideoEntityId,
										videoondemand.VideoOnDemandTitle as VideoName,
										videoondemand.VideoOnDemandDescription as Description,
										videoondemand.VideoOnDemandTotalViews as TotalViews
	
										FROM videoondemand
	
										INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
											AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
	
										WHERE videoondemand.VideoOnDemandIsOnline=1
											AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
											AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
											AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
											AND videoondemand.VideoOnDemandId = :VideoOnDemandId
STR;
								break;
							default :
								return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
								break;
						}
						// echo $sql;
						$bind = array (
								':VideoOnDemandId' => $ChannelOrVODId,
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Video' ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Video' ) ) );
						}
					}
					break;
				case 'v2' :
				case 'V2' :
					if ($IsChannel === '1') {
						switch ($Platform) {
							case 'Android' :
							case 'android' :
							case 'ANDROID' :
								$sql = <<<STR
								SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channels.ChannelId AS VideoEntityId,
										channels.ChannelName as VideoName,
                                        IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
                                        IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewChannelThumbnailPath,
                                        channels.ChannelStreamUrlLQ AS VideoStreamUrlLQ,
                                        channels.ChannelStreamUrlMQ AS VideoStreamUrlMQ,
										channels.ChannelStreamUrlHQ AS VideoStreamUrlHQ,
										channels.ChannelStreamUrlHD AS VideoStreamUrlHD,
										channels.ChannelChatGroupId AS VideoChatGroupId,
										true AS IsVideoChannel,
										'1' AS VideoType,
                                        IF( packages.PackageId = 10, false, true ) AS IsVideoFree,
                                        channels.ChannelIsDVR AS IsVideoDVR,
										channels.ChannelDescription as VideoDescription,
										channels.ChannelTotalViews as VideoTotalViews,
										IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
										IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
										IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
										IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

	
                                        FROM channels
					
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
	
							            LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
	
							            LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
			
										WHERE channels.ChannelIsOnline=1
                                            AND packages.PackageId IN (6,7,8,10)
											AND channels.channelId =:ChannelId
STR;
								break;
							case 'Web' :
							case 'web' :
							case 'WEB' :
								$sql = <<<STR
								SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channels.ChannelId AS VideoEntityId,
										channels.ChannelName as VideoName,
                                        IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
                                        IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewChannelThumbnailPath,
                                        channels.ChannelStreamUrlLQ AS VideoStreamUrlLQ,
                                        channels.ChannelStreamUrlMQ AS VideoStreamUrlMQ,
										channels.ChannelStreamUrlHQ AS VideoStreamUrlHQ,
										true AS IsVideoChannel,
										'1' AS VideoType,
                                        IF( packages.PackageId = 10, false, true ) AS IsVideoFree,
                                        channels.ChannelIsDVR AS IsVideoDVR,
										channels.ChannelDescription as VideoDescription,
										channels.ChannelTotalViews as VideoTotalViews
	
                                        FROM channels
			
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
	
							            LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
	
							            LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
		
										WHERE channels.ChannelIsOnline=1
                                            AND packages.PackageId IN (6,7,8,10)
											AND channels.channelId =:ChannelId
STR;
								break;
							default :
								return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
								break;
						}
						// echo $sql;
						$bind = array (
								':ChannelId' => $ChannelOrVODId,
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							/*
							 * if ( isset($results [0] ['IsVideoDVR']) && $results [0] ['IsVideoDVR'])
							 * {
							 * $results [0] ['VideoStreamUrlLQ'] = isset($results [0] ['VideoStreamUrlLQ']) && $results [0] ['VideoStreamUrlLQ'] != NULL ? $results [0] ['VideoStreamUrlLQ'] . 'DVR&' : $results [0] ['VideoStreamUrlLQ'];
							 * $results [0] ['VideoStreamUrlMQ'] = isset($results [0] ['VideoStreamUrlMQ']) && $results [0] ['VideoStreamUrlMQ'] != NULL ? $results [0] ['VideoStreamUrlMQ'] . 'DVR&' : $results [0] ['VideoStreamUrlMQ'];
							 * $results [0] ['VideoStreamUrlHQ'] = isset($results [0] ['VideoStreamUrlHQ']) && $results [0] ['VideoStreamUrlHQ'] != NULL ? $results [0] ['VideoStreamUrlHQ'] . 'DVR&' : $results [0] ['VideoStreamUrlHQ'];
							 * }
							 */
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Video' ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Video' ) ) );
						}
					} else {
						switch ($Platform) {
							case 'Android' :
							case 'android' :
							case 'ANDROID' :
							case 'Web' :
							case 'web' :
							case 'WEB' :
								$sql = <<<STR
								SELECT videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
										videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
										videoondemand.VideoOnDemandId AS VideoEntityId,
										videoondemand.VideoOnDemandTitle as VideoName,
										IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
						IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
						IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
										videoondemand.VideoOnDemandLQVideo AS VideoStreamUrlLQ,
                                        videoondemand.VideoOnDemandMQVideo AS VideoStreamUrlMQ,
										videoondemand.VideoOnDemandHQVideo AS VideoStreamUrlHQ,
										null AS VideoChatGroupId,
                                        false AS IsVideoChannel,
										IF (videoondemandcategories.VideoOnDemandCategoryId IN (3,6,8),'2','3') AS VideoType,
										videoondemand.VideoOnDemandIsFree AS IsVideoFree,
										false AS IsVideoDVR,
										videoondemand.VideoOnDemandDescription as VideoDescription,
										videoondemand.VideoOnDemandTotalViews as VideoTotalViews,
										IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
										IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
										IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
										IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

	
										FROM videoondemand
	
										INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
											AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
										LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
										LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

										WHERE videoondemand.VideoOnDemandIsOnline=1
											AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
											AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
											AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
											AND videoondemand.VideoOnDemandId = :VideoOnDemandId
STR;
								break;
							default :
								return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
								break;
						}
						// echo $sql;
						$bind = array (
								':VideoOnDemandId' => $ChannelOrVODId,
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Video' ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Video' ) ) );
						}
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Video' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Video' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}     
	/**
	 * Function to Get All Available VODs List
	 *
	 * @param Request $request        	
	 * @param Response $response        	
	 */
	public static function getAllVODCategories(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$DateTime = filter_var ( $request->getAttribute ( 'DateTime' ), FILTER_SANITIZE_STRING );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					switch ($Platform) {
						case 'Android' :
						case 'android' :
						case 'Web' :
						case 'web' :
						case 'ios' :
						case 'IOS' :
							$sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryparentId AS VODTabId,
									videoondemand.VideoOnDemandCategoryId AS VODCategoryId,
									videoondemandcategories.VideoOnDemandCategoryname AS VODCategoryName,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice,
STR;
							$sql .= "
									IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VODCategoryThumbnailPath,
									IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VODCategoryImagePath,
									IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VODCategoryImagePathLarge,
									IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVODCategoryThumbnailPath,";
							
							$sql .= <<<STR
									videoondemandcategories.VideoOnDemandCategoryDescription AS VODCategoryDescription,
									(SELECT COUNT(vod.VideoOnDemandId) FROM videoondemand vod WHERE vod.VideoOnDemandCategoryId=videoondemandcategories.VideoOnDemandCategoryId) AS VODCategoryTotalVideos
					
							FROM videoondemand
			
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
								AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
							
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId

            LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
							WHERE videoondemand.VideoOnDemandIsOnline=1
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
								AND 
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
					
							GROUP BY VODCategoryId DESC;
STR;
							// echo $sql;
							$bind = array (
									':CountryCode' => $CountryCode 
							);
							$results = $db->run ( $sql, $bind );
							
							$sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryId AS VODTabId,
									videoondemandcategories.VideoOnDemandCategoryname AS VODTabName,
									videoondemandcategories.VideoOnDemandCategoryClickURL AS VODTabURL,
STR;
							$sql .= "
									IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VODTabThumbnailPath,
									IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VODTabImagePath,
									IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VODTabImagePathLarge,
									IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVideoOnDemandCategorythumbPath,";
							
							$sql .= <<<STR
									videoondemandcategories.IsVast AS IsVast,
									videoondemandcategories.AdvertisementVastURL AS AdvertisementVastURL,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
									IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
									IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

							FROM winettv.videoondemandcategories
					
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId

							LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
							WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
								AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
								AND videoondemandcategories.VideoOnDemandCategoryId NOT IN (3,6,8,1199)
								AND 
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemandcategories.VideoOnDemandCategoryId
											 IN (
												SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
												WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
													AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
											    )
									ELSE 1 END
					
							ORDER BY VODTabId;
STR;
							// echo $sql;
							$bind = array (
									':CountryCode' => $CountryCode 
							);
							$parentCategories = $db->run ( $sql, $bind );
							
							if ($results) {
								Format::formatResponseData ( $results );
								Format::formatResponseData ( $parentCategories );
								
								// Creating Parent Categories Array
								$i = 0;
								$tabArray = array ();
								Format::formatResponseData ( $parentCategories );
								foreach ( $parentCategories as $dataRow ) {
									$tabArray [$i] ['VODTabId'] = $dataRow ['VODTabId'];
									$tabArray [$i] ['VODTabName'] = $dataRow ['VODTabName'];
									$tabArray [$i] ['VODTabURL'] = $dataRow ['VODTabURL'];
									$tabArray [$i] ['VODTabThumbnailPath'] = $dataRow ['VODTabThumbnailPath'];
									$tabArray [$i] ['VODTabImagePath'] = $dataRow ['VODTabImagePath'];
									$tabArray [$i] ['VODTabImagePathLarge'] = $dataRow ['VODTabImagePathLarge'];
									$tabArray [$i] ['IsVast'] = $dataRow ['IsVast'];
									$tabArray [$i] ['AdvertisementVastURL'] = $dataRow ['AdvertisementVastURL'];
									$tabArray [$i] ['VODCategories'] = NULL;
									$i ++;
								}
								
								// Merging Category Array into Parent Categories Array
								foreach ( $results as $row ) {
									foreach ( $tabArray as $key => $assrow ) {
										if ($assrow ['VODTabId'] === $row ['VODTabId']) {
											$count = count ( $tabArray [$key] ['VODCategories'] );
											$tabArray [$key] ['VODCategories'] [$count] = array_splice ( $row, 1 );
											$flag = false;
										}
									}
								}
								
								return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $tabArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'VODTabs' ) ) );
							} else {
								return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'VODTabs' ) ) );
							}
							break;
						case 'androidoffline':
						case 'ANDROIDOFFLINE':
						    $Sql = <<<STR
							SELECT * FROM (
							
                                SELECT videoondemandcategories.VideoOnDemandCategoryparentId AS VideoParentCategoryId,
										videoondemandcategories.VideoOnDemandCategoryId AS VideoCategoryId,
									    videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
									    IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VideoCategoryThumbnailPath,
									    IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VideoCategoryImagePath,
									    IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VideoCategoryImagePathLarge,
									    IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVideoOnDemandCategorythumbPath,
									    videoondemandcategories.VideoOnDemandCategoryDescription AS VideoCategoryDescription,
										videoondemandcategories.VideoOnDemandCategoryIsOnline AS VideoCategoryIsOnline,
										videoondemandcategories.VideoOnDemandCategoryClickURL AS VideoCategoryURL,
										videoondemandcategories.IsVast AS IsVast,
										videoondemandcategories.AdvertisementVastURL AS AdvertisementVastURL,
									    videoondemandcategories.VideoOnDemandCategoryAddedDate AS VideoCategoryAddedDate,
									    videoondemandcategories.VideoOnDemandCategoryUpdatedDate AS VideoCategoryUpdatedDate
									    
									    
									    
							    FROM videoondemandcategories
							    
                                INNER JOIN videoondemand ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
								    AND videoondemandcategories.VideoOnDemandCategoryparentId <> 0
									AND
										CASE
				                        WHEN :CountryCode != 'PK'
										THEN
											videoondemandcategories.VideoOnDemandCategoryId
												 IN (
													SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
													WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
														AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
												    )
										ELSE 1 END
										
                            UNION ALL
                            
                                SELECT videoondemandcategories.VideoOnDemandCategoryparentId AS VideoParentCategoryId,
										videoondemandcategories.VideoOnDemandCategoryId AS VideoCategoryId,
									    videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
									    IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VideoCategoryThumbnailPath,
									    IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VideoCategoryImagePath,
									    IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VideoCategoryImagePathLarge,
										IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVideoOnDemandCategorythumbPath,
									    videoondemandcategories.VideoOnDemandCategoryDescription AS VideoCategoryDescription,
										videoondemandcategories.VideoOnDemandCategoryIsOnline AS VideoCategoryIsOnline,
										videoondemandcategories.VideoOnDemandCategoryClickURL AS VideoCategoryURL,
										videoondemandcategories.IsVast AS IsVast,
										videoondemandcategories.AdvertisementVastURL AS AdvertisementVastURL,
									    videoondemandcategories.VideoOnDemandCategoryAddedDate AS VideoCategoryAddedDate,
									    videoondemandcategories.VideoOnDemandCategoryUpdatedDate AS VideoCategoryUpdatedDate
									    
							    FROM videoondemandcategories
							    
							    WHERE videoondemandcategories.VideoOnDemandCategoryparentId = 0
									AND
										CASE
				                        WHEN :CountryCode != 'PK'
										THEN
											videoondemandcategories.VideoOnDemandCategoryId
												 IN (
													SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
													WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
														AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
												    )
										ELSE 1 END
										
                            ) AS jointVODs
STR;
						    $Sql .= "
									WHERE jointVODs.VideoCategoryUpdatedDate > '" . $DateTime . "'
									    
			                            GROUP BY jointVODs.VideoCategoryId
									    
			                            ORDER BY jointVODs.VideoCategoryId";
						    // echo $sql;
						    $bind = array(
						        ':ImagesDomainName' => Config::$imagesDomainName,
						        ':CountryCode' => $CountryCode
						    );
						    $results = $db->run($Sql, $bind);
						    
						    $Sql = <<<STR
							SELECT seasons.SeasonID AS SeasonCategoryId,
									seasons.SeasonNo AS SeasonNo,
                                    seasons.SeasonTitle AS SeasonTitle,
									IF(seasons.SeasonThumbnail NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.SeasonThumbnail ), seasons.SeasonThumbnail) AS SeasonThumbnailPath,
                                    IF(seasons.SeasonMobileSmallImage NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.SeasonMobileSmallImage ), seasons.SeasonMobileSmallImage) AS SeasonImagePath,
                                    IF(seasons.SeasonMobileLargeImage NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.SeasonMobileLargeImage ), seasons.SeasonMobileLargeImage) AS SeasonImagePathLarge,
									IF(seasons.NewSeasonThumbnail NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.NewSeasonThumbnail ), seasons.NewSeasonThumbnail) AS NewSeasonThumbnailPath,
                                    seasons.SeasonDescription AS SeasonDescription
							FROM seasons
STR;
						    $Sql .= "
									WHERE seasons.SeasonUpdatedDate > '" . $DateTime . "'";
						    // echo $sql;
						    $bind = array(
						        ':ImagesDomainName' => Config::$imagesDomainName
						    );
						    $seasons = $db->run($Sql, $bind);
						    
						    if ($results || $seasons) {
						        Format::formatResponseData($results, 1);
						        Format::formatResponseData($seasons);
						        return General::getResponse($response->write(SuccessObject::getSeasonsSuccessObject(Message::getMessage('M_DATA'), $results, $seasons)));
						    } else {
						        return General::getResponse($response->write(SuccessObject::getSeasonsSuccessObject(Message::getMessage('W_NO_CONTENT'))));
						    }
						    break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					break;
				case 'v2' :
				case 'V2' :
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					switch ($Platform) {
						case 'Android' :
						case 'android' :
						case 'Web' :
						case 'web' :
							$sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryparentId AS VODTabId,
									videoondemand.VideoOnDemandCategoryId AS VODCategoryId,
									videoondemandcategories.VideoOnDemandCategoryname AS VODCategoryName,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice,
STR;
							$sql .= "
									IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VODCategoryThumbnailPath,
									IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VODCategoryImagePath,
									IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VODCategoryImagePathLarge,
									IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVODCategoryThumbnailPath,";
							
							$sql .= <<<STR
									videoondemandcategories.VideoOnDemandCategoryDescription AS VODCategoryDescription,
									(SELECT COUNT(vod.VideoOnDemandId) FROM videoondemand vod WHERE vod.VideoOnDemandCategoryId=videoondemandcategories.VideoOnDemandCategoryId) AS VODCategoryTotalVideos,
                                    NULL AS VODCategorySeasons
					
							FROM videoondemand
			
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
								AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
			LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId

            LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
							WHERE videoondemand.VideoOnDemandIsOnline=1
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
								AND 
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
					
							GROUP BY VODCategoryId DESC;
STR;
							// echo $sql;
							$bind = array (
									':CountryCode' => $CountryCode 
							);
							$results = $db->run ( $sql, $bind );
							
							$sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryId AS VODTabId,
									videoondemandcategories.VideoOnDemandCategoryname AS VODTabName,
									videoondemandcategories.VideoOnDemandCategoryClickURL AS VODTabURL,
STR;
							$sql .= "
									IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VODTabThumbnailPath,
									IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VODTabImagePath,
									IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VODTabImagePathLarge,
									IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVideoOnDemandCategorythumbPath,";
							
							$sql .= <<<STR
									videoondemandcategories.IsVast AS IsVast,
									videoondemandcategories.AdvertisementVastURL AS AdvertisementVastURL,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
									IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

							FROM winettv.videoondemandcategories
					LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId

							LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
							WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
								AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
								AND videoondemandcategories.VideoOnDemandCategoryId NOT IN (3,6,8)
								AND 
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemandcategories.VideoOnDemandCategoryId
											 IN (
												SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
												WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
													AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
											    )
									ELSE 1 END
					
							ORDER BY VODTabId;
STR;
							// echo $sql;
							$bind = array (
									':CountryCode' => $CountryCode 
							);
							$parentCategories = $db->run ( $sql, $bind );
							
							$sql = <<<STR
							SELECT seasons.SeasonID AS VODCategoryId,
									seasons.SeasonNo AS SeasonNo,
                                    seasons.SeasonTitle AS SeasonTitle,
									IF(seasons.SeasonThumbnail NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.SeasonThumbnail ), seasons.SeasonThumbnail) AS SeasonThumbnailPath,
                                    IF(seasons.SeasonMobileSmallImage NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.SeasonMobileSmallImage ), seasons.SeasonMobileSmallImage) AS SeasonImagePath,
                                    IF(seasons.SeasonMobileLargeImage NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.SeasonMobileLargeImage ), seasons.SeasonMobileLargeImage) AS SeasonImagePathLarge,
									IF(seasons.NewSeasonThumbnail NOT LIKE 'http://%',CONCAT( :ImagesDomainName, seasons.NewSeasonThumbnail ), seasons.NewSeasonThumbnail) AS NewSeasonThumbnailPath,
                                    seasons.SeasonDescription AS SeasonDescription
							FROM seasons;
STR;
							// echo $sql;
							$bind = array (
									':ImagesDomainName' => Config::$imagesDomainName 
							);
							$seasons = $db->run ( $sql, $bind );
							
							// print_r($seasons);
							
							if ($results) {
								Format::formatResponseData ( $results );
								Format::formatResponseData ( $parentCategories );
								Format::formatResponseData ( $seasons );
								
								// Creating Parent Categories Array
								$i = 0;
								$tabArray = array ();
								Format::formatResponseData ( $parentCategories );
								foreach ( $parentCategories as $dataRow ) {
									$tabArray [$i] ['VODTabId'] = $dataRow ['VODTabId'];
									$tabArray [$i] ['VODTabName'] = $dataRow ['VODTabName'];
									$tabArray [$i] ['VODTabURL'] = $dataRow ['VODTabURL'];
									$tabArray [$i] ['VODTabThumbnailPath'] = $dataRow ['VODTabThumbnailPath'];
									$tabArray [$i] ['VODTabImagePath'] = $dataRow ['VODTabImagePath'];
									$tabArray [$i] ['VODTabImagePathLarge'] = $dataRow ['VODTabImagePathLarge'];
									$tabArray [$i] ['IsVast'] = $dataRow ['IsVast'];
									$tabArray [$i] ['AdvertisementVastURL'] = $dataRow ['AdvertisementVastURL'];
									$tabArray [$i] ['VODCategories'] = NULL;
									$i ++;
								}
								
								// Merging Seasons into Category Array
								foreach ( $seasons as $row ) {
									foreach ( $results as $key => $assrow ) {
										if ($assrow ['VODCategoryId'] === $row ['VODCategoryId']) {
											$count = count ( $results [$key] ['VODCategorySeasons'] );
											$results [$key] ['VODCategorySeasons'] [$count] = $row;
										}
										
										if ($results [$key] ['VODCategorySeasons'] == NULL) {
											$results [$key] ['VODCategorySeasons'] = array ();
										}
									}
								}
								
								// Merging Category Array into Parent Categories Array
								foreach ( $results as $row ) {
									foreach ( $tabArray as $key => $assrow ) {
										if ($assrow ['VODTabId'] === $row ['VODTabId']) {
											$count = count ( $tabArray [$key] ['VODCategories'] );
											$tabArray [$key] ['VODCategories'] [$count] = array_splice ( $row, 1 );
											$flag = false;
										}
									}
								}
								
								return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $tabArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'VODTabs' ) ) );
							} else {
								return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'VODTabs' ) ) );
							}
							break;
						case 'tv' :
						case 'TV' :
							$sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryparentId AS VODParentCategoryId,
									videoondemandcategories.VideoOnDemandCategoryId AS VODCategoryId,
									videoondemandcategories.VideoOnDemandCategoryname AS VODCategoryName,
STR;
							$sql .= "
									IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VODCategoryThumbnailPath,
									IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VODCategoryImagePath,
									IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VODCategoryImagePathLarge,
									IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVODCategoryThumbnailPath,";
							
							$sql .= <<<STR
									videoondemandcategories.VideoOnDemandCategoryDescription AS VODCategoryDescription,
									videoondemandcategories.VideoOnDemandCategoryAddedDate AS VODCategoryAddedDate
									
									
					
							FROM videoondemandcategories
			
							WHERE videoondemandcategories.VideoOnDemandCategoryIsOnline=1
STR;
							// echo $sql;
							$bind = array (
									':CountryCode' => $CountryCode 
							);
							$results = $db->run ( $sql, $bind );
							
							if ($results) {
								Format::formatResponseData ( $results, 1 );
								return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'VODCategories' ) ) );
							} else {
								return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'VODCategories' ) ) );
							}
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'VODTabs' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'VODTabs' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getVODsByCategoryWithOutLimit(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$CategoryId = $request->getAttribute ( 'CategoryId' );
		$DateTime = filter_var ( $request->getAttribute ( 'DateTime' ), FILTER_SANITIZE_STRING );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					switch ($Platform) {
						case 'AndroidOffline' :
						case 'androidoffline' :
						case 'ANDROIDOFFLINE' :
							$Sql = <<<STR
							SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
									videoondemand.VideoOnDemandTitle AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
									videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsOnline AS IsVideoOnline,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree,
									false AS IsVideoChannel,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandUpdatedDate AS VideoUpdatedDate,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

	
									FROM videoondemand
							
									INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
									LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
									LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
									WHERE videoondemand.VideoOnDemandCategoryId = :CategoryId
										AND
											CASE
					                        WHEN :CountryCode != 'PK'
											THEN
												videoondemand.VideoOnDemandCategoryId
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
							$Sql .= " AND videoondemand.VideoOnDemandUpdatedDate > '" . $DateTime . "'
							
						                GROUP BY VideoEntityId
										ORDER BY VideoAddedDate;";
							// echo $sql;
							$bind = Array (
									':CountryCode' => $CountryCode,
									':ImagesDomainName' => Config::$imagesDomainName,
									':CategoryId' => $CategoryId 
							);
							$results = $db->run ( $Sql, $bind );
							
							if ($results) {
								Format::formatResponseData ( $results, 1 );
								
								return General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), NULL, $results ) ) );
							} else {
								return General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
							}
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					), NULL, NULL, 'Videos' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Videos' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Videos' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getVODsByCategory(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$CategoryId = $request->getAttribute ( 'CategoryId' );
		$OffSet = $request->getAttribute ( 'OffSet' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					switch ($Platform) {
						case 'Android' :
						case 'android' :
							$sql = <<<STR
							SELECT videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
							videoondemand.VideoOnDemandId AS VideoEntityId,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
							IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							false AS IsVideoChannel,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice					
							FROM videoondemand
				
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
							
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId

							LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
				
							WHERE videoondemand.VideoOnDemandIsOnline=1
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
								AND (videoondemand.VideoOnDemandCategoryId = :CategoryId1 OR videoondemand.VideoOnDemandCategoryId = :CategoryId2)
								AND
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
				
			                GROUP BY VideoEntityId
							ORDER BY VideoAddedDate DESC
STR;
							$sql .= " LIMIT " . Config::$getVODsAndMoviesLimit . " OFFSET " . $OffSet;
							break;
						case 'ios' :
						case 'IOS' :
							$sql = <<<STR
							SELECT videoondemand.VideoOnDemandCategoryId AS VideoCategory,
							videoondemand.VideoOnDemandId AS VideoEntityId,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
							IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
							IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
							IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoImageThumbnail,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							false AS IsVideoChannel
			
							FROM videoondemand
							
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
							
							WHERE videoondemand.VideoOnDemandIsOnline=1
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
								AND (videoondemand.VideoOnDemandCategoryId = :CategoryId1 OR videoondemand.VideoOnDemandCategoryId = :CategoryId2)
								AND
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
							
			                GROUP BY VideoEntityId
							ORDER BY VideoAddedDate DESC
STR;
							// echo $sql;
							$bind = Array (
									':CountryCode' => $CountryCode,
									':ImagesDomainName' => Config::$imagesDomainName,
									':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
									':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId 
							);
							$results = $db->run ( $sql, $bind );
							
							if ($results) {
								Format::formatResponseData ( $results );
								
								return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Videos' ) ) );
							} else {
								return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Videos' ) ) );
							}
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					// echo $sql;
					$bind = Array (
							':CountryCode' => $CountryCode,
							':ImagesDomainName' => Config::$imagesDomainName,
							':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
							':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId 
					);
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						Format::formatResponseData ( $results );
						
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), Config::$getVODsAndMoviesLimit, count ( $results ), 'Videos' ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), Config::$getVODsAndMoviesLimit, count ( $results ), 'Videos' ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					), NULL, NULL, 'Videos' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Videos' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Videos' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getVODsBySeason(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$CategoryId = $request->getAttribute ( 'CategoryId' );
		$SeasonNo = $request->getAttribute ( 'SeasonNo' );
		$OffSet = $request->getAttribute ( 'OffSet' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					switch ($Platform) {
						case 'Android' :
						case 'android' :
							$sql = <<<STR
							SELECT videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
                            videoondemand.VideoOnDemandSeasonNo AS VideoSeasonNo,
							videoondemand.VideoOnDemandId AS VideoEntityId,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
							IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							false AS IsVideoChannel,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

					
							FROM videoondemand
				
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

							WHERE videoondemand.VideoOnDemandIsOnline=1
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
								AND (videoondemand.VideoOnDemandCategoryId = :CategoryId1 OR videoondemand.VideoOnDemandCategoryId = :CategoryId2)
                                AND videoondemand.VideoOnDemandSeasonNo = :SeasonNo
								AND
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
				
			                GROUP BY VideoEntityId
							ORDER BY VideoAddedDate DESC
STR;
							$sql .= " LIMIT " . Config::$getVODsAndMoviesLimit . " OFFSET " . $OffSet;
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					// echo $sql;
					$bind = Array (
							':CountryCode' => $CountryCode,
							':ImagesDomainName' => Config::$imagesDomainName,
							':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
							':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId,
							':SeasonNo' => $SeasonNo 
					);
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						Format::formatResponseData ( $results );
						
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), Config::$getVODsAndMoviesLimit, count ( $results ), 'Videos' ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), Config::$getVODsAndMoviesLimit, count ( $results ), 'Videos' ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					), NULL, NULL, 'Videos' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Videos' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Videos' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getAllMoviesWithCategories(Request $request, Response $response) {
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language ); 
		$Platform = $request->getAttribute ( 'Platform' );
		$DateTime = filter_var ( $request->getAttribute ( 'DateTime' ), FILTER_SANITIZE_STRING );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			
			include_once '../geoip/geoip.php';
			$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
			// echo $CountryCode;
			// $CountryCode = 'PK';
			switch ($Platform) {
				case 'Web' :
				case 'web' :
				case 'TV' :
				case 'tv' :
				case 'android' :
				case 'Android' :
				case 'ANDROID' :
					$sql = <<<STR
					SELECT videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
							videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
							videoondemand.VideoOnDemandId AS VideoEntityId,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
							IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							false AS IsVideoChannel,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

								
							FROM videoondemand
							
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
							AND videoondemandcategories.VideoOnDemandCategoryId IN (3,6,8)
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
							WHERE videoondemand.VideoOnDemandIsOnline=1
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
								AND 
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
							
			                GROUP BY VideoEntityId
							ORDER BY VideoCategoryName,VideoAddedDate DESC
STR;
					// echo $sql;
					$bind = Array (
							':CountryCode' => $CountryCode,
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results, 1 );
						$i = 0;
						$assArray = array ();
						// $assArray [0] ['VideoCategoryId'] = - 1;
						// $assArray [0] ['VideoCategoryName'] = 'All';
						// $assArray [0] ['TotalItems'] = 0;
						// $assArray [0] ['CurrentItems'] = 1;
						// $assArray [0] ['Videos'] = [ ];
						foreach ( $results as $key => $row ) {
							// print_r($row );
							$flag = true;
							foreach ( $assArray as $key => $assrow ) {
								// print_r($row );
								if ($assrow ['VideoCategoryName'] === $row ['VideoCategoryName']) {
									
									$tempRow = array_splice ( $row, 1 );
									
									$countSingle = count ( $assArray [$key] ['Videos'] );
									$assArray [$key] ['TotalItems'] += 1;
									// if ($countSingle < Config::$WebPageSize) {
									// $assArray [$key] ['CurrentItems'] = $countSingle + 1;
									$assArray [$key] ['Videos'] [$countSingle] = $tempRow;
									// }
									
									// $countAll = count ( $assArray [0] ['Videos'] );
									// $assArray [0] ['TotalItems'] += 1;
									// if ($countAll < Config::$WebPageSize) {
									// $assArray [0] ['CurrentItems'] = $countAll + 1;
									// ;
									// $assArray [0] ['Videos'] [$countAll] = $tempRow;
									// }
									$flag = false;
								}
							}
							if ($flag) {
								$assArray [$i] ['VideoCategoryId'] = $row ['VideoCategoryId'];
								$assArray [$i] ['VideoCategoryName'] = $row ['VideoCategoryName'];
								$tempRow = array_splice ( $row, 1 );
								$assArray [$i] ['TotalItems'] = 1;
								// $assArray [$i] ['CurrentItems'] = 1;
								$assArray [$i] ['CurrentItems'] = Config::$WebPageSize;
								$assArray [$i] ['Videos'] [] = $tempRow;
								
								// $countAll = count ( $assArray [0] ['Videos'] );
								// $assArray [0] ['TotalItems'] += 1;
								// if ($countAll < Config::$WebPageSize) {
								// $assArray [0] ['CurrentItems'] = $countAll + 1;
								// $assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
								// }
								// print_r($assArray[$i]);
								$i ++;
							}
						}
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) );
					}
					break;
				case 'androidoffline' :
				case 'ANDROIDOFFLINE' :
					$Sql = <<<STR
					SELECT videoondemandcategories.VideoOnDemandCategoryparentId AS VideoParentCategoryId,
							videoondemandcategories.VideoOnDemandCategoryId AS VideoCategoryId,
						    videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
						    IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VideoCategoryThumbnailPath,
						    IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VideoCategoryImagePath,
						    IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VideoCategoryImagePathLarge,
						    IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName,videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVideoCategoryThumbnailPath,
						    videoondemandcategories.VideoOnDemandCategoryDescription AS VideoCategoryDescription,
							videoondemandcategories.VideoOnDemandCategoryIsOnline AS VideoCategoryIsOnline,
							videoondemandcategories.VideoOnDemandCategoryClickURL AS VideoCategoryURL,
							videoondemandcategories.IsVast AS IsVast,
							videoondemandcategories.AdvertisementVastURL AS AdvertisementVastURL,
						    videoondemandcategories.VideoOnDemandCategoryAddedDate AS VideoCategoryAddedDate,
						    videoondemandcategories.VideoOnDemandCategoryUpdatedDate AS VideoCategoryUpdatedDate
							
					FROM videoondemandcategories
							
					WHERE videoondemandcategories.VideoOnDemandCategoryId IN (3,6,8)
						AND
							CASE
	                        WHEN :CountryCode != 'PK'
							THEN
								videoondemandcategories.VideoOnDemandCategoryId
									 IN (
										SELECT videoondemandcategories.VideoOnDemandCategoryId FROM videoondemandcategories
										WHERE videoondemandcategories.VideoOnDemandCategoryparentId=0
											AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally=1
									    )
							ELSE 1 END
STR;
					$Sql .= "
						AND videoondemandcategories.VideoOnDemandCategoryUpdatedDate > '" . $DateTime . "'
							
					GROUP BY videoondemandcategories.VideoOnDemandCategoryId
							
					ORDER BY videoondemandcategories.VideoOnDemandCategoryId";
					// echo $sql;
					$bind = array (
							':ImagesDomainName' => Config::$imagesDomainName,
							':CountryCode' => $CountryCode 
					);
					$CategoriesArray = $db->run ( $Sql, $bind );
					
					$Sql = <<<STR
					SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
							videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsOnline AS IsVideoOnline,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							false AS IsVideoChannel,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandUpdatedDate AS VideoUpdatedDate,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice


							FROM videoondemand
					
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
								AND videoondemandcategories.VideoOnDemandCategoryId IN (3,6,8)
					
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId
							
							LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
							WHERE CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
					$Sql .= "
								AND videoondemand.VideoOnDemandUpdatedDate > '" . $DateTime . "'
										
							GROUP BY VideoEntityId
							ORDER BY VideoAddedDate;";
					// echo $Sql;
					$Bind = array (
							':CountryCode' => $CountryCode,
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					
					$VODsArray = $db->run ( $Sql, $Bind );
					// Formatting the Data
					Format::formatResponseData ( $CategoriesArray, 1 );
					Format::formatResponseData ( $VODsArray, 1 );
					return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $CategoriesArray, $VODsArray ) ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Categories' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getAllVODsWithCategories(Request $request, Response $response) {
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			
			include_once '../geoip/geoip.php';
			$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
			// echo $CountryCode;
			// $CountryCode = 'PK';
			switch ($Platform) {
				case 'Web' :
				case 'web' :
				case 'android' :
				case 'Android' :
				case 'ANDROID' :
					$sql = <<<STR
					SELECT videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
							videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
							videoondemand.VideoOnDemandId AS VideoEntityId,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
							IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							false AS IsVideoChannel,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

	
							FROM videoondemand
				
							INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
							AND videoondemandcategories.VideoOnDemandCategoryId NOT IN (3,6,8)
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

							WHERE videoondemand.VideoOnDemandIsOnline=1
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
								AND
									CASE
			                        WHEN :CountryCode != 'PK'
									THEN
										videoondemand.VideoOnDemandCategoryId
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
				
			                GROUP BY VideoEntityId
							ORDER BY VideoCategoryName,VideoAddedDate DESC
STR;
					// echo $sql;
					$bind = Array (
							':CountryCode' => $CountryCode,
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results );
						$i = 0;
						$assArray = array ();
						// $assArray [0] ['VideoCategoryId'] = - 1;
						// $assArray [0] ['VideoCategoryName'] = 'All';
						// $assArray [0] ['TotalItems'] = 0;
						// $assArray [0] ['CurrentItems'] = 1;
						// $assArray [0] ['Videos'] = [ ];
						foreach ( $results as $key => $row ) {
							// print_r($row );
							$flag = true;
							foreach ( $assArray as $key => $assrow ) {
								// print_r($row );
								if ($assrow ['VideoCategoryName'] === $row ['VideoCategoryName']) {
									
									$tempRow = array_splice ( $row, 1 );
									
									$countSingle = count ( $assArray [$key] ['Videos'] );
									$assArray [$key] ['TotalItems'] += 1;
									// if ($countSingle < Config::$WebPageSize) {
									// $assArray [$key] ['CurrentItems'] = $countSingle + 1;
									$assArray [$key] ['Videos'] [$countSingle] = $tempRow;
									// }
									
									// $countAll = count ( $assArray [0] ['Videos'] );
									// $assArray [0] ['TotalItems'] += 1;
									// if ($countAll < Config::$WebPageSize) {
									// $assArray [0] ['CurrentItems'] = $countAll + 1;
									// ;
									// $assArray [0] ['Videos'] [$countAll] = $tempRow;
									// }
									$flag = false;
								}
							}
							if ($flag) {
								$assArray [$i] ['VideoCategoryId'] = $row ['VideoCategoryId'];
								$assArray [$i] ['VideoCategoryName'] = $row ['VideoCategoryName'];
								$tempRow = array_splice ( $row, 1 );
								$assArray [$i] ['TotalItems'] = 1;
								// $assArray [$i] ['CurrentItems'] = 1;
								$assArray [$i] ['CurrentItems'] = Config::$WebPageSize;
								$assArray [$i] ['Videos'] [] = $tempRow;
								
								// $countAll = count ( $assArray [0] ['Videos'] );
								// $assArray [0] ['TotalItems'] += 1;
								// if ($countAll < Config::$WebPageSize) {
								// $assArray [0] ['CurrentItems'] = $countAll + 1;
								// $assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
								// }
								// print_r($assArray[$i]);
								$i ++;
							}
						}
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) );
					}
					break;
				case 'ios' :
				case 'IOS' :
					$Sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryId AS VideoCategoryId,
									videoondemandcategories.VideoOnDemandCategoryparentId AS VideoParentCategoryId,
									videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
									IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb) AS VideoCategoryImageThumbnail,
									IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VideoCategoryImagePath,
									IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VideoCategoryImagePathLarge,
									IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVideoCategoryImageThumbnail,
									videoondemandcategories.VideoOnDemandCategoryDescription AS VideoCategoryDescription
							FROM videoondemandcategories
	
							WHERE videoondemandcategories.VideoOnDemandCategoryIsOnline=1
								AND videoondemandcategories.VideoOnDemandCategoryId NOT IN (3,6,8)
				
							ORDER BY VideoCategoryId
STR;
					// echo $Sql;
					$Bind = array (
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					
					$CategoriesArray = $db->run ( $Sql, $Bind );
					if ($CategoriesArray) {
						$Sql = <<<STR
								SELECT videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
								videoondemand.VideoOnDemandId AS VideoEntityId,
								videoondemand.VideoOnDemandTitle AS VideoName,
								videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
								NULL AS VideoPosterPath,
								IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
								IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
								IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
								videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
								videoondemand.VideoOnDemandHDVideo AS VideoStreamUrl,
								videoondemand.VideoOnDemandSDVideo AS VideoStreamUrlLow,
								videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
								NULL AS VideoPackageId,
								NULL AS VideoRating,
								false AS IsVideoChannel
				
								FROM videoondemand
	
								INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
									AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
									AND videoondemandcategories.VideoOnDemandCategoryId NOT IN (3,6,8)
				
								WHERE videoondemand.VideoOnDemandIsOnline=1
									AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
									AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
									AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
	
				                GROUP BY VideoEntityId
								ORDER BY VideoEntityId
STR;
						// echo $Sql;
						$Bind = array (
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						
						$VODsArray = $db->run ( $Sql, $Bind );
						if ($VODsArray) {
							// Formatting the Data
							Format::formatResponseData ( $CategoriesArray );
							Format::formatResponseData ( $VODsArray );
							return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $CategoriesArray, $VODsArray ) ) ) );
						} else {
							return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
						}
					} else {
						return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Categories' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getMoviesByPage(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$CategoryId = $request->getAttribute ( 'CategoryId' );
		$PageNumber = ( int ) $request->getAttribute ( 'PageNumber' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
				
					$sql = <<<STR
					SELECT SQL_CALC_FOUND_ROWS videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
					videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
					videoondemand.VideoOnDemandId AS VideoEntityId,
					videoondemand.VideoOnDemandTitle AS VideoName,
					videoondemand.VideoOnDemandDescription AS VideoDescription,
					IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
					IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
					IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
					IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
					videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
					NULL AS VideoRating,
					videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
					videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
					videoondemand.VideoOnDemandIsFree AS IsVideoFree,
					false AS IsVideoChannel,
					IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
					IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
					IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
					IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

		
					FROM videoondemand
	
					INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
					AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
					LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

					WHERE videoondemand.VideoOnDemandIsOnline=1
						AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
						AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
						AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
						AND (videoondemand.VideoOnDemandCategoryId = :CategoryId1 OR videoondemand.VideoOnDemandCategoryId = :CategoryId2)
						AND 
							CASE
	                        WHEN :CountryCode != 'PK'
							THEN
								videoondemand.VideoOnDemandCategoryId
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
	
	                GROUP BY VideoEntityId
					ORDER BY VideoAddedDate
STR;
					$sql .= " LIMIT " . Config::$WebPageSize . " OFFSET " . ($PageNumber - 1) * Config::$WebPageSize;
					// echo $sql;
					$bind = Array (
							':CountryCode' => $CountryCode,
							':ImagesDomainName' => Config::$imagesDomainName,
							':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
							':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId 
					);
					$results = $db->run ( $sql, $bind );
					
					$sql = "SELECT FOUND_ROWS();";
					$TotalItems = $db->run ( $sql, $bind );
					
					if ($results) {
						// Formatting the Data
						Format::formatResponseData ( $results );
						Format::formatResponseData ( $TotalItems );
						return General::getResponse ( $response->write ( SuccessObject::getPageVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), (Config::$WebPageSize > $TotalItems [0] ['FOUND_ROWS()'] ? $TotalItems [0] ['FOUND_ROWS()'] : Config::$WebPageSize), $TotalItems [0] ['FOUND_ROWS()'] ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					), NULL, NULL, 'Videos' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Videos' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Videos' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getVODsByPage(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$CategoryId = $request->getAttribute ( 'CategoryId' );
		$PageNumber = ( int ) $request->getAttribute ( 'PageNumber' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					$sql = <<<STR
					SELECT SQL_CALC_FOUND_ROWS videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
					videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
					videoondemand.VideoOnDemandId AS VideoEntityId,
					videoondemand.VideoOnDemandTitle AS VideoName,
					IFNULL(videoondemand.VideoOnDemandEpisodeNo,0) AS VideoEpisodeNo,
					videoondemand.VideoOnDemandDescription AS VideoDescription,
					IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
					videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
					NULL AS VideoRating,
					videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
					videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
					videoondemand.VideoOnDemandIsFree AS IsVideoFree,
					false AS IsVideoChannel,
					IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

			
					FROM videoondemand
		
					INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
					AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
					LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

					WHERE videoondemand.VideoOnDemandIsOnline=1
						AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
						AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
						AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
						AND (videoondemand.VideoOnDemandCategoryId = :CategoryId1 OR videoondemand.VideoOnDemandCategoryId = :CategoryId2)
						AND 
							CASE
	                        WHEN :CountryCode != 'PK'
							THEN
								videoondemand.VideoOnDemandCategoryId
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
					
	                GROUP BY VideoEntityId
					ORDER BY VideoEpisodeNo DESC, VideoAddedDate DESC
STR;
					$sql .= " LIMIT " . Config::$WebPageSize . " OFFSET " . ($PageNumber - 1) * Config::$WebPageSize;
					// echo $sql;
					$bind = Array (
							':ImagesDomainName' => Config::$imagesDomainName,
							':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
							':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId,
							':CountryCode' => $CountryCode 
					);
					$results = $db->run ( $sql, $bind );
					
					$sql = "SELECT FOUND_ROWS();";
					$TotalItems = $db->run ( $sql, $bind );
					
					if ($results) {
						// Formatting the Data
						Format::formatResponseData ( $results );
						Format::formatResponseData ( $TotalItems );
						
						return General::getResponse ( $response->write ( SuccessObject::getPageVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), (Config::$WebPageSize > $TotalItems [0] ['FOUND_ROWS()'] ? $TotalItems [0] ['FOUND_ROWS()'] : Config::$WebPageSize), $TotalItems [0] ['FOUND_ROWS()'] ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					), NULL, NULL, 'Videos' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Videos' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Videos' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getAllMoviesCategories(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					$sql = <<<STR
					SELECT videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
					videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
					videoondemandcategories.VideoOnDemandCategoryClickURL AS VideoCategoryURL,
STR;
					$sql .= "
					IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategorythumb),videoondemandcategories.VideoOnDemandCategorythumb) AS VideoCategoryThumbnailPath,
					IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.NewVideoOnDemandCategorythumb),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewVideoCategoryThumbnailPath,
					IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMobileSmall),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS VideoCategoryImagePath,
					IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT('" . Config::$imagesDomainName . "',videoondemandcategories.VideoOnDemandCategoryMoblieLarge),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS VideoCategoryImagePathLarge,
			";
					
					$sql .= <<<STR
					videoondemandcategories.IsVast AS IsVast,
					videoondemandcategories.AdvertisementVastURL AS AdvertisementVastURL,
					IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

					FROM winettv.videoondemand
			
					INNER JOIN winettv.videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
					AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
					AND videoondemandcategories.VideoOnDemandCategoryId IN (3,6,8)
			LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

					WHERE videoondemand.VideoOnDemandIsOnline=1
						AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
						AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
						AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
						AND 
							CASE
	                        WHEN :CountryCode != 'PK'
							THEN
								videoondemand.VideoOnDemandCategoryId
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

	                GROUP BY VideoCategoryId
					ORDER BY VideoCategoryName
STR;
					// echo $sql;
					$bind = Array (
							':CountryCode' => $CountryCode 
					);
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results );
						$i = 0;
						$assArray = array ();
						// TODO : Make All Category Dynamic
						// $assArray [0] ['VideoCategoryId'] = - 1;
						// $assArray [0] ['VideoCategoryName'] = 'All Movies';
						// $assArray [0] ['VideoCategoryThumbnailPath'] = 'http://www.pitelevision.com/images/channels/category/All.jpg';
						// $assArray [0] ['VideoCategoryImagePath'] = '';
						// $assArray [0] ['VideoCategoryImagePathLarge'] = '';
						foreach ( $results as $key => $row ) {
							$assArray [$i ++] = $row;
						}
						
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					), NULL, NULL, 'Categories' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Categories' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Categories' ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	/**
	 * Function to Get All Available Channels List
	 *
	 * @param Request $request        	
	 * @param Response $response        	
	 */
	public static function getAllChannelsWithCategories(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$DateTime = filter_var ( $request->getAttribute ( 'DateTime' ), FILTER_SANITIZE_STRING );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					switch ($Platform) {
						case 'Android' :
						case 'android' :
							$Sql = <<<STR
							SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
							channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
							channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
							channelcategories.IsVast AS IsVast,
							channelcategories.AdvertisementVastURL AS AdvertisementVastURL,
							channels.ChannelId AS VideoEntityId,
							channels.ChannelName AS VideoName,
							channels.ChannelDescription AS VideoDescription,
							IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
							IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
							IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
							IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
							channels.ChannelCategory AS VideoCategoryId,
							packages.PackageId AS VideoPackageId,
							channels.ChannelTotalViews AS VideoTotalViews,
							channels.ChannelRating AS VideoRating,
							channels.ChannelAddedDate AS VideoAddedDate,
							NULL AS VideoDuration,
							IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
							IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
							channels.ChannelRssFeedUrl AS VideoRssFeedURL,
							IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

									
							FROM channels
				
							INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
				
							LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
				
							LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
				
							WHERE channels.ChannelIsOnline=1
								AND packages.PackageId IN (6,7,8,10)
								AND
									CASE
		                            WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
									THEN
										CASE
		                                WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
										WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
		                                ELSE 1 END
									WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
									THEN 0
		                            ELSE 1 END
				
			                GROUP BY VideoEntityId
							ORDER BY channelcategories.ChannelSequenceNumber DESC, VideoTotalViews DESC;
STR;
							break;
						case 'Web' :
						case 'web' :
							$Sql = <<<STR
							SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
							channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
							channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
							1 AS IsVast,
							'https://bs.serving-sys.com/Serving?cn=display&c=23&pl=VAST&pli=20460720&PluID=0&pos=623&ord=[timestamp]&cim=1' AS AdvertisementVastURL,
							channels.ChannelId AS VideoEntityId,
							channels.ChannelName AS VideoName,
							channels.ChannelDescription AS VideoDescription,
							IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
							IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
							IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
							IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
							channels.ChannelCategory AS VideoCategoryId,
							packages.PackageId AS VideoPackageId,
							channels.ChannelTotalViews AS VideoTotalViews,
							channels.ChannelRating AS VideoRating,
							channels.ChannelAddedDate AS VideoAddedDate,
							NULL AS VideoDuration,
							IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree,
							true AS IsVideoChannel
					
							FROM winettv.channels
							
							INNER JOIN winettv.channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
							LEFT JOIN winettv.packagechannels ON channels.ChannelId =	packagechannels.channelId
							
							LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId
							
							WHERE channels.ChannelIsOnline=1
								AND packages.PackageId IN (6,7,8)
								AND
									CASE
		                            WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
									THEN
										CASE
		                                WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
										WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
		                                ELSE 1 END
									WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
									THEN 0
		                            ELSE 1 END
							
			                GROUP BY VideoEntityId
							ORDER BY VideoTotalViews DESC;
STR;
							break;
						case 'androidoffline' :
						case 'ANDROIDOFFLINE' :
							$Sql = <<<STR
							SELECT * FROM (
								SELECT channels.ChannelCategory AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
										channelcategories.IsVast AS IsVast,
										channelcategories.AdvertisementVastURL AS AdvertisementVastURL,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber,
										channelcategories.ChannelCategoryAddedDate AS VideoCategoryAddedDate,
										channelcategories.ChannelCategoryUpdatedDate AS VideoCategoryUpdatedDate,
										IF(channelcategories.ChannelCategoryId=11,true,false) AS IsRadio
									
										FROM channels
							
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
							
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
							
										WHERE packages.PackageId IN (6,8,15,16,2)
							
						                GROUP BY VideoCategoryId
									
							UNION ALL
							
								SELECT channelcategories.ChannelCategoryId AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
										channelcategories.IsVast AS IsVast,
										channelcategories.AdvertisementVastURL AS AdvertisementVastURL,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber,
										channelcategories.ChannelCategoryAddedDate AS VideoCategoryAddedDate,
										channelcategories.ChannelCategoryUpdatedDate AS VideoCategoryUpdatedDate,
										IF(channelcategories.ChannelCategoryId=11,true,false) AS IsRadio
									
										FROM channelcategories
										WHERE channelcategories.ChannelCategoryId=1
							) cats
STR;
							$Sql .= " WHERE cats.VideoCategoryUpdatedDate > '" . $DateTime . "'
										ORDER BY cats.VideoCategorySequenceNumber DESC, cats.VideoCategoryName;";
							// echo $Sql;
							$Bind = array (
									':ImagesDomainName' => Config::$imagesDomainName 
							);
							$CategoriesArray = $db->run ( $Sql, $Bind );
							$Sql = <<<STR
								SELECT channels.ChannelId AS VideoEntityId,
										channels.ChannelName AS VideoName,
										channels.ChannelDescription AS VideoDescription,
										IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
										IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
										IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
										channels.ChannelCategory AS VideoCategoryId,
										packages.PackageId AS VideoPackageId,
										channels.ChannelTotalViews AS VideoTotalViews,
										channels.ChannelRating AS VideoRating,
										NULL AS VideoDuration,
										channels.ChannelIsOnline AS IsVideoOnline,
										IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
										IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
										channels.ChannelIsAllowedInternationally AS VideoIsAllowedInternationally,
										channels.ChannelAllowCountryCodeList AS VideoAllowCountryCodeList,
										channels.ChannelCountryCodeList AS VideoCountryCodeList,
										channels.ChannelRssFeedUrl AS VideoRssFeedURL,
										channels.ChannelAddedDate AS VideoAddedDate,
										channels.ChannelUpdatedDate AS VideoUpdatedDate,
										IF(channelcategories.ChannelCategoryId=11,true,false) AS IsRadio
					
										FROM channels
										
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
							
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
							
										WHERE packages.PackageId IN (6,7,8,10)
											AND
												CASE
					                            WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
												THEN
													CASE
					                                WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
													WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
					                                ELSE 1 END
												WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
												THEN 0
					                            ELSE 1 END
STR;
							$Sql .= " AND channels.ChannelUpdatedDate > '" . $DateTime . "'
						                GROUP BY VideoEntityId
										ORDER BY channelcategories.ChannelSequenceNumber DESC, VideoTotalViews DESC;";
							// echo $Sql;
							$Bind = array (
									':ImagesDomainName' => Config::$imagesDomainName,
									':CountryCode' => $CountryCode,
									':CountryCodePattern' => "%$CountryCode%" 
							);
							
							$ChannelsArray = $db->run ( $Sql, $Bind );
							if ($ChannelsArray) {
								// Formatting the Data
								Format::formatResponseData ( $CategoriesArray );
								Format::formatResponseData ( $ChannelsArray );
								return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $CategoriesArray, $ChannelsArray ) ) ) );
							} else {
								return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
							}
							break;
						case 'ios' :
						case 'IOS' :
							$Sql = <<<STR
							SELECT * FROM (
								SELECT channels.ChannelCategory AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber
					
										FROM channels
				
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
				
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
				
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
				
										WHERE channels.ChannelIsOnline=1
											AND packages.PackageId IN (6,7,8)
				
						                GROUP BY VideoCategoryId
					
							UNION ALL
				
								SELECT channelcategories.ChannelCategoryId AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber
					
										FROM channelcategories
										WHERE channelcategories.ChannelCategoryId=1
							) cats
							ORDER BY cats.VideoCategorySequenceNumber DESC, cats.VideoCategoryName;
STR;
							// echo $Sql;
							$Bind = array (
									':ImagesDomainName' => Config::$imagesDomainName 
							);
							
							$CategoriesArray = $db->run ( $Sql, $Bind );
							if ($CategoriesArray) {
								$Sql = <<<STR
								SELECT channels.ChannelCategory AS VideoCategoryId,
										channels.ChannelId AS VideoEntityId,
										channels.ChannelName AS VideoName,
										channels.ChannelDescription AS VideoDescription,
										IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
										IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
										IF(channels.ChannelPosterPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelPosterPath ),channels.ChannelPosterPath) AS VideoPosterPath,
										IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
										channels.ChannelIOSStreamUrl AS VideoStreamUrl,
										channels.ChannelIOSStreamUrlLow AS VideoStreamUrlLow,
										channels.ChannelTotalViews AS VideoTotalViews,
										channels.ChannelAddedDate AS VideoAddedDate,
										packages.PackageId AS VideoPackageId,
										channels.ChannelRating AS VideoRating,
										IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel
			
										FROM channels
							
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
							
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
							
										WHERE channels.ChannelIsOnline=1
											AND packages.PackageId IN (6,7,8)
							
						                GROUP BY VideoEntityId
										ORDER BY VideoTotalViews DESC;
STR;
								// echo $Sql;
								$Bind = array (
										':ImagesDomainName' => Config::$imagesDomainName 
								);
								
								$ChannelsArray = $db->run ( $Sql, $Bind );
								if ($ChannelsArray) {
									// Formatting the Data
									Format::formatResponseData ( $CategoriesArray );
									Format::formatResponseData ( $ChannelsArray );
									return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $CategoriesArray, $ChannelsArray ) ) ) );
								} else {
									return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
								}
							} else {
								return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
							}
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					// echo $Sql;
					$Bind = array (
							':ImagesDomainName' => Config::$imagesDomainName,
							':CountryCode' => $CountryCode,
							':CountryCodePattern' => "%$CountryCode%" 
					);
					$results = $db->run ( $Sql, $Bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results );
						$Sql = <<<STR
						SELECT -1 AS VideoCategoryId,
								channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
								channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
								channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
								channelcategories.IsVast AS IsVast,
								channelcategories.AdvertisementVastURL AS AdvertisementVastURL
									
								FROM channelcategories
                            	WHERE channelcategories.ChannelCategoryId = 1
STR;
						$allCategory = $db->run ( $Sql );
						Format::formatResponseData ( $allCategory );
						$i = 1;
						$assArray = array ();
						// TODO : Make All Category Dynamic
						$assArray [0] = $allCategory [0];
						$assArray [0] ['Videos'] = [ ];
						foreach ( $results as $key => $row ) {
							// print_r($row );
							$flag = true;
							foreach ( $assArray as $key => $assrow ) {
								// print_r($row );
								if ($assrow ['VideoCategoryId'] === $row ['VideoCategoryId']) {
									if ($row ['VideoEntityId'] != 202) {
										$tempRow = array_splice ( $row, 5 );
										$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
										$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
									}
									$flag = false;
								}
							}
							if ($flag) {
								$assArray [$i] ['VideoCategoryId'] = $row ['VideoCategoryId'];
								$assArray [$i] ['VideoCategoryName'] = $row ['VideoCategoryName'];
								$assArray [$i] ['VideoCategoryImagePath'] = $row ['VideoCategoryImagePath'];
								$assArray [$i] ['VideoCategoryURL'] = $row ['VideoCategoryURL'];
								$assArray [$i] ['IsVast'] = $row ['IsVast'];
								$assArray [$i] ['AdvertisementVastURL'] = $row ['AdvertisementVastURL'];
								$tempRow = array_splice ( $row, 5 );
								$assArray [$i] ['Videos'] [] = $tempRow;
								$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
								// print_r($assArray[$i]);
								$i ++;
							}
						}
						function compareForSorting($Value1, $Value2) {
							if ($Value1 ['VideoTotalViews'] == $Value2 ['VideoTotalViews']) {
								return 0;
							}
							return ($Value1 ['VideoTotalViews'] > $Value2 ['VideoTotalViews']) ? - 1 : 1;
						}
						
						usort ( $assArray [0] ['Videos'], "compareForSorting" );
						
						return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) ) );
					} else {
						return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) ) );
					}
					break;
				case 'v2' :
				case 'V2' : // Local/International Filter Disabled
					$Sql = <<<STR
					SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
					channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
					channels.ChannelId AS VideoEntityId,
					channels.ChannelName AS VideoName,
					channels.ChannelDescription AS VideoDescription,
					IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
					IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
					IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
					IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
					channels.ChannelCategory AS VideoCategoryId,
					packages.PackageId AS VideoPackageId,
					channels.ChannelTotalViews AS VideoTotalViews,
					channels.ChannelRating AS VideoRating,
					channels.ChannelAddedDate AS VideoAddedDate,
					NULL AS VideoDuration,
					IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree,
					IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
					IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

					
					FROM winettv.channels
		
					INNER JOIN winettv.channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
		
					LEFT JOIN winettv.packagechannels ON channels.ChannelId =	packagechannels.channelId
		
					LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId
		
					WHERE channels.ChannelIsOnline=1
						AND packages.PackageId IN (6,7,8,10)
		
	                GROUP BY VideoEntityId
					ORDER BY VideoTotalViews DESC;
STR;
					// echo $sql;
					$Bind = array (
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					$results = $db->run ( $Sql, $Bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results );
						$i = 1;
						$assArray = array ();
						// TODO : Make All Category Dynamic
						$assArray [0] ['VideoCategoryId'] = - 1;
						$assArray [0] ['VideoCategoryName'] = 'All';
						$assArray [0] ['VideoCategoryImagePath'] = 'http://www.pitelevision.com/images/channels/category/All.jpg';
						$assArray [0] ['Videos'] = [ ];
						foreach ( $results as $key => $row ) {
							// print_r($row );
							$flag = true;
							foreach ( $assArray as $key => $assrow ) {
								// print_r($row );
								if ($assrow ['VideoCategoryName'] === $row ['VideoCategoryName']) {
									$tempRow = array_splice ( $row, 2 );
									$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
									$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
									$flag = false;
								}
							}
							if ($flag) {
								$assArray [$i] ['VideoCategoryId'] = $row ['VideoCategoryId'];
								$assArray [$i] ['VideoCategoryName'] = $row ['VideoCategoryName'];
								$assArray [$i] ['VideoCategoryImagePath'] = $row ['VideoCategoryImagePath'];
								$tempRow = array_splice ( $row, 2 );
								$assArray [$i] ['Videos'] [] = $tempRow;
								$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
								// print_r($assArray[$i]);
								$i ++;
							}
						}
						return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) ) );
					} else {
						return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) ) );
					}
					break;
				case 'v3' :
				case 'V3' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( 'In Process.', NULL, NULL, 'Categories' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Categories' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Categories' ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getChannelsRssFeed(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					switch ($Platform) {
						case 'Android' :
						case 'android' :
							$Sql = <<<STR
							SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
							channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
							channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
							channelcategories.IsVast AS IsVast,
							channelcategories.AdvertisementVastURL AS AdvertisementVastURL,
							rssfeed.RssFeedId AS VideoEntityId,
							rssfeed.RssFeedName AS VideoName,
							rssfeed.RssFeedUrl AS VideoRssFeedURL,
							rssfeed.RssFeedDescription AS VideoDescription,
							IF(rssfeed.RssFeedThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, rssfeed.RssFeedThumbnailPath ),rssfeed.RssFeedThumbnailPath) AS VideoImageThumbnail,
							IF(rssfeed.RssFeedMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, rssfeed.RssFeedMobileSmall ),rssfeed.RssFeedMobileSmall) AS VideoImagePath,
							IF(rssfeed.RssFeedMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, rssfeed.RssFeedMobileLarge ),rssfeed.RssFeedMobileLarge) AS VideoImagePathLarge
					
							FROM rssfeed
	
							INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = rssfeed.RssFeedCategoryId
	
							WHERE rssfeed.RssFeedIsOnline=1
								AND
									CASE
		                            WHEN :CountryCode != 'PK' AND rssfeed.RssFeedIsAllowedInternationally = 1
									THEN
										CASE
		                                WHEN RssFeedAllowCountryCodeList=0 THEN (rssfeed.RssFeedCountryCodeList NOT LIKE :CountryCodePattern)
										WHEN RssFeedAllowCountryCodeList=1 THEN (rssfeed.RssFeedCountryCodeList LIKE :CountryCodePattern)
		                                ELSE 1 END
									WHEN :CountryCode != 'PK' AND rssfeed.RssFeedIsAllowedInternationally = 0
									THEN 0
		                            ELSE 1 END
	
			                GROUP BY VideoEntityId
							ORDER BY VideoEntityId;
STR;
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					// echo $Sql;
					$Bind = array (
							':ImagesDomainName' => Config::$imagesDomainName,
							':CountryCode' => $CountryCode,
							':CountryCodePattern' => "%$CountryCode%" 
					);
					$results = $db->run ( $Sql, $Bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results );
						$i = 0;
						$assArray = array ();
						// TODO : Make All Category Dynamic
						foreach ( $results as $key => $row ) {
							// print_r($row );
							$flag = true;
							foreach ( $assArray as $key => $assrow ) {
								// print_r($row );
								if ($assrow ['VideoCategoryName'] === $row ['VideoCategoryName']) {
									$tempRow = array_splice ( $row, 5 );
									$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
									$flag = false;
								}
							}
							if ($flag) {
								$assArray [$i] ['VideoCategoryName'] = $row ['VideoCategoryName'];
								$assArray [$i] ['VideoCategoryImagePath'] = $row ['VideoCategoryImagePath'];
								$assArray [$i] ['VideoCategoryURL'] = $row ['VideoCategoryURL'];
								$assArray [$i] ['IsVast'] = $row ['IsVast'];
								$assArray [$i] ['AdvertisementVastURL'] = $row ['AdvertisementVastURL'];
								$tempRow = array_splice ( $row, 5 );
								$assArray [$i] ['Videos'] [] = $tempRow;
								// print_r($assArray[$i]);
								$i ++;
							}
						}
						function compareForSorting($Value1, $Value2) {
							if ($Value1 ['VideoTotalViews'] == $Value2 ['VideoTotalViews']) {
								return 0;
							}
							return ($Value1 ['VideoTotalViews'] > $Value2 ['VideoTotalViews']) ? - 1 : 1;
						}
						
						usort ( $assArray [0] ['Videos'], "compareForSorting" );
						
						return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) ) );
					} else {
						return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) ) );
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Categories' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Categories' ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	/**
	 * Function to Get Related Channels Or VODs
	 *
	 * @param Request $request        	
	 * @param Response $response        	
	 */
	// TODO : Remove winettv name from tables name
	public static function getRelatedChannelsOrVODs(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$ChannelOrVODId = $request->getAttribute ( 'ChannelOrVODId' );
		$IsChannel = $request->getAttribute ( 'IsChannel' );
		$results = NULL;
	
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
	
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					$VideoObject = array ();
						
					// Getting Video Object First
					
					
					if($ChannelOrVODId==214){
						$sql = <<<STR
						SELECT channels.ChannelId AS VideoEntityId,
						channels.ChannelName AS VideoName,
						channels.ChannelDescription AS VideoDescription,
						IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
						IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
						IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
						IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
						channels.ChannelCategory AS VideoCategoryId,
						packages.PackageId AS VideoPackageId,
						channels.ChannelTotalViews AS VideoTotalViews,
						channels.ChannelRating AS VideoRating,
						channels.ChannelAddedDate AS VideoAddedDate,
						NULL AS VideoDuration,
						IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
						IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
						IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice
	
						FROM channels
			
						INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
			
						LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
			
						LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
			
						WHERE channels.ChannelIsOnline=1
							AND channels.ChannelId = :VideoId
							AND packages.PackageId IN (6,7,8,10)
							AND
								CASE
                            	WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
								THEN
									CASE
                                	WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
									WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
                                	ELSE 1 END
								WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
								THEN 0
                            	ELSE 1 END
	                	GROUP BY VideoEntityId
						ORDER BY VideoTotalViews DESC
STR;
	
						// echo $sql;
						$bind = array (
								':VideoId' => $ChannelOrVODId,
								':ImagesDomainName' => Config::$imagesDomainName,
								':CountryCode' => $CountryCode,
								':CountryCodePattern' => "%$CountryCode%"
						);
						$VideoObject = $db->run ( $sql, $bind );
						
						if($VideoObject)
						{
							Format::formatResponseData($VideoObject);
								switch ($Platform) {
									case 'Android' :
									case 'android' :
										$sql = <<<STR
									SELECT videoondemand.VideoOnDemandCategoryId AS SectionId,
									videoondemandcategories.VideoOnDemandCategoryname AS SectionName,
									videoondemand.VideoOnDemandId AS VideoEntityId,
									videoondemand.VideoOnDemandTitle AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
									videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
									0 AS VideoPackageId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree,
									false AS IsVideoChannel,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

				
									FROM videoondemand
	
									INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
										AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
									LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

									WHERE 
                                                                                videoondemand.VideoOnDemandCategoryId =1208 
                                                                                
                                                                                AND
                                                                                videoondemand.VideoOnDemandIsOnline=1
										AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
										AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
										AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
										AND
											CASE
											WHEN :CountryCode != 'PK'
											THEN
												videoondemand.VideoOnDemandCategoryId
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
	
									GROUP BY VideoEntityId
									ORDER BY VideoTotalViews DESC
STR;
										break;
									default :
										return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
										break;
								}
								$sql .= " LIMIT 12";
	
								// echo $sql;
								$bind = array (
										':CountryCode' => $CountryCode,
										':ImagesDomainName' => Config::$imagesDomainName
								);
	
								$results = $db->run ( $sql, $bind );
	
								Format::formatResponseData ( $results );
								$i = 0;
								$assArray = array ();
								foreach ( $results as $key => $row ) {
									$flag = true;
									foreach ( $assArray as $key => $assrow ) {
										if ($assrow ['SectionName'] === $row ['SectionName']) {
											$tempRow = array_splice ( $row, 2 );
											$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
											$flag = false;
										}
									}
									if ($flag) {
										$assArray [$i] ['SectionId'] = $row ['SectionId'];
										$assArray [$i] ['SectionName'] = $row ['SectionName'];
										$tempRow = array_splice ( $row, 2 );
										$assArray [$i] ['Videos'] [] = $tempRow;
										$i ++;
									}
								}
								return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $VideoObject[0], $assArray ) ) );
						}
						
					}else{
						if ($IsChannel === '1') {
						$sql = <<<STR
						SELECT channels.ChannelId AS VideoEntityId,
						channels.ChannelName AS VideoName,
						channels.ChannelDescription AS VideoDescription,
						IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
						IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
						IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
						IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
						channels.ChannelCategory AS VideoCategoryId,
						packages.PackageId AS VideoPackageId,
						channels.ChannelTotalViews AS VideoTotalViews,
						channels.ChannelRating AS VideoRating,
						channels.ChannelAddedDate AS VideoAddedDate,
						NULL AS VideoDuration,
						IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
						IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
						IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

	
						FROM channels
			
						INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
			
						LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
			
						LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
			
						WHERE channels.ChannelIsOnline=1
							AND channels.ChannelId = :VideoId
							AND packages.PackageId IN (6,7,8,10)
							AND
								CASE
                            	WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
								THEN
									CASE
                                	WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
									WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
                                	ELSE 1 END
								WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
								THEN 0
                            	ELSE 1 END
	                	GROUP BY VideoEntityId
						ORDER BY VideoTotalViews DESC
STR;
	
						// echo $sql;
						$bind = array (
								':VideoId' => $ChannelOrVODId,
								':ImagesDomainName' => Config::$imagesDomainName,
								':CountryCode' => $CountryCode,
								':CountryCodePattern' => "%$CountryCode%"
						);
						$VideoObject = $db->run ( $sql, $bind );
					} else {
						$sql = <<<STR
						SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
						videoondemand.VideoOnDemandTitle AS VideoName,
						videoondemand.VideoOnDemandDescription AS VideoDescription,
						IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
						IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
						IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
						IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
						videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
						0 AS VideoPackageId,
						videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
						NULL AS VideoRating,
						videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
						videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
						videoondemand.VideoOnDemandIsFree AS IsVideoFree,
						false AS IsVideoChannel,
						IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

	
						FROM videoondemand
	
						INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
	LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
						WHERE videoondemand.VideoOnDemandIsOnline=1
							AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
							AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
							AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
							AND videoondemand.VideoOnDemandId = :VideoId
							AND
								CASE
			                    WHEN :CountryCode != 'PK'
								THEN
									videoondemand.VideoOnDemandCategoryId
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
	
	                	GROUP BY VideoEntityId
						ORDER BY VideoTotalViews DESC
STR;
	
						// echo $sql;
						$bind = array (
								':CountryCode' => $CountryCode,
								':VideoId' => $ChannelOrVODId,
								':ImagesDomainName' => Config::$imagesDomainName
						);
	
						$VideoObject = $db->run ( $sql, $bind );
					}
						
					if ($VideoObject) {
						Format::formatResponseData ( $VideoObject );
						if ($IsChannel === '1') {
							switch ($Platform) {
								case 'Android' :
								case 'android' :
									$sql = <<<STR
								SELECT channels.ChannelCategory AS SectionId,
								channelcategories.ChannelCategoryDisplayTitle AS SectionName,
								channels.ChannelId AS VideoEntityId,
								channels.ChannelName AS VideoName,
								channels.ChannelDescription AS VideoDescription,
								IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
								IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
								IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
								IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
								channels.ChannelCategory AS VideoCategoryId,
								packages.PackageId AS VideoPackageId,
								channels.ChannelTotalViews AS VideoTotalViews,
								channels.ChannelRating AS VideoRating,
								channels.ChannelAddedDate AS VideoAddedDate,
								NULL AS VideoDuration,
								IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
								IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice
	
								FROM channels
			
								INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
			
								LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
			
								LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
			
								WHERE channels.ChannelIsOnline=1
									AND channels.ChannelCategory = (SELECT channels.channelCategory FROM channels where channels.channelId=:ChannelId)
									AND channels.channelId <> :ChannelId
									AND packages.PackageId IN (6,7,8,10)
									AND
										CASE
		                            	WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
										THEN
											CASE
		                                	WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
											WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
		                                	ELSE 1 END
										WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
										THEN 0
		                            	ELSE 1 END
	
			                	GROUP BY VideoEntityId
								ORDER BY VideoTotalViews DESC
STR;
									break;
								default :
									return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
									break;
							}
							$sql .= " LIMIT 12";
								
							// echo $sql;
							$bind = array (
									':ImagesDomainName' => Config::$imagesDomainName,
									':ChannelId' => $ChannelOrVODId,
									':CountryCode' => $CountryCode,
									':CountryCodePattern' => "%$CountryCode%"
							);
							$results = $db->run ( $sql, $bind );
								
							if ($results) {
								Format::formatResponseData ( $results );
								$i = 0;
								$assArray = array ();
								foreach ( $results as $key => $row ) {
									$flag = true;
									foreach ( $assArray as $key => $assrow ) {
										if ($assrow ['SectionName'] === $row ['SectionName']) {
											$tempRow = array_splice ( $row, 2 );
											$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
											$flag = false;
										}
									}
									if ($flag) {
										$assArray [$i] ['SectionId'] = $row ['SectionId'];
										$assArray [$i] ['SectionName'] = $row ['SectionName'];
										$tempRow = array_splice ( $row, 2 );
										$assArray [$i] ['Videos'] [] = $tempRow;
										$i ++;
									}
								}
								return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $VideoObject [0], $assArray ) ) );
							} else {
								switch ($Platform) {
									case 'Android' :
									case 'android' :
										$sql = <<<STR
									SELECT channels.ChannelCategory AS SectionId,
									channelcategories.ChannelCategoryDisplayTitle AS SectionName,
									channels.ChannelId AS VideoEntityId,
									channels.ChannelName AS VideoName,
									channels.ChannelDescription AS VideoDescription,
									IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
									IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
									IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
									IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
									channels.ChannelCategory AS VideoCategoryId,
									packages.PackageId AS VideoPackageId,
									channels.ChannelTotalViews AS VideoTotalViews,
									channels.ChannelRating AS VideoRating,
									channels.ChannelAddedDate AS VideoAddedDate,
									NULL AS VideoDuration,
									IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
									IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice
						
									FROM channels
	
									INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
	
									LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
	
									LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
	
									WHERE channels.ChannelIsOnline=1
										AND packages.PackageId IN (6,7,8,10)
										AND
											CASE
											WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
											THEN
												CASE
												WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
												WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
												ELSE 1 END
											WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
											THEN 0
											ELSE 1 END
						
									GROUP BY VideoEntityId
									ORDER BY VideoTotalViews DESC
STR;
										break;
									default :
										return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
										break;
								}
								$sql .= " LIMIT 12";
	
								// echo $sql;
								$bind = array (
										':ImagesDomainName' => Config::$imagesDomainName,
										':CountryCode' => $CountryCode,
										':CountryCodePattern' => "%$CountryCode%"
								);
								$results = $db->run ( $sql, $bind );
								Format::formatResponseData ( $results );
								$i = 0;
								$assArray = array ();
								foreach ( $results as $key => $row ) {
									$flag = true;
									foreach ( $assArray as $key => $assrow ) {
										if ($assrow ['SectionName'] === $row ['SectionName']) {
											$tempRow = array_splice ( $row, 2 );
											$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
											$flag = false;
										}
									}
									if ($flag) {
										$assArray [$i] ['SectionId'] = $row ['SectionId'];
										$assArray [$i] ['SectionName'] = $row ['SectionName'];
										$tempRow = array_splice ( $row, 2 );
										$assArray [$i] ['Videos'] [] = $tempRow;
										$i ++;
									}
								}
								return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $VideoObject [0], $assArray ) ) );
							}
						} else {
							switch ($Platform) {
								case 'Android' :
								case 'android' :
									$sql = <<<STR
								SELECT videoondemand.VideoOnDemandCategoryId AS SectionId,
								videoondemandcategories.VideoOnDemandCategoryname AS SectionName,
								videoondemand.VideoOnDemandId AS VideoEntityId,
								videoondemand.VideoOnDemandTitle AS VideoName,
								videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
								IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
								IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
								IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
								videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
								0 AS VideoPackageId,
								videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
								NULL AS VideoRating,
								videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
								videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
								videoondemand.VideoOnDemandIsFree AS IsVideoFree,
								false AS IsVideoChannel,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

	
								FROM videoondemand
			
								INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
									AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
			LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
								WHERE videoondemand.VideoOnDemandIsOnline=1
									AND videoondemand.VideoOnDemandId<>:VideoOnDemandId
									AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
									AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
									AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
									AND videoondemand.VideoOnDemandCategoryId = (SELECT videoondemand.VideoOnDemandCategoryId FROM winettv.videoondemand where videoondemand.VideoOnDemandId=:VideoOnDemandId)
									AND
										CASE
										WHEN :CountryCode != 'PK'
										THEN
											videoondemand.VideoOnDemandCategoryId
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
					
								GROUP BY VideoEntityId
								ORDER BY VideoTotalViews DESC
STR;
									break;
								default :
									return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
									break;
							}
							$sql .= " LIMIT 12";
								
							// echo $sql;
							$bind = array (
									':CountryCode' => $CountryCode,
									':ImagesDomainName' => Config::$imagesDomainName,
									':VideoOnDemandId' => $ChannelOrVODId
							);
								
							$results = $db->run ( $sql, $bind );
								
							if ($results) {
								Format::formatResponseData ( $results );
								$i = 0;
								$assArray = array ();
								foreach ( $results as $key => $row ) {
									$flag = true;
									foreach ( $assArray as $key => $assrow ) {
										if ($assrow ['SectionName'] === $row ['SectionName']) {
											$tempRow = array_splice ( $row, 2 );
											$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
											$flag = false;
										}
									}
									if ($flag) {
										$assArray [$i] ['SectionId'] = $row ['SectionId'];
										$assArray [$i] ['SectionName'] = $row ['SectionName'];
										$tempRow = array_splice ( $row, 2 );
										$assArray [$i] ['Videos'] [] = $tempRow;
										$i ++;
									}
								}
								return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $VideoObject [0], $assArray ) ) );
							} else {
								switch ($Platform) {
									case 'Android' :
									case 'android' :
										$sql = <<<STR
									SELECT videoondemand.VideoOnDemandCategoryId AS SectionId,
									videoondemandcategories.VideoOnDemandCategoryname AS SectionName,
									videoondemand.VideoOnDemandId AS VideoEntityId,
									videoondemand.VideoOnDemandTitle AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
									videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
									0 AS VideoPackageId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree,
									false AS IsVideoChannel,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

				
									FROM videoondemand
	
									INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
										AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
	LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
									WHERE videoondemand.VideoOnDemandCategoryId IN('1084','835') 
                                                                                 AND
                                                                                videoondemand.VideoOnDemandCategoryId=7
                                                                                AND
										videoondemand.VideoOnDemandIsOnline=1
										AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
										AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
										AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
										AND
											CASE
											WHEN :CountryCode != 'PK'
											THEN
												videoondemand.VideoOnDemandCategoryId
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
	
									GROUP BY VideoEntityId
									ORDER BY VideoTotalViews DESC
STR;
										break;
									default :
										return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
										break;
								}
								$sql .= " LIMIT 12";
	
								// echo $sql;
								$bind = array (
										':CountryCode' => $CountryCode,
										':ImagesDomainName' => Config::$imagesDomainName
								);
	
								$results = $db->run ( $sql, $bind );
	
								Format::formatResponseData ( $results );
								$i = 0;
								$assArray = array ();
								foreach ( $results as $key => $row ) {
									$flag = true;
									foreach ( $assArray as $key => $assrow ) {
										if ($assrow ['SectionName'] === $row ['SectionName']) {
											$tempRow = array_splice ( $row, 2 );
											$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
											$flag = false;
										}
									}
									if ($flag) {
										$assArray [$i] ['SectionId'] = $row ['SectionId'];
										$assArray [$i] ['SectionName'] = $row ['SectionName'];
										$tempRow = array_splice ( $row, 2 );
										$assArray [$i] ['Videos'] [] = $tempRow;
										$i ++;
									}
								}
								return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $VideoObject [0], $assArray ) ) );
							}
						}
					} else {
						return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
					}
					}
					
					
					break;
				default :
					return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	
	
	//-------------------get Vod Detail---------------------------------------------//
	public static function getVideoDetail(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$VideoId = $request->getAttribute ( 'VideoId' );
		$IsChannel = $request->getAttribute ( 'IsChannel' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					if ($IsChannel === '1') {
						$sql = <<<STR
						SELECT channels.ChannelId AS VideoEntityId,
						channels.ChannelName AS VideoName,
						channels.ChannelDescription AS VideoDescription,
						IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
						IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
						IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
						IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
						channels.ChannelCategory AS VideoCategoryId,
						packages.PackageId AS VideoPackageId,
						channels.ChannelTotalViews AS VideoTotalViews,
						channels.ChannelRating AS VideoRating,
						channels.ChannelAddedDate AS VideoAddedDate,
						NULL AS VideoDuration,
						IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
						IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
						IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice
			
						FROM channels
	
						INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
	
						LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
	
						LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
	
						WHERE channels.ChannelIsOnline=1
							AND channels.ChannelId = :VideoId
							AND packages.PackageId IN (6,7,8,10)
							AND
								CASE
                            	WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
								THEN
									CASE
                                	WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
									WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
                                	ELSE 1 END
								WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
								THEN 0
                            	ELSE 1 END
	                	GROUP BY VideoEntityId
						ORDER BY VideoTotalViews DESC
STR;
						
						// echo $sql;
						$bind = array (
								':VideoId' => $VideoId,
								':ImagesDomainName' => Config::$imagesDomainName,
								':CountryCode' => $CountryCode,
								':CountryCodePattern' => "%$CountryCode%" 
						);
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ) ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
						}
					} else {
						$sql = <<<STR
						SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
						videoondemand.VideoOnDemandTitle AS VideoName,
						videoondemand.VideoOnDemandDescription AS VideoDescription,
						IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
						0 AS VideoPackageId,
						videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
						NULL AS VideoRating,
						videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
						videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
						videoondemand.VideoOnDemandIsFree AS IsVideoFree,
						false AS IsVideoChannel,
						IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

			
						FROM videoondemand
			
						INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
			LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
						WHERE videoondemand.VideoOnDemandIsOnline=1
							AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
							AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
							AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
							AND videoondemand.VideoOnDemandId = :VideoId
							AND
								CASE
			                    WHEN :CountryCode != 'PK'
								THEN
									videoondemand.VideoOnDemandCategoryId
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
			
	                	GROUP BY VideoEntityId
						ORDER BY VideoTotalViews DESC
STR;
						
						// echo $sql;
						$bind = array (
								':CountryCode' => $CountryCode,
								':VideoId' => $VideoId,
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ) ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
						}
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Videos' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function getRelatedMoreInfo(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$CategoryId = $request->getAttribute ( 'CategoryId' );
		$IsChannel = $request->getAttribute ( 'IsChannel' );
		$OffSet = $request->getAttribute ( 'OffSet' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					if ($IsChannel === '1') {
						$sql = <<<STR
						SELECT channels.ChannelId AS VideoEntityId,
						channels.ChannelName AS VideoName,
						channels.ChannelDescription AS VideoDescription,
						IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
						IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
						IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
						IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
						channels.ChannelCategory AS VideoCategoryId,
						packages.PackageId AS VideoPackageId,
						channels.ChannelTotalViews AS VideoTotalViews,
						channels.ChannelRating AS VideoRating,
						channels.ChannelAddedDate AS VideoAddedDate,
						NULL AS VideoDuration,
						IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
						IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
						IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice
					
						FROM channels
		
						INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
		
						LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
		
						LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
		
						WHERE channels.ChannelIsOnline=1
							AND channels.ChannelCategory = :CategoryId
							AND packages.PackageId IN (6,7,8,10)
							AND
								CASE
                            	WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
								THEN
									CASE
                                	WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
									WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
                                	ELSE 1 END
								WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
								THEN 0
                            	ELSE 1 END
	                	GROUP BY VideoEntityId
						ORDER BY VideoTotalViews DESC
STR;
						$sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
						
						// echo $sql;
						$bind = array (
								':CategoryId' => $CategoryId,
								':ImagesDomainName' => Config::$imagesDomainName,
								':CountryCode' => $CountryCode,
								':CountryCodePattern' => "%$CountryCode%" 
						);
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), Config::$ChannelsANDVODsLimit, count ( $results ), 'Videos' ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), Config::$ChannelsANDVODsLimit, count ( $results ), 'Videos' ) ) );
						}
					} else {
						$sql = <<<STR
						SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
						videoondemand.VideoOnDemandTitle AS VideoName,
						videoondemand.VideoOnDemandDescription AS VideoDescription,
						IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
						0 AS VideoPackageId,
						videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
						NULL AS VideoRating,
						videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
						videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
						videoondemand.VideoOnDemandIsFree AS IsVideoFree,
						false AS IsVideoChannel,
						IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

					
						FROM videoondemand
					
						INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
							AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
						LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

						WHERE videoondemand.VideoOnDemandIsOnline=1
							AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
							AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
							AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
							AND videoondemand.VideoOnDemandCategoryId = :CategoryId
							AND 
								CASE
			                    WHEN :CountryCode != 'PK'
								THEN
									videoondemand.VideoOnDemandCategoryId
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
					
	                	GROUP BY VideoEntityId
						ORDER BY VideoTotalViews DESC
STR;
						$sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
						
						// echo $sql;
						$bind = array (
								':CountryCode' => $CountryCode,
								':CategoryId' => $CategoryId,
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), Config::$ChannelsANDVODsLimit, count ( $results ), 'Videos' ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), Config::$ChannelsANDVODsLimit, count ( $results ), 'Videos' ) ) );
						}
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Videos' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	/**
	 * Function to Search Channels and VODs
	 *
	 * @param Request $request        	
	 * @param Response $response        	
	 */
	public static function searchAllChannelsWithCategories(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$SearchString = $request->getAttribute ( 'SearchString' );
		$OffSet = $request->getAttribute ( 'OffSet' );
		$results = NULL;
		
		try {
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
					if ($SearchString == null || $SearchString == '') {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_EMPTY_SEARCH' ) ) ) );
					} else {
						$db = parent::getDataBase ();
						
						include_once '../geoip/geoip.php';
						$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
						// echo $CountryCode;
						// $CountryCode = 'PK';
						
						$sql = "
						SELECT * FROM (
								
								SELECT channels.ChannelId AS VideoEntityId,
								channels.ChannelName AS VideoName,
								channels.ChannelDescription AS VideoDescription,
								IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
								IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
								IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
								IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
								channels.ChannelCategory AS VideoCategoryId,
								packages.PackageId AS VideoPackageId,
								channels.ChannelTotalViews AS VideoTotalViews,
								channels.ChannelRating AS VideoRating,
								channels.ChannelAddedDate AS VideoAddedDate,
								NULL AS VideoDuration,
								IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
								IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice
					
								FROM channels
					
								LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
					
								LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
					
								WHERE channels.ChannelIsOnline=1
									AND (channels.ChannelName LIKE :SearchString)
									AND packages.PackageId IN (6,7,8,10)
									AND
										CASE
                            			WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
										THEN
											CASE
                                			WHEN ChannelAllowCountryCodeList=0 THEN (ChannelCountryCodeList NOT LIKE :CountryCodePattern)
											WHEN ChannelAllowCountryCodeList=1 THEN (ChannelCountryCodeList LIKE :CountryCodePattern)
                                			ELSE 1 END
										WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
										THEN 0
                            			ELSE 1 END
					
	                    		GROUP BY VideoEntityId
								
								
								UNION ALL
								
								
								SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
								videoondemand.VideoOnDemandTitle AS VideoName,
								videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
								IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
								IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
								IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
								videoondemand.VideoOnDemandCategoryId AS VideoCategory,
								0 AS VideoPackageId,
								videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
								NULL AS VideoRating,
								videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
								videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
								videoondemand.VideoOnDemandIsFree AS IsVideoFree,
								false AS IsVideoChannel,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

					
								FROM videoondemand
										
								INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
									AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
								LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
								LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
					
								WHERE videoondemand.VideoOnDemandIsOnline=1
									AND (videoondemand.VideoOnDemandTitle LIKE :SearchString)
									AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
									AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
									AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
									AND 
										CASE
					                    WHEN :CountryCode != 'PK'
										THEN
											videoondemand.VideoOnDemandCategoryId
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
					
	                    		GROUP BY VideoEntityId
						) channelvods
						ORDER BY channelvods.VideoTotalViews DESC
						LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
						
						$bind = array (
								':ImagesDomainName' => Config::$imagesDomainName,
								':CountryCode' => $CountryCode,
								':CountryCodePattern' => "%$CountryCode%",
								':SearchString' => '%' . $SearchString . '%' 
						);
						// echo $sql;
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							// Formatting the Data
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), Config::$ChannelsANDVODsLimit, count ( $results ) ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), Config::$ChannelsANDVODsLimit, count ( $results ) ) ) );
						}
					}
					break;
				case 'v2' :
				case 'V2' : // Local/International Filter Disabled
					if ($SearchString == null || $SearchString == '') {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_EMPTY_SEARCH' ) ) ) );
					} else {
						$db = parent::getDataBase ();
						
						include_once '../geoip/geoip.php';
						$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
						// echo $CountryCode;
						// $CountryCode = 'PK';
						
						$sql = "
						SELECT * FROM (
								
								SELECT channels.ChannelId AS VideoEntityId,
								channels.ChannelName AS VideoName,
								channels.ChannelDescription AS VideoDescription,
								IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
								IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
								IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
								IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
								channels.ChannelCategory AS VideoCategory,
								packages.PackageId AS VideoPackageId,
								channels.ChannelTotalViews AS VideoTotalViews,
								channels.ChannelRating AS VideoRating,
								channels.ChannelAddedDate AS VideoAddedDate,
								NULL AS VideoDuration,
								IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
								IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel
					
								FROM channels
					
								LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
					
								LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
					
								WHERE channels.ChannelIsOnline=1
									AND (channels.ChannelName LIKE :SearchString)
									AND packages.PackageId IN (6,7,8,10)
									AND
										CASE
                            			WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
										THEN
											CASE
                                			WHEN ChannelAllowCountryCodeList=0 THEN (ChannelCountryCodeList NOT LIKE :CountryCodePattern)
											WHEN ChannelAllowCountryCodeList=1 THEN (ChannelCountryCodeList LIKE :CountryCodePattern)
                                			ELSE 1 END
										WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
										THEN 0
                            			ELSE 1 END
					
	                    		GROUP BY VideoEntityId
								
								
								UNION ALL
								
								
								SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
								videoondemand.VideoOnDemandTitle AS VideoName,
								videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
								IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoImageThumbnail,
								IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
								IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
								videoondemand.VideoOnDemandCategoryId AS VideoCategory,
								0 AS VideoPackageId,
								videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
								NULL AS VideoRating,
								videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
								videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
								videoondemand.VideoOnDemandIsFree AS IsVideoFree,
								false AS IsVideoChannel
					
								FROM videoondemand
										
								INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
									AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
					
								WHERE videoondemand.VideoOnDemandIsOnline=1
									AND (videoondemand.VideoOnDemandTitle LIKE :SearchString)
									AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
									AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
									AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
									AND 
										CASE
					                    WHEN :CountryCode != 'PK'
										THEN
											videoondemand.VideoOnDemandCategoryId
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
					
	                    		GROUP BY VideoEntityId
						) channelvods
						ORDER BY channelvods.VideoTotalViews DESC
						LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
						
						$bind = array (
								':ImagesDomainName' => Config::$imagesDomainName,
								':CountryCode' => $CountryCode,
								':CountryCodePattern' => "%$CountryCode%",
								':SearchString' => '%' . $SearchString . '%' 
						);
						// echo $sql;
						$results = $db->run ( $sql, $bind );
						
						if ($results) {
							// Formatting the Data
							Format::formatResponseData ( $results );
							return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), $SearchString, count ( $results ) ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), Config::$ChannelsANDVODsLimit, count ( $results ) ) ) );
						}
					}
					break;
				case 'v3' :
				case 'V3' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					), NULL, NULL ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	public static function searchInAllContent(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$SearchString = $request->getAttribute ( 'SearchString' );
		$PageNumber = ( int ) $request->getAttribute ( 'PageNumber' );
		$results = NULL;
		
		try {
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					if ($SearchString == null || $SearchString == '') {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_EMPTY_SEARCH' ) ) ) );
					} else {
						
						include_once '../geoip/geoip.php';
						$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
						// echo $CountryCode;
						// $CountryCode = 'PK';
						
						$db = parent::getDataBase ();
						
						switch ($Platform) {
							case 'Android' :
							case 'android' :
								$sql = "
								SELECT SQL_CALC_FOUND_ROWS * FROM (
								
										SELECT channels.ChannelId AS VideoEntityId,
										channels.ChannelName AS VideoName,
										channels.ChannelDescription AS VideoDescription,
										IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
										IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
										IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
										channels.ChannelCategory AS VideoCategoryId,
										packages.PackageId AS VideoPackageId,
										channels.ChannelTotalViews AS VideoTotalViews,
										channels.ChannelRating AS VideoRating,
										channels.ChannelAddedDate AS VideoAddedDate,
										NULL AS VideoDuration,
										IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
										IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel
								
										FROM channels
								
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
								
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
								
										WHERE channels.ChannelIsOnline=1
											AND (channels.ChannelName LIKE :SearchString)
											AND packages.PackageId IN (6,7,8,10)
											AND
												CASE
		                            			WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
												THEN
													CASE
		                                			WHEN ChannelAllowCountryCodeList=0 THEN (ChannelCountryCodeList NOT LIKE :CountryCodePattern)
													WHEN ChannelAllowCountryCodeList=1 THEN (ChannelCountryCodeList LIKE :CountryCodePattern)
		                                			ELSE 1 END
												WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
												THEN 0
		                            			ELSE 1 END
								
			                    		GROUP BY VideoEntityId
								
								
										UNION ALL
								
								
										SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
										videoondemand.VideoOnDemandTitle AS VideoName,
										videoondemand.VideoOnDemandDescription AS VideoDescription,
										IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
										IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoImageThumbnail,
										IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
										IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
										videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
										0 AS VideoPackageId,
										videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
										NULL AS VideoRating,
										videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
										videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
										videoondemand.VideoOnDemandIsFree AS IsVideoFree,
										false AS IsVideoChannel
								
										FROM videoondemand
										
										INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
											AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
								
										WHERE videoondemand.VideoOnDemandIsOnline=1
											AND (videoondemand.VideoOnDemandTitle LIKE :SearchString)
											AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
											AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
											AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
											AND
												CASE
							                    WHEN :CountryCode != 'PK'
												THEN
													videoondemand.VideoOnDemandCategoryId
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
								
			                    		GROUP BY VideoEntityId
								) channelvods
								ORDER BY channelvods.VideoTotalViews DESC
								LIMIT " . Config::$WebPageSize . " OFFSET " . ($PageNumber - 1) * Config::$WebPageSize;
								break;
							case 'Web' :
							case 'web' :
								$sql = "
								SELECT SQL_CALC_FOUND_ROWS * FROM (
										
										SELECT channels.ChannelId AS VideoEntityId,
										channels.ChannelName AS VideoName,
										channels.ChannelDescription AS VideoDescription,
										IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
										IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
										IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
										channels.ChannelCategory AS VideoCategoryId,
										packages.PackageId AS VideoPackageId,
										channels.ChannelTotalViews AS VideoTotalViews,
										channels.ChannelRating AS VideoRating,
										channels.ChannelAddedDate AS VideoAddedDate,
										NULL AS VideoDuration,
										IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree,
										IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel
				
										FROM channels
				
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
				
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
				
										WHERE channels.ChannelIsOnline=1
											AND (channels.ChannelName LIKE :SearchString)
											AND packages.PackageId IN (6,7,8)
											AND
												CASE
		                            			WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
												THEN
													CASE
		                                			WHEN ChannelAllowCountryCodeList=0 THEN (ChannelCountryCodeList NOT LIKE :CountryCodePattern)
													WHEN ChannelAllowCountryCodeList=1 THEN (ChannelCountryCodeList LIKE :CountryCodePattern)
		                                			ELSE 1 END
												WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
												THEN 0
		                            			ELSE 1 END
				
			                    		GROUP BY VideoEntityId
										
										
										UNION ALL
										
										
										SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
										videoondemand.VideoOnDemandTitle AS VideoName,
										videoondemand.VideoOnDemandDescription AS VideoDescription,
										IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
										IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoImageThumbnail,
										IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
										IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
										videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
										0 AS VideoPackageId,
										videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
										NULL AS VideoRating,
										videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
										videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
										videoondemand.VideoOnDemandIsFree AS IsVideoFree,
										false AS IsVideoChannel
				
										FROM videoondemand
										
										INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
											AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
				
										WHERE videoondemand.VideoOnDemandIsOnline=1
											AND (videoondemand.VideoOnDemandTitle LIKE :SearchString)
											AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
											AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
											AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
											AND
												CASE
							                    WHEN :CountryCode != 'PK'
												THEN
													videoondemand.VideoOnDemandCategoryId
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
				
			                    		GROUP BY VideoEntityId
								) channelvods
								ORDER BY channelvods.VideoTotalViews DESC
								LIMIT " . Config::$WebPageSize . " OFFSET " . ($PageNumber - 1) * Config::$WebPageSize;
								break;
							default :
								return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
								break;
						}
						
						$bind = array (
								':ImagesDomainName' => Config::$imagesDomainName,
								':CountryCode' => $CountryCode,
								':CountryCodePattern' => "%$CountryCode%",
								':SearchString' => '%' . $SearchString . '%' 
						);
						
						// echo $sql;
						$results = $db->run ( $sql, $bind );
						
						$sql = "SELECT FOUND_ROWS();";
						$TotalItems = $db->run ( $sql, $bind );
						
						if ($results) {
							// Formatting the Data
							Format::formatResponseData ( $results );
							Format::formatResponseData ( $TotalItems );
							return General::getResponse ( $response->write ( SuccessObject::getPageVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), (Config::$WebPageSize > $TotalItems [0] ['FOUND_ROWS()'] ? $TotalItems [0] ['FOUND_ROWS()'] : Config::$WebPageSize), $TotalItems [0] ['FOUND_ROWS()'] ) ) );
						} else {
							$TotalItems [0] ['FOUND_ROWS()'] = 1;
							$results [0] ['VideoEntityId'] = 0;
							$results [0] ['VideoName'] = "No Records Found.";
							$results [0] ['VideoDescription'] = "Nothing is available related to your search. Please try other terms.";
							$results [0] ['VideoImageThumbnail'] = "http://www.pitelevision.com/images/thumb/NoDataFound.png";
							$results [0] ['VideoImagePath'] = "http://www.pitelevision.com/images/thumb/NoDataFound.png";
							$results [0] ['VideoImagePathLarge'] = "http://www.pitelevision.com/images/thumb/NoDataFound.png";
							$results [0] ['IsVideoFree'] = true;
							$results [0] ['IsVideoChannel'] = true;
							// $results[0]['']="";
							return General::getResponse ( $response->write ( SuccessObject::getPageVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), (Config::$WebPageSize > $TotalItems [0] ['FOUND_ROWS()'] ? $TotalItems [0] ['FOUND_ROWS()'] : Config::$WebPageSize), $TotalItems [0] ['FOUND_ROWS()'] ) ) );
						}
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	
	
	/**
	 * Function to Get All Available Channels List
	 *
	 * @param Request $request        	
	 * @param Response $response        	
	 */
	 public static function getAllChannelsWithCategories2(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$DateTime = filter_var ( $request->getAttribute ( 'DateTime' ), FILTER_SANITIZE_STRING );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
					
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
					switch ($Platform) {
						case 'Android' :
						case 'android' :
							$Sql = <<<STR
							SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
							channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
							channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
							channelcategories.IsVast AS IsVast,
							channelcategories.AdvertisementVastURL AS AdvertisementVastURL,
							channels.ChannelId AS VideoEntityId,
							channels.ChannelName AS VideoName,
							channels.ChannelDescription AS VideoDescription,
							IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
							IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
							IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
							IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
							channels.ChannelCategory AS VideoCategoryId,
							packages.PackageId AS VideoPackageId,
							channels.ChannelTotalViews AS VideoTotalViews,
							channels.ChannelRating AS VideoRating,
							channels.ChannelAddedDate AS VideoAddedDate,
							NULL AS VideoDuration,
							IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
							IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
							channels.ChannelRssFeedUrl AS VideoRssFeedURL
									
							FROM channels
				
							INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
				
							LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
				
							LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
				
							WHERE channels.ChannelIsOnline=1
								AND packages.PackageId IN (6,7,8,10)
								AND
									CASE
		                            WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
									THEN
										CASE
		                                WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
										WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
		                                ELSE 1 END
									WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
									THEN 0
		                            ELSE 1 END
				
			                GROUP BY VideoEntityId
							ORDER BY channelcategories.ChannelSequenceNumber DESC, VideoTotalViews DESC;
STR;
							break;
						case 'Web' :
						case 'web' :
							$Sql = <<<STR
							SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
							channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
							channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
							1 AS IsVast,
							'https://bs.serving-sys.com/Serving?cn=display&c=23&pl=VAST&pli=20460720&PluID=0&pos=623&ord=[timestamp]&cim=1' AS AdvertisementVastURL,
							channels.ChannelId AS VideoEntityId,
							channels.ChannelName AS VideoName,
							channels.ChannelDescription AS VideoDescription,
							IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
							IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
							IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
							IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
							channels.ChannelCategory AS VideoCategoryId,
							packages.PackageId AS VideoPackageId,
							channels.ChannelTotalViews AS VideoTotalViews,
							channels.ChannelRating AS VideoRating,
							channels.ChannelAddedDate AS VideoAddedDate,
							NULL AS VideoDuration,
							IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree,
							true AS IsVideoChannel
					
							FROM winettv.channels
							
							INNER JOIN winettv.channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
							LEFT JOIN winettv.packagechannels ON channels.ChannelId =	packagechannels.channelId
							
							LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId
							
							WHERE channels.ChannelIsOnline=1
								AND packages.PackageId IN (6,7,8)
								AND
									CASE
		                            WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
									THEN
										CASE
		                                WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
										WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
		                                ELSE 1 END
									WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
									THEN 0
		                            ELSE 1 END
							
			                GROUP BY VideoEntityId
							ORDER BY VideoTotalViews DESC;
STR;
							break;
						case 'androidoffline' :
						case 'ANDROIDOFFLINE' :
							$Sql = <<<STR
							SELECT * FROM (
								SELECT channels.ChannelCategory AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
										channelcategories.IsVast AS IsVast,
										channelcategories.AdvertisementVastURL AS AdvertisementVastURL,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber,
										channelcategories.ChannelCategoryAddedDate AS VideoCategoryAddedDate,
										channelcategories.ChannelCategoryUpdatedDate AS VideoCategoryUpdatedDate
									
										FROM channels
							
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
							
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
							
										WHERE packages.PackageId IN (6,8,15,16,2)
							
						                GROUP BY VideoCategoryId
									
							UNION ALL
							
								SELECT channelcategories.ChannelCategoryId AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
										channelcategories.IsVast AS IsVast,
										channelcategories.AdvertisementVastURL AS AdvertisementVastURL,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber,
										channelcategories.ChannelCategoryAddedDate AS VideoCategoryAddedDate,
										channelcategories.ChannelCategoryUpdatedDate AS VideoCategoryUpdatedDate
									
										FROM channelcategories
										WHERE channelcategories.ChannelCategoryId=1
							) cats
STR;
							$Sql .= " WHERE cats.VideoCategoryUpdatedDate > '" . $DateTime . "'
										ORDER BY cats.VideoCategorySequenceNumber DESC, cats.VideoCategoryName;";
							// echo $Sql;
							$Bind = array (
									':ImagesDomainName' => Config::$imagesDomainName 
							);
							$CategoriesArray = $db->run ( $Sql, $Bind );
							$Sql = <<<STR
								SELECT channels.ChannelId AS VideoEntityId,
										channels.ChannelName AS VideoName,
										channels.ChannelDescription AS VideoDescription,
										IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
										IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
										IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
										channels.ChannelCategory AS VideoCategoryId,
										packages.PackageId AS VideoPackageId,
										channels.ChannelTotalViews AS VideoTotalViews,
										channels.ChannelRating AS VideoRating,
										NULL AS VideoDuration,
										channels.ChannelIsOnline AS IsVideoOnline,
										IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
										IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel,
										channels.ChannelIsAllowedInternationally AS VideoIsAllowedInternationally,
										channels.ChannelAllowCountryCodeList AS VideoAllowCountryCodeList,
										channels.ChannelCountryCodeList AS VideoCountryCodeList,
										channels.ChannelRssFeedUrl AS VideoRssFeedURL,
										channels.ChannelAddedDate AS VideoAddedDate,
										channels.ChannelUpdatedDate AS VideoUpdatedDate,
										IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Free') AS PackageName,
										IF(packages.PackageId NOT IN (6,15,16,2),true,false) AS PackageIsFree,
										IF(packages.PackageId IN (6,15,16,2),packages.PackageProductId,'1010') AS PackageProduct,
										IF(packages.PackageId IN (6,15,16,2),packages.PackagePrice,'0') AS PackagePrice
					
										FROM channels
										
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
							
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
							
										WHERE packages.PackageId IN (6,8,15,16,2)
											AND
												CASE
					                            WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 1
												THEN
													CASE
					                                WHEN ChannelAllowCountryCodeList=0 THEN (channels.ChannelCountryCodeList NOT LIKE :CountryCodePattern)
													WHEN ChannelAllowCountryCodeList=1 THEN (channels.ChannelCountryCodeList LIKE :CountryCodePattern)
					                                ELSE 1 END
												WHEN :CountryCode != 'PK' AND channels.ChannelIsAllowedInternationally = 0
												THEN 0
					                            ELSE 1 END
STR;
							$Sql .= " AND channels.ChannelUpdatedDate > '" . $DateTime . "'
						                GROUP BY VideoEntityId
										ORDER BY channelcategories.ChannelSequenceNumber DESC, VideoTotalViews DESC;";
							// echo $Sql;
							$Bind = array (
									':ImagesDomainName' => Config::$imagesDomainName,
									':CountryCode' => $CountryCode,
									':CountryCodePattern' => "%$CountryCode%" 
							);
							
							$ChannelsArray = $db->run ( $Sql, $Bind );
							if ($ChannelsArray) {
								// Formatting the Data
								Format::formatResponseData ( $CategoriesArray );
								Format::formatResponseData ( $ChannelsArray );
								return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $CategoriesArray, $ChannelsArray ) ) ) );
							} else {
								return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
							}
							break;
						case 'ios' :
						case 'IOS' :
							$Sql = <<<STR
							SELECT * FROM (
								SELECT channels.ChannelCategory AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber
					
										FROM channels
				
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
				
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
				
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
				
										WHERE channels.ChannelIsOnline=1
											AND packages.PackageId IN (6,7,8)
				
						                GROUP BY VideoCategoryId
					
							UNION ALL
				
								SELECT channelcategories.ChannelCategoryId AS VideoCategoryId,
										channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
										channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
										channelcategories.ChannelCategoryDescription AS VideoCategoryDescription,
										channelcategories.ChannelSequenceNumber AS VideoCategorySequenceNumber
					
										FROM channelcategories
										WHERE channelcategories.ChannelCategoryId=1
							) cats
							ORDER BY cats.VideoCategorySequenceNumber DESC, cats.VideoCategoryName;
STR;
							// echo $Sql;
							$Bind = array (
									':ImagesDomainName' => Config::$imagesDomainName 
							);
							
							$CategoriesArray = $db->run ( $Sql, $Bind );
							if ($CategoriesArray) {
								$Sql = <<<STR
								SELECT channels.ChannelCategory AS VideoCategoryId,
										channels.ChannelId AS VideoEntityId,
										channels.ChannelName AS VideoName,
										channels.ChannelDescription AS VideoDescription,
										IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
										IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
										IF(channels.ChannelPosterPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelPosterPath ),channels.ChannelPosterPath) AS VideoPosterPath,
										IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
										IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
										channels.ChannelIOSStreamUrl AS VideoStreamUrl,
										channels.ChannelIOSStreamUrlLow AS VideoStreamUrlLow,
										channels.ChannelTotalViews AS VideoTotalViews,
										channels.ChannelAddedDate AS VideoAddedDate,
										packages.PackageId AS VideoPackageId,
										channels.ChannelRating AS VideoRating,
										IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel
			
										FROM channels
							
										INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
							
										LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
							
										LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
							
										WHERE channels.ChannelIsOnline=1
											AND packages.PackageId IN (6,7,8)
							
						                GROUP BY VideoEntityId
										ORDER BY VideoTotalViews DESC;
STR;
								// echo $Sql;
								$Bind = array (
										':ImagesDomainName' => Config::$imagesDomainName 
								);
								
								$ChannelsArray = $db->run ( $Sql, $Bind );
								if ($ChannelsArray) {
									// Formatting the Data
									Format::formatResponseData ( $CategoriesArray );
									Format::formatResponseData ( $ChannelsArray );
									return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), $CategoriesArray, $ChannelsArray ) ) ) );
								} else {
									return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
								}
							} else {
								return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) ) );
							}
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					// echo $Sql;
					$Bind = array (
							':ImagesDomainName' => Config::$imagesDomainName,
							':CountryCode' => $CountryCode,
							':CountryCodePattern' => "%$CountryCode%" 
					);
					$results = $db->run ( $Sql, $Bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results );
						$Sql = <<<STR
						SELECT -1 AS VideoCategoryId,
								channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
								channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
								channelcategories.ChannelCategoryClickURL AS VideoCategoryURL,
								channelcategories.IsVast AS IsVast,
								channelcategories.AdvertisementVastURL AS AdvertisementVastURL
									
								FROM channelcategories
                            	WHERE channelcategories.ChannelCategoryId = 1
STR;
						$allCategory = $db->run ( $Sql );
						Format::formatResponseData ( $allCategory );
						$i = 1;
						$assArray = array ();
						// TODO : Make All Category Dynamic
						$assArray [0] = $allCategory [0];
						$assArray [0] ['Videos'] = [ ];
						foreach ( $results as $key => $row ) {
							// print_r($row );
							$flag = true;
							foreach ( $assArray as $key => $assrow ) {
								// print_r($row );
								if ($assrow ['VideoCategoryId'] === $row ['VideoCategoryId']) {
									if ($row ['VideoEntityId'] != 202) {
										$tempRow = array_splice ( $row, 5 );
										$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
										$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
									}
									$flag = false;
								}
							}
							if ($flag) {
								$assArray [$i] ['VideoCategoryId'] = $row ['VideoCategoryId'];
								$assArray [$i] ['VideoCategoryName'] = $row ['VideoCategoryName'];
								$assArray [$i] ['VideoCategoryImagePath'] = $row ['VideoCategoryImagePath'];
								$assArray [$i] ['VideoCategoryURL'] = $row ['VideoCategoryURL'];
								$assArray [$i] ['IsVast'] = $row ['IsVast'];
								$assArray [$i] ['AdvertisementVastURL'] = $row ['AdvertisementVastURL'];
								$tempRow = array_splice ( $row, 5 );
								$assArray [$i] ['Videos'] [] = $tempRow;
								$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
								// print_r($assArray[$i]);
								$i ++;
							}
						}
						function compareForSorting($Value1, $Value2) {
							if ($Value1 ['VideoTotalViews'] == $Value2 ['VideoTotalViews']) {
								return 0;
							}
							return ($Value1 ['VideoTotalViews'] > $Value2 ['VideoTotalViews']) ? - 1 : 1;
						}
						
						usort ( $assArray [0] ['Videos'], "compareForSorting" );
						
						return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) ) );
					} else {
						return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) ) );
					}
					break;
				case 'v2' :
				case 'V2' : // Local/International Filter Disabled
					$Sql = <<<STR
					SELECT channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
					channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
					channels.ChannelId AS VideoEntityId,
					channels.ChannelName AS VideoName,
					channels.ChannelDescription AS VideoDescription,
					IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
					IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
					IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
					IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
					channels.ChannelCategory AS VideoCategoryId,
					packages.PackageId AS VideoPackageId,
					channels.ChannelTotalViews AS VideoTotalViews,
					channels.ChannelRating AS VideoRating,
					channels.ChannelAddedDate AS VideoAddedDate,
					NULL AS VideoDuration,
					IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree,
					IF( packages.PackageId = 8, 0, 1 ) AS IsVideoChannel
					
					FROM winettv.channels
		
					INNER JOIN winettv.channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
		
					LEFT JOIN winettv.packagechannels ON channels.ChannelId =	packagechannels.channelId
		
					LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId
		
					WHERE channels.ChannelIsOnline=1
						AND packages.PackageId IN (6,7,8,10)
		
	                GROUP BY VideoEntityId
					ORDER BY VideoTotalViews DESC;
STR;
					// echo $sql;
					$Bind = array (
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					$results = $db->run ( $Sql, $Bind );
					
					if ($results) {
						// print_r($results);
						// Formatting the Data
						Format::formatResponseData ( $results );
						$i = 1;
						$assArray = array ();
						// TODO : Make All Category Dynamic
						$assArray [0] ['VideoCategoryId'] = - 1;
						$assArray [0] ['VideoCategoryName'] = 'All';
						$assArray [0] ['VideoCategoryImagePath'] = 'http://www.pitelevision.com/images/channels/category/All.jpg';
						$assArray [0] ['Videos'] = [ ];
						foreach ( $results as $key => $row ) {
							// print_r($row );
							$flag = true;
							foreach ( $assArray as $key => $assrow ) {
								// print_r($row );
								if ($assrow ['VideoCategoryName'] === $row ['VideoCategoryName']) {
									$tempRow = array_splice ( $row, 2 );
									$assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
									$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
									$flag = false;
								}
							}
							if ($flag) {
								$assArray [$i] ['VideoCategoryId'] = $row ['VideoCategoryId'];
								$assArray [$i] ['VideoCategoryName'] = $row ['VideoCategoryName'];
								$assArray [$i] ['VideoCategoryImagePath'] = $row ['VideoCategoryImagePath'];
								$tempRow = array_splice ( $row, 2 );
								$assArray [$i] ['Videos'] [] = $tempRow;
								$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
								// print_r($assArray[$i]);
								$i ++;
							}
						}
						return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $assArray, Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Categories' ) ) ) );
					} else {
						return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Categories' ) ) ) );
					}
					break;
				case 'v3' :
				case 'V3' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( 'In Process.', NULL, NULL, 'Categories' ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ), NULL, NULL, 'Categories' ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ), NULL, NULL, 'Categories' ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	 
	 
	public static function testQuery3(Request $request, Response $response){
    set_time_limit(10000);

$db = parent::getDataBase();
$idArray = [
	182908  ,
	1143553 ,
	1605432 ,
	2118442 ,
	2254466 ,
	2345678 ,
	3419670 ,
	3593789 ,
	3772755 ,
	6307973 ,
	7014020 ,
	7202154 ,
	7204350 ,
	7204860 ,
	7210856 ,
	7215056 ,
	7254821 ,
	7330757 ,
	7332432 ,
	7338598 ,
	7406895 ,
	7508816 ,
	7543213 ,
	7543787 ,
	7544954 ,
	7545507 ,
	7568902 ,
	7579196 ,
	7584480 ,
	7656295 ,
	7662447 ,
	7662681 ,
	7663001 ,
	7704014 ,
	7709546 ,
	7746881 ,
	7749262 ,
	7753974 ,
	7755897 ,
	7756644 ,
	7780617 ,
	7812669 ,
	7829316 ,
	7833868 ,
	7835704 ,
	7836840 ,
	7838991 ,
	7839792 ,
	7839827 ,
	7840265 ,
	7840631 ,
	7858524 ,
	7859098 ,
	7859378 ,
	7859964 ,
	7869030 ,
	7869976 ,
	7870191 ,
	7870283 ,
	7888771 ,
	7890461 ,
	7891445 ,
	7892077 ,
	7892239 ,
	7892391 ,
	7893194 ,
	7893264 ,
	7893895 ,
	7894831 ,
	];
$totalRecord=0;
$length = count($idArray);
for($i=0;$i<$length;$i++){
$bind = array(
        ":UserId" => $idArray[$i],
    ":PackageCode"=> 1007,
    
);
$results = $db->select("userpackages", "UserId=:UserId AND PackageCode=:PackageCode", $bind);
if(sizeof($results)>2){
    $totalRecord++;
    $UserPackageId=$results[1]['UserPackageId'];
    $bind = array(
        ":UserPackageId" => $UserPackageId,
    );
    $sql = <<<STR
    DELETE FROM userpackages  WHERE UserPackageId=:UserPackageId
STR;
$resultsCount = $db->run($sql, $bind);


$totalRecord++;
    $UserPackageId=$results[2]['UserPackageId'];
    $bind = array(
        ":UserPackageId" => $UserPackageId,
    );
    $sql = <<<STR
    DELETE FROM userpackages  WHERE UserPackageId=:UserPackageId
STR;
$resultsCount = $db->run($sql, $bind);
}

else if(sizeof($results)>1){
    $totalRecord++;
    $UserPackageId=$results[1]['UserPackageId'];
    $bind = array(
        ":UserPackageId" => $UserPackageId,
    );
    $sql = <<<STR
    DELETE FROM userpackages  WHERE UserPackageId=:UserPackageId
STR;
$resultsCount = $db->run($sql, $bind);
}
}
echo "Total deleted Records : ".$totalRecord;             
} 
	 
	 
	
	
public static function testQuery4(Request $request, Response $response){
    set_time_limit(10000);

$db = parent::getDataBase();
$idArray = [
	182908 ,
	1143553,
	1605432,
	2118442,
	2254466,
	2345678,
	3419670,
	3593789,
	3772755,
	6241694,
	6307973,
	7014020,
	7202154,
	7204350,
	7204860,
	7210856,
	7215056,
	7254821,
	7330757,
	7332432,
	7338598,
	7406895,
	7508816,
	7543213,
	7543787,
	7544954,
	7545507,
	7568902,
	7579196,
	7584480,
	7656295,
	7662447,
	7662681,
	7663001,
	7704014,
	7709546,
	7746881,
	7749262,
	7753974,
	7755897,
	7756644,
	7780617,
	7812669,
	7829316,
	7833868,
	7835704,
	7836840,
	7838991,
	7839792,
	7839827,
	7840265,
	7840631,
	7858524,
	7859098,
	7859378,
	7859964,
	7869030,
	7869976,
	7870191,
	7870283,
	7888771,
	7890461,
	7891445,
	7892077,
	7892239,
	7892391,
	7893194,
	7893264,
	7893895,
	7894831,
	];
$totalRecord=0;
$length = count($idArray);
for($i=0;$i<$length;$i++){
$bind = array(
        ":UserId" => $idArray[$i],
    ":PackageCode"=> 1007,
    
);
$results = $db->select("userpackages", "UserId=:UserId AND PackageCode=:PackageCode", $bind);
if(sizeof($results)>2){
    $totalRecord++;
    $UserPackageId=$results[1]['UserPackageId'];
    $bind = array(
        ":UserPackageId" => $UserPackageId,
    );
    $sql = <<<STR
    DELETE FROM userpackages  WHERE UserPackageId=:UserPackageId
STR;
$resultsCount = $db->run($sql, $bind);


$totalRecord++;
    $UserPackageId=$results[2]['UserPackageId'];
    $bind = array(
        ":UserPackageId" => $UserPackageId,
    );
    $sql = <<<STR
    DELETE FROM userpackages  WHERE UserPackageId=:UserPackageId
STR;
$resultsCount = $db->run($sql, $bind);
}

else if(sizeof($results)>1){
    $totalRecord++;
    $UserPackageId=$results[1]['UserPackageId'];
    $bind = array(
        ":UserPackageId" => $UserPackageId,
    );
    $sql = <<<STR
    DELETE FROM userpackages  WHERE UserPackageId=:UserPackageId
STR;
$resultsCount = $db->run($sql, $bind);
}
}
echo "Total deleted Records : ".$totalRecord;             
} 	
	
	
	
	
}