<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class to Handle all Services Related to Application Settings
 *
 * @author SAIF UD DIN
 *        
 */
class AppSettings extends Config
{ 

    public static function getRelatedContent(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $ChannelOrVODId = $request->getAttribute('ChannelOrVODId');
        $IsChannel = $request->getAttribute('IsChannel');
        $results = NULL;
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled                    
                    
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';
                    $VideoObject = array();
                    
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
									IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS NewVideoOnDemandThumb,
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
                        $bind = array(
                            ':VideoId' => $ChannelOrVODId,
                            ':ImagesDomainName' => Config::$imagesDomainName,
                            ':CountryCode' => $CountryCode,
                            ':CountryCodePattern' => "%$CountryCode%"
                        );
                        $VideoObject = $db->run($sql, $bind);
                    } else {
                        $sql = <<<STR
						SELECT videoondemand.VideoOnDemandId AS VideoEntityId,
						videoondemand.VideoOnDemandTitle AS VideoName,
						videoondemand.VideoOnDemandDescription AS VideoDescription,
						IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS NewVideoOnDemandThumb,
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
                        $bind = array(
                            ':CountryCode' => $CountryCode,
                            ':VideoId' => $ChannelOrVODId,
                            ':ImagesDomainName' => Config::$imagesDomainName
                        );
                        
                        $VideoObject = $db->run($sql, $bind);
                    }
                    
                    if ($VideoObject) {
                        Format::formatResponseData($VideoObject);
                        if ($IsChannel === '1') {
                            switch ($Platform) {
                                case 'Android':
                                case 'android':
                                    $sql = <<<STR
								SELECT channels.ChannelCategory AS SectionId,
								channelcategories.ChannelCategoryDisplayTitle AS SectionName,
								channels.ChannelId AS VideoEntityId,
								channels.ChannelName AS VideoName,
								channels.ChannelDescription AS VideoDescription,
								IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
								IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
								IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
								IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewChannelThumbnailPath,
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
                                default:
                                    return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                    break;
                            }
                            $sql .= " LIMIT 12";
                            
                            // echo $sql;
                            $bind = array(
                                ':ImagesDomainName' => Config::$imagesDomainName,
                                ':ChannelId' => $ChannelOrVODId,
                                ':CountryCode' => $CountryCode,
                                ':CountryCodePattern' => "%$CountryCode%"
                            );
                            $results = $db->run($sql, $bind);
                            
                            if ($results) {
                                Format::formatResponseData($results);
                                $i = 0;
                                $assArray = array();
                                foreach ($results as $key => $row) {
                                    $flag = true;
                                    foreach ($assArray as $key => $assrow) {
										if ($assrow['SectionName'] === $row['SectionName']) {
											if($row['IsVideoFree']==true)
											{
												$tempRow = array_splice($row, 2,15);
												$assArray[$key]['Videos'][count($assArray[$key]['Videos'])] = $tempRow;
													
											}else{
												$tempRow = array_splice($row, 2);
												$assArray[$key]['Videos'][count($assArray[$key]['Videos'])] = $tempRow;
											}
											$flag = false;
										}
                                    }
									if ($flag) {
                                        $assArray[$i]['SectionId'] = $row['SectionId'];
                                        $assArray[$i]['SectionName'] = $row['SectionName'];
										if($row['IsVideoFree']==true)
										{
											$tempRow = array_splice($row, 2,15);
											$assArray[$i]['Videos'][] = $tempRow;
											
										}else{
											$tempRow = array_splice($row, 2);
											$assArray[$i]['Videos'][] = $tempRow;
										}
										$i ++;
                                    }
                                }
								if($VideoObject[0]['IsVideoFree']==1){
									$VideoObject[0] = array_splice($VideoObject[0], 0,15);
								}
                                return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('M_DATA'), $VideoObject[0], $assArray)));
                            } else {
                                switch ($Platform) {
                                    case 'Android':
                                    case 'android':
                                        $sql = <<<STR
									SELECT channels.ChannelCategory AS SectionId,
									channelcategories.ChannelCategoryDisplayTitle AS SectionName,
									channels.ChannelId AS VideoEntityId,
									channels.ChannelName AS VideoName,
									channels.ChannelDescription AS VideoDescription,
									IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
									IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
									IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
									IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewChannelThumbnailPath,
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
                                    default:
                                        return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                        break;
                                }
                                $sql .= " LIMIT 12";
                                
                                // echo $sql;
                                $bind = array(
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode,
                                    ':CountryCodePattern' => "%$CountryCode%"
                                );
                                $results = $db->run($sql, $bind);
                                Format::formatResponseData($results);
                                $i = 0;
                                $assArray = array();
                                foreach ($results as $key => $row) {
                                    $flag = true;
                                    foreach ($assArray as $key => $assrow) {
                                        if ($assrow['SectionName'] === $row['SectionName']) {
                                            $tempRow = array_splice($row, 2);
                                            $assArray[$key]['Videos'][count($assArray[$key]['Videos'])] = $tempRow;
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $assArray[$i]['SectionId'] = $row['SectionId'];
                                        $assArray[$i]['SectionName'] = $row['SectionName'];
                                        $tempRow = array_splice($row, 2);
                                        $assArray[$i]['Videos'][] = $tempRow;
                                        $i ++;
                                    }
                                }
                                return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('M_DATA'), $VideoObject[0], $assArray)));
                            }
                        } else {
                            switch ($Platform) {
                                case 'Android':
                                case 'android':
                                    $sql = <<<STR
								SELECT videoondemand.VideoOnDemandCategoryId AS SectionId,
								videoondemandcategories.VideoOnDemandCategoryname AS SectionName,
								videoondemand.VideoOnDemandId AS VideoEntityId,
								videoondemand.VideoOnDemandTitle AS VideoName,
								videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS NewVideoOnDemandThumb,
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
                                default:
                                    return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                    break;
                            }
                            $sql .= " LIMIT 12";
                            
                            // echo $sql;
                            $bind = array(
                                ':CountryCode' => $CountryCode,
                                ':ImagesDomainName' => Config::$imagesDomainName,
                                ':VideoOnDemandId' => $ChannelOrVODId
                            );
                            
                            $results = $db->run($sql, $bind);
                            
                            if ($results) {
                                Format::formatResponseData($results);
                                $i = 0;
                                $assArray = array();
                                foreach ($results as $key => $row) {
                                    $flag = true;
                                    foreach ($assArray as $key => $assrow) {
                                        if ($assrow['SectionName'] === $row['SectionName']) {
											if($row['IsVideoFree']==true)
											{
												$tempRow = array_splice($row, 2,13);
												$assArray[$key]['Videos'][count($assArray[$key]['Videos'])] = $tempRow;
												
											}else{
												$tempRow = array_splice($row, 2);
												$assArray[$key]['Videos'][count($assArray[$key]['Videos'])] = $tempRow;
											}
											$flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $assArray[$i]['SectionId'] = $row['SectionId'];
                                        $assArray[$i]['SectionName'] = $row['SectionName'];
										if($row['IsVideoFree']==true)
										{
											$tempRow = array_splice($row, 2,13);
											$assArray[$i]['Videos'][] = $tempRow;
											
										}else{
											$tempRow = array_splice($row, 2);
											$assArray[$i]['Videos'][] = $tempRow;
										}
										$i ++;
                                    }
                                }
								if($VideoObject[0]['IsVideoFree']==1){
									$VideoObject[0] = array_splice($VideoObject[0], 0,13);
								}
								//echo '<pre>';print_r($VideoObject);die;
                                return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('M_DATA'), $VideoObject[0], $assArray)));
                            } else {
                                switch ($Platform) {
                                    case 'Android':
                                    case 'android':
                                        $sql = <<<STR
									SELECT videoondemand.VideoOnDemandCategoryId AS SectionId,
									videoondemandcategories.VideoOnDemandCategoryname AS SectionName,
									videoondemand.VideoOnDemandId AS VideoEntityId,
									videoondemand.VideoOnDemandTitle AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandThumb,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImagePath,
									IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS NewVideoOnDemandThumb,
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
                                    default:
                                        return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('E_INVALID_PLATFORM'))));
                                        break;
                                }
                                $sql .= " LIMIT 12";
                                
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode,
                                    ':ImagesDomainName' => Config::$imagesDomainName
                                );
                                
                                $results = $db->run($sql, $bind);
                                
                                Format::formatResponseData($results);
                                $i = 0;
                                $assArray = array();
                                foreach ($results as $key => $row) {
                                    $flag = true;
                                    foreach ($assArray as $key => $assrow) {
                                        if ($assrow['SectionName'] === $row['SectionName']) {
                                            $tempRow = array_splice($row, 2);
                                            $assArray[$key]['Videos'][count($assArray[$key]['Videos'])] = $tempRow;
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $assArray[$i]['SectionId'] = $row['SectionId'];
                                        $assArray[$i]['SectionName'] = $row['SectionName'];
                                        $tempRow = array_splice($row, 2);
                                        $assArray[$i]['Videos'][] = $tempRow;
                                        $i ++;
                                    }
                                }
                                return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('M_DATA'), $VideoObject[0], $assArray)));
                            }
                        }
                    } else {
                        return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('W_NO_CONTENT'))));
                    }
					}
                    break;
                default:
                    return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(SuccessObject::getRelatedVideoSuccessObject(Message::getPDOMessage($e))));
        } finally {
            $results = NULL;
            $db = NULL;
        }
    }
	
	

    public static function getContentDetailWithAd(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $VideoType = $request->getAttribute('VideoType');
        $VideoEntityId = $request->getAttribute('VideoEntityId');
        $PackageId = $request->getAttribute('PackageId');
        $AdType = 1;
        $AdViewType = 2;
        $Age = 15;
        $Gender = 'All';
        $ResponseData = null;
        $results = null;
        
        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();
            
            switch ($Version) {
                case 'v1':
                case 'V1':
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            $bind = array();
                            if ($VideoType === "1") {
                                $sql = <<<STR
                                CALL getChannelWithAd(
                                        :VideoEntityId,
                                        :PackageId,
                                        :AdType,
                                        :AdViewType,
                                    	:Age,
                                        :Gender,
                                        :CountryCode,
                                        :CountryCodePattern
                                )
STR;
                                $bind = array(
                                    ":VideoEntityId" => $VideoEntityId,
                                    ":PackageId" => $PackageId,
                                    ":AdType" => $AdType,
                                    ":AdViewType" => $AdViewType,
                                    ":Age" => $Age,
                                    ":Gender" => $Gender,
                                    ":CountryCode" => $CountryCode,
                                    ":CountryCodePattern" => "%$CountryCode%"
                                );
                                
                                $results = $db->run($sql, $bind);
                                
                                if ($results) {
                                    Format::formatResponseData($results);
                                    
                                    if (! $results[0]['IsVast']) {
                                        $results[0]['IsVast'] = true;
                                        $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                    }
                                    
                                    if (isset($results[0]['AdvertisementAgencyId']) && $results[0]['AdvertisementAgencyId'] != null && isset($results[0]['AdvertisementClientId']) && $results[0]['AdvertisementClientId'] != null && isset($results[0]['AdvertisementCampaignId']) && $results[0]['AdvertisementCampaignId'] != null && isset($results[0]['AdvertisementId']) && $results[0]['AdvertisementId'] != null) {
                                        $ResponseData['Response'] = Message::getMessage('M_DATA');
                                        $ResponseData['AdVideo'] = $results[0];
                                        return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                    } else {
                                        $ResponseData['Response'] = Message::getMessage('M_DATA');
                                        $ResponseData['AdVideo'] = $results[0];
                                        return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                    }
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                            } else if ($VideoType === "2") {
                                $sql = <<<STR
								CALL getVodWithAd(
                                        :VideoEntityId,
                                        :PackageId,
                                        :AdType,
                                        :AdViewType,
                                    	:Age,
                                        :Gender,
                                        :CountryCode,
                                        :CountryCodePattern
                                )
STR;
                                $bind = array(
                                    ":VideoEntityId" => $VideoEntityId,
                                    ":PackageId" => $PackageId,
                                    ":AdType" => $AdType,
                                    ":AdViewType" => $AdViewType,
                                    ":Age" => $Age,
                                    ":Gender" => $Gender,
                                    ":CountryCode" => $CountryCode,
                                    ":CountryCodePattern" => "%$CountryCode%"
                                );
                                $results = $db->run($sql, $bind);
                                if ($results) {
                                    Format::formatResponseData($results);
                                    
                                    if (! $results[0]['IsVast']) {
                                        $results[0]['IsVast'] = true;
                                        $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                    }
                                    
                                    $Sql = <<<STR
									call getMidrolls();
STR;
                                    $Midrolls = $db->run($Sql);
                                    Format::formatResponseData($Midrolls);
                                    if (isset($results[0]['AdvertisementAgencyId']) && $results[0]['AdvertisementAgencyId'] != null && isset($results[0]['AdvertisementClientId']) && $results[0]['AdvertisementClientId'] != null && isset($results[0]['AdvertisementCampaignId']) && $results[0]['AdvertisementCampaignId'] != null && isset($results[0]['AdvertisementId']) && $results[0]['AdvertisementId'] != null) {
                                        $ResponseData['Response'] = Message::getMessage('M_DATA');
                                        $ResponseData['AdVideo'] = $results[0];
                                        $ResponseData['Midrolls'] = $Midrolls;
                                        return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                    } else {
                                        $ResponseData['Response'] = Message::getMessage('M_DATA');
                                        $ResponseData['AdVideo'] = $results[0];
                                        $ResponseData['Midrolls'] = $Midrolls;
                                        return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                    }
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                            } else if ($VideoType === "3") {
                                $Sql = <<<STR
                                call getChannelsAgainstCategory(
									:ChannelCategoryId,
									:ImagesDomainName,
									:CountryCode,
									:CountryCodePattern
								);
STR;
                                // echo $Sql;
                                $Bind = array(
                                    ':ChannelCategoryId' => $VideoEntityId,
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode,
                                    ':CountryCodePattern' => "%$CountryCode%"
                                );
                                $results = $db->run($Sql, $Bind);
                                
                                if ($results) {
                                    // print_r($results);
                                    // Formatting the Data
                                    Format::formatResponseData($results);
                                    
                                    $ResponseData['Response'] = Message::getMessage('M_DATA');
                                    $ResponseData['Videos'] = $results;
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                                break;
                            } else if ($VideoType === "4") {
                                $sql = <<<STR
    							call getVODCategoriesAgainstParentID(
									:ParentCategoryId,
									:ImagesDomainName,
									:CountryCode
								);
STR;
                                // echo $sql;
                                $bind = array(
                                    ':ParentCategoryId' => $VideoEntityId,
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode
                                );
                                $results = $db->run($sql, $bind);
                                
                                if ($results) {
                                    Format::formatResponseData($results);
                                    
                                    $ResponseData['Response'] = Message::getMessage('M_DATA');
                                    $ResponseData['Videos'] = $results;
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                            } else if ($VideoType === "5") {
                                $Sql = <<<STR
    							call getVODsAgainstCategory(
									:CategoryId,
									:ImagesDomainName,
									:CountryCode
								);
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode,
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CategoryId' => $VideoEntityId
                                );
                                $results = $db->run($Sql, $bind);
                                
                                if ($results) {
                                    Format::formatResponseData($results, 1);
                                    
                                    $ResponseData['Response'] = Message::getMessage('M_DATA');
                                    $ResponseData['Videos'] = $results;
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                            } else if ($VideoType === "6") {
                                $sql = <<<STR
        						call getSeasons(
                                    :SeasonID,
                                     :ImagesDomainName
                                );
STR;
                                // echo $sql;
                                $bind = array(
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':SeasonID' => $VideoEntityId
                                );
                                $seasons = $db->run($sql, $bind);
                                
                                // print_r($seasons);
                                
                                if ($seasons) {
                                    Format::formatResponseData($seasons);
                                    
                                    $ResponseData['Response'] = Message::getMessage('M_DATA');
                                    $ResponseData['Videos'] = $seasons;
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                            } else if ($VideoType === "7") {
                                $sql = <<<STR
                                SELECT 2 AS VideoType,
                                videoondemand.VideoOnDemandId AS VideoEntityId,
    							videoondemand.VideoOnDemandSeasonNo AS VideoSeasonNo,
								videoondemand.VideoOnDemandTitle AS VideoName,
								videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(videoondemand.erosData=1,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
								IF(videoondemand.erosData=1,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
								videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
                                null AS VideoPackageId,
								videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
								NULL AS VideoRating,
								videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
								videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
								videoondemand.VideoOnDemandIsFree AS IsVideoFree,
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
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode,
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CategoryId1' => $VideoEntityId == '-1' ? '3' : $VideoEntityId,
                                    ':CategoryId2' => $VideoEntityId == '-1' ? '8' : $VideoEntityId,
                                    ':SeasonNo' => $PackageId
                                );
                                $results = $db->run($sql, $bind);
                                
                                if ($results) {
                                    Format::formatResponseData($results);
                                    
                                    $ResponseData['Response'] = Message::getMessage('M_DATA');
                                    $ResponseData['Videos'] = $results;
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                            } else if ($VideoType === "8") {
                                $sql = <<<STR
        						SELECT catchuptv.CatchUpSDVideo AS VideoStreamUrlLQ,
                                null AS VideoStreamUrlMQ,
                                catchuptv.CatchUpHDVideo AS VideoStreamUrlHQ,
								null AS VideoStreamUrlHQ,
                                null AS VideoStreamUrlHD,
								null AS VideoChatGroupId,
								false AS ShowFakeLogo

                                FROM catchuptv

                                INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = catchuptv.CatchUpCategoryID

							    WHERE catchuptv.CatchUpIsOnline=1
								    AND catchuptv.CatchUpID = :VideoEntityId

STR;
                                // echo $sql;
                                $bind = array(
                                    ':VideoEntityId' => $VideoEntityId
                                );
                                
                                $results = $db->run($sql, $bind);
                                
                                if ($results) {
                                    Format::formatResponseData($results);
                                    $ResponseData['Response'] = Message::getMessage('M_DATA');
                                    $ResponseData['AdVideo'] = $results[0];
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
                            } else if ($VideoType === "9") {
                                $sql = <<<STR
                                SELECT 2 AS VideoType,
    							videoondemand.VideoOnDemandId AS VideoEntityId,
                                null AS VideoSeasonNo,
    							videoondemand.VideoOnDemandTitle AS VideoName,
    							videoondemand.VideoOnDemandDescription AS VideoDescription,
    							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
								IF(videoondemand.erosData=1,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
								null AS VideoCategoryId,
                                null AS VideoPackageId,
                                videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
    							null AS VideoRating,
    							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
    							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
    							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
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
    							ORDER BY VideoAddedDate DESC
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode,
                                    ':ImagesDomainName' => Config::$imagesDomainName
                                );
                                $results = $db->run($sql, $bind);
                                
                                if ($results) {
                                    // print_r($results);
                                    // Formatting the Data
                                    Format::formatResponseData($results);
                                    
                                    $ResponseData['Response'] = Message::getMessage('M_DATA');
                                    $ResponseData['Videos'] = $results;
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                } else {
                                    $ResponseData['Response'] = Message::getMessage('W_NO_CONTENT');
                                    return General::getResponse($response->write(ResponseObject::getResponseObject($ResponseData)));
                                }
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
            $db = null;
            $results = null;
            $Version = null;
            $Language = null;
            $Platform = null;
            $VideoType = null;
            $VideoEntityId = null;
            $PackageId = null;
            $AdType = null;
            $AdViewType = null;
            $Age = null;
            $Gender = null;
        }
    }

    public static function getHomePageDetail(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $results = null;
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                            $sql = <<<STR
							SELECT homesection.SectionId,
                                    homesection.SectionName,
                                    homesection.SectionShowMore AS IsSectionMore,
                                    homesection.SectionMoreType,
                                    homesection.SectionMoreEntityId,
									homecontent.ContentType AS VideoType,
									homecontent.ContentVideoEntityId AS VideoEntityId,
									homecontent.ContentName AS VideoName,
									null AS VideoDescription,
									IF( homecontent.ContentImage NOT LIKE 'http://%', CONCAT( :ImagesDomainName, homecontent.ContentImage ), homecontent.ContentImage ) AS VideoImagePath,
									IF( homecontent.NewContentImage NOT LIKE 'http://%', CONCAT( :ImagesDomainName, homecontent.NewContentImage ), homecontent.NewContentImage ) AS NewContentImage,
									NULL AS VideoCategoryId,
									NULL AS VideoPackageId,
									NULL AS VideoTotalViews,
									NULL AS VideoRating,
									homecontent.ContentAddedDate AS VideoAddedDate,
									NULL AS VideoDuration,
									homecontent.ContentIsFree AS IsVideoFree

									FROM homecontent

									INNER JOIN homesection ON homesection.SectionId = homecontent.ContentSectionId
		         	           			AND homesection.SectionIsOnline='1'

									WHERE homecontent.ContentIsOnline = 1
										AND CASE WHEN :CountryCode != 'PK' AND homecontent.ContentIsAllowedInternationally = 0
                                            THEN 0
		                            		ELSE 1 END
							         ORDER BY homesection.SectionSequenceNo, homecontent.ContentSequenceNo
STR;
                            // echo $sql;
                            $bind = array(
                                ':ImagesDomainName' => Config::$imagesDomainName,
                                ':CountryCode' => $CountryCode
                            );
                            $results = $db->run($sql, $bind);
                            
                            // print_r($tabbanners);
                            
                            if ($results) {
                                // Formatting the Data
                                Format::formatResponseData($results);
                                
                                // Creating Section Array with Details
                                $i = 0;
                                $homepageArray = array();
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($homepageArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
                                            $count = count($homepageArray[$key]['Videos']);
                                            $homepageArray[$key]['Videos'][$count] = array_splice($row, 5);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $homepageArray[$i]['SectionId'] = $row['SectionId'];
                                        $homepageArray[$i]['SectionName'] = $row['SectionName'];
                                        $homepageArray[$i]['IsSectionMore'] = $row['IsSectionMore'];
                                        $homepageArray[$i]['SectionMoreType'] = $row['SectionMoreType'];
                                        $homepageArray[$i]['SectionMoreEntityId'] = $row['SectionMoreEntityId'];
                                        $homepageArray[$i]['Videos'][] = array_splice($row, 5);
                                        $i ++;
                                    }
                                }
                                
                                $sql = <<<STR
    							SELECT BannerImage AS TabPosterPath,
    									BannerIsVideo AS IsPosterVideo,
    									BannerVideoIsChannel AS IsVideoChannel,
    									BannerVideoEntityId AS VideoEntityId,
    									BannerClickURL AS TabURL

    							FROM homebanner

    							WHERE homebanner.BannerIsOnline='1'
    								AND CASE
    									WHEN :CountryCode != 'PK'
    									THEN
    										BannerIsAllowedInternationally = '1'
    									ELSE 1 END;
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode
                                );
                                $homebanner = $db->run($sql, $bind);
                                Format::formatResponseData($homebanner);
                                
                                $sql = <<<STR
    							SELECT * FROM (
								SELECT tab.TabId,
								tab.TabName,
								tab.TabPosterPath,
								tab.TabClickURL AS TabURL,
								tab.sorttabs AS sorttabs,
								section.SectionId,
								section.SectionName,
								true AS IsSectionMore,
								null AS SectionMoreType,
								null AS SectionMoreEntityId,
								1 AS VideoType,
								sectiondetail.ContentId AS VideoEntityId,
								channels.ChannelName AS VideoName,
								channels.ChannelDescription AS VideoDescription,
								IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
								IF( channels.NewChannelThumbnailPath NOT LIKE 'http://%', CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ), channels.NewChannelThumbnailPath ) AS NewChannelThumbnailPath,
								channels.ChannelCategory AS VideoCategoryId,
								packages.PackageId AS VideoPackageId,
								channels.ChannelTotalViews AS VideoTotalViews,
								channels.ChannelRating AS VideoRating,
								channels.ChannelAddedDate AS VideoAddedDate,
								NULL AS VideoDuration,
								IF(packages.PackageIsFree=1,true,false) AS IsVideoFree
								
								FROM sectiondetail

								INNER JOIN section ON section.SectionId = sectiondetail.SectionId
								AND section.IsOnline='1'
								
								INNER JOIN tab ON tab.TabId=section.SectionTabId
								AND tab.IsOnline='1' AND tab.sorttabs IS NOT NULL						
							   
								
								INNER JOIN channels ON channels.ChannelId = sectiondetail.ContentId
								AND channels.ChannelIsOnline=1

								
								
								LEFT JOIN packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

								LEFT JOIN packages ON packages.PackageId = packagechannels.packageId

								
								
								WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

								UNION ALL

								SELECT tab.TabId,
								tab.TabName,
								tab.TabPosterPath,
								tab.TabClickURL AS TabURL,
								tab.sorttabs AS sorttabs,
								section.SectionId,
								section.SectionName,
								true AS IsSectionMore,
								null AS SectionMoreType,
								null AS SectionMoreEntityId,
								2 AS VideoType,
								sectiondetail.ContentId AS VideoEntityId,					
								IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryname IS NULL, videoondemand.VideoOnDemandTitle, videoondemandcategories.VideoOnDemandCategoryname), videoondemand.VideoOnDemandTitle) AS VideoName,
								videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(section.SectionId !=44, IF(IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall) IS NULL,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall),IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall)), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
								IF(section.SectionId !=44, IF(IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb) IS NULL,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb),IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb)), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
								videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
								0 AS VideoPackageId,
								videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
								NULL AS VideoRating,
								videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
								videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
								videoondemand.VideoOnDemandIsFree AS IsVideoFree
								
		   
								FROM sectiondetail

								INNER JOIN section ON section.SectionId = sectiondetail.SectionId
								AND section.IsOnline='1'

								INNER JOIN tab ON tab.TabId=section.SectionTabId
								AND tab.IsOnline='1' AND tab.sorttabs IS NOT NULL

							  
							   
								INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
								AND videoondemand.VideoOnDemandIsOnline=1

								
								 LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
								 AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
							   
								
								WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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

					) channelvods
					ORDER BY channelvods.sorttabs, channelvods.SectionId, channelvods.VideoTotalViews DESC
STR;
                                // echo $sql;
                                $bind = array(
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode,
                                    ':CountryCodePattern' => "%$CountryCode%"
                                );
                                $results = $db->run($sql, $bind);
                                
                                $sql = <<<STR
    							SELECT BannerTabId AS TabId,
    									BannerId,
    									BannerPath AS TabPosterPath,
    									BannerIsVideo AS IsPosterVideo,
    									BannerVideoIsChannel AS IsVideoChannel,
    									BannerVideoEntityId AS VideoEntityId,
    									BannerURL AS TabURL

    							FROM tabbanners

    							WHERE tabbanners.BannerIsOnline='1'
    								AND CASE
    									WHEN :CountryCode != 'PK'
    									THEN
    										BannerIsAllowedInternationally = '1'
    									ELSE 1 END;
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode
                                );
                                $tabbanners = $db->run($sql, $bind);
                                
                                // print_r($tabbanners);
                                
                                // Formatting the Data
                                Format::formatResponseData($results);
                                Format::formatResponseData($tabbanners);
                                
                                
                                
                                
                                
                                
                                // Creating Section Array with Details
                                $i = 0;
                                $sectionArray = array();
                                $limit = rand(3, 70);
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
                                            $count = count($sectionArray[$key]['Videos']);
                                            if ($count <= $limit) {
                                                $sectionArray[$key]['Videos'][$count] = array_splice($row, 9);
                                            }
                                            
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['TabURL'] = $row['TabURL'];										
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['IsSectionMore'] = $row['IsSectionMore'];
                                        $sectionArray[$i]['SectionMoreType'] = $row['SectionMoreType'];
                                        $sectionArray[$i]['SectionMoreEntityId'] = $row['SectionMoreEntityId'];
                                        $sectionArray[$i]['Videos'][] = array_splice($row, 9);
                                        $i ++;
                                    }
                                }
                                
                                // Shuffling The Video Items
                                $bind = array(
                                    ':AppSettingPlatform' => $Platform
                                );
                                $AppSettings = $db->select("appsettings", "AppSettingPlatform=:AppSettingPlatform", $bind, 'AppSettingIsFeaturedPageRandom,AppSettingSectionsToRandomize');
                                
                                if ($AppSettings) {
                                    if ($AppSettings[0]['AppSettingIsFeaturedPageRandom']) {
                                        if ($AppSettings[0]['AppSettingSectionsToRandomize']) {
                                            $Sections = explode(",", $AppSettings[0]['AppSettingSectionsToRandomize']);
                                            foreach ($sectionArray as $key => $row) {
                                                if (in_array($sectionArray[$key]['SectionId'], $Sections)) {
                                                    shuffle($sectionArray[$key]['Videos']);
                                                }
                                            }
                                        } else {
                                            foreach ($sectionArray as $key => $row) {
                                                shuffle($sectionArray[$key]['Videos']);
                                            }
                                        }
                                    }
                                }
                                
                                $tabArray = array();
                                
                                // Merging Homapage Tab in Tab Array
                                /*
								$i = 0;
                                $tabArray[$i]['TabId'] = null;
                                $tabArray[$i]['TabName'] = null;
                                $tabArray[$i]['TabPosterPath'] = null;
                                $tabArray[$i]['TabURL'] = null;
								$tabArray[$i]['sorttabs'] = null;
                                $tabArray[$i]['Sections'] = null;
                                $tabArray[$i]['Banners'] = null;
                                */
                                // Merging Section Array into Tab Array
                                $i = 0;
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 4);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['TabURL'] = $row['TabURL'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 4);
                                        $tabArray[$i]['Banners'] = array();
                                        $i ++;
                                    }
                                }
                                
                                // Merging Tab Banners
                                foreach ($tabbanners as $row) {
                                    // print_r($row);
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Banners']);
                                            $tabArray[$key]['Banners'][$count] = array_splice($row, 2);
                                        }
                                    }
                                }
                                
								
                                
                                return General::getResponse($response->write(
                                        SuccessObject::getSectionsSuccessObject(
                                        $tabArray, Message::getMessage('M_DATA'), null, null,
                                        AppSettings::localGetAdURL('V1', 'en', 'android', '9', 'All','15')                                        
                                        ,AppSettings::getOtpBanners('V1', 'en', 'android',$CountryCode))
                                        )
                                        );
                            } else {
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('W_NO_CONTENT'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    private static function localGetAdURL($Version, $Language, $Platform, $AdType, $Gender, $Age)
    {
        $results = null;
        
        try {
            $db = parent::getDataBase();
            
            switch ($Version) {
                case 'v1':
                case 'V1':
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
                                ":AdType" => $AdType
                            );
                            $results = $db->run($sql, $bind);
                            
							
                            if ($results) {
                                Format::formatResponseData($results);
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 9) {
                                    if (! $results[0]['IsVast']) {
                                        $results[0]['IsVast'] = true;
                                        $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                    }
                                }
                                
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 1) {
                                    return null;
                                }
                                if (isset($results[0]['AdvertisementId']) && $results[0]['AdvertisementId'] === 17) {
                                    return null;
                                }
                                return $results[0];
                            } else {
                                return null;
                            }
                            break;
                        default:
                            return null;
                            break;
                    }
                    break;
                default:
                    return null;
                    break;
            }
        } catch (PDOException $e) {
            return null;
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function updateAppDownload(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        $Platform = $request->getAttribute('Platform');
        $Result = null;
        
        try {
            parent::setConfig($Language);
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    // Updating APK Downloads
                    $sql = <<<STR
						UPDATE apkdownloads
						SET apkdownloads.ApkDownloadCount = ApkDownloadCount + 1
						WHERE apkdownloads.ApkDownloadPlatform = :Platform
STR;
                    $bind = array(
                        ":Platform" => $Platform
                    );
                    $Result = $db->run($sql, $bind);
                    
                    if ($Result) {
                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_UPDATE'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_UPDATE'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getTempUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $Result = null;
            $db = null;
        }
    }

    /**
     * Function To Get App Settings According To Platform
     *
     * @param Request $request
     * @param Response $response
     */
    public static function getAppSettings(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $results = null;
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    
                    $bind = array(
                        ":AppSettingPlatform" => $Platform
                    );
                    $results = $db->select("appsettings", "AppSettingPlatform=:AppSettingPlatform", $bind);
                    
                    if ($results) {
                        Format::formatResponseData($results);
                        $results[0]['CountryCode'] = $CountryCode;
						$results[0]['isMoblink'] = AppSettings::getUserIpAddress();
                        return General::getResponse($response->write(SuccessObject::getGeneralSuccessObject($results[0], Message::getMessage('M_DATA'), 'AppSettings')));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getAppSettingsErrorObject(Message::getMessage('W_NO_CONTENT'))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getAppSettingsErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getAppSettingsErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getAppSettingsErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    public static function getSectionMoreInfo(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $SectionId = $request->getAttribute('SectionId');
        $OffSet = $request->getAttribute('OffSet');
        $results = null;
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                            $sql = <<<STR
							SELECT * FROM (
									SELECT sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									channels.ChannelName AS VideoName,
									channels.ChannelDescription AS VideoDescription,
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
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
									IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

									FROM winettv.sectiondetail

									INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
										AND section.IsOnline='1'

									INNER JOIN winettv.channels ON channels.ChannelId = sectiondetail.ContentId
										AND channels.ChannelIsOnline=1

									LEFT JOIN winettv.packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

									LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId

									WHERE sectiondetail.IsOnline='1'
										AND sectiondetail.IsChannel = 1
										AND section.SectionId=:SectionId
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

							UNION ALL

									SELECT sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									IF(videoondemandcategories.VideoOnDemandCategoryname IS NULL, videoondemand.VideoOnDemandTitle, videoondemandcategories.VideoOnDemandCategoryname) AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
									IF(videoondemand.erosData=1,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
									videoondemand.VideoOnDemandCategoryId AS VideoCategory,
									0 AS VideoPackageId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice


									FROM winettv.sectiondetail

									INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
										AND section.IsOnline='1'

									INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN winettv.videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
										AND videoondemand.VideoOnDemandIsOnline=1

									LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
									AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
									LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
									LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
									WHERE sectiondetail.IsOnline = 1
										AND sectiondetail.IsChannel = 0
										AND section.SectionId=:SectionId
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
STR;
                            $sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
                            break;
                        case 'ios':
                        case 'IOS':
                            $sql = <<<STR
							SELECT * FROM (
									SELECT sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									channels.ChannelName AS VideoName,
									channels.ChannelDescription AS VideoDescription,
									IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
									IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
									IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
									channels.ChannelCategory AS VideoCategoryId,
									channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
									packages.PackageId AS VideoPackageId,
									channels.ChannelTotalViews AS VideoTotalViews,
									channels.ChannelRating AS VideoRating,
									channels.ChannelAddedDate AS VideoAddedDate,
									NULL AS VideoDuration,
									IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree

									FROM sectiondetail

									INNER JOIN section ON section.SectionId = sectiondetail.SectionId
										AND section.IsOnline='1'

									INNER JOIN channels ON channels.ChannelId = sectiondetail.ContentId
										AND channels.ChannelIsOnline=1

									INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory

									LEFT JOIN packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

									LEFT JOIN packages ON packages.PackageId = packagechannels.packageId

									WHERE sectiondetail.IsOnline='1'
										AND sectiondetail.IsChannel = 1
										AND section.SectionId=:SectionId
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

							UNION ALL

									SELECT sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									videoondemand.VideoOnDemandTitle AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
									IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
									IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
									videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
									videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
									0 AS VideoPackageId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree

									FROM sectiondetail

									INNER JOIN section ON section.SectionId = sectiondetail.SectionId
										AND section.IsOnline='1'

									INNER JOIN tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
										AND videoondemand.VideoOnDemandIsOnline=1

									INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId

									WHERE sectiondetail.IsOnline = 1
										AND sectiondetail.IsChannel = 0
										AND section.SectionId=:SectionId
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
STR;
                            // $sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    
                    $bind = array(
                        ":SectionId" => $SectionId,
                        ':ImagesDomainName' => Config::$imagesDomainName,
                        ':CountryCode' => $CountryCode,
                        ':CountryCodePattern' => "%$CountryCode%"
                    );
                    $results = $db->run($sql, $bind);
                    
                    if ($results) {
                        // Formatting the Data
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($results, Message::getMessage('M_DATA'), Config::$ChannelsANDVODsLimit, count($results))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('W_NO_CONTENT'), Config::$ChannelsANDVODsLimit, count($results))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    $sql = <<<STR
					SELECT * FROM (
							SELECT sectiondetail.ContentId AS VideoEntityId,
							sectiondetail.IsChannel AS IsVideoChannel,
							channels.ChannelName AS VideoName,
							channels.ChannelDescription AS VideoDescription,
							IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
							IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
							IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
							channels.ChannelCategory AS VideoCategory,
							packages.PackageId AS VideoPackageId,
							channels.ChannelTotalViews AS VideoTotalViews,
							channels.ChannelRating AS VideoRating,
							channels.ChannelAddedDate AS VideoAddedDate,
							NULL AS VideoDuration,
							IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
							IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
							IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
							IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
							IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

							FROM winettv.sectiondetail

							INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
                    			AND section.IsOnline='1'

                    		INNER JOIN winettv.channels ON channels.ChannelId = sectiondetail.ContentId
                    			AND channels.ChannelIsOnline=1

							LEFT JOIN winettv.packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

							LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId

							WHERE sectiondetail.IsOnline='1'
								AND sectiondetail.IsChannel = 1
                    			AND section.SectionId=:SectionId
								AND packages.PackageId IN (6,7,8,10)

                    		GROUP BY VideoEntityId

					UNION ALL

							SELECT sectiondetail.ContentId AS VideoEntityId,
							sectiondetail.IsChannel AS IsVideoChannel,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
							IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,videoondemand.VideoOnDemandCategoryId AS VideoCategory,
							0 AS VideoPackageId,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice


							FROM winettv.sectiondetail

							INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
	                    		AND section.IsOnline='1'

	                    	INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
								AND tab.IsOnline='1'

							INNER JOIN winettv.videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
	                    		AND videoondemand.VideoOnDemandIsOnline=1
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
							LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId

							WHERE sectiondetail.IsOnline = 1
								AND sectiondetail.IsChannel = 0
								AND section.SectionId=:SectionId
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL

	                    	GROUP BY VideoEntityId

					) channelvods
					ORDER BY channelvods.VideoTotalViews DESC
STR;
                    $sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
                    
                    $bind = array(
                        ":SectionId" => $SectionId,
                        ':ImagesDomainName' => Config::$imagesDomainName
                    );
                    $results = $db->run($sql, $bind);
                    //echo '<pre>';print_r($results);die;
                    if ($results) {
                        // Formatting the Data
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($results, Message::getMessage('M_DATA'), Config::$ChannelsANDVODsLimit, count($results))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('W_NO_CONTENT'), Config::$ChannelsANDVODsLimit, count($results))));
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(array(
                        'In Process.'
                    ), null, null)));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), null, null)));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }

    /**
     * Function to get App Menu Sections and thier Details
     *
     * @param Request $request
     * @param Response $response
     */
    public static function getSectionAndDetail(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $results = null;
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                            $sql = <<<STR
							SELECT * FROM (
									SELECT tab.TabId,
									tab.TabName,
									tab.TabPosterPath,
									tab.TabClickURL AS TabURL,
									section.SectionId,
									section.SectionName,
                                    true AS IsSectionMore,
									sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									channels.ChannelName AS VideoName,
									channels.ChannelDescription AS VideoDescription,
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
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
									IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
									IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

									FROM winettv.sectiondetail

									INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
		         	           			AND section.IsOnline='1'

		          	          		INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN winettv.channels ON channels.ChannelId = sectiondetail.ContentId
		                   	 			AND channels.ChannelIsOnline=1

									LEFT JOIN winettv.packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

									LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId

									WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

							UNION ALL

									SELECT tab.TabId,
									tab.TabName,
									tab.TabPosterPath,
									tab.TabClickURL AS TabURL,
									section.SectionId,
									section.SectionName,
                                    true AS IsSectionMore,
									sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									IF(videoondemandcategories.VideoOnDemandCategoryname IS NULL, videoondemand.VideoOnDemandTitle, videoondemandcategories.VideoOnDemandCategoryname) AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
									IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
									IF(videoondemand.erosData=1, IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
									videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
									0 AS VideoPackageId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice


									FROM winettv.sectiondetail

									INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
			                    		AND section.IsOnline='1'

			                    	INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN winettv.videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
			                    		AND videoondemand.VideoOnDemandIsOnline=1

									LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
									AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1	
									LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
									LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId	
									WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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

							) channelvods
							ORDER BY channelvods.TabId, channelvods.SectionId, channelvods.VideoTotalViews DESC
STR;
                            // echo $sql;
                            $bind = array(
                                ':ImagesDomainName' => Config::$imagesDomainName,
                                ':CountryCode' => $CountryCode,
                                ':CountryCodePattern' => "%$CountryCode%"
                            );
                            $results = $db->run($sql, $bind);
                            
                            $sql = <<<STR
							SELECT tab.TabId,tab.TabName,section.SectionName

							FROM tab

							INNER JOIN winettv.section ON section.SectionTabId = tab.TabId
		                    	AND section.IsOnline='1'

							WHERE tab.IsOnline='1';
STR;
                            $tabs = $db->run($sql);
                            
                            $sql = <<<STR
							SELECT BannerTabId AS TabId,
									BannerId,
									BannerPath AS TabPosterPath,
									BannerIsVideo AS IsPosterVideo,
									BannerVideoIsChannel AS IsVideoChannel,
									BannerVideoEntityId AS VideoEntityId,
									BannerURL AS TabURL

							FROM tabbanners

							INNER JOIN tab ON tabbanners.BannerTabId = tab.TabId
		                    	AND tab.IsOnline = '1'

							WHERE tabbanners.BannerIsOnline='1'
								AND CASE
									WHEN :CountryCode != 'PK'
									THEN
										BannerIsAllowedInternationally = '1'
									ELSE 1 END;
STR;
                            // echo $sql;
                            $bind = array(
                                ':CountryCode' => $CountryCode
                            );
                            $tabbanners = $db->run($sql, $bind);
                            
                            // print_r($tabbanners);
                            
                            if ($results) {
                                // Formatting the Data
                                Format::formatResponseData($results);
                                Format::formatResponseData($tabbanners);
                                
                                // Creating Section Array with Details
                                $i = 0;
                                $sectionArray = array();
                                $limit = rand(3, 7);
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
											if($row['IsVideoFree']==true){
												$count = count($sectionArray[$key]['Videos']);
												if ($count <= $limit) {
													$sectionArray[$key]['Videos'][$count] = array_splice($row, 7,14);
												}
                                            }else{
												$count = count($sectionArray[$key]['Videos']);
												if ($count <= $limit) {
													$sectionArray[$key]['Videos'][$count] = array_splice($row, 7);
												}
											}
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['TabURL'] = $row['TabURL'];
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['IsSectionMore'] = $row['IsSectionMore'];
										//echo '<pre>';print_r($row);die;
										if($row['IsVideoFree']==1){
											$sectionArray[$i]['Videos'][] = array_splice($row, 7,14);
										}else{
											$sectionArray[$i]['Videos'][] = array_splice($row, 7);
										}
                                        $i ++;
                                    }
                                }
                                
                                // Shuffling The Video Items
                                $bind = array(
                                    ':AppSettingPlatform' => $Platform
                                );
                                $AppSettings = $db->select("appsettings", "AppSettingPlatform=:AppSettingPlatform", $bind, 'AppSettingIsFeaturedPageRandom,AppSettingSectionsToRandomize');
                                
                                if ($AppSettings) {
                                    if ($AppSettings[0]['AppSettingIsFeaturedPageRandom']) {
                                        if ($AppSettings[0]['AppSettingSectionsToRandomize']) {
                                            $Sections = explode(",", $AppSettings[0]['AppSettingSectionsToRandomize']);
                                            foreach ($sectionArray as $key => $row) {
                                                if (in_array($sectionArray[$key]['SectionId'], $Sections)) {
                                                    shuffle($sectionArray[$key]['Videos']);
                                                }
                                            }
                                        } else {
                                            foreach ($sectionArray as $key => $row) {
                                                shuffle($sectionArray[$key]['Videos']);
                                            }
                                        }
                                    }
                                }
                                
                                // Creating Tab Array
                                $i = 0;
                                $tabArray = array();
                                foreach ($tabs as $dataRow) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $dataRow['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            if ($count <= $limit) {
                                                $tabArray[$key]['Sections'][$count] = array_splice($dataRow, 1);
                                            }
                                            
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $dataRow['TabId'];
                                        $tabArray[$i]['TabName'] = $dataRow['TabName'];
                                        $tabArray[$i]['Sections'][] = array_splice($dataRow, 1);
                                        $i ++;
                                    }
                                }
                                
                                // Merging Section Array into Tab Array
                                $i = 0;
                                $tabArray = array();
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 4);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['TabURL'] = $row['TabURL'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 4);
                                        $tabArray[$i]['Banners'] = array();
                                        $i ++;
                                    }
                                }
                                
                                // Merging Tab Banners
                                foreach ($tabbanners as $row) {
                                    // print_r($row);
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Banners']);
                                            $tabArray[$key]['Banners'][$count] = array_splice($row, 2);
                                        }
                                    }
                                }
                                
                                return General::getResponse($response->write(SuccessObject::getSectionSuccessObject($tabArray, Message::getMessage('M_DATA'), null, null, AppSettings::localGetAdURL('V1', 'en', 'android', '9', 'All', '15'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('W_NO_CONTENT'))));
                            }
                            break;
                        case 'ios':
                        case 'IOS':
                            $sql = <<<STR
							SELECT * FROM (
									SELECT tab.TabId,
									tab.TabName,
									tab.TabPosterPath,
									section.SectionId,
									section.SectionName,
									sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									channels.ChannelName AS VideoName,
									channels.ChannelDescription AS VideoDescription,
									IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
									IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
									IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
									channels.ChannelCategory AS VideoCategoryId,
									channelcategories.ChannelCategoryDisplayTitle AS VideoCategoryName,
									packages.PackageId AS VideoPackageId,
									channels.ChannelTotalViews AS VideoTotalViews,
									channels.ChannelRating AS VideoRating,
									channels.ChannelAddedDate AS VideoAddedDate,
									NULL AS VideoDuration,
									IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree

									FROM sectiondetail

									INNER JOIN section ON section.SectionId = sectiondetail.SectionId
		         	           			AND section.IsOnline='1'

		          	          		INNER JOIN tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN channels ON channels.ChannelId = sectiondetail.ContentId
		                   	 			AND channels.ChannelIsOnline=1

									INNER JOIN channelcategories ON channelcategories.ChannelCategoryId = channels.ChannelCategory

									LEFT JOIN packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

									LEFT JOIN packages ON packages.PackageId = packagechannels.packageId

									WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

							UNION ALL

									SELECT tab.TabId,
									tab.TabName,
									tab.TabPosterPath,
									section.SectionId,
									section.SectionName,
									sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									videoondemand.VideoOnDemandTitle AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
									IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
									IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
									videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
									videoondemandcategories.VideoOnDemandCategoryname AS VideoCategoryName,
									0 AS VideoPackageId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree

									FROM sectiondetail

									INNER JOIN section ON section.SectionId = sectiondetail.SectionId
			                    		AND section.IsOnline='1'

			                    	INNER JOIN tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
			                    		AND videoondemand.VideoOnDemandIsOnline=1

									INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId

									WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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

							) channelvods
							ORDER BY channelvods.TabId, channelvods.SectionId
