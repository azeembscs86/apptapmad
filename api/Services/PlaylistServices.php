<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
class PlaylistServices extends Config {
	public static function getAllFavouriteList(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		$Platform = $request->getAttribute ( 'Platform' );
		$UserID = $request->getAttribute ( 'UserID' );
		
		try {
			parent::setConfig ( $Language );
			$db = parent::getDataBase ();
			
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					$sql = <<<STR
					SELECT userfavourites.UserId AS UserId,
							userfavourites.FavouriteName AS FavouriteListName
							
							FROM userfavourites
			
                    		WHERE userfavourites.UserId = :UserId
	
	                		GROUP BY UserId,FavouriteListName
STR;
					// echo $sql;
					$bind = array (
							":UserId" => $UserID 
					);
					
					$results = $db->run ( $sql, $bind );
					
					if ($results) {
						Format::formatResponseData ( $results );
						return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ), Config::$getVODsAndMoviesLimit, count ( $results ) ) ) );
					} else {
						return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ), Config::$getVODsAndMoviesLimit, count ( $results ) ) ) );
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					) ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$db = null;
		}
	}
	public static function getFavouriteListing(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		$Platform = $request->getAttribute ( 'Platform' );
		$UserID = $request->getAttribute ( 'UserID' );
		$FavouriteName = $request->getAttribute ( 'FavouriteName' );
		
		try {
			parent::setConfig ( $Language );
			$db = parent::getDataBase ();
			
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					include_once '../geoip/geoip.php';
					$CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
					
					// $CountryCode = 'PK';

                    switch ($Platform) {
						case 'Android' :
						case 'android' :
						case 'ANDROID' :
						case 'Web' :
						case 'web' :
						case 'WEB' :
					        $sql = <<<STR
                            SELECT * FROM (
					            SELECT 
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'10') AS PackagePrice,
								userfavourites.UserId AS UserId,
					            channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
					            channelcategories.ChannelCategoryPreview AS VideoCategoryImagePath,
                                userfavourites.FavouriteContentId AS VideoEntityId,
					            channels.ChannelName AS VideoName,
					            channels.ChannelDescription AS VideoDescription,
					            IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
								IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
								IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
					            channels.ChannelCategory AS VideoCategoryId,
                                packages.PackageId AS VideoPackageId,
					            channels.ChannelTotalViews AS VideoTotalViews,
                                channels.ChannelRating AS VideoRating,
                                channels.ChannelAddedDate AS VideoAddedDate,
                                NULL AS VideoDuration,
                                IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
                                userfavourites.IsChannel AS IsVideoChannel
    
					            FROM userfavourites
					
                                INNER JOIN channels ON channels.ChannelId = userfavourites.FavouriteContentId
						            AND channels.ChannelIsOnline=1
					
					            INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory
					
					            LEFT JOIN packagechannels ON channels.ChannelId =	packagechannels.channelId
				
								LEFT JOIN packages ON packages.PackageId = packagechannels.packageId
					
                                WHERE userfavourites.IsChannel = 1
						            AND userfavourites.UserId = :UserId
                                    AND userfavourites.FavouriteName = :FavouriteName
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
                    
                            UNION ALL

					            SELECT 
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'10',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice,
								userfavourites.UserId AS UserId,
					            videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
                                videoondemandcategories.VideoOnDemandCategorythumb  AS VideoCategoryImagePath,
					            userfavourites.FavouriteContentId AS VideoEntityId,
					            videoondemand.VideoOnDemandTitle AS VideoName,
					            videoondemand.VideoOnDemandDescription AS VideoDescription,
                                IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
					            IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
					            IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
					            videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
					            NULL AS VideoPackageId,
                                videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
					            NULL AS VideoRating,
					            videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
					            videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
					            videoondemand.VideoOnDemandIsFree AS IsVideoFree,
                                userfavourites.IsChannel AS IsVideoChannel
    
					            FROM userfavourites
                    
                                INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = userfavourites.FavouriteContentId
						            AND videoondemand.VideoOnDemandIsOnline=1
						            AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
						            AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
						            AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL
					
					            INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
						            AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
								LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
								LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
					            WHERE userfavourites.IsChannel = 0
						            AND userfavourites.UserId = :UserId
						            AND userfavourites.FavouriteName = :FavouriteName
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
                            ) joinedResult
                            ORDER BY joinedResult.IsVideoChannel DESC
STR;
					        // echo $sql;
					        $bind = array (
							        ':ImagesDomainName' => Config::$imagesDomainName,
							        ':UserId' => $UserID,
							        ':FavouriteName' => $FavouriteName,
							        ':CountryCode' => $CountryCode,
							        ':CountryCodePattern' => "%$CountryCode%" 
					        );
					
					        $results = $db->run ( $sql, $bind );
					
					        if ($results) {
						        Format::formatResponseData ( $results );
						        return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DATA' ) ) ) );
					        } else {
						        return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'W_NO_CONTENT' ) ) ) );
					        }
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					break;
				case 'v2' :
				case 'V2' :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( array (
							'In Process.' 
					) ) ) );
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$db = null;
		}
	}
	public static function addFavouriteListing(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		$Platform = $request->getAttribute ( 'Platform' );
		$UserFavourites ['UserId'] = $request->getAttribute ( 'UserID' );
		$UserFavourites ['FavouriteName'] = $request->getAttribute ( 'FavouriteName' );
		$UserFavourites ['VideoEntityId'] = $request->getAttribute ( 'ContentId' );
		$UserFavourites ['IsVideoChannel'] = $request->getAttribute ( 'IsChannel' );
		
		try {
			parent::setConfig ( $Language );
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
                            $Bind = array (
							    ":UserId" => isset ( $UserFavourites ['UserId'] ) ? $UserFavourites ['UserId'] : null,
							    ":FavouriteName" => isset ( $UserFavourites ['FavouriteName'] ) ? $UserFavourites ['FavouriteName'] : null,
							    ":FavouriteContentId" => isset ( $UserFavourites ['VideoEntityId'] ) ? $UserFavourites ['VideoEntityId'] : null,
							    ":IsChannel" => isset ( $UserFavourites ['IsVideoChannel'] ) ? $UserFavourites ['IsVideoChannel'] : null 
					        );
					        if ($db->select ( "userfavourites", "UserId = :UserId AND FavouriteName = :FavouriteName AND FavouriteContentId = :FavouriteContentId AND  IsChannel = :IsChannel", $Bind )) {
					            return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_EXISTS' ) ) ) );
					        } else {
						        $InsertionArray = array (
							            "UserId" => isset ( $UserFavourites ['UserId'] ) ? $UserFavourites ['UserId'] : null,
							            "FavouriteName" => isset ( $UserFavourites ['FavouriteName'] ) ? $UserFavourites ['FavouriteName'] : null,
							            "FavouriteContentId" => isset ( $UserFavourites ['VideoEntityId'] ) ? $UserFavourites ['VideoEntityId'] : null,
							            "IsChannel" => isset ( $UserFavourites ['IsVideoChannel'] ) ? $UserFavourites ['IsVideoChannel'] : null 
					            );
					            // print_r ( $insert );
					            if ($db->insert ( "userfavourites", $InsertionArray )) {
                                    $results = array($UserFavourites);
                                    Format::formatResponseData ( $results );
						            return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_INSERT' ) ) ) );
					            } else {
						            return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_NO_INSERT' ) ) ) );
					            }
					        }
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$db = null;
		}
	}
	public static function deleteFavouriteListing(Request $request, Response $response) {
		$Version = $request->getAttribute ( 'Version' );
		$Language = $request->getAttribute ( 'Language' );
		$Platform = $request->getAttribute ( 'Platform' );
		$UserFavourites ['UserId'] = $request->getAttribute ( 'UserID' );
		$UserFavourites ['FavouriteName'] = $request->getAttribute ( 'FavouriteName' );
		$UserFavourites ['VideoEntityId'] = $request->getAttribute ( 'ContentId' );
		$UserFavourites ['IsVideoChannel'] = $request->getAttribute ( 'IsChannel' );
		
		try {
			parent::setConfig ( $Language );
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
					        $Bind = array (
							        ":UserId" => isset ( $UserFavourites ['UserId'] ) ? $UserFavourites ['UserId'] : null,
							        ":FavouriteName" => isset ( $UserFavourites ['FavouriteName'] ) ? $UserFavourites ['FavouriteName'] : null,
							        ":FavouriteContentId" => isset ( $UserFavourites ['VideoEntityId'] ) ? $UserFavourites ['VideoEntityId'] : null,
							        ":IsChannel" => isset ( $UserFavourites ['IsVideoChannel'] ) ? $UserFavourites ['IsVideoChannel'] : null 
					        );
					        if ($db->delete ( "userfavourites", "UserId = :UserId AND FavouriteName = :FavouriteName AND FavouriteContentId = :FavouriteContentId AND  IsChannel = :IsChannel", $Bind )) {
						        $results = array($UserFavourites);
                                Format::formatResponseData ( $results );
                                return General::getResponse ( $response->write ( SuccessObject::getVideoSuccessObject ( $results, Message::getMessage ( 'M_DELETE' ) ) ) );
					        } else {
						        return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_NO_DELETE' ) ) ) );
					        }
							break;
						default :
							return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_PLATFORM' ) ) ) );
							break;
					}
					break;
				default :
					return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getMessage ( 'E_INVALID_SERVICE_VERSION' ) ) ) );
					break;
			}
		} catch ( PDOException $e ) {
			return General::getResponse ( $response->write ( ErrorObject::getVideoErrorObject ( Message::getPDOMessage ( $e ) ) ) );
		} finally {
			$db = null;
		}
	}
}