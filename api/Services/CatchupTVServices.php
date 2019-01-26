<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
/**
 * Class to Handle all Services Related to Catchup TV Services
 *
 * @author SAIF UD DIN
 *        
 */
class CatchupTVServices extends Config {
	public static function getCatchupTVURL(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$VideoEntityId = $request->getAttribute ( 'VideoEntityId' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					switch ($Platform) {
						case 'Android' :
						case 'android' :
						case 'ANDROID' :
						case 'Web' :
						case 'web' :
						case 'WEB' :
							$sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
									catchuptv.CatchUpCategoryID AS VideoCategoryId,
									catchuptv.CatchUpID AS VideoEntityId,
									catchuptv.CatchUpTitle as VideoName,
                                    IF(catchuptv.CatchUpMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileSmall ),catchuptv.CatchUpMobileSmall) AS VideoImagePath,
								    IF(catchuptv.CatchUpMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileLarge ),catchuptv.CatchUpMobileLarge) AS VideoImagePathLarge,
									IF(catchuptv.NewCatchUpThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.NewCatchUpThumb ),catchuptv.NewCatchUpThumb) AS NewCatchUpThumb,
                                    catchuptv.CatchUpSDVideo AS VideoStreamUrlLQ,
                                    catchuptv.CatchUpHDVideo AS VideoStreamUrlMQ,
									null AS VideoStreamUrlHQ,
                                    false AS IsVideoChannel,
									4 AS VideoType,
									false AS IsVideoDVR,
									catchuptv.CatchUpDescription as VideoDescription,
									catchuptv.CatchUpTotalViews as VideoTotalViews,
									'Local Packages' AS PackageName,								
									'1007' AS PackageProduct,
									'15' AS PackagePrice
                                    FROM catchuptv

                                    INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = catchuptv.CatchUpCategoryID
			
									
									
								    WHERE catchuptv.CatchUpIsOnline=1
									    AND catchuptv.CatchUpID = :VideoEntityId

STR;
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					// echo $sql;
					$bind = array (
							':VideoEntityId' => $VideoEntityId,
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						Format::formatResponseData ( $results );
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Video' ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Video' ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					switch ($Platform) {
						case 'Android' :
						case 'android' :
						case 'ANDROID' :
						case 'Web' :
						case 'web' :
						case 'WEB' :
							$sql = <<<STR
							SELECT videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
									catchuptv.CatchUpCategoryID AS VideoCategoryId,
									catchuptv.CatchUpID AS VideoEntityId,
									catchuptv.CatchUpTitle as VideoName,
                                    IF(catchuptv.CatchUpMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileSmall ),catchuptv.CatchUpMobileSmall) AS VideoImagePath,
								    IF(catchuptv.CatchUpMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileLarge ),catchuptv.CatchUpMobileLarge) AS VideoImagePathLarge,
									IF(catchuptv.NewCatchUpThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.NewCatchUpThumb ),catchuptv.NewCatchUpThumb) AS NewCatchUpThumb,
                                    catchuptv.CatchUpSDVideo AS VideoStreamUrlLow,
                                    catchuptv.CatchUpHDVideo AS VideoStreamUrl,
									null AS VideoStreamUrlHD,
                                    false AS IsVideoChannel,
									4 AS VideoType,
									false AS IsVideoDVR,
									catchuptv.CatchUpDescription as VideoDescription,
									catchuptv.CatchUpTotalViews as VideoTotalViews,
									
					
                                    FROM catchuptv

                                    INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = catchuptv.CatchUpCategoryID
										AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
									
								    WHERE catchuptv.CatchUpIsOnline=1
									    AND catchuptv.CatchUpID = :VideoEntityId