STR;
                            // echo $sql;
                            $bind = array(
                                ':ImagesDomainName' => Config::$imagesDomainName,
                                ':CountryCode' => $CountryCode,
                                ':CountryCodePattern' => "%$CountryCode%"
                            );
                            $results = $db->run($sql, $bind);
                            
                            $sql = <<<STR
							SELECT tab.TabId,tab.TabName,section.SectionName

							FROM winettv.tab

							INNER JOIN winettv.section ON section.SectionTabId = tab.TabId
		                    	AND section.IsOnline='1'

							WHERE tab.IsOnline='1';
STR;
                            $tabs = $db->run($sql);
                            
                            if ($results) {
                                // Formatting the Data
                                Format::formatResponseData($results);
                                
                                // Creating Section Array with Details
                                $i = 0;
                                $sectionArray = array();
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
                                            $count = count($sectionArray[$key]['Videos']);
                                            $sectionArray[$key]['Videos'][$count] = array_splice($row, 5);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['Videos'][] = array_splice($row, 5);
                                        $i ++;
                                    }
                                }
                                
                                // Shuffling The Video Items
                                $bind = array(
                                    ':AppSettingPlatform' => $Platform
                                );
                                $AppSettings = $db->select("appsettings", "AppSettingPlatform=:AppSettingPlatform", $bind, 'AppSettingIsFeaturedPageRandom,AppSettingSectionsToRandomize');
                                
                                if ($AppSettings) {
                                    if ($AppSettings[0]['AppSettingIsFeaturedPageRandom']) {
                                        if ($AppSettings[0]['AppSettingSectionsToRandomize']) {
                                            $Sections = explode(",", $AppSettings[0]['AppSettingSectionsToRandomize']);
                                            foreach ($sectionArray as $key => $row) {
                                                if (in_array($sectionArray[$key]['SectionId'], $Sections)) {
                                                    shuffle($sectionArray[$key]['Videos']);
                                                }
                                            }
                                        } else {
                                            foreach ($sectionArray as $key => $row) {
                                                shuffle($sectionArray[$key]['Videos']);
                                            }
                                        }
                                    }
                                }
                                
                                // Creating Tab Array
                                $i = 0;
                                $tabArray = array();
                                foreach ($tabs as $dataRow) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $dataRow['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($dataRow, 1);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $dataRow['TabId'];
                                        $tabArray[$i]['TabName'] = $dataRow['TabName'];
                                        $tabArray[$i]['Sections'][] = array_splice($dataRow, 1);
                                        $i ++;
                                    }
                                }
                                
                                // Merging Section Array into Tab Array
                                $i = 0;
                                $tabArray = array();
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 3);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 3);
                                        $i ++;
                                    }
                                }
                                return General::getResponse($response->write(SuccessObject::getSectionSuccessObject($tabArray, Message::getMessage('M_DATA'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('W_NO_CONTENT'))));
                            }
                            break;
                        case 'Web':
                        case 'web':
                            $sql = <<<STR
							SELECT * FROM (
									SELECT tab.TabId,
									tab.TabName,
									tab.TabPosterPath,
									tab.TabUrl,
									tab.TabDescription,
									tab.TabVideoCategoryId,
									tab.TabCarouselTotalColumns,
									section.SectionId,
									section.SectionName,
									sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									channels.ChannelName AS VideoName,
									channels.ChannelDescription AS VideoDescription,
									IF(channels.ChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelThumbnailPath ),channels.ChannelThumbnailPath) AS VideoImageThumbnail,
									IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
									IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
									IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
									channels.ChannelCategory AS VideoCategory,
									packages.PackageId AS VideoPackageId,
									channels.ChannelTotalViews AS VideoTotalViews,
									channels.ChannelRating AS VideoRating,
									channels.ChannelAddedDate AS VideoAddedDate,
									NULL AS VideoDuration,
									IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree

									FROM winettv.sectiondetail

									INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
         	           					AND section.IsOnline='1'

		          	          		INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN winettv.channels ON channels.ChannelId = sectiondetail.ContentId
		                   	 			AND channels.ChannelIsOnline=1

									LEFT JOIN winettv.packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

									LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId

									WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

							UNION ALL

									SELECT tab.TabId,
									tab.TabName,
									tab.TabPosterPath,
									tab.TabUrl,
									tab.TabDescription,
									tab.TabVideoCategoryId,
									tab.TabCarouselTotalColumns,
									section.SectionId,
									section.SectionName,
									sectiondetail.ContentId AS VideoEntityId,
									sectiondetail.IsChannel AS IsVideoChannel,
									videoondemand.VideoOnDemandTitle AS VideoName,
									videoondemand.VideoOnDemandDescription AS VideoDescription,
									IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
									IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
									IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
									IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
									videoondemand.VideoOnDemandCategoryId AS VideoCategory,
									0 AS VideoPackageId,
									videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
									NULL AS VideoRating,
									videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
									videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
									videoondemand.VideoOnDemandIsFree AS IsVideoFree

									FROM winettv.sectiondetail

									INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
			                    		AND section.IsOnline='1'

			                    	INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
										AND tab.IsOnline='1'

									INNER JOIN winettv.videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
			                    		AND videoondemand.VideoOnDemandIsOnline=1

									WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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

							) channelvods
							ORDER BY channelvods.TabId, channelvods.SectionId
STR;
                            // echo $sql;
                            $bind = array(
                                ':ImagesDomainName' => Config::$imagesDomainName,
                                ':CountryCode' => $CountryCode,
                                ':CountryCodePattern' => "%$CountryCode%"
                            );
                            $results = $db->run($sql, $bind);
                            
                            $sql = <<<STR
							SELECT tab.TabId,tab.TabName,section.SectionName
							FROM winettv.tab
		                    INNER JOIN winettv.section ON section.SectionTabId = tab.TabId
		                    AND section.IsOnline='1'
							WHERE tab.IsOnline='1';
STR;
                            $tabs = $db->run($sql);
                            
                            if ($results) {
                                // Formatting the Data
                                Format::formatResponseData($results);
                                
                                // Creating Section Array with Details
                                $i = 0;
                                $sectionArray = array();
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
                                            $count = count($sectionArray[$key]['Videos']);
                                            $sectionArray[$key]['Videos'][$count] = array_splice($row, 9);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['TabUrl'] = $row['TabUrl'];
                                        $sectionArray[$i]['TabDescription'] = $row['TabDescription'];
                                        $sectionArray[$i]['TabVideoCategoryId'] = $row['TabVideoCategoryId'];
                                        $sectionArray[$i]['TabCarouselTotalColumns'] = $row['TabCarouselTotalColumns'];
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['Videos'][] = array_splice($row, 9);
                                        $i ++;
                                    }
                                }
                                
                                // Shuffling The Video Items
                                $bind = array(
                                    ':AppSettingPlatform' => $Platform
                                );
                                $AppSettings = $db->select("appsettings", "AppSettingPlatform=:AppSettingPlatform", $bind, 'AppSettingIsFeaturedPageRandom,AppSettingSectionsToRandomize');
                                
                                if ($AppSettings) {
                                    if ($AppSettings[0]['AppSettingIsFeaturedPageRandom']) {
                                        if ($AppSettings[0]['AppSettingSectionsToRandomize']) {
                                            $Sections = explode(",", $AppSettings[0]['AppSettingSectionsToRandomize']);
                                            foreach ($sectionArray as $key => $row) {
                                                if (in_array($sectionArray[$key]['SectionId'], $Sections)) {
                                                    shuffle($sectionArray[$key]['Videos']);
                                                }
                                            }
                                        } else {
                                            foreach ($sectionArray as $key => $row) {
                                                shuffle($sectionArray[$key]['Videos']);
                                            }
                                        }
                                    }
                                }
                                
                                // Creating Tab Array
                                $i = 0;
                                $tabArray = array();
                                foreach ($tabs as $dataRow) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $dataRow['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($dataRow, 1);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $dataRow['TabId'];
                                        $tabArray[$i]['TabName'] = $dataRow['TabName'];
                                        $tabArray[$i]['Sections'][] = array_splice($dataRow, 1);
                                        $i ++;
                                    }
                                }
                                
                                // Merging Section Array into Tab Array
                                $i = 0;
                                $tabArray = array();
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 7);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['TabUrl'] = $row['TabUrl'];
                                        $tabArray[$i]['TabDescription'] = $row['TabDescription'];
                                        $tabArray[$i]['TabVideoCategoryId'] = $row['TabVideoCategoryId'];
                                        $tabArray[$i]['TabCarouselTotalColumns'] = $row['TabCarouselTotalColumns'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 7);
                                        $i ++;
                                    }
                                }
                                return General::getResponse($response->write(SuccessObject::getSectionSuccessObject($tabArray, Message::getMessage('M_DATA'))));
                            } else {
                                return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('W_NO_CONTENT'))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'll':
                case 'll': // Local/International Filter Disabled
                    $sql = <<<STR
					SELECT * FROM (
							SELECT tab.TabId,
							tab.TabName,
							tab.TabPosterPath,
							section.SectionId,
							section.SectionName,
							sectiondetail.ContentId AS VideoEntityId,
							sectiondetail.IsChannel AS IsVideoChannel,
							channels.ChannelName AS VideoName,
							channels.ChannelDescription AS VideoDescription,
							IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
							IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
							IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
							channels.ChannelCategory AS VideoCategory,
							packages.PackageId AS VideoPackageId,
							channels.ChannelTotalViews AS VideoTotalViews,
							channels.ChannelRating AS VideoRating,
							channels.ChannelAddedDate AS VideoAddedDate,
							NULL AS VideoDuration,
							IF(packages.PackageOneMonthPrice=0,true,false) AS IsVideoFree

							FROM winettv.sectiondetail

							INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
         	           			AND section.IsOnline='1'

          	          		INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
								AND tab.IsOnline='1'

							INNER JOIN winettv.channels ON channels.ChannelId = sectiondetail.ContentId
                   	 			AND channels.ChannelIsOnline=1

							LEFT JOIN winettv.packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

							LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId

							WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
								AND packages.PackageId IN (6,7,8,10)

                    		GROUP BY VideoEntityId

					UNION ALL

							SELECT tab.TabId,
							tab.TabName,
							tab.TabPosterPath,
							section.SectionId,
							section.SectionName,
							sectiondetail.ContentId AS VideoEntityId,
							sectiondetail.IsChannel AS IsVideoChannel,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
							IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
							IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
							videoondemand.VideoOnDemandCategoryId AS VideoCategory,
							0 AS VideoPackageId,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							true AS IsVideoFree

							FROM winettv.sectiondetail

							INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
	                    		AND section.IsOnline='1'

	                    	INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
								AND tab.IsOnline='1'

							INNER JOIN winettv.videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
	                    		AND videoondemand.VideoOnDemandIsOnline=1

							WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL

	                    	GROUP BY VideoEntityId

					) channelvods
					ORDER BY channelvods.TabId, channelvods.SectionId
STR;
                    // echo $sql;
                    $bind = array(
                        ':ImagesDomainName' => Config::$imagesDomainName
                    );
                    $results = $db->run($sql, $bind);
                    
                    $sql = <<<STR
					SELECT tab.TabId,tab.TabName,section.SectionName
					FROM winettv.tab
                    INNER JOIN winettv.section ON section.SectionTabId = tab.TabId
                    AND section.IsOnline='1'
					WHERE tab.IsOnline='1';
STR;
                    $tabs = $db->run($sql);
                    
                    if ($results) {
                        // Formatting the Data
                        Format::formatResponseData($results);
                        
                        // Creating Section Array with Details
                        $i = 0;
                        $sectionArray = array();
                        $limit = rand(12, 15);
                        foreach ($results as $row) {
                            $flag = true;
                            foreach ($sectionArray as $key => $assrow) {
                                if ($assrow['SectionId'] === $row['SectionId']) {
                                    $count = count($sectionArray[$key]['Videos']);
                                    if ($count <= $limit) {
                                        $sectionArray[$key]['Videos'][$count] = array_splice($row, 5);
                                    }
                                    
                                    $flag = false;
                                }
                            }
                            if ($flag) {
                                $sectionArray[$i]['TabId'] = $row['TabId'];
                                $sectionArray[$i]['TabName'] = $row['TabName'];
                                $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                $sectionArray[$i]['Videos'][] = array_splice($row, 5);
                                $i ++;
                            }
                        }
                        
                        // Shuffling The Video Items
                        foreach ($sectionArray as $key => $row) {
                            shuffle($sectionArray[$key]['Videos']);
                        }
                        
                        // Creating Tab Array
                        $i = 0;
                        $tabArray = array();
                        foreach ($tabs as $dataRow) {
                            $flag = true;
                            foreach ($tabArray as $key => $assrow) {
                                if ($assrow['TabId'] === $dataRow['TabId']) {
                                    $count = count($tabArray[$key]['Sections']);
                                    if ($count <= $limit) {
                                        $tabArray[$key]['Sections'][$count] = array_splice($dataRow, 1);
                                    }
                                    
                                    $flag = false;
                                }
                            }
                            if ($flag) {
                                $tabArray[$i]['TabId'] = $dataRow['TabId'];
                                $tabArray[$i]['TabName'] = $dataRow['TabName'];
                                $tabArray[$i]['Sections'][] = array_splice($dataRow, 1);
                                $i ++;
                            }
                        }
                        
                        // Merging Section Array into Tab Array
                        $i = 0;
                        $tabArray = array();
                        foreach ($sectionArray as $row) {
                            $flag = true;
                            foreach ($tabArray as $key => $assrow) {
                                if ($assrow['TabId'] === $row['TabId']) {
                                    $count = count($tabArray[$key]['Sections']);
                                    $tabArray[$key]['Sections'][$count] = array_splice($row, 3);
                                    $flag = false;
                                }
                            }
                            if ($flag) {
                                $tabArray[$i]['TabId'] = $row['TabId'];
                                $tabArray[$i]['TabName'] = $row['TabName'];
                                $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                $tabArray[$i]['Sections'][] = array_splice($row, 3);
                                $i ++;
                            }
                        }
                        return General::getResponse($response->write(SuccessObject::getSectionSuccessObject($tabArray, Message::getMessage('M_DATA'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('W_NO_CONTENT'))));
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
    }
	
	
	//-------------------------get OTP Banners--------------------------------------------
    private static function getOtpBanners($Version, $Language, $Platform,$CountryCode)
    {       	   
        
        //$Version = $request->getAttribute('Version');
        //$Language = $request->getAttribute('Language');
       
        //$Platform = $request->getAttribute('Platform');
        $results = null;
        try {
            $db = parent::getDataBase();
            switch ($Version)
            {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                   
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                    $sql = <<<STR
    		        SELECT OtpBannerId AS OtpBannerId,
    				OtpBannerPath AS OtpBannerPath,
    				OtpBannerIsOnline AS OtpBannerIsOnline,
    				IsAllowedInternationally AS IsAllowedInternationally,
    				OptBannerIsVideo AS OptBannerIsVideo,
                                OtpBannerURL AS OtpBannerURL
                                FROM otpbanners
                            WHERE otpbanners.OtpBannerIsOnline='0'
    			        AND CASE
    			    WHEN :CountryCode != 'PK'
    			    THEN
    			    IsAllowedInternationally = '1'
    			ELSE 1 END;
STR;
                          

                                
                                $bind = array(
                                    ':CountryCode' => $CountryCode
                                );
                                $results = $db->run($sql, $bind);
                                  
								Format::formatResponseData($results);
                                
                                return $results[0];
                                
                                
                 }
                 
                 break;
                case 'v3':
                case 'V3':
                    return null;
                    break;
                default:
                    return null;
                    break;
            }
        } catch (PDOException $e) {
            
        }finally {
            $results = null;
            $db = null;
        }
        
        
    }
	
	
	
	
	
	public static function getHomePageDetail2(Request $request, Response $response)
    {		
		$Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $results = null;        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);                    
                    switch ($Platform) {
                        case 'ANDROID': 
                        case 'android':
                               $sql = <<<STR
                            SELECT * FROM (
                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
								section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                true AS IsSectionMore,
                                null AS SectionMoreType,
                                null AS SectionMoreEntityId,
								IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,								
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                videoondemandcategories.VideoOnDemandCategoryname AS CategoryName,
								IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb) AS CategorythumbImage,
                                IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewCategoryImage,
								IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS CategoryMobileSmallImage,
								IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS CategoryMobileLargeImage,
								videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,     
								IF(packages.PackageIsFree=1,true,false) AS IsCategoryFree,							
                                1 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                channels.ChannelName AS VideoName,
                                channels.ChannelDescription AS VideoDescription,
                                IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
                                IF( channels.NewChannelThumbnailPath NOT LIKE 'http://%', CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ), channels.NewChannelThumbnailPath ) AS NewChannelThumbnailPath,
                                channels.ChannelCategory AS VideoCategoryId,
                                packages.PackageId AS VideoPackageId,
                                channels.ChannelTotalViews AS VideoTotalViews,
                                channels.ChannelRating AS VideoRating,
                                channels.ChannelAddedDate AS VideoAddedDate,
                                NULL AS VideoDuration,
                                IF(packages.PackageIsFree=1,true,false) AS IsVideoFree

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.TabId!=20 AND tab.sorttabs IS NOT NULL
                                
                                LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
                                 
								LEFT JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryid = sectiondetail.CategoryId
                                AND videoondemandsubcategories.SubCategoryIsOnline=1 
								
                                INNER JOIN channels ON channels.ChannelId = sectiondetail.ContentId
                                AND channels.ChannelIsOnline=1

                                LEFT JOIN packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

                                LEFT JOIN packages ON packages.PackageId = packagechannels.packageId

                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

                                UNION ALL

                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
								section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                true AS IsSectionMore,
                                null AS SectionMoreType,
                                null AS SectionMoreEntityId,                                
                                IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,								
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                IF(section.SectionId !=44, videoondemandcategories.VideoOnDemandCategoryname, videoondemand.VideoOnDemandTitle) AS CategoryName,				
                IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS CategorythumbImage,
                IF(section.SectionId !=44, IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewCategoryImage,
                IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall),IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS CategoryMobileSmallImage,
                IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge),IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS CategoryMobileLargeImage,
                videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,
								videoondemand.VideoOnDemandIsFree AS IsCategoryFree,
                                2 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                videoondemand.VideoOnDemandTitle AS VideoName,
                                videoondemand.VideoOnDemandDescription AS VideoDescription,
                                IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
                                IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
                                videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
                                0 AS VideoPackageId,
                                videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
                                NULL AS VideoRating,
                                videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
                                videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
                                videoondemand.VideoOnDemandIsFree AS IsVideoFree

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.TabId!=20 AND tab.sorttabs IS NOT NULL
                                    
                                 LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1   
                                    
                                INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
                                AND videoondemand.VideoOnDemandIsOnline=1
                                    
                                LEFT JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
                                AND videoondemandsubcategories.SubCategoryIsOnline=1 

								LEFT JOIN categoriespackages ON sectiondetail.CategoryId=categoriespackages.categoryId
            
								LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
								
								
                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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
                                ) channelvods
                                ORDER BY channelvods.sorttabs,channelvods.SequenceNo DESC,VideoTotalViews DESC
STR;
                                // echo $sql;
                                $bind = array(
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode,
                                    ':CountryCodePattern' => "%$CountryCode%"
                                );
                                $results = $db->run($sql, $bind);
                                
                                $sql = <<<STR
                                        SELECT BannerTabId AS TabId,
                                        BannerId,
                                        BannerPath AS TabPosterPath,
                                        BannerIsVideo AS IsPosterVideo,
                                        BannerVideoIsChannel AS IsVideoChannel,
                                        BannerVideoEntityId AS VideoEntityId,
                                        BannerURL AS TabURL

                                        FROM tabbanners
                                        WHERE tabbanners.BannerIsOnline='1'
                                        AND CASE
                                        WHEN :CountryCode != 'PK'
                                        THEN
                                        BannerIsAllowedInternationally = '1'
                                        ELSE 1 END;
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode
                                );
                                $tabbanners = $db->run($sql, $bind);                                
                                // print_r($tabbanners);                                
                                // Formatting the Data
                                Format::formatResponseData($results);
                                Format::formatResponseData($tabbanners);					
                                // Creating Section Array with Details
                               $i = 0;
                                $sectionArray = array();
                                $limit = rand(3, 70);
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
                                            $count1 = count($sectionArray[$key]['Categories']);
                                            $count = count($sectionArray[$key]['Videos']);
                                            if ($count <= $limit) {
                                                //----------------condition for checking categories-------------------------------//
                                                if($row['CategoryId']!=null){
                                                    $sectionArray[$key]['Categories'][$count1] = array_splice($row, 13,8);  //if categories then display categories
                                                    $sectionArray[$key]['Videos']=array();  //if categories then videos null
                                                }else{
                                                    $sectionArray[$key]['Categories']=array();//if categories null then display videos 
                                                    $sectionArray[$key]['Videos'][$count] = array_splice($row, 22); //if categories null then display videos 
                                                }
                                                
                                            }
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {                                        
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['TabURL'] = $row['TabURL'];										
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['IsSectionMore'] = $row['IsSectionMore'];
                                        $sectionArray[$i]['SectionMoreType'] = $row['SectionMoreType'];
                                        $sectionArray[$i]['SectionMoreEntityId'] = $row['SectionMoreEntityId'];
                                        $sectionArray[$i]['IsCategories'] = $row['IsCategories'];                                        
                                        if($row['CategoryId']){
                                          $sectionArray[$i]['Categories'][] = array_splice($row, 13,8);   //assign catgories
                                          $sectionArray[$i]['Videos']=array();  //assign video null array
                                        }else{
                                            $sectionArray[$i]['Categories']=array();  //assign catgories null
                                            $sectionArray[$i]['Videos'][] = array_splice($row, 22);  //assign videos
                                        }
                                        $i ++;
                                    }
                                }                  
                                
                                $tabArray = array();
                                
                                $i = 0;
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 4);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['TabURL'] = $row['TabURL'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 4);
                                        $tabArray[$i]['Banners'] = array();
                                        $i ++;
                                    }
                                }
                                
                                // Merging Tab Banners
                                foreach ($tabbanners as $row) {
                                    // print_r($row);
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Banners']);
                                            $tabArray[$key]['Banners'][$count] = array_splice($row, 2);
                                        }
                                    }
                                }
                                return General::getResponse($response->write(
                                        SuccessObject::getBucketSectionsSuccesssObject(
                                        $tabArray, Message::getMessage('M_DATA'), null, null,
                                        AppSettings::localGetAdURL('V1', 'en', 'android', '9', 'All','15')                                      
                                        ,AppSettings::getOtpBanners('V1', 'en', 'android',$CountryCode),
                                         AppSettings::getAllPackages('V1', 'en', 'android'),
										 AppSettings::getAndroidBucketSatus('V1', 'en', 'ios'))
                                        )
                                        );                           
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
	}
	
	
	
	
	public static function getHomePageDetailWithPackages(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $results = null;
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);                     
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'ANDROID': 
                        case 'android':                            
                              $sql = <<<STR
                            SELECT * FROM (
                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
								section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                true AS IsSectionMore,
                                null AS SectionMoreType,
                                null AS SectionMoreEntityId,								
								IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,
								false AS IsSeason,
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                videoondemandcategories.VideoOnDemandCategoryname AS CategoryName,
								IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb) AS CategorythumbImage,
                                IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewCategoryImage,
								IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS CategoryMobileSmallImage,
								IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS CategoryMobileLargeImage,
								videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,     
								IF(packages.PackageIsFree=1,true,false) AS IsCategoryFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice,
								sectiondetail.IsChannel AS IsVideoChannel,										
                                1 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                channels.ChannelName AS VideoName,
                                channels.ChannelDescription AS VideoDescription,
                                IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
                                IF( channels.NewChannelThumbnailPath NOT LIKE 'http://%', CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ), channels.NewChannelThumbnailPath ) AS NewChannelThumbnailPath,
                                channels.ChannelCategory AS VideoCategoryId,
                                packages.PackageId AS VideoPackageId,
                                channels.ChannelTotalViews AS VideoTotalViews,
                                channels.ChannelRating AS VideoRating,
                                channels.ChannelAddedDate AS VideoAddedDate,
                                NULL AS VideoDuration,
						        IF(packages.PackageIsFree=1,true,false) AS IsVideoFree

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.sorttabs IS NOT NULL
                                
                                LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1                              
								
								
                                INNER JOIN channels ON channels.ChannelId = sectiondetail.ContentId
                                AND channels.ChannelIsOnline=1

                                LEFT JOIN packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

                                LEFT JOIN packages ON packages.PackageId = packagechannels.packageId

                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

                                UNION ALL

                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
								section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                true AS IsSectionMore,
                                null AS SectionMoreType,
                                null AS SectionMoreEntityId,                                
                                IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,
								IF(videoondemand.SubCategoryId!='NULL',true,false) AS IsSeason, 
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                IF(section.SectionId !=44, videoondemandcategories.VideoOnDemandCategoryname, videoondemand.VideoOnDemandTitle) AS CategoryName,				
								IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS CategorythumbImage,
								IF(section.SectionId !=44, IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewCategoryImage,
								IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall),IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS CategoryMobileSmallImage,
								IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge),IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS CategoryMobileLargeImage,
								videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,
								videoondemand.VideoOnDemandIsFree AS IsCategoryFree,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
								IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'10',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice,
								false AS IsVideoChannel,
                                2 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                videoondemand.VideoOnDemandTitle AS VideoName,
                                videoondemand.VideoOnDemandDescription AS VideoDescription,
								IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
								IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
								videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
                                0 AS VideoPackageId,
                                videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
                                NULL AS VideoRating,
                                videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
                                videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
                                videoondemand.VideoOnDemandIsFree AS IsVideoFree
								

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.sorttabs IS NOT NULL
                                    
                                LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1   
                                    
                                INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
                                AND videoondemand.VideoOnDemandIsOnline=1                                    
                               
								LEFT JOIN categoriespackages ON sectiondetail.CategoryId=categoriespackages.categoryId
            
								LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
								

                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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
                                ) channelvods
                                ORDER BY channelvods.sorttabs ,channelvods.SequenceNo DESC,RAND(),VideoTotalViews DESC
STR;
                                // echo $sql;
                                $bind = array(
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode,
                                    ':CountryCodePattern' => "%$CountryCode%"
                                );
                                $results = $db->run($sql, $bind);
                                
                                $sql = <<<STR
                                        SELECT BannerTabId AS TabId,
                                        BannerId,
                                        BannerPath AS TabPosterPath,
                                        BannerIsVideo AS IsPosterVideo,
                                        BannerVideoIsChannel AS IsVideoChannel,
                                        BannerVideoEntityId AS VideoEntityId,
                                        BannerURL AS TabURL

                                        FROM tabbanners
                                        WHERE tabbanners.BannerIsOnline='1'
                                        AND CASE
                                        WHEN :CountryCode != 'PK'
                                        THEN
                                        BannerIsAllowedInternationally = '1'
                                        ELSE 1 END;
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode
                                );
                                $tabbanners = $db->run($sql, $bind);                                
                                // print_r($tabbanners);                                
                                // Formatting the Data
                                Format::formatResponseData($results);
                                Format::formatResponseData($tabbanners);					
                                // Creating Section Array with Details
                               $i = 0;
                                $sectionArray = array();
                                $limit = rand(3, 70);
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
											if($row['IsVideoFree']==true)
											{
												$count1 = count($sectionArray[$key]['Categories']);
												$count = count($sectionArray[$key]['Videos']);
												if ($count <= $limit) {
													//----------------condition for checking categories-------------------------------//
													if($row['CategoryId']!=null){
														if($row['IsCategoryFree']==true){
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 14,8);  //if categories then display categories
														}else{
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 14,12);  //if categories then display categories
														}
														$sectionArray[$key]['Videos']=array();  //if categories then videos null
													}else{
														$sectionArray[$key]['Categories']=array();//if categories null then display videos 
														$sectionArray[$key]['Videos'][$count] = array_splice($row, 23); //if categories null then display videos 
													}
													
												}
											}else{
												$count1 = count($sectionArray[$key]['Categories']);
												$count = count($sectionArray[$key]['Videos']);
												if ($count <= $limit) {
													//----------------condition for checking categories-------------------------------//
													if($row['CategoryId']!=null){
														if($row['IsCategoryFree']==true){
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 14,8);  //if categories then display categories
														}else{
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 14,12);  //if categories then display categories
														}
														$sectionArray[$key]['Videos']=array();  //if categories then videos null
													}else{
														$sectionArray[$key]['Categories']=array();//if categories null then display videos 
														$sectionArray[$key]['Videos'][$count] = array_splice($row, 22); //if categories null then display videos 
													}
													
												}
											}
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {                                        
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['TabURL'] = $row['TabURL'];										
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['IsSectionMore'] = $row['IsSectionMore'];
                                        $sectionArray[$i]['SectionMoreType'] = $row['SectionMoreType'];
                                        $sectionArray[$i]['SectionMoreEntityId'] = $row['SectionMoreEntityId'];
                                        $sectionArray[$i]['IsCategories'] = $row['IsCategories'];                                      
										if($row['IsVideoFree']==true)
										{
											if($row['CategoryId']){
												if($row['IsCategoryFree']==true){
													$sectionArray[$i]['Categories'][] = array_splice($row, 14,8);   //assign catgories
												}else{
													$sectionArray[$i]['Categories'][] = array_splice($row, 14,12);   //assign catgories
												}
												$sectionArray[$i]['Videos']=array();  //assign video null array
											}else{											
												$sectionArray[$i]['Categories']=array();  //assign catgories null
												$sectionArray[$i]['Videos'][] = array_splice($row, 23);  //assign videos
											}
										}else{
											if($row['CategoryId']){	
												if($row['IsCategoryFree']==true){
													$sectionArray[$i]['Categories'][] = array_splice($row, 14,8);   //assign catgories
												}else{
													$sectionArray[$i]['Categories'][] = array_splice($row, 14,12);   //assign catgories
												}
												$sectionArray[$i]['Videos']=array();  //assign video null array
											}else{
												$sectionArray[$i]['Categories']=array();  //assign catgories null
												$sectionArray[$i]['Videos'][] = array_splice($row, 22);  //assign videos
											}
										}
                                        $i ++;
                                    }
                                }                                
                                $tabArray = array();
                                
                                $i = 0;
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 4);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['TabURL'] = $row['TabURL'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 4);
                                        $tabArray[$i]['Banners'] = array();
                                        $i ++;
                                    }
                                }
                                
                                // Merging Tab Banners
                                foreach ($tabbanners as $row) {
                                    // print_r($row);
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Banners']);
                                            $tabArray[$key]['Banners'][$count] = array_splice($row, 2);
                                        }
                                    }
                                }								
                               
                                return General::getResponse($response->write(
                                        SuccessObject::getBucketSectionsSuccesssObject(
                                        $tabArray, Message::getMessage('M_DATA'), null, null,
                                        AppSettings::localGetAdURL('V1', 'en', 'android', '9', 'All','15')                                     
                                        ,AppSettings::getOtpBanners('V1', 'en', 'android',$CountryCode),
                                         AppSettings::getAllPackages('V1', 'en', 'android'),
										 AppSettings::getAndroidBucketSatus('V1', 'en', 'android'))
                                        )
                                        );                           
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
	}
	
	
	
        
		//------------------------------get Season and Vod's by Category Id---------------------------------------//
        public static function getSeasonVodByCategoryId(Request $request, Response $response)
        {	
           $Version = $request->getAttribute ( 'Version' );
		   $Language = $request->getAttribute ( 'Language' );
		   parent::setConfig ( $Language );
		   $Platform = $request->getAttribute ( 'Platform' );
		   $CategoryId = $request->getAttribute ( 'CategoryId' );	                
		   $results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					 
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
               switch ($Platform) {
                 case 'android' :
                case 'ANDROID' :
				
            $sql = <<<STR
            SELECT             
            IF(videoondemand.SubCategoryId!='NULL',true,false) AS IsSeason,             
            videoondemandsubcategories.SubCategoryId AS SeasonId,
            videoondemandsubcategories.SubCategoryname AS SeasonName,
            videoondemandsubcategories.SubCategorythumb AS SeasonthumbImage,
            videoondemandsubcategories.SubCategoryMobileSmall AS SeasonMobileSmall,
            videoondemandsubcategories.SubCategoryMoblieLarge AS SeasonMoblieLarge,
            videoondemandsubcategories.SubNewCategorythumb AS SeasonNewCategorythumb,
            videoondemandsubcategories.SubCategoryDescription AS SeasonDescription,
            videoondemandsubcategories.SubCategoryIsOnline AS SeasonIsOnline,    			
            videoondemand.VideoOnDemandId AS VideoEntityId,
            videoondemand.VideoOnDemandTitle AS VideoName,
            videoondemand.VideoOnDemandDescription AS VideoDescription,
            IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
			IF(videoondemand.erosData=1,IF(IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) IS NULL,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandMobileSmall),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)),IF(IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) IS NULL,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandMobileSmall),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb))) AS NewVideoImageThumbnail,
			IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
			IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
			videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
            NULL AS VideoRating,
			videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
            0 AS VideoPackageId,
            videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
            videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
            videoondemand.VideoOnDemandIsFree AS IsVideoFree,            
            false AS IsVideoChannel,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'10',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

			
			FROM videoondemand
                         
            INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
            AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
            
            INNER JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
            AND videoondemandsubcategories.SubCategoryIsOnline=1 AND videoondemand.VideoOnDemandCategoryId=videoondemandcategories.VideoOnDemandCategoryId
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
        ORDER BY SeasonId DESC
STR;
$sql .= " LIMIT " . Config::$getVODsAndMoviesLimit;
						// echo $sql;
	$bind = Array (
		':CountryCode' => $CountryCode,
                ':ImagesDomainName' => Config::$imagesDomainName,
		':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
		':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId 
		);
		$results = $db->run ( $sql, $bind );
                
                if ($results) {                    
                    
$Sql = <<<STR
                    SELECT 
                        IF(videoondemand.SubCategoryId!='NULL',true,false) AS IsSeason,
                        videoondemandsubcategories.SubCategoryId AS SeasonId,
                        videoondemandsubcategories.SubCategoryname AS SeasonName,
                        videoondemandsubcategories.SubCategorythumb AS SeasonthumbImage,
                        videoondemandsubcategories.SubCategoryMobileSmall AS SeasonMobileSmall,
                        videoondemandsubcategories.SubCategoryMoblieLarge AS SeasonMoblieLarge,
                        videoondemandsubcategories.SubNewCategorythumb AS SeasonNewCategorythumb,
						videoondemandsubcategories.SubCategoryDescription AS SeasonDescription,
                        videoondemandsubcategories.SubCategoryIsOnline AS SeasonIsOnline
                        
                        FROM  videoondemandsubcategories 
        
                        INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemandsubcategories.VideoOnDemandCategoryId
                        AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
        
                        INNER JOIN videoondemand ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
                        AND videoondemand.VideoOnDemandCategoryId=videoondemandsubcategories.VideoOnDemandCategoryId
STR;

                        Format::formatResponseData ($results);
                        $allCategory = $db->run ($Sql);
                        Format::formatResponseData ($allCategory);                        
                        $i = 0;
			$assArray = array ();
			// TODO : Make All Category Dynamic
			$assArray [0] = $allCategory [0];
			$assArray [0] ['Videos'] = [ ];                        
                        foreach ( $results as $key => $row ) {
                         
                        if($row['SeasonId']!=null){   
                            $flag = true;
			foreach ( $assArray as $key => $assrow ) {
			// print_r($row );
			if ($assrow ['SeasonId'] === $row ['SeasonId']) {                 
                            $tempRow = array_splice ( $row, 9 );
                            $assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
                            //$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
                            $flag = false;
			}                        
                        }
			if ($flag) {       	                        
				$assArray [$i] ['SeasonId'] = $row ['SeasonId'];
				$assArray [$i] ['SeasonName'] = $row ['SeasonName'];
				$assArray [$i] ['SeasonthumbImage'] = $row ['SeasonthumbImage'];
				$assArray [$i] ['SeasonMobileSmall'] = $row ['SeasonMobileSmall'];
				$assArray [$i] ['SeasonNewCategorythumb'] = $row ['SeasonNewCategorythumb'];
				$assArray [$i] ['SeasonDescription'] = $row ['SeasonDescription'];
				$assArray [$i] ['SeasonIsOnline'] = $row ['SeasonIsOnline'];                                
				$tempRow = array_splice ( $row, 9 );
				$assArray [$i] ['Videos'] [] = $tempRow;                               
				//$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
				// print_r($assArray[$i]);
				$i ++;
				}
                                }
				}
                            return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideosSuccessObjects ( $assArray, Message::getMessage ( 'M_DATA' ), Config::$getVODsAndMoviesLimit, 15,true, 'Seasons' ) ) ) );
                        
                        }else
                        {
                            $sql = <<<STR
        SELECT            
            videoondemand.VideoOnDemandId AS VideoEntityId,
            videoondemand.VideoOnDemandTitle AS VideoName,
            videoondemand.VideoOnDemandDescription AS VideoDescription,
            IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS VideoImageThumbnail,
			IF(videoondemand.erosData=1,IF(IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) IS NULL,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandMobileSmall),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)),IF(IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) IS NULL,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandMobileSmall),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb))) AS NewVideoImageThumbnail,
			IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
			IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge), IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
			videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
            NULL AS VideoRating,
            videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
            videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
            videoondemand.VideoOnDemandIsFree AS IsVideoFree,            
            false AS IsVideoChannel,
			videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
            0 AS VideoPackageId,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
			IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'10',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

            FROM videoondemand
                         
            LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
            AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
            
            LEFT JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
            AND videoondemandsubcategories.SubCategoryIsOnline=1
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
        ORDER BY VideoEntityId DESC
STR;
$sql .= " LIMIT " . Config::$getVODsAndMoviesLimit;
						// echo $sql;
	$bind = Array (
		':CountryCode' => $CountryCode,
                ':ImagesDomainName' => Config::$imagesDomainName,
		':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
		':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId 
		);
		$results = $db->run ( $sql, $bind );
                Format::formatResponseData ($results);
                        }
                
                //echo '<pre>';print_r($results);die;
                
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
                            
                            return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideosSuccessObjects ( $results, Message::getMessage ( 'M_DATA' ), Config::$getVODsAndMoviesLimit, 15,false, 'Videos' ) ) ) );
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
		
		//-----------------------get section more info with categories---------------------------//
		public static function getCategoriesSectionMoreInfo(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $SectionId = $request->getAttribute('SectionId');
        $IsCategory = $request->getAttribute('IsCategory');
        $OffSet = $request->getAttribute('OffSet');
        $results = null;
        if($IsCategory==='true')
        {
            try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'android' :
                case 'Android' :
				case 'ANDROID' :
$sql = <<<STR

        	SELECT                 
                    section.SectionId,                    
                    videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                    IF(section.SectionId !=44, videoondemandcategories.VideoOnDemandCategoryname, videoondemand.VideoOnDemandTitle) AS CategoryName,
					IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS CategorythumbImage,
					IF(section.SectionId !=44, IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewCategoryImage,
					IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall),IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS CategoryMobileSmallImage,
					IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge),IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS CategoryMobileLargeImage,
                    videoondemand.VideoOnDemandIsFree AS IsCategoryFree,
					videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline    
                    
                    FROM sectiondetail

		INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                    AND section.IsOnline='1'              
                                
                INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                    AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
                                 
		
	        INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
                                AND videoondemand.VideoOnDemandIsOnline=1
                                
                LEFT JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryid = videoondemand.SubCategoryId
                AND videoondemandsubcategories.SubCategoryIsOnline=1 
                    
                WHERE 
                                sectiondetail.IsOnline='1'
                                AND 
                                section.SectionId=:SectionId	
ORDER BY VoDCategoryId DESC
STR;
                            $sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
                          //echo $sql;
                    //exit;
                    $bind = array(
                        ":SectionId" => $SectionId,
                        ':ImagesDomainName' => Config::$imagesDomainName,
                        ':CountryCode' => $CountryCode,
                        ':CountryCodePattern' => "%$CountryCode%"
                    );
                    $results = $db->run($sql, $bind);
                   
                    if ($results) {
                        // Formatting the Data
                       Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getCategoriesVideoSuccessObject($results, Message::getMessage('M_DATA'), Config::$ChannelsANDVODsLimit, count($results))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('W_NO_CONTENT'), Config::$ChannelsANDVODsLimit, count($results))));
                    }
                            
                            break;                        
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    
                    
                    
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(array(
                        'In Process.'
                    ), null, null)));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), null, null)));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
        }else
        {
            try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);
                    // echo $CountryCode;
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'android' :
                case 'Android' :
				case 'ANDROID' :
$sql = <<<STR
	SELECT * FROM (
        	SELECT 
		
		sectiondetail.ContentId AS VideoEntityId,
		sectiondetail.IsChannel AS IsVideoChannel,
		channels.ChannelName AS VideoName,
		channels.ChannelDescription AS VideoDescription,
		IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
        IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
        IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
		channels.ChannelCategory AS VideoCategoryId,
		packages.PackageId AS VideoPackageId,
		channels.ChannelTotalViews AS VideoTotalViews,
		channels.ChannelRating AS VideoRating,
		channels.ChannelAddedDate AS VideoAddedDate,
		NULL AS VideoDuration,
		1 AS VideoType,
		IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
		IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
								IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
								IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

		FROM winettv.sectiondetail

		INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
		AND section.IsOnline='1'
		INNER JOIN winettv.channels ON channels.ChannelId = sectiondetail.ContentId
		AND channels.ChannelIsOnline=1
        	LEFT JOIN winettv.packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

                LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId
        
                WHERE sectiondetail.IsOnline='1'
                    AND sectiondetail.IsChannel = 1
                    AND section.SectionId=:SectionId
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
	UNION ALL
                SELECT sectiondetail.ContentId AS VideoEntityId,
                sectiondetail.IsChannel AS IsVideoChannel,
                videoondemand.VideoOnDemandTitle AS VideoName,
                videoondemand.VideoOnDemandDescription AS VideoDescription,
				IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
				IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
				IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
				videoondemand.VideoOnDemandCategoryId AS VideoCategory,
                0 AS VideoPackageId,
                videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
                NULL AS VideoRating,
                videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
                videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
                videoondemand.VideoOnDemandIsFree AS IsVideoFree,
				2 AS VideoType,
				IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
									IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice

                FROM winettv.sectiondetail
                INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
                        AND section.IsOnline='1'
                INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
                        AND tab.IsOnline='1'
                INNER JOIN winettv.videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
                        AND videoondemand.VideoOnDemandIsOnline=1
                LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
				LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
				LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
                WHERE sectiondetail.IsOnline = 1
                    AND sectiondetail.IsChannel = 0
                    AND section.SectionId=:SectionId
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
STR;
                            $sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
                            break;                        
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    
                    $bind = array(
                        ":SectionId" => $SectionId,
                        ':ImagesDomainName' => Config::$imagesDomainName,
                        ':CountryCode' => $CountryCode,
                        ':CountryCodePattern' => "%$CountryCode%"
                    );
                    $results = $db->run($sql, $bind);
                    
                    if ($results) {
                        // Formatting the Data
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($results, Message::getMessage('M_DATA'), Config::$ChannelsANDVODsLimit, count($results))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('W_NO_CONTENT'), Config::$ChannelsANDVODsLimit, count($results))));
                    }
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    $sql = <<<STR
					SELECT * FROM (
							SELECT sectiondetail.ContentId AS VideoEntityId,
							sectiondetail.IsChannel AS IsVideoChannel,
							channels.ChannelName AS VideoName,
							channels.ChannelDescription AS VideoDescription,
							IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
							IF(channels.ChannelMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileLarge ),channels.ChannelMobileLarge) AS VideoImagePathLarge,
							IF(channels.NewChannelThumbnailPath NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ),channels.NewChannelThumbnailPath) AS NewVideoImageThumbnail,
							channels.ChannelCategory AS VideoCategory,
							packages.PackageId AS VideoPackageId,
							channels.ChannelTotalViews AS VideoTotalViews,
							channels.ChannelRating AS VideoRating,
							channels.ChannelAddedDate AS VideoAddedDate,
							NULL AS VideoDuration,
							1 AS VideoType,
							IF(packages.PackageIsFree=1,true,false) AS IsVideoFree,
							IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
							IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
							IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
							IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice

							FROM winettv.sectiondetail

							INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
                    			AND section.IsOnline='1'

                    		INNER JOIN winettv.channels ON channels.ChannelId = sectiondetail.ContentId
                    			AND channels.ChannelIsOnline=1

							LEFT JOIN winettv.packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

							LEFT JOIN winettv.packages ON packages.PackageId = packagechannels.packageId

							WHERE sectiondetail.IsOnline='1'
								AND sectiondetail.IsChannel = 1
                    			AND section.SectionId=:SectionId
								AND packages.PackageId IN (6,7,8,10)

                    		GROUP BY VideoEntityId

					UNION ALL

							SELECT sectiondetail.ContentId AS VideoEntityId,
							sectiondetail.IsChannel AS IsVideoChannel,
							videoondemand.VideoOnDemandTitle AS VideoName,
							videoondemand.VideoOnDemandDescription AS VideoDescription,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileSmall,IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
							IF(videoondemand.erosData=1,videoondemand.VideoOnDemandMobileLarge,IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS VideoImagePathLarge,
							IF(videoondemand.erosData=1,videoondemand.NewVideoOnDemandThumb,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
							videoondemand.VideoOnDemandCategoryId AS VideoCategory,
							0 AS VideoPackageId,
							videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
							NULL AS VideoRating,
							videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
							videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
							videoondemand.VideoOnDemandIsFree AS IsVideoFree,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
							IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'15',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice


							FROM winettv.sectiondetail

							INNER JOIN winettv.section ON section.SectionId = sectiondetail.SectionId
	                    		AND section.IsOnline='1'

	                    	INNER JOIN winettv.tab ON tab.TabId=section.SectionTabId
								AND tab.IsOnline='1'

							INNER JOIN winettv.videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
	                    		AND videoondemand.VideoOnDemandIsOnline=1
							LEFT JOIN categoriespackages ON videoondemandcategories.VideoOnDemandCategoryId =categoriespackages.categoryId	
						LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
							WHERE sectiondetail.IsOnline = 1
								AND sectiondetail.IsChannel = 0
								AND section.SectionId=:SectionId
								AND videoondemand.VideoOnDemandHDVideo IS NOT NULL
								AND videoondemand.VideoOnDemandMobileSmall IS NOT NULL
								AND videoondemand.VideoOnDemandMobileLarge IS NOT NULL

	                    	GROUP BY VideoEntityId

					) channelvods
					ORDER BY channelvods.VideoTotalViews DESC
STR;
                    $sql .= " LIMIT " . Config::$ChannelsANDVODsLimit . " OFFSET " . $OffSet;
                    
                    $bind = array(
                        ":SectionId" => $SectionId,
                        ':ImagesDomainName' => Config::$imagesDomainName
                    );
                    $results = $db->run($sql, $bind);
                    
                    if ($results) {
                        // Formatting the Data
                        Format::formatResponseData($results);
                        return General::getResponse($response->write(SuccessObject::getVideoSuccessObject($results, Message::getMessage('M_DATA'), Config::$ChannelsANDVODsLimit, count($results))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('W_NO_CONTENT'), Config::$ChannelsANDVODsLimit, count($results))));
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(array(
                        'In Process.'
                    ), null, null)));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'), null, null)));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getVideoErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
        
        }
       
        
        
        
    }		
		
		
        
        //----------------------------get all packages---------------------------------------
    private static function getAllPackages($Version, $Language, $Platform)
    {   
        $packages = array();
        try {
            $db = parent::getDataBase();
            switch ($Version)
            {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                   
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                    $sql = <<<STR
    		        SELECT                                 
                            PackageId AS PackageId,
                            PackageName AS PackageName,
                            IF(PackageIsFree=1,true,false) AS PackageIsFree,
                            PackageProductId AS PackageProduct,
                            PackagePrice AS    PackagePrice,
							PackageDescription AS    PackageDescription,
							packages.packageImage  AS packageImage							
                            FROM packages 
                           WHERE PackageIsFree=0 AND isnewpackage=1
                                ORDER by PackageId ASC;
STR;

                          
		$packages = $db->run($sql);                                  
		Format::formatResponseData($packages);   


                        		
		return $packages;
		
		//print_r($results);
                 }
                    break;
                default:
                    return null;
                    break;
            }
        } catch (PDOException $e) {
            
        }finally {
            $results = null;
            $db = null;
        }
        
        
    }
		
        
    //------------------------------get Bucket For Android-----------------------------------//
public static function getAndroidBucketSatus($Version, $Language, $Platform)
{
    $buckets = array();
        try {
            $db = parent::getDataBase();
            switch ($Version)
            {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                   
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                    $sql = <<<STR
    		                                        
                       SELECT 
                            IF(FIND_IN_SET('100003', OperatorId),true,false) AS ZongBucket,
                            IF(FIND_IN_SET('100001', OperatorId),true,false) AS MobilinkBucket,
                            IF(FIND_IN_SET('100002', OperatorId),true,false) AS TelenorBucket
                            FROM dynamicbucket WHERE BucketStatus=1 AND FIND_IN_SET('Android', Platform);
STR;

                          
		$buckets = $db->run($sql); 
                if(count($buckets)!=1)
                { 				
					
					$buckets1 = array("ZongBucket"=>"offZong","MobilinkBucket"=>"offMobilink","TelenorBucket"=>"offTelenor");
					Format::formatResponseData($buckets1);  
					return $buckets1;					
					
                }else{
					Format::formatResponseData($buckets);
					return $buckets[0];
				}
				
				
                
		
		//print_r($results);
                 }
                    break;
                default:
                    return null;
                    break;
            }
        } catch (PDOException $e) {
            
        }finally {
            $buckets = null;
            $db = null;
        }
    
} 


//------------------------------get Season and Vod's by Category Id---------------------------------------//
        public static function getVodsCategoryIdWithPackages(Request $request, Response $response)
        {	
           $Version = $request->getAttribute ( 'Version' );
		   $Language = $request->getAttribute ( 'Language' );
		   parent::setConfig ( $Language );
		   $Platform = $request->getAttribute ( 'Platform' );
		   $CategoryId = $request->getAttribute ( 'CategoryId' );	                
		   $results = NULL;
		
		try {
			$db = parent::getDataBase ();
			switch ($Version) {
				case 'v1' :
				case 'V1' :
					
					 
					$CountryCode = getCountryCode ( $_SERVER ['REMOTE_ADDR'] );
					// echo $CountryCode;
					// $CountryCode = 'PK';
					
               switch ($Platform) {
                 case 'android' :
                case 'ANDROID' :
				
            $sql = <<<STR
            SELECT             
            IF(videoondemand.SubCategoryId!='NULL',true,false) AS IsSeason,             
            videoondemandsubcategories.SubCategoryId AS SeasonId,
            videoondemandsubcategories.SubCategoryname AS SeasonName,
            videoondemandsubcategories.SubCategorythumb AS SeasonthumbImage,
            videoondemandsubcategories.SubCategoryMobileSmall AS SeasonMobileSmall,
            videoondemandsubcategories.SubCategoryMoblieLarge AS SeasonMoblieLarge,
            videoondemandsubcategories.SubNewCategorythumb AS SeasonNewCategorythumb,
            videoondemandsubcategories.SubCategoryDescription AS SeasonDescription,
            videoondemandsubcategories.SubCategoryIsOnline AS SeasonIsOnline,    			
            videoondemand.VideoOnDemandId AS VideoEntityId,
            videoondemand.VideoOnDemandTitle AS VideoName,
            videoondemand.VideoOnDemandDescription AS VideoDescription,
            IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
            IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
            IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
            IF(IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) IS NULL,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandMobileSmall),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
            videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
            NULL AS VideoRating,
            videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
            videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
            videoondemand.VideoOnDemandIsFree AS IsVideoFree,   
			IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Free') AS PackageName,
            IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
            IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1010') AS PackageProduct,
            IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'0') AS PackagePrice,
            false AS IsVideoChannel
			
			FROM videoondemand
                         
            INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
            AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
            
            INNER JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
            AND videoondemandsubcategories.SubCategoryIsOnline=1 AND videoondemand.VideoOnDemandCategoryId=videoondemandcategories.VideoOnDemandCategoryId
            
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
        ORDER BY SeasonId DESC
STR;
$sql .= " LIMIT " . Config::$getVODsAndMoviesLimit;
						// echo $sql;
	$bind = Array (
		':CountryCode' => $CountryCode,
                ':ImagesDomainName' => Config::$imagesDomainName,
		':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
		':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId 
		);
		$results = $db->run ( $sql, $bind );
                
                if ($results) {                    
                    
$Sql = <<<STR
                    SELECT 
                        IF(videoondemand.SubCategoryId!='NULL',true,false) AS IsSeason,
                        videoondemandsubcategories.SubCategoryId AS SeasonId,
                        videoondemandsubcategories.SubCategoryname AS SeasonName,
                        videoondemandsubcategories.SubCategorythumb AS SeasonthumbImage,
                        videoondemandsubcategories.SubCategoryMobileSmall AS SeasonMobileSmall,
                        videoondemandsubcategories.SubCategoryMoblieLarge AS SeasonMoblieLarge,
                        videoondemandsubcategories.SubNewCategorythumb AS SeasonNewCategorythumb,
						videoondemandsubcategories.SubCategoryDescription AS SeasonDescription,
                        videoondemandsubcategories.SubCategoryIsOnline AS SeasonIsOnline
                        
                        FROM  videoondemandsubcategories 
        
                        INNER JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemandsubcategories.VideoOnDemandCategoryId
                        AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
        
                        INNER JOIN videoondemand ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
                        AND videoondemand.VideoOnDemandCategoryId=videoondemandsubcategories.VideoOnDemandCategoryId
STR;

                        Format::formatResponseData ($results);
                        $allCategory = $db->run ($Sql);
                        Format::formatResponseData ($allCategory);                        
                        $i = 0;
			$assArray = array ();
			// TODO : Make All Category Dynamic
			$assArray [0] = $allCategory [0];
			$assArray [0] ['Videos'] = [ ];                        
                        foreach ( $results as $key => $row ) {
                         
                        if($row['SeasonId']!=null){   
                            $flag = true;
			foreach ( $assArray as $key => $assrow ) {
			// print_r($row );
			if ($assrow ['SeasonId'] === $row ['SeasonId']) {                 
                            $tempRow = array_splice ( $row, 9 );
                            $assArray [$key] ['Videos'] [count ( $assArray [$key] ['Videos'] )] = $tempRow;
                            //$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
                            $flag = false;
			}                        
                        }
			if ($flag) {                               
				$assArray [$i] ['SeasonId'] = $row ['SeasonId'];
				$assArray [$i] ['SeasonName'] = $row ['SeasonName'];
				$assArray [$i] ['SeasonthumbImage'] = $row ['SeasonthumbImage'];
				$assArray [$i] ['SeasonMobileSmall'] = $row ['SeasonMobileSmall'];
				$assArray [$i] ['SeasonNewCategorythumb'] = $row ['SeasonNewCategorythumb'];
				$assArray [$i] ['SeasonDescription'] = $row ['SeasonDescription'];
				$assArray [$i] ['SeasonIsOnline'] = $row ['SeasonIsOnline'];                                
				$tempRow = array_splice ( $row, 9 );
				$assArray [$i] ['Videos'] [] = $tempRow;                               
				//$assArray [0] ['Videos'] [count ( $assArray [0] ['Videos'] )] = $tempRow;
				// print_r($assArray[$i]);
				$i ++;
				}
                                }
				}
                            return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideosSuccessObjects ( $assArray, Message::getMessage ( 'M_DATA' ), Config::$getVODsAndMoviesLimit, 15,true, 'Seasons' ) ) ) );
                        
                        }else
                        {
                            $sql = <<<STR
        SELECT            
            videoondemand.VideoOnDemandId AS VideoEntityId,
            videoondemand.VideoOnDemandTitle AS VideoName,
            videoondemand.VideoOnDemandDescription AS VideoDescription,
            IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb) AS VideoImageThumbnail,
            IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
            IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge) AS VideoImagePathLarge,
            IF(IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) IS NULL,IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandMobileSmall),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoImageThumbnail,
            videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
            NULL AS VideoRating,
            videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
            videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
            videoondemand.VideoOnDemandIsFree AS IsVideoFree,            
            false AS IsVideoChannel,
			IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Free') AS PackageName,
            IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
            IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1010') AS PackageProduct,
            IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'0') AS PackagePrice
            FROM videoondemand
                         
            LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = videoondemand.VideoOnDemandCategoryId
            AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
            
            LEFT JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
            AND videoondemandsubcategories.SubCategoryIsOnline=1
                         
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
        ORDER BY VideoEntityId DESC
STR;
$sql .= " LIMIT " . Config::$getVODsAndMoviesLimit;
						// echo $sql;
	$bind = Array (
		':CountryCode' => $CountryCode,
                ':ImagesDomainName' => Config::$imagesDomainName,
		':CategoryId1' => $CategoryId == '-1' ? '3' : $CategoryId,
		':CategoryId2' => $CategoryId == '-1' ? '8' : $CategoryId 
		);
		$results = $db->run ( $sql, $bind );
                Format::formatResponseData ($results);
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
                            
                            return General::getResponse ( General::getResponse ( $response->write ( SuccessObject::getVideosSuccessObjects ( $results, Message::getMessage ( 'M_DATA' ), Config::$getVODsAndMoviesLimit, 15,false, 'Videos' ) ) ) );
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
	
		

//-----------------------------get user IP Address---------------------------------------------//



public static function getUserIpAddress()
{
	
	$given_ipv4= AppSettings::get_client_ip();	
	if(AppSettings::net_match('119.160.116.219/22',$given_ipv4) || AppSettings::net_match('119.160.64.0/21',$given_ipv4) || AppSettings::net_match('119.160.96.0/21',$given_ipv4)){
	   return true;
	}else{
		return false;
	}; //true 
}	


public static function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}


public static function net_match($network, $ip) {
	
	  $ip_arr = explode('/', $network);
      $network_long = ip2long($ip_arr[0]);

      $x = ip2long($ip_arr[1]);
      $mask =  long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
      $ip_long = ip2long($ip);

      // echo ">".$ip_arr[1]."> ".decbin($mask)."\n";
      return ($ip_long & $mask) == ($network_long & $mask);
}

	
public static function getHomePageDetail3(Request $request, Response $response)
    {		
		$Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $results = null;        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);                    
                    switch ($Platform) {
                        case 'ANDROID': 
                        case 'android':
                               $sql = <<<STR
                            SELECT * FROM (
                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
								section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                true AS IsSectionMore,
                                null AS SectionMoreType,
                                null AS SectionMoreEntityId,
								IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,								
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                videoondemandcategories.VideoOnDemandCategoryname AS CategoryName,
								IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb) AS CategorythumbImage,
                                IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewCategoryImage,
								IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall) AS CategoryMobileSmallImage,
								IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge) AS CategoryMobileLargeImage,
								videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,     
								IF(packages.PackageIsFree=1,true,false) AS IsCategoryFree,							
                                1 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                channels.ChannelName AS VideoName,
                                channels.ChannelDescription AS VideoDescription,
                                IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
                                IF( channels.NewChannelThumbnailPath NOT LIKE 'http://%', CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ), channels.NewChannelThumbnailPath ) AS NewChannelThumbnailPath,
                                channels.ChannelCategory AS VideoCategoryId,
                                packages.PackageId AS VideoPackageId,
                                channels.ChannelTotalViews AS VideoTotalViews,
                                channels.ChannelRating AS VideoRating,
                                channels.ChannelAddedDate AS VideoAddedDate,
                                NULL AS VideoDuration,
                                IF(packages.PackageIsFree=1,true,false) AS IsVideoFree

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.TabId!=20 AND tab.sorttabs IS NOT NULL
                                
                                LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1
                                 
								LEFT JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryid = sectiondetail.CategoryId
                                AND videoondemandsubcategories.SubCategoryIsOnline=1 
								
                                INNER JOIN channels ON channels.ChannelId = sectiondetail.ContentId
                                AND channels.ChannelIsOnline=1

                                LEFT JOIN packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

                                LEFT JOIN packages ON packages.PackageId = packagechannels.packageId

                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

                                UNION ALL

                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
								section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                true AS IsSectionMore,
                                null AS SectionMoreType,
                                null AS SectionMoreEntityId,                                
                                IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,								
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                IF(section.SectionId !=44, videoondemandcategories.VideoOnDemandCategoryname, videoondemand.VideoOnDemandTitle) AS CategoryName,				
                IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS CategorythumbImage,
                IF(section.SectionId !=44, IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewCategoryImage,
                IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMobileSmall ),videoondemandcategories.VideoOnDemandCategoryMobileSmall),IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS CategoryMobileSmallImage,
                IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategoryMoblieLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategoryMoblieLarge ),videoondemandcategories.VideoOnDemandCategoryMoblieLarge),IF(videoondemand.VideoOnDemandMobileLarge NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileLarge ),videoondemand.VideoOnDemandMobileLarge)) AS CategoryMobileLargeImage,
                videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,
								videoondemand.VideoOnDemandIsFree AS IsCategoryFree,
                                2 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                videoondemand.VideoOnDemandTitle AS VideoName,
                                videoondemand.VideoOnDemandDescription AS VideoDescription,
                                IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall) AS VideoImagePath,
                                IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb) AS NewVideoOnDemandThumb,
                                videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
                                0 AS VideoPackageId,
                                videoondemand.VideoOnDemandTotalViews AS VideoTotalViews,
                                NULL AS VideoRating,
                                videoondemand.VideoOnDemandAddedDate AS VideoAddedDate,
                                videoondemand.VideoOnDemandDurationSeconds AS VideoDuration,
                                videoondemand.VideoOnDemandIsFree AS IsVideoFree

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.TabId!=20 AND tab.sorttabs IS NOT NULL
                                    
                                 LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1   
                                    
                                INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
                                AND videoondemand.VideoOnDemandIsOnline=1
                                    
                                LEFT JOIN videoondemandsubcategories ON videoondemandsubcategories.SubCategoryId = videoondemand.SubCategoryId
                                AND videoondemandsubcategories.SubCategoryIsOnline=1 

								LEFT JOIN categoriespackages ON sectiondetail.CategoryId=categoriespackages.categoryId
            
								LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
								
								
                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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
                                ) channelvods
                                ORDER BY channelvods.sorttabs,channelvods.SequenceNo DESC,VideoTotalViews DESC
STR;
                                // echo $sql;
                                $bind = array(
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode,
                                    ':CountryCodePattern' => "%$CountryCode%"
                                );
                                $results = $db->run($sql, $bind);
                                
                                $sql = <<<STR
                                        SELECT BannerTabId AS TabId,
                                        BannerId,
                                        BannerPath AS TabPosterPath,
                                        BannerIsVideo AS IsPosterVideo,
                                        BannerVideoIsChannel AS IsVideoChannel,
                                        BannerVideoEntityId AS VideoEntityId,
                                        BannerURL AS TabURL

                                        FROM tabbanners
                                        WHERE tabbanners.BannerIsOnline='1'
                                        AND CASE
                                        WHEN :CountryCode != 'PK'
                                        THEN
                                        BannerIsAllowedInternationally = '1'
                                        ELSE 1 END;
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode
                                );
                                $tabbanners = $db->run($sql, $bind);                                
                                // print_r($tabbanners);                                
                                // Formatting the Data
                                Format::formatResponseData($results);
                                Format::formatResponseData($tabbanners);					
                                // Creating Section Array with Details
                               $i = 0;
                                $sectionArray = array();
                                $limit = rand(3, 70);
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
                                            $count1 = count($sectionArray[$key]['Categories']);
                                            $count = count($sectionArray[$key]['Videos']);
                                            if ($count <= $limit) {
                                                //----------------condition for checking categories-------------------------------//
                                                if($row['CategoryId']!=null){
                                                    $sectionArray[$key]['Categories'][$count1] = array_splice($row, 13,8);  //if categories then display categories
                                                    $sectionArray[$key]['Videos']=array();  //if categories then videos null
                                                }else{
                                                    $sectionArray[$key]['Categories']=array();//if categories null then display videos 
                                                    $sectionArray[$key]['Videos'][$count] = array_splice($row, 22); //if categories null then display videos 
                                                }
                                                
                                            }
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {                                        
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['TabURL'] = $row['TabURL'];										
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['IsSectionMore'] = $row['IsSectionMore'];
                                        $sectionArray[$i]['SectionMoreType'] = $row['SectionMoreType'];
                                        $sectionArray[$i]['SectionMoreEntityId'] = $row['SectionMoreEntityId'];
                                        $sectionArray[$i]['IsCategories'] = $row['IsCategories'];                                        
                                        if($row['CategoryId']){
                                          $sectionArray[$i]['Categories'][] = array_splice($row, 13,8);   //assign catgories
                                          $sectionArray[$i]['Videos']=array();  //assign video null array
                                        }else{
                                            $sectionArray[$i]['Categories']=array();  //assign catgories null
                                            $sectionArray[$i]['Videos'][] = array_splice($row, 22);  //assign videos
                                        }
                                        $i ++;
                                    }
                                }                  
                                
                                $tabArray = array();
                                
                                $i = 0;
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 4);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['TabURL'] = $row['TabURL'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 4);
                                        $tabArray[$i]['Banners'] = array();
                                        $i ++;
                                    }
                                }
                                
                                // Merging Tab Banners
                                foreach ($tabbanners as $row) {
                                    // print_r($row);
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Banners']);
                                            $tabArray[$key]['Banners'][$count] = array_splice($row, 2);
                                        }
                                    }
                                }
								$rezlt = AppSettings::localGetAdURL3('V1', 'en', 'android', '9', 'All','15');
								echo '<pre>';
								print_r($rezlt);die;
                                return General::getResponse($response->write(
                                        SuccessObject::getBucketSectionsSuccesssObject(
                                        $tabArray, Message::getMessage('M_DATA'), null, null,
                                        AppSettings::localGetAdURL3('V1', 'en', 'android', '9', 'All','15')                                      
                                        ,AppSettings::getOtpBanners('V1', 'en', 'android',$CountryCode),
                                         AppSettings::getAllPackages('V1', 'en', 'android'),
										 AppSettings::getAndroidBucketSatus('V1', 'en', 'ios'))
                                        )
                                        );                           
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
	}

	private static function localGetAdURL3($Version, $Language, $Platform, $AdType, $Gender, $Age)
    {
        $results = null;
        
        try {
            $db = parent::getDataBase();
            
            switch ($Version) {
                case 'v1':
                case 'V1':
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
                            
                             //echo $sql;die;
                            $bind = array(
                                ":Age" => $Age,
                                ":Gender" => $Gender,
                                ":AdType" => $AdType
                            );
                            $results = $db->run($sql, $bind);
                            
                            if ($results) {
                                Format::formatResponseData($results);
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 9) {
                                    if (! $results[0]['IsVast']) {
                                        $results[0]['IsVast'] = true;
                                        $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                    }
                                }
                                
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 1) {
                                    return null;
                                }
                                if (isset($results[0]['AdvertisementId']) && $results[0]['AdvertisementId'] === 17) {
                                    return null;
                                }
                                return $results[0];
                            } else {
                                return null;
                            }
                            break;
                        default:
                            return null;
                            break;
                    }
                    break;
                default:
                    return null;
                    break;
            }
        } catch (PDOException $e) {
            return null;
        } finally {
            $results = null;
            $db = null;
        }
    }


	
	//----------------------------get Optimized home page detail--------------------------------------//	
public static function getHomePageWithBucketPackages(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $results = null;
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1': // Local/International Filter Enabled
                    
                     
                    $CountryCode = getCountryCode($_SERVER['REMOTE_ADDR']);                     
                    // $CountryCode = 'PK';
                    
                    switch ($Platform) {
                        case 'ANDROID': 
                        case 'android':                            
                              $sql = <<<STR
                            SELECT * FROM (
                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
				section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                null AS SectionMoreType,
				IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,
				false AS IsSeason,
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                videoondemandcategories.VideoOnDemandCategoryname AS CategoryName,
				IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb) AS CategorythumbImage,
                                IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb) AS NewCategoryImage,
                                videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,     
                                IF(packages.PackageIsFree=1,true,false) AS IsCategoryFree,
                                IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages') AS PackageName,
                                IF(packages.PackageId NOT IN (6,8,15,16,2),true,false) AS PackageIsFree,
                                IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007') AS PackageProduct,
                                IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15') AS PackagePrice,
                                sectiondetail.IsChannel AS IsVideoChannel,										
                                1 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                channels.ChannelName AS VideoName,
                                channels.ChannelDescription AS VideoDescription,
                                IF(channels.ChannelMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, channels.ChannelMobileSmall ),channels.ChannelMobileSmall) AS VideoImagePath,
                                IF( channels.NewChannelThumbnailPath NOT LIKE 'http://%', CONCAT( :ImagesDomainName, channels.NewChannelThumbnailPath ), channels.NewChannelThumbnailPath ) AS NewChannelThumbnailPath,
                                channels.ChannelCategory AS VideoCategoryId,
                                packages.PackageId AS VideoPackageId,                                
                                IF(packages.PackageIsFree=1,true,false) AS IsVideoFree

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.sorttabs IS NOT NULL
                                
                                LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1                              
								
								
                                INNER JOIN channels ON channels.ChannelId = sectiondetail.ContentId
                                AND channels.ChannelIsOnline=1

                                LEFT JOIN packagechannels ON sectiondetail.ContentId =	packagechannels.channelId

                                LEFT JOIN packages ON packages.PackageId = packagechannels.packageId

                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 1
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

                                UNION ALL

                                SELECT 
                                tab.TabId,
                                tab.TabName,
                                tab.TabPosterPath,
                                tab.TabClickURL AS TabURL,
                                tab.sorttabs AS sorttabs,
				section.SequenceNo AS SequenceNo,
                                section.SectionId,
                                section.SectionName,
                                null AS SectionMoreType,
                                IF(sectiondetail.CategoryId!='NULL',true,false) AS IsCategories, 
                                sectiondetail.CategoryId AS CategoryId,
                                IF(videoondemand.SubCategoryId!='NULL',true,false) AS IsSeason, 
                                videoondemandcategories.VideoOnDemandCategoryId AS VoDCategoryId,
                                IF(section.SectionId !=44, videoondemandcategories.VideoOnDemandCategoryname, videoondemand.VideoOnDemandTitle) AS CategoryName,				
                                IF(section.SectionId !=44, IF(videoondemandcategories.VideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.VideoOnDemandCategorythumb ),videoondemandcategories.VideoOnDemandCategorythumb), IF(videoondemand.VideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandThumb ),videoondemand.VideoOnDemandThumb)) AS CategorythumbImage,
                                IF(section.SectionId !=44, IF(videoondemandcategories.NewVideoOnDemandCategorythumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemandcategories.NewVideoOnDemandCategorythumb ),videoondemandcategories.NewVideoOnDemandCategorythumb), IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewCategoryImage,
                                videoondemandcategories.VideoOnDemandCategoryIsOnline AS CategoryIsOnline,
                                videoondemand.VideoOnDemandIsFree AS IsCategoryFree,
                                IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'Movies',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageName,'Local Packages')) AS PackageName,
                                IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),false,IF(packages.PackageId NOT IN (6,8,15,16,2),true,false)) AS PackageIsFree,
                                IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'1005',IF(packages.PackageId IN (6,8,15,16,2),packages.PackageProductId,'1007')) AS PackageProduct,
                                IF((videoondemand.erosData=1 OR videoondemand.VideoOnDemandCategoryId=8),'10',IF(packages.PackageId IN (6,8,15,16,2),packages.PackagePrice,'15')) AS PackagePrice,
                                false AS IsVideoChannel,
                                2 AS VideoType,
                                sectiondetail.ContentId AS VideoEntityId,
                                videoondemand.VideoOnDemandTitle AS VideoName,
                                videoondemand.VideoOnDemandDescription AS VideoDescription,
                                IF(videoondemand.erosData=1, IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall), IF(videoondemand.VideoOnDemandMobileSmall NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.VideoOnDemandMobileSmall ),videoondemand.VideoOnDemandMobileSmall)) AS VideoImagePath,
                                IF(videoondemand.erosData=1,IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'https://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb),IF(videoondemand.NewVideoOnDemandThumb NOT LIKE 'http://%',CONCAT( :ImagesDomainName, videoondemand.NewVideoOnDemandThumb ),videoondemand.NewVideoOnDemandThumb)) AS NewVideoOnDemandThumb,
                                videoondemand.VideoOnDemandCategoryId AS VideoCategoryId,
                                0 AS VideoPackageId,
                                videoondemand.VideoOnDemandIsFree AS IsVideoFree
								

                                FROM sectiondetail

                                INNER JOIN section ON section.SectionId = sectiondetail.SectionId
                                AND section.IsOnline='1'

                                INNER JOIN tab ON tab.TabId=section.SectionTabId
                                AND tab.IsOnline='1' AND tab.sorttabs IS NOT NULL
                                    
                                LEFT JOIN videoondemandcategories ON videoondemandcategories.VideoOnDemandCategoryId = sectiondetail.CategoryId
                                AND videoondemandcategories.VideoOnDemandCategoryIsOnline=1   
                                    
                                INNER JOIN videoondemand ON videoondemand.VideoOnDemandId = sectiondetail.ContentId
                                AND videoondemand.VideoOnDemandIsOnline=1                                    
                               
                                LEFT JOIN categoriespackages ON sectiondetail.CategoryId=categoriespackages.categoryId

                                LEFT JOIN packages ON packages.PackageId = categoriespackages.packageId
								

                                WHERE sectiondetail.IsOnline = 1 AND sectiondetail.IsChannel = 0
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
                                ) channelvods
                                ORDER BY channelvods.sorttabs ,channelvods.SequenceNo DESC,RAND()
STR;
                                // echo $sql;
                                $bind = array(
                                    ':ImagesDomainName' => Config::$imagesDomainName,
                                    ':CountryCode' => $CountryCode,
                                    ':CountryCodePattern' => "%$CountryCode%"
                                );
                                $results = $db->run($sql, $bind);
                                
                                $sql = <<<STR
                                        SELECT BannerTabId AS TabId,
                                        BannerId,
                                        BannerPath AS TabPosterPath,
                                        BannerIsVideo AS IsPosterVideo,
                                        BannerVideoIsChannel AS IsVideoChannel,
                                        BannerVideoEntityId AS VideoEntityId,
                                        BannerURL AS TabURL

                                        FROM tabbanners
                                        WHERE tabbanners.BannerIsOnline='1'
                                        AND CASE
                                        WHEN :CountryCode != 'PK'
                                        THEN
                                        BannerIsAllowedInternationally = '1'
                                        ELSE 1 END;
STR;
                                // echo $sql;
                                $bind = array(
                                    ':CountryCode' => $CountryCode
                                );
                                $tabbanners = $db->run($sql, $bind);                                
                                // print_r($tabbanners);                                
                                // Formatting the Data
                                Format::formatResponseData($results);
                                Format::formatResponseData($tabbanners);					
                                // Creating Section Array with Details
                               $i = 0;
                                $sectionArray = array();
                                $limit = rand(3, 70);
                                foreach ($results as $row) {
                                    $flag = true;
                                    foreach ($sectionArray as $key => $assrow) {
                                        if ($assrow['SectionId'] === $row['SectionId']) {
											if($row['IsVideoFree']==true)
											{
												$count1 = count($sectionArray[$key]['Categories']);
												$count = count($sectionArray[$key]['Videos']);
												if ($count <= $limit) {
													//----------------condition for checking categories-------------------------------//
													if($row['CategoryId']!=null){
														if($row['IsCategoryFree']==true){
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 10,12);  //if categories then display categories
														}else{
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 10,12);  //if categories then display categories
														}
														$sectionArray[$key]['Videos']=array();  //if categories then videos null
													}else{
														$sectionArray[$key]['Categories']=array();//if categories null then display videos 
														$sectionArray[$key]['Videos'][$count] = array_splice($row, 19); //if categories null then display videos 
													}
													
												}
											}else{
												$count1 = count($sectionArray[$key]['Categories']);
												$count = count($sectionArray[$key]['Videos']);
												if ($count <= $limit) {
													//----------------condition for checking categories-------------------------------//
													if($row['CategoryId']!=null){
														if($row['IsCategoryFree']==true){
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 10,12);  //if categories then display categories
														}else{
															$sectionArray[$key]['Categories'][$count1] = array_splice($row, 10,12);  //if categories then display categories
														}
														$sectionArray[$key]['Videos']=array();  //if categories then videos null
													}else{
														$sectionArray[$key]['Categories']=array();//if categories null then display videos 
														$sectionArray[$key]['Videos'][$count] = array_splice($row, 19); //if categories null then display videos 
													}
													
												}
											}
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {                                        
                                        $sectionArray[$i]['TabId'] = $row['TabId'];
                                        $sectionArray[$i]['TabName'] = $row['TabName'];
                                        $sectionArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $sectionArray[$i]['TabURL'] = $row['TabURL'];										
                                        $sectionArray[$i]['SectionId'] = $row['SectionId'];
                                        $sectionArray[$i]['SectionName'] = $row['SectionName'];
                                        $sectionArray[$i]['IsSectionMore'] = $row['IsSectionMore'];
                                        $sectionArray[$i]['IsCategories'] = $row['IsCategories'];                                      
										if($row['IsVideoFree']==true)
										{
											if($row['CategoryId']){
												if($row['IsCategoryFree']==true){
													$sectionArray[$i]['Categories'][] = array_splice($row, 10,12);   //assign catgories
												}else{
													$sectionArray[$i]['Categories'][] = array_splice($row, 10,12);   //assign catgories
												}
												$sectionArray[$i]['Videos']=array();  //assign video null array
											}else{											
												$sectionArray[$i]['Categories']=array();  //assign catgories null
												$sectionArray[$i]['Videos'][] = array_splice($row, 19);  //assign videos
											}
										}else{
											if($row['CategoryId']){	
												if($row['IsCategoryFree']==true){
													$sectionArray[$i]['Categories'][] = array_splice($row, 10,12);   //assign catgories
												}else{
													$sectionArray[$i]['Categories'][] = array_splice($row, 10,12);   //assign catgories
												}
												$sectionArray[$i]['Videos']=array();  //assign video null array
											}else{
												$sectionArray[$i]['Categories']=array();  //assign catgories null
												$sectionArray[$i]['Videos'][] = array_splice($row, 19);  //assign videos
											}
										}
                                        $i ++;
                                    }
                                }                                
                                $tabArray = array();
                                
                                $i = 0;
                                foreach ($sectionArray as $row) {
                                    $flag = true;
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Sections']);
                                            $tabArray[$key]['Sections'][$count] = array_splice($row, 4);
                                            $flag = false;
                                        }
                                    }
                                    if ($flag) {
                                        $tabArray[$i]['TabId'] = $row['TabId'];
                                        $tabArray[$i]['TabName'] = $row['TabName'];
                                        $tabArray[$i]['TabPosterPath'] = $row['TabPosterPath'];
                                        $tabArray[$i]['TabURL'] = $row['TabURL'];
                                        $tabArray[$i]['Sections'][] = array_splice($row, 4);
                                        $tabArray[$i]['Banners'] = array();
                                        $i ++;
                                    }
                                }
                                
                                // Merging Tab Banners
                                foreach ($tabbanners as $row) {
                                    // print_r($row);
                                    foreach ($tabArray as $key => $assrow) {
                                        if ($assrow['TabId'] === $row['TabId']) {
                                            $count = count($tabArray[$key]['Banners']);
                                            $tabArray[$key]['Banners'][$count] = array_splice($row, 2);
                                        }
                                    }
                                }								
                               
                                return General::getResponse($response->write(
                                        SuccessObject::getBucketSectionsSuccesssObject(
                                        $tabArray, Message::getMessage('M_DATA'), null, null,
                                        AppSettings::localGetOptimizedAdURL('V1', 'en', 'android', '9', 'All','15')                                     
                                        ,AppSettings::getOtpBanners('V1', 'en', 'android',$CountryCode),
                                         AppSettings::getAllPackages('V1', 'en', 'android'),
										 AppSettings::getAndroidBucketSatus('V1', 'en', 'android'))
                                        )
                                        );                           
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v3':
                case 'V3':
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getSectionErrorObject(Message::getPDOMessage($e))));
        } finally {
            $results = null;
            $db = null;
        }
	}
	
	
	private static function localGetOptimizedAdURL($Version, $Language, $Platform, $AdType, $Gender, $Age)
    {
        $results = null;
        
        try {
            $db = parent::getDataBase();
            
            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                            $sql = <<<STR
				SELECT                                    
                                    AdvertisementName,
                                    AdvertisementUrl,
                                    IsJavascriptTag,
                                    IsAllowSkipAd,
                                    AdvertisementShowSkipAfter,
                                    IsAllowOnNonPlayer,
                                    AdvertisementVastURL,
                                    IsVast
                                    
                                FROM (
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
                                ":AdType" => $AdType
                            );
                            $results = $db->run($sql, $bind);
                            
							
                            if ($results) {
                                Format::formatResponseData($results);
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 9) {
                                    if (! $results[0]['IsVast']) {
                                        $results[0]['IsVast'] = true;
                                        $results[0]['AdvertisementVastURL'] = "http://app.tapmad.com/api/getVastAd/V1/en/androidvast/" . $results[0]['AdvertisementId'];
                                    }
                                }
                                
                                if (isset($results[0]['AdvertisementTypeId']) && $results[0]['AdvertisementTypeId'] === 1) {
                                    return null;
                                }
                                if (isset($results[0]['AdvertisementId']) && $results[0]['AdvertisementId'] === 17) {
                                    return null;
                                }
                                return $results[0];
                            } else {
                                return null;
                            }
                            break;
                        default:
                            return null;
                            break;
                    }
                    break;
                default:
                    return null;
                    break;
            }
        } catch (PDOException $e) {
            return null;
        } finally {
            $results = null;
            $db = null;
        }
    }

public static function testQuery(Request $request, Response $response){
        set_time_limit(10000);

 $db = parent::getDataBase();
 $idArray = [
    7615907,
    7615942,
    7616009,
    7616011,
    7616032,
    7616033,
    7616042,
    7616044,
    7616060,
    7616095,
    7616096,
    7616105,
    7616125,
    7616141,
    7616146,
    7616154,
    7616188,
    7616204,
    7616214,
    7616225,
    7616242,
    7616249,
    7616268,
    7616283,
    7616295,
    7616335,
    7616358,
    7616375,
    7616384,
    7616387,
    7616388,
    7616389,
    7616392,
    7616402,
    7616406,
    7616439,
    7616489,
    7616498,
    7616505,
    7616528,
    7616530,
    7616617,
    7616649,
    7616692,
    7616698,
    7616703,
    7616709,
    7616713,
    7616717,
    7616745,
    7616761,
    7616772,
    7616773,
    7616802,
    7616823,
    7616828,
    7616840,
    7616842,
    7616887,
    7616896,
    7616900,
    7616918,
    7616939,
    7616952,
    7616953,
    7617018,
    7617040,
    7617043,
    7617082,
    7617107,
    7617123,
    7617124,
    7617136,
    7617140,
    7617156,
    7617157,
    7617159,
    7617176,
    7617260,
    7617314,
    7617329,
    7617341,
    7617368,
    7617399,
    7617401,
    7617420,
    7617428,
    7617430,
    7617431,
    7617432,
    7617434,
    7617441,
    7617446,
    7617453,
    7617475,
    7617489,
    7617504,
    7617506,
    7617531,
    7617532,
    7617541,
    7617544,
    7617568,
    7617569,
    7617578,
    7617581,
    7617584,
    7617619,
    7617649,
    7617677,
    7617682,
    7617730,
    7617738,
    7617744,
    7617756,
    7617765,
    7617766,
    7617769,
    7617777,
    7617781,
    7617791,
    7617821,
    7617829,
    7617840,
    7617842,
    7617845,
    7617896,
    7617903,
    7617905,
    7617920,
    7617930,
    7617984,
    7617989,
    7617992,
    7617994,
    7618026,
    7618034,
    7618048,
    7618058,
    7618063,
    7618088,
    7618106,
    7618115,
    7618117,
    7618163,
    7618171,
    7618180,
    7618182,
    7618183,
    7618188,
    7618191,
    7618211,
    7618232,
    7618239,
    7618242,
    7618259,
    7618262,
    7618283,
    7618314,
    7618325,
    7618330,
    7618337,
    7618360,
    7618388,
    7618390,
    7618399,
    7618406,
    7618414,
    7618424,
    7618446,
    7618499,
    7618517,
    7618535,
    7618558,
    7618573,
    7618594,
    7618672,
    7618673,
    7618684,
    7618689,
    7618699,
    7618715,
    7618733,
    7618737,
    7618763,
    7618803,
    7618820,
    7618824,
    7618827,
    7618880,
    7618893,
    7618936,
    7618953,
    7618959,
    7618960,
    7619007,
    7619020,
    7619036,
    7619058,
    7619085,
    7619086,
    7619107,
    7619114,
    7619157,
    7619161,
    7619180,
    7619194,
    7619220,
    7619236,
    7619251,
    7619252,
    7619286,
    7619292,
    7619314,
    7619329,
    7619344,
    7619354,
    7619369,
    7619380,
    7619422,
    7619424,
    7619440,
    7619444,
    7619463,
    7619474,
    7619476,
    7619494,
    7619518,
    7619533,
    7619545,
    7619548,
    7619557,
    7619611,
    7619660,
    7619662,
    7619688,
    7619699,
    7619789,
    7619790,
    7619801,
    7619821,
    7619824,
    7619830,
    7619892,
    7619914,
    7619917,
    7619922,
    7619924,
    7619939,
    7619952,
    7619956,
    7619963,
    7619968,
    7619976,
    7619978,
    7619981,
    7619982,
    7619996,
    7620056,
    7620057,
    7620119,
    7620122,
    7620125,
    7620136,
    7620142,
    7620209,
    7620210,
    7620246,
    7620250,
    7620274,
    7620275,
    7620278,
    7620341,
    7620349,
    7620353,
    7620360,
    7620372,
    7620394,
    7620395,
    7620398,
    7620403,
    7620418,
    7620424,
    7620464,
    7620490,
    7620493,
    7620509,
    7620512,
    7620515,
    7620518,
    7620531,
    7620557,
    7620564,
    7620619,
    7620625,
    7620645,
    7620656,
    7620666,
    7620677,
    7620685,
    7620687,
    7620723,
    7620729,
    7620749,
    7620757,
    7620804,
    7620811,
    7620848,
    7620897,
    7620924,
    7620948,
    7620965,
    7620990,
    7621105,
    7621108,
    7621127,
    7621136,
    7621151,
    7621174,
    7621176,
    7621177,
    7621209,
    7621294,
    7621313,
    7621314,
    7621344,
    7621357,
    7621373,
    7621381,
    7621394,
    7621398,
    7621402,
    7621404,
    7621506,
    7621512,
    7621522,
    7621531,
    7621536,
    7621547,
    7621572,
    7621585,
    7621588,
    7621620,
    7621635,
    7621638,
    7621655,
    7621659,
    7621686,
    7621692,
    7621697,
    7621698,
    7621710,
    7621720,
    7621722,
    7621727,
    7621770,
    7621777,
    7621778,
    7621805,
    7622192,
    7622209,
    7622330,
    7622346,
    7622348,
    7622410,
    7622432,
    7622499,
    7622554,
    7622621,
    7622654,
    7622671,
    7622692,
    7622711,
    7622717,
    7622722,
    7622732,
    7622788,
    7622850,
    7622867,
    7622995,
    7623016,
    7623033,
    7623048,
    7623113,
    7623132,
    7623163,
    7623192,
    7623445,
    7623458,
    7623462,
    7623476,
    7623490,
    7623543,
    7623561,
    7623633,
    7623662,
    7623664,
    7623709,
    7623730,
    7623761,
    7623789,
    7623822,
    7623935,
    7623943,
    7623956,
    7623971,
    7623979,
    7624017,
    7624058,
    7624082,
    7624087,
    7624110,
    7624126,
    7624224,
    7624226,
    7624260,
    7624268,
    7624269,
    7624298,
    7624302,
    7624310,
    7624341,
    7624403,
    7624537,
    7624644,
    7624918,
    7624931,
    7624943,
    7625004,
    7625039,
    7625047,
    7625048,
    7625107,
    7625143,
    7625158,
    7625265,
    7625341,
    7625478,
    7625553,
    7625564,
    7625703,
    7626044,
    7626053,
    7626163,
    7626262,
    7626274,
    7626322,
    7626352,
    7626499,
    7626540,
    7626549,
    7626563,
    7626584,
    7626587,
    7626729,
    7627013,
    7627041,
    7627102,
    7627129,
    7627274,
    7627391,
    7627471,
    7627640,
    7627738,
    7627775,
    7627787,
    7627823,
    7627876,
    7627893,
    7628001,
    7628097,
    7628154,
    7628228,
    7628234,
    7628260,
    7628336,
    7628342,
    7628371,
    7628438,
    7628458,
    7628466,
    7628477,
    7628491,
    7628510,
    7628534,
    7628563,
    7628595,
    7628639,
    7628698,
    7628703,
    7628707,
    7628714,
    7628747,
    7628754,
    7628774,
    7628800,
    7628832,
    7628845,
    7628933,
    7628986,
    7628999,
    7629028,
    7629063,
    7629146,
    7629261,
    7629487,
    7629508,
    7629614,
    7629700,
    7629724,
    7629734,
    7629743,
    7629782,
    7629787,
    7629820,
    7629838,
    7629862,
    7629885,
    7629909,
    7629936,
    7629997,
    7630074,
    7630141,
    7630147,
    7630156,
    7630170,
    7630172,
    7630174,
    7630178,
    7630181,
    7630218,
    7630255,
    7630301,
    7630313,
    7630332,
    7630351,
    7630353,
    7630372,
    7630396,
    7630401,
    7630404,
    7630406,
    7630421,
    7630424,
    7630429,
    7630445,
    7630466,
    7630484,
    7630540,
    7630580,
    7630605,
    7630627,
    7630647,
    7630807,
    7630831,
    7630900,
    7630910,
    7630913,
    7630917,
    7630935,
    7630960,
    7631042,
    7631068,
    7631081,
    7631151,
    7631197,
    7631201,
    7631289,
    7631297,
    7631320,
    7631330,
    7631358,
    7631395,
    7631397,
    7631477,
    7631494,
    7631520,
    7631566,
    7631621,
    7631666,
    7631681,
    7631803,
    7631894,
    7632259,
    7632381,
    7632446,
    7632513,
    7632561,
    7632594,
    7632728,
    7632745,
    7632799,
    7632837,
    7632865,
    7632871,
    7632938,
    7632956,
    7632970,
    7633037,
    7633077,
    7633101,
    7633169,
    7633205,
    7633233,
    7633243,
    7633293,
    7633294,
    7633351,
    7633362,
    7633414,
    7633415,
    7633422,
    7633535,
    7633615,
    7633654,
    7633879,
    7633890,
    7633893,
    7633948,
    7633951,
    7634105,
    7634124,
    7634128,
    7634155,
    7634197,
    7634248,
    7634325,
    7634556,
    7634581,
    7634582,
    7634612,
    7634691,
    7634751,
    7634759,
    7634797,
    7634943,
    7635003,
    7635004,
    7635072,
    7635151,
    7635157,
    7635162,
    7635218,
    7635399,
    7635479,
    7635499,
    7635618,
    7635671,
    7635805,
    7635848,
    7635854,
    7635888,
    7635898,
    7635899,
    7635946,
    7635957,
    7635958,
    7635985,
    7636029,
    7636041,
    7636099,
    7636149,
    7636155,
    7636181,
    7636182,
    7636189,
    7636263,
    7636382,
    7636399,
    7636484,
    7636525,
    7636530,
    7636538,
    7636564,
    7636611,
    7636614,
    7636641,
    7636699,
    7636725,
    7636802,
    7636835,
    7636877,
    7636884,
    7636912,
    7636933,
    7636939,
    7636943,
    7636944,
    7636959,
    7636981,
    7637030,
    7637074,
    7637100,
    7637160,
    7637183,
    7637244,
    7637362,
    7637374,
    7637430,
    7637452,
    7637477,
    7637481,
    7637498,
    7637547,
    7637625,
    7637707,
    7637719,
    7637771,
    7637834,
    7637867,
    7637885,
    7637938,
    7637939,
    7638002,
    7638040,
    7638069,
    7638191,
    7638201,
    7638293,
    7638315,
    7638414,
    7638439,
    7638521,
    7638571,
    7638588,
    7638644,
    7638664,
    7638703,
    7638714,
    7638715,
    7638749,
    7638834,
    7638854,
    7638863,
    7638864,
    7638875,
    7638921,
    7638945,
    7639206,
    7639259,
    7639359,
    7639370,
    7639406,
    7639446,
    7639543,
    7639557,
    7639607,
    7639614,
    7639620,
    7639637,
    7639648,
    7639673,
    7639714,
    7639784,
    7639792,
    7639802,
    7639859,
    7639865,
    7639891,
    7639899,
    7639912,
    7639914,
    7639915,
    7639920,
    7639936,
    7639943,
    7640020,
    7640022,
    7640119,
    7640203,
    7640226,
    7640229,
    7640302,
    7640304,
    7640321,
    7640330,
    7640385,
    7640395,
    7640408,
    7640448,
    7640517,
    7640595,
    7640608,
    7640676,
    7640723,
    7640804,
    7640830,
    7640891,
    7640939,
    7640959,
    7640970,
    7640983,
    7640992,
    7640993,
    7641008,
    7641063,
    7641075,
    7641083,
    7641086,
    7641096,
    7641171,
    7641179,
    7641203,
    7641232,
    7641236,
    7641242,
    7641303,
    7641304,
    7641325,
    7641343,
    7641361,
    7641368,
    7641369,
    7641394,
    7641400,
    7641406,
    7641456,
    7641466,
    7641470,
    7641471,
    7641488,
    7641490,
    7641495,
    7641505,
    7641508,
    7641527,
    7641549,
    7641563,
    7641568,
    7641569,
    7641571,
    7641575,
    7641587,
    7641595,
    7641604,
    7641612,
    7641663,
    7641672,
    7641685,
    7641688,
    7641712,
    7641728,
    7641749,
    7641757,
    7641759,
    7641761,
    7641768,
    7641771,
    7641779,
    7641794,
    7641795,
    7641799,
    7641801,
    7641805,
    7641812,
    7641846,
    7641863,
    7641865,
    7641889,
    7641907,
    7641921,
    7641934,
    7641936,
    7641942,
    7641955,
    7641963,
    7641965,
    7642003,
    7642007,
    7642011,
    7642027,
    7642051,
    7642091,
    7642093,
    7642104,
    7642126,
    7642144,
    7642168,
    7642177,
    7642219,
    7642250,
    7642271,
    7642309,
    7642339,
    7642389,
    7642407,
    7642419,
    7642427,
    7642428,
    7642432,
    7642496,
    7642526,
    7642568,
    7642576,
    7642585,
    7642608,
    7642631,
    7642633,
    7642665,
    7642677,
    7642692,
    7642708,
    7642714,
    7642719,
    7642736,
    7642774,
    7642794,
    7642804,
    7642842,
    7642847,
    7642868,
    7642881,
    7642884,
    7642889,
    7642897,
    7642924,
    7642965,
    7642979,
    7643011,
    7643043,
    7643048,
    7643069,
    7643077,
    7643082,
    7643109,
    7643121,
    7643132,
    7643184,
    7643191,
    7643205,
    7643214,
    7643229,
    7643244,
    7643265,
    7643279,
    7643305,
    7643318,
    7643320,
    7643328,
    7643341,
    7643366,
    7643367,
    7643371,
    7643409,
    7643412,
    7643415,
    7643423,
    7643431,
    7643447,
    7643457,
    7643459,
    7643466,
    7643491,
    7643515,
    7643522,
    7643534,
    7643552,
    7643556,
    7643568,
    7643579,
    7643622,
    7643627,
    7643644,
    7643650,
    7643655,
    7643696,
    7643730,
    7643737,
    7643751,
    7643770,
    7643793,
    7643799,
    7643808,
    7643809,
    7643826,
    7643839,
    7643878,
    7643885,
    7643899,
    7643923,
    7643936,
    7643942,
    7643944,
    7643966,
    7643977,
    7643991,
    7644006,
    7644021,
    7644043,
    7644048,
    7644058,
    7644061,
    7644065,
    7644074,
    7644105,
    7644106,
    7644121,
    7644125,
    7644135,
    7644141,
    7644171,
    7644212,
    7644218,
    7644233,
    7644236,
    7644250,
    7644252,
    7644265,
    7644295,
    7644307,
    7644308,
    7644314,
    7644330,
    7644383,
    7644397,
    7644399,
    7644402,
    7644407,
    7644417,
    7644442,
    7644455,
    7644459,
    7644463,
    7644466,
    7644580,
    7644589,
    7644591,
    7644595,
    7644601,
    7644633,
    7644638,
    7644646,
    7644651,
    7644659,
    7644666,
    7644671,
    7644688,
    7644691,
    7644698,
    7644701,
    7644705,
    7644718,
    7644728,
    7644737,
    7644754,
    7644767,
    7644789,
    7644822,
    7644850,
    7644859,
    7644874,
    7644886,
    7644888,
    7644897,
    7644900,
    7644909,
    7644913,
    7644942,
    7645002,
    7645006,
    7645032,
    7645063,
    7645068,
    7645073,
    7645076,
    7645082,
    7645105,
    7645112,
    7645118,
    7645188,
    7645192,
    7645193,
    7645199,
    7645219,
    7645222,
    7645248,
    7645249,
    7645256,
    7645280,
    7645310,
    7645323,
    7645333,
    7645345,
    7645348,
    7645353,
    7645412,
    7645424,
    7645436,
    7645445,
    7645466,
    7645467,
    7645476,
    7645494,
    7645528,
    7645535,
    7645537,
    7645545,
    7645567,
    7645569,
    7645593,
    7645602,
    7645628,
    7645641,
    7645645,
    7645655,
    7645692,
    7645724,
    7645729,
    7645730,
    7645750,
    7645761,
    7645790,
    7645839,
    7645851,
    7645853,
    7645861,
    7645865,
    7645869,
    7645877,
    7645879,
    7645889,
    7645896,
    7645900,
    7645905,
    7645916,
    7645927,
    7645929,
    7645971,
    7645975,
    7645978,
    7645987,
    7645995,
    7646014,
    7646028,
    7646032,
    7646038,
    7646083,
    7646085,
    7646089,
    7646094,
    7646099,
    7646112,
    7646118,
    7646119,
    7646130,
    7646145,
    7646172,
    7646193,
    7646225,
    7646226,
    7646233,
    7646252,
    7646256,
    7646284,
    7646290,
    7646308,
    7646318,
    7646331,
    7646346,
    7646349,
    7646353,
    7646360,
    7646361,
    7646369,
    7646401,
    7646469,
    7646502,
    7646514,
    7646526,
    7646528,
    7646542,
    7646555,
    7646591,
    7646601,
    7646603,
    7646604,
    7646615,
    7646633,
    7646674,
    7646675,
    7646707,
    7646715,
    7646731,
    7646775,
    7646787,
    7646789,
    7646809,
    7646811,
    7646814,
    7646833,
    7646839,
    7646878,
    7646881,
    7646888,
    7646892,
    7646913,
    7646931,
    7646952,
    7646958,
    7646969,
    7646993,
    7646998,
    7647002,
    7647014,
    7647022,
    7647024,
    7647031,
    7647036,
    7647054,
    7647068,
    7647084,
    7647090,
    7647093,
    7647096,
    7647104,
    7647108,
    7647112,
    7647120,
    7647131,
    7647138,
    7647140,
    7647157,
    7647166,
    7647187,
    7647193,
    7647220,
    7647258,
    7647271,
    7647272,
    7647277,
    7647284,
    7647287,
    7647332,
    7647356,
    7647396,
    7647398,
    7647415,
    7647417,
    7647440,
    7647452,
    7647472,
    7647511,
    7647544,
    7647551,
    7647553,
    7647557,
    7647617,
    7647624,
    7647637,
    7647669,
    7647676,
    7647677,
    7647678,
    7647687,
    7647703,
    7647707,
    7647718,
    7647769,
    7647788,
    7647795,
    7647799,
    7647848,
    7647852,
    7647884,
    7647891,
    7647893,
    7647917,
    7647919,
    7647928,
    7647929,
    7647965,
    7648011,
    7648012,
    7648017,
    7648025,
    7648035,
    7648046,
    7648064,
    7648102,
    7648202,
    7648214,
    7648215,
    7648225,
    7648232,
    7648237,
    7648273,
    7648285,
    7648302,
    7648306,
    7648315,
    7648336,
    7648339,
    7648378,
    7648399,
    7648411,
    7648429,
    7648433,
    7648453,
    7648464,
    7648493,
    7648499,
    7648551,
    7648562,
    7648569,
    7648576,
    7648587,
    7648630,
    7648633,
    7648637,
    7648640,
    7648656,
    7648691,
    7648710,
    7648716,
    7648727,
    7648734,
    7648739,
    7648766,
    7648770,
    7648772,
    7648775,
    7648777,
    7648804,
    7648822,
    7648858,
    7648872,
    7648912,
    7648933,
    7648964,
    7648968,
    7648970,
    7648971,
    7648981,
    7649000,
    7649006,
    7649048,
    7649050,
    7649052,
    7649056,
    7649072,
    7649092,
    7649099,
    7649113,
    7649114,
    7649116,
    7649137,
    7649139,
    7649143,
    7649150,
    7649167,
    7649204,
    7649219,
    7649220,
    7649266,
    7649293,
    7649296,
    7649321,
    7649322,
    7649351,
    7649353,
    7649368,
    7649371,
    7649384,
    7649387,
    7649388,
    7649437,
    7649440,
    7649444,
    7649449,
    7649469,
    7649475,
    7649482,
    7649528,
    7649533,
    7649540,
    7649560,
    7649563,
    7649583,
    7649589,
    7649617,
    7649671,
    7649696,
    7649726,
    7649732,
    7649755,
    7649879,
    7649920,
    7649945,
    7649968,
    7649988,
    7649991,
    7649992,
    7650006,
    7650019,
    7650045,
    7650048,
    7650058,
    7650082,
    7650093,
    7650107,
    7650124,
    7650146,
    7650152,
    7650153,
    7650164,
    7650218,
    7650222,
    7650255,
    7650636,
    7650666,
    7650719,
    7650794,
    7650838,
    7650857,
    7651048,
    7651100,
    7651176,
    7651363,
    7651402,
    7651440,
    7651475,
    7651495,
    7651515,
    7651523,
    7651631,
    7651698,
    7651713,
    7651717,
    7651916,
    7651951,
    7652073,
    7652111,
    7652352,
    7652391,
    7652439,
    7652553,
    7652592,
    7652608,
    7652628,
    7652665,
    7652711,
    7652815,
    7652855,
    7653107,
    7653250,
    7653348,
    7653353,
    7653469,
    7653550,
    7653561,
    7653581,
    7653609,
    7653637,
    7653719,
    7653722,
    7653799,
    7653807,
    7653885,
    7653945,
    7654016,
    7654020,
    7654074,
    7654088,
    7654100,
    7654152,
    7654165,
    7654192,
    7654203,
    7654275,
    7654288,
    7654299,
    7654307,
    7654427,
    7654459,
    7654479,
    7654524,
    7654537,
    7654550,
    7654634,
    7654691,
    7654721,
    7654737,
    7654764,
    7654836,
    7654859,
    7654872,
    7654916,
    7654988,
    7655018,
    7655025,
    7655041,
    7655064,
    7655187,
    7655241,
    7655265,
    7655381,
    7655398,
    7655476,
    7655566,
    7655653,
    7655678,
    7655733,
    7655737,
    7655904,
    7656101,
    7656215,
    7656295,
    7656489,
    7656534,
    7656677,
    7656937,
    7656999,
    7657036,
    7657140,
    7657148,
    7657448,
    7657614,
    7657615,
    7657755,
    7657909,
    7658037,
    7658230,
    7658394,
    7658438,
    7658451,
    7658801,
    7658896,
    7658938,
    7659141,
    7659179,
    7659374,
    7659498,
    7659567,
    7659657,
    7659863,
    7660286,
    7660344,
    7660840,
    7660907,
    7661039,
    7661048,
    7661113,
    7661122,
    7661292,
    7661336,
    7661503,
    7661647,
    7661769,
    7661910,
    7661949,
    7661963,
    7661976,
    7661998,
    7662001,
    7662021,
    7662023,
    7662039,
    7662043,
    7662055,
    7662058,
    7662064,
    7662084,
    7662086,
    7662100,
    7662119,
    7662131,
    7662150,
    7662184,
    7662204,
    7662212,
    7662218,
    7662220,
    7662225,
    7662247,
    7662267,
    7662270,
    7662282,
    7662291,
    7662314,
    7662333,
    7662402,
    7662404,
    7662428,
    7662433,
    7662443,
    7662447,
    7662466,
    7662479,
    7662492,
    7662494,
    7662496,
    7662503,
    7662545,
    7662549,
    7662580,
    7662585,
    7662590,
    7662592,
    7662596,
    7662612,
    7662621,
    7662649,
    7662650,
    7662655,
    7662662,
    7662677,
    7662681,
    7662709,
    7662766,
    7662770,
    7662789,
    7662793,
    7662811,
    7662828,
    7662830,
    7662836,
    7662840,
    7662858,
    7662861,
    7662907,
    7662925,
    7662931,
    7662945,
    7662949,
    7662960,
    7662963,
    7662983,
    7662986,
    7662987,
    7663001,
    7663005,
    7663029,
    7663061,
    7663062,
    7663075,
    7663099,
    7663109,
    7663133,
    7663152,
    7663162,
    7663173,
    7663180,
    7663210,
    7663211,
    7663219,
    7663221,
    7663242,
    7663244,
    7663258,
    7663259,
    7663260,
    7663281,
    7663289,
    7663307,
    7663325,
    7663340,
    7663365,
    7663368,
    7663369,
    7663378,
    7663379,
    7663380,
    7663399,
    7663417,
    7663422,
    7663466,
    7663467,
    7663475,
    7663487,
    7663582,
    7663592,
    7663595,
    7663609,
    7663619,
    7663622,
    7663625,
    7663657,
    7663693,
    7663702,
    7663709,
    7663714,
    7663723,
    7663741,
    7663748,
    7663791,
    7663792,
    7663804,
    7663823,
    7663827,
    7663837,
    7663852,
    7663919,
    7663953,
    7663972,
    7663975,
    7664011,
    7664038,
    7664044,
    7664056,
    7664058,
    7664079,
    7664080,
    7664082,
    7664084,
    7664117,
    7664133,
    7664150,
    7664156,
    7664160,
    7664168,
    7664169,
    7664183,
    7664184,
    7664191,
    7664193,
    7664203,
    7664235,
    7664238,
    7664241,
    7664252,
    7664267,
    7664273,
    7664282,
    7664289,
    7664302,
    7664319,
    7664332,
    7664338,
    7664345,
    7664351,
    7664379,
    7664383,
    7664400,
    7664401,
    7664403,
    7664415,
    7664430,
    7664467,
    7664475,
    7664476,
    7664482,
    7664511,
    7664553,
    7664566,
    7664577,
    7664593,
    7664607,
    7664613,
    7664626,
    7664632,
    7664644,
    7664653,
    7664659,
    7664674,
    7664748,
    7664756,
    7664763,
    7664767,
    7664781,
    7664788,
    7664804,
    7664826,
    7664839,
    7664847,
    7664856,
    7664933,
    7664939,
    7664944,
    7664968,
    7665010,
    7665032,
    7665036,
    7665044,
    7665046,
    7665058,
    7665061,
    7665129,
    7665131,
    7665151,
    7665163,
    7665164,
    7665184,
    7665206,
    7665251,
    7665259,
    7665276,
    7665296,
    7665356,
    7665366,
    7665367,
    7665411,
    7665418,
    7665439,
    7665446,
    7665465,
    7665468,
    7665477,
    7665491,
    7665502,
    7665513,
    7665521,
    7665535,
    7665547,
    7665558,
    7665569,
    7665584,
    7665596,
    7665609,
    7665626,
    7665629,
    7665643,
    7665646,
    7665674,
    7665682,
    7665691,
    7665703,
    7665732,
    7665786,
    7665791,
    7665793,
    7665799,
    7665822,
    7665843,
    7665883,
    7665885,
    7665895,
    7665918,
    7665939,
    7665960,
    7665967,
    7665969,
    7665976,
    7665988,
    7665991,
    7666000,
    7666005,
    7666009,
    7666023,
    7666026,
    7666036,
    7666045,
    7666048,
    7666050,
    7666064,
    7666072,
    7666105,
    7666117,
    7666127,
    7666157,
    7666165,
    7666182,
    7666197,
    7666206,
    7666209,
    7666237,
    7666288,
    7666319,
    7666328,
    7666331,
    7666352,
    7666357,
    7666361,
    7666393,
    7666396,
    7666404,
    7666428,
    7666447,
    7666455,
    7666469,
    7666472,
    7666495,
    7666508,
    7666521,
    7666548,
    7666555,
    7666567,
    7666575,
    7666580,
    7666582,
    7666610,
    7666611,
    7666635,
    7666668,
    7666701,
    7666702,
    7666705,
    7666718,
    7666721,
    7666731,
    7666738,
    7666744,
    7666764,
    7666768,
    7666774,
    7666781,
    7666788,
    7666789,
    7666795,
    7666800,
    7666807,
    7666812,
    7666816,
    7666817,
    7666835,
    7666837,
    7666855,
    7666860,
    7666888,
    7666899,
    7666908,
    7666913,
    7666922,
    7666925,
    7666954,
    7666962,
    7666989,
    7666998,
    7667008,
    7667013,
    7667039,
    7667041,
    7667060,
    7667077,
    7667089,
    7667102,
    7667113,
    7667118,
    7667132,
    7667136,
    7667142,
    7667158,
    7667183,
    7667207,
    7667223,
    7667242,
    7667258,
    7667295,
    7667305,
    7667321,
    7667334,
    7667345,
    7667349,
    7667356,
    7667378,
    7667380,
    7667387,
    7667459,
    7667479,
    7667481,
    7667508,
    7667542,
    7667544,
    7667556,
    7667566,
    7667570,
    7667588,
    7667603,
    7667623,
    7667637,
    7667652,
    7667677,
    7667693,
    7667695,
    7667742,
    7667756,
    7667766,
    7667770,
    7667789,
    7667792,
    7667830,
    7667838,
    7667851,
    7667852,
    7667876,
    7667883,
    7667887,
    7667889,
    7667891,
    7667897,
    7667898,
    7667921,
    7667940,
    7667942,
    7667960,
    7667992,
    7668000,
    7668019,
    7668031,
    7668036,
    7668037,
    7668045,
    7668047,
    7668057,
    7668073,
    7668102,
    7668117,
    7668143,
    7668153,
    7668156,
    7668170,
    7668173,
    7668178,
    7668202,
    7668218,
    7668226,
    7668233,
    7668243,
    7668254,
    7668264,
    7668279,
    7668300,
    7668311,
    7668317,
    7668324,
    7668369,
    7668375,
    7668377,
    7668378,
    7668382,
    7668385,
    7668405,
    7668426,
    7668432,
    7668436,
    7668458,
    7668459,
    7668471,
    7668473,
    7668478,
    7668491,
    7668492,
    7668501,
    7668517,
    7668521,
    7668524,
    7668537,
    7668557,
    7668574,
    7668595,
    7668596,
    7668607,
    7668609,
    7668616,
    7668622,
    7668640,
    7668647,
    7668651,
    7668652,
    7668659,
    7668678,
    7668684,
    7668690,
    7668695,
    7668700,
    7668708,
    7668755,
    7668758,
    7668778,
    7668824,
    7668861,
    7668867,
    7668881,
    7668887,
    7668894,
    7668911,
    7668916,
    7668919,
    7668935,
    7668937,
    7668940,
    7668949,
    7668986,
    7668999,
    7669008,
    7669047,
    7669081,
    7669121,
    7669143,
    7669149,
    7669153,
    7669197,
    7669202,
    7669209,
    7669223,
    7669370,
    7669391,
    7669401,
    7669404,
    7669442,
    7669460,
    7669488,
    7669496,
    7669501,
    7669530,
    7669565,
    7669568,
    7669569,
    7669582,
    7669588,
    7669590,
    7669595,
    7669599,
    7669606,
    7669609,
    7669618,
    7669620,
    7669654,
    7669659,
    7669664,
    7669673,
    7669700,
    7669713,
    7669714,
    7669715,
    7669717,
    7669728,
    7669731,
    7669732,
    7669736,
    7669758,
    7669793,
    7669800,
    7669805,
    7669840,
    7669842,
    7669877,
    7669895,
    7669928,
    7669936,
    7669944,
    7669946,
    7669961,
    7669973,
    7669977,
    7669985,
    7669997,
    7670010,
    7670017,
    7670043,
    7670064,
    7670068,
    7670074,
    7670088,
    7670096,
    7670110,
    7670140,
    7670213,
    7670224,
    7670240,
    7670258,
    7670266,
    7670288,
    7670328,
    7670330,
    7670363,
    7670472,
    7670558,
    7670574,
    7670590,
    7670593,
    7670644,
    7670686,
    7670733,
    7670772,
    7670774,
    7670924,
    7671014,
    7671022,
    7671043,
    7671053,
    7671054,
    7671075,
    7671100,
    7671129,
    7671134,
    7671156,
    7671157,
    7671160,
    7671215,
    7671216,
    7671244,
    7671259,
    7671263,
    7671321,
    7671358,
    7671437,
    7671451,
    7671458,
    7671503,
    7671523,
    7671538,
    7671587,
    7671831,
    7671853,
    7671876,
    7672165,
    7672189,
    7672198,
    7672440,
    7672501,
    7672546,
    7672586,
    7672935,
    7673074,
    7673149,
    7673150,
    7673165,
    7673295,
    7673366,
    7673440,
    7673508,
    7673567,
    7673626,
    7673679,
    7673835,
    7673910,
    7674196,
    7674386,
    7674448,
    7674508,
    7674545,
    7674572,
    7674592,
    7674611,
    7674612,
    7674647,
    7674677,
    7674768,
    7674914,
    7675048,
    7675101,
    7675260,
    7675445,
    7675459,
    7675484,
    7675666,
    7675700,
    7675864,
    7675932,
    7676232,
    7676341,
    7676393,
    7676396,
    7676511,
    7676526,
    7676532,
    7676555,
    7676556,
    7676589,
    7676617,
    7676631,
    7676638,
    7676639,
    7676645,
    7676648,
    7676719,
    7676720,
    7676758,
    7676778,
    7676788,
    7676803,
    7676843,
    7676852,
    7676858,
    7676894,
    7676919,
    7676938,
    7676954,
    7676963,
    7677037,
    7677041,
    7677061,
    7677066,
    7677128,
    7677214,
    7677291,
    7677320,
    7677328,
    7677355,
    7677386,
    7677390,
    7677414,
    7677417,
    7677422,
    7677439,
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