STR;
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					// echo $sql;
					$bind = array (
							':VideoEntityId' => $VideoEntityId,
							':ImagesDomainName' => Config::$imagesDomainName 
					);
					
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						Format::formatResponseData ( $results );
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results [0], Message::getMessage ( 'M_DATA' ), NULL, NULL, 'Video' ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), NULL, NULL, 'Video' ) ) );
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
	public static function getCatchupTV(Request $request, Response $response) {
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
			if ($CountryCode === 'PK') {
				switch ($Platform) {
					case 'AndroidOffline' :
					case 'androidoffline' :
					case 'ANDROIDOFFLINE' :
						$Sql = <<<STR
						SELECT catchuptv.CatchUpID AS VideoEntityId,
								catchuptv.CatchUpTitle AS VideoName,
								catchuptv.CatchUpDescription AS VideoDescription,
								IF(catchuptv.CatchUpThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpThumb ),catchuptv.CatchUpThumb) AS VideoImageThumbnail,
								IF(catchuptv.CatchUpMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileSmall ),catchuptv.CatchUpMobileSmall) AS VideoImagePath,
								IF(catchuptv.CatchUpMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileLarge ),catchuptv.CatchUpMobileLarge) AS VideoImagePathLarge,
								IF(catchuptv.NewCatchUpThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.NewCatchUpThumb ),catchuptv.NewCatchUpThumb) AS NewCatchUpThumb,
								0 AS VideoCategoryId,
								catchuptv.CatchUpChannelID AS VideoChannelId,
								catchuptv.CatchUpTotalViews AS VideoTotalViews,
								NULL AS VideoRating,
								TIME(catchuptv.CatchUpDuration) AS VideoDuration,
								catchuptv.CatchUpIsOnline AS IsVideoOnline,
								false AS IsVideoFree,
								false AS IsVideoChannel,
								catchuptv.CatchUpReleaseDate AS VideoReleaseDate,
								catchuptv.CatchUpAddedDate AS VideoAddedDate,
								catchuptv.CatchUpUpdatedDate AS VideoUpdatedDate,
								TIME(catchuptv.CatchUpDuration) AS VideoDuration,
								false AS IsVideoFree,
								'Premium' AS PackageName,								
								'1007' AS PackageProduct,
								'15' AS PackagePrice
								

								
			
								FROM catchuptv
								
			
								WHERE DATE(catchuptv.CatchUpReleaseDate) BETWEEN SUBDATE(CURRENT_DATE,30) AND CURRENT_DATE()
STR;
						$Sql .= "
									AND catchuptv.CatchUpUpdatedDate > '" . $DateTime . "'
											
								GROUP BY catchuptv.CatchUpID
											
								ORDER BY catchuptv.CatchUpReleaseDate;";
						// echo $Sql;
						$Bind = array (
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						
						$VODsArray = $db->run ( $Sql, $Bind );
						if ($VODsArray) {
							// Formatting the Data
							Format::formatResponseData ( $VODsArray, 1 );
							return General::getResponse ( $response->write ( SuccessObject::getTVVideoSuccessObject ( Message::getMessage ( 'M_DATA' ), NULL, $VODsArray ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getTVVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
						}
						break;
					case 'Android' :
					case 'android' :
					case 'ANDROID' :
						$Sql = <<<STR
                        SELECT catchuptv.CatchUpChannelID AS TabId,
								channels.ChannelName AS TabName,
								IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS TabPosterPath,
								DATE_FORMAT(catchuptv.CatchUpReleaseDate,'%m%d%Y') AS SectionId,
								DATE_FORMAT(catchuptv.CatchUpReleaseDate,'%W (%d-%m-%Y)') AS SectionName,
                                false AS IsSectionMore,
								catchuptv.CatchUpID AS VideoEntityId,
								false AS IsVideoChannel,
								catchuptv.CatchUpTitle AS VideoName,
								catchuptv.CatchUpDescription AS VideoDescription,
								IF(catchuptv.CatchUpThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpThumb ),catchuptv.CatchUpThumb) AS VideoImageThumbnail,
								IF(catchuptv.CatchUpPreview NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpPreview ),catchuptv.CatchUpPreview) AS VideoPosterPath,
								IF(catchuptv.CatchUpMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileSmall ),catchuptv.CatchUpMobileSmall) AS VideoImagePath,
								IF(catchuptv.CatchUpMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileLarge ),catchuptv.CatchUpMobileLarge) AS VideoImagePathLarge,
								IF(catchuptv.NewCatchUpThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.NewCatchUpThumb ),catchuptv.NewCatchUpThumb) AS NewCatchUpThumb,
								catchuptv.CatchUpSDVideo AS VideoStreamUrlLQ,
                                catchuptv.CatchUpHDVideo AS VideoStreamUrlMQ,
								null AS VideoStreamUrlHQ,
                                0 AS VideoCategoryId,
								NULL AS VideoPackageId,
								catchuptv.CatchUpTotalViews AS VideoTotalViews,
								NULL AS VideoRating,
                                4 AS VideoType,
								catchuptv.CatchUpAddedDate AS VideoAddedDate,
                                catchuptv.CatchUpReleaseDate AS VideoReleaseDate,
								TIME(catchuptv.CatchUpDuration) AS VideoDuration,
								false AS IsVideoFree,
								'Premium' AS PackageName,								
								'1007' AS PackageProduct,
								'15' AS PackagePrice
								

								FROM catchuptv

                                INNER JOIN channels ON channels.ChannelId = catchuptv.CatchUpChannelID
		                   	 			AND channels.ChannelIsOnline=1
										
								
								
								WHERE catchuptv.CatchUpIsOnline=1
									AND DATE(catchuptv.CatchUpReleaseDate) BETWEEN SUBDATE(CURRENT_DATE,30) AND CURRENT_DATE()

								
								ORDER BY catchuptv.CatchUpReleaseDate desc
STR;
						// echo $Sql;
						$Bind = array (
								':ImagesDomainName' => Config::$imagesDomainName 
						);
						
						$VODsArray = $db->run ( $Sql, $Bind );
						
						$sql = <<<STR
					SELECT catchuptv.CatchUpChannelID AS TabId,
							channels.ChannelName AS TabName
				
					FROM catchuptv

					INNER JOIN channels ON channels.ChannelId = catchuptv.CatchUpChannelID
		                AND channels.ChannelIsOnline=1
				
					GROUP BY TabId;
STR;
						$ChannelsArray = $db->run ( $sql );
						if ($VODsArray) {
							// Formatting the Data
							Format::formatResponseData ( $VODsArray, 1 );
							Format::formatResponseData ( $ChannelsArray, 1 );
							
							// Creating Section Array with Details
							$i = 0;
							$sectionArray = array ();
							foreach ( $VODsArray as $row ) {
								$flag = true;
								foreach ( $sectionArray as $key => $assrow ) {
									if ($assrow ['SectionId'] === $row ['SectionId'] && $assrow ['TabId'] === $row ['TabId']) {
										$count = count ( $sectionArray [$key] ['Videos'] );
										$sectionArray [$key] ['Videos'] [$count] = array_splice ( $row, 6 );
										$flag = false;
									}
								}
								if ($flag) {
									$sectionArray [$i] ['TabId'] = $row ['TabId'];
									$sectionArray [$i] ['TabName'] = $row ['TabName'];
									$sectionArray [$i] ['TabPosterPath'] = $row ['TabPosterPath'];
									$sectionArray [$i] ['SectionId'] = $row ['SectionId'];
									$sectionArray [$i] ['SectionName'] = $row ['SectionName'];
									$sectionArray [$i] ['IsSectionMore'] = $row ['IsSectionMore'];
									$sectionArray [$i] ['Videos'] [] = array_splice ( $row, 6 );
									$i ++;
								}
							}
							
							// print_r($sectionArray);
							
							// Creating Tab Array
							$i = 0;
							$tabArray = array ();
							foreach ( $ChannelsArray as $dataRow ) {
								$flag = true;
								foreach ( $tabArray as $key => $assrow ) {
									if ($assrow ['TabId'] === $dataRow ['TabId']) {
										$count = count ( $tabArray [$key] ['Sections'] );
										if ($count <= $limit)
											$tabArray [$key] ['Sections'] [$count] = array_splice ( $dataRow, 1 );
										$flag = false;
									}
								}
								if ($flag) {
									$tabArray [$i] ['TabId'] = $dataRow ['TabId'];
									$tabArray [$i] ['TabName'] = $dataRow ['TabName'];
									$tabArray [$i] ['Sections'] [] = array_splice ( $dataRow, 1 );
									$i ++;
								}
							}
							
							// Merging Section Array into Tab Array
							$i = 0;
							$tabArray = array ();
							foreach ( $sectionArray as $row ) {
								$flag = true;
								foreach ( $tabArray as $key => $assrow ) {
									if ($assrow ['TabId'] === $row ['TabId']) {
										$count = count ( $tabArray [$key] ['Sections'] );
										$tabArray [$key] ['Sections'] [$count] = array_splice ( $row, 3 );
										$flag = false;
									}
								}
								if ($flag) {
									$tabArray [$i] ['TabId'] = $row ['TabId'];
									$tabArray [$i] ['TabName'] = $row ['TabName'];
									$tabArray [$i] ['TabPosterPath'] = $row ['TabPosterPath'];
									$tabArray [$i] ['Sections'] [] = array_splice ( $row, 3 );
									$i ++;
								}
							}
							return General::getResponse ( $response->write ( SuccessObject::getSectionSuccessObject ( $tabArray, Message::getMessage ( 'M_DATA' ) ) ) );
						} else {
							return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
						}
						break;
					default :
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
						break;
				}
			} else {
				return General::getResponse ( $response->write ( ErrorObject::getSectionErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$results = NULL;
			$db = NULL;
		}
	}
	
	
	//-----------------------get Catch up Related Videos---------------------------------//
	public static function getRelatedCatchup(Request $request, Response $response) 
    {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		parent::setConfig ( $Language );
		$Platform = $request->getAttribute ( 'Platform' );
		$CatchUpCategoryID = $request->getAttribute ( 'CatchUpCategoryID' );
		$CatchUpChannelID = $request->getAttribute ( 'CatchUpChannelID' );
		$results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' : // Local/International Filter Enabled
                                    switch ($Platform) {
                                        case 'Android' :
                                        case 'android' :
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
                                        $sql = <<<STR
                                        SELECT 
                                        catchuptv.CatchUpID AS VideoEntityId,
                                        catchuptv.CatchUpTitle AS VideoName,
                                        catchuptv.CatchUpDescription AS VideoDescription,
                                        IF(catchuptv.CatchUpThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpThumb ),catchuptv.CatchUpThumb) AS VideoImageThumbnail,
                                        IF(catchuptv.CatchUpMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileSmall ),catchuptv.CatchUpMobileSmall) AS VideoImagePath,
                                        IF(catchuptv.CatchUpMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, catchuptv.CatchUpMobileLarge ),catchuptv.CatchUpMobileLarge) AS VideoImagePathLarge,
                                        catchuptv.CatchUpChannelID AS VideoChannelId,
                                        TIME(catchuptv.CatchUpDuration) AS VideoDuration,
                                        catchuptv.CatchUpIsOnline AS IsVideoOnline,
                                        false AS IsVideoFree,
                                        IF(catchuptv.CatchUpChannelID!='null',true,false) AS IsVideoChannel,        
                                        catchuptv.CatchUpReleaseDate AS VideoReleaseDate,
                                        catchuptv.CatchUpAddedDate AS VideoAddedDate,
                                        catchuptv.CatchUpUpdatedDate AS VideoUpdatedDate,
										'Premium' AS PackageName,								
										'1007' AS PackageProduct

                                        FROM catchuptv        
                                        INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId=catchuptv.CatchUpCategoryID
                                        AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
                                        WHERE catchuptv.CatchUpCategoryID=:CatchUpCategoryID OR catchuptv.CatchUpChannelID=:CatchUpChannelID        
                                        AND CASE WHEN :CountryCode != 'PK' AND videoondemandcategories.VideoOnDemandCategoryIsAllowedInternationally = 0
                                        THEN 0
                                        ELSE 1 END
                                        GROUP BY catchuptv.CatchUpID
					ORDER BY catchuptv.CatchUpReleaseDate;
STR;
$sql .= "LIMIT" . Config::$CatchupVODsLimit;
						
                                        $bind = array (
                                                        ':CatchUpCategoryID' => $CatchUpCategoryID,
                                                        ':CatchUpChannelID' => $CatchUpChannelID,
                                                        ':ImagesDomainName' => Config::$imagesDomainName,
                                                        ':CountryCode' => $CountryCode
                                        );
                                        $results = $db->run ( $sql, $bind );
                                        
                                        if ($results) {
                                                Format::formatResponseData ( $results );                                                
                                                return General::getResponse($response->write(SuccessObject::getRelatedcatchupVideoSuccessObject(Message::getMessage('M_DATA'), $results ,Config::$CatchupVODsLimit, count($results))));
                                        } else {
                                                return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
                                        }
					
                                        break;
                                        default :
                                                return General::getResponse ( $response->write ( SuccessObject::getRelatedVideoSuccessObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
                                                break;
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
}