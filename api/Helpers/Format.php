<?php

/**
 * Helper Class To Provide Formatting & Conversion Related Functions
 * @author SAIF UD DIN
 *
 */
class Format
{
	
	

    /**
     * Function To Convert Seconds In ("01 hour 34 mins") Format
     *
     * @param INT $sec
     */
    public static function SecondsToHHMMSS($sec)
    {
        if ($sec) {
            $hms = NULL;
            $hours = intval($sec / 3600);
            if ($hours)
                $hms = $hours > 1 ? str_pad($hours, 2, "0", STR_PAD_LEFT) . " hours " : str_pad($hours, 2, "0", STR_PAD_LEFT) . " hour ";
            $minutes = intval(($sec / 60) % 60);
            if ($minutes)
                $hms .= $minutes > 1 ? str_pad($minutes, 2, "0", STR_PAD_LEFT) . " mins" : str_pad($minutes, 2, "0", STR_PAD_LEFT) . " min";
            // $seconds = intval ( $sec % 60 );
            // if ($seconds)
            // $hms .= $seconds > 1 ? str_pad ( $seconds, 2, "0", STR_PAD_LEFT ) . " secs" : str_pad ( $seconds, 2, "0", STR_PAD_LEFT ) . " sec";
            
            return $hms;
        } else {
            return NULL;
        }
    }

    /**
     * Function To Convert DateTime In ("01 Aug 2016") Format
     *
     * @param STRING $DateTime
     * @return STRING
     */
    public static function getDate($DateTime)
    {
        if ($DateTime) {
            $Date = new DateTime($DateTime);
            return $Date->format('d M Y');
        } else {
            return NULL;
        }
    }

    /**
     * Function To Convert Age In DOB
     *
     * @param STRING $DateTime
     * @return STRING
     */
    public static function getDOBFromAge($Age)
    {
        return date('Y-m-d', strtotime($Age . ' years ago'));
    }
	/**
     * Function GET mobile no only start with 03,+92,0092
     *
     * @param STRING $DateTime
     * @return STRING
     */
    public static function mobileNOformat($mobilenumber)
    {
        /*$prfixs=substr($mobilenumber, 0, 4);
        $prfix=substr($mobilenumber, 0, 2);
        if (preg_match_all('[^\+92]', $mobilenumber)) {
            return 1;
        }elseif ($prfixs=='0092') {
            return 1;
        }elseif($prfix=='03')
        {
            return 1;
        }else {
            return 0;
        }*/
		$prfixs=substr($mobilenumber, 0, 4);
        $prfix=substr($mobilenumber, 0, 2);
        //echo $prfix;die;
        $preg_match_all=preg_match_all('[^\+92]', $mobilenumber);
        $OperatorPrefixes = array(
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
            "310",
            "311",
            "312",
            "313",
            "314",
            "315",
            "316",
            "317",
            '333',
            '334',
            '332',
            '331',
            '335',
            '336',
            '320',
            '321',
            '322',
            '323',
            '324',
            '325',
            '326',
            '327',
            '328',
            '329',
        );
        if ($preg_match_all===1) {
            //echo 'preg'.'<br>';
            $mobilenumber = ltrim($mobilenumber, '+92');
            $prefix=substr($mobilenumber, 0, 3);
            $zero=substr($prefix, 0, 1);
            if($zero==0){
                return 0;
            }else{
                //echo $zero.'<br>';
                //echo $prefix.'<br>';
                if(in_array($prefix, $OperatorPrefixes)){
                    return 1;
                }else{
                    return 0;
                }
            }
        }elseif ($prfixs=='0092') {
            $wrongNo=substr($mobilenumber, 0, 5);
            if($wrongNo=='00920'){
                return 0;
            }else{
                $mobilenumber = ltrim($mobilenumber, '0092');
                $prefix=substr($mobilenumber, 0, 3);
                //echo $mobilenumber;
                if(in_array($prefix, $OperatorPrefixes)){
                    return 1;
                }else{
                    return 0;
                }
            }
        }elseif($prfix=='03'){
            //echo 'prfix'.'<br>';
            $mobilenumber = ltrim($mobilenumber, '0');
            $prefix=substr($mobilenumber, 0, 3);
            if(in_array($prefix, $OperatorPrefixes)){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }
    /**
     * General Function To Format Response Data
     *
     * @param ARRAY $Results
     */
    public static function formatResponseData(&$Results, $Type = null)
    {
        foreach ($Results as $key => $row) {
            if (isset($row['TabId']))
                $Results[$key]['TabId'] = (int) $row['TabId'];
			if (isset($row['SectionTabId']))
                $Results[$key]['SectionTabId'] = (int) $row['SectionTabId'];			
            if (isset($row['TabVideoCategoryId']))
                $Results[$key]['TabVideoCategoryId'] = (int) $row['TabVideoCategoryId'];
            if (isset($row['TabCarouselTotalColumns']))
                $Results[$key]['TabCarouselTotalColumns'] = (int) $row['TabCarouselTotalColumns'];
            if (isset($row['SectionId']))
                $Results[$key]['SectionId'] = (int) $row['SectionId'];
			if (isset($row['SectionVCId']))
                $Results[$key]['SectionVCId'] = (int) $row['SectionVCId'];			
            if (isset($row['SectionMoreType']))
                $Results[$key]['SectionMoreType'] = (int) $row['SectionMoreType'];
            if (isset($row['SectionMoreEntityId']))
                $Results[$key]['SectionMoreEntityId'] = (int) $row['SectionMoreEntityId'];
            if (isset($row['VideoEntityId']))
                $Results[$key]['VideoEntityId'] = (int) $row['VideoEntityId'];
            if (isset($row['VideoCategory']))
                $Results[$key]['VideoCategory'] = (int) $row['VideoCategory'];
            if (isset($row['VideoParentCategory']))
                $Results[$key]['VideoParentCategory'] = (int) $row['VideoParentCategory'];
            if (isset($row['VideoPackageId']))
                $Results[$key]['VideoPackageId'] = (int) $row['VideoPackageId'];
            if (isset($row['VideoTotalViews']))
                $Results[$key]['VideoTotalViews'] = (int) $row['VideoTotalViews'];
            if (isset($row['VideoTabId']))
                $Results[$key]['VideoTabId'] = (int) $row['VideoTabId'];
            if (isset($row['CategoryId']))
                $Results[$key]['CategoryId'] = (int) $row['CategoryId'];
			if (isset($row['IsExpiredPackage']))
                $Results[$key]['IsExpiredPackage'] = $row['IsExpiredPackage'] == '0' ? true : false;
			if (isset($row['CategoryIsOnline']))
                $Results[$key]['CategoryIsOnline'] = $row['CategoryIsOnline'] != '0' ? true : false;
			if (isset($row['IsCategories']))
                $Results[$key]['IsCategories'] = $row['IsCategories'] != '0' ? true : false;    
			if (isset($row['PackageId']))
                $Results[$key]['PackageId'] = (int) $row['PackageId'];            
            if (isset($row['PackageProduct']))
                $Results[$key]['PackageProduct'] = (int) $row['PackageProduct'];            
            if (isset($row['PackagePrice']))
                $Results[$key]['PackagePrice'] = (int) $row['PackagePrice'];
			if (isset($row['PackageIsFree']))
                $Results[$key]['PackageIsFree'] = $row['PackageIsFree'] != '0' ? true : false;
			if (isset($row['IsRadio']))
                $Results[$key]['IsRadio'] = $row['IsRadio'] != '0' ? true : false;			
			if (isset($row['OtpBannerId']))
                $Results[$key]['OtpBannerId'] = (int) $row['OtpBannerId'];        
			if (isset($row['MoblinkAppSettingShowPlayerScreenBanner']))
                $Results[$key]['MoblinkAppSettingShowPlayerScreenBanner'] = $row['MoblinkAppSettingShowPlayerScreenBanner'] != '0' ? true : false;
			if (isset($row['MoblinkAppSettingShowFeaturedScreenBanner']))
                $Results[$key]['MoblinkAppSettingShowFeaturedScreenBanner'] = $row['MoblinkAppSettingShowFeaturedScreenBanner'] != '0' ? true : false;
			if (isset($row['MoblinkAppSettingShowDetailScreenBanner']))
                $Results[$key]['MoblinkAppSettingShowDetailScreenBanner'] = $row['MoblinkAppSettingShowDetailScreenBanner'] != '0' ? true : false;			
			if (isset($row['OtpBannerIsOnline']))
                $Results[$key]['OtpBannerIsOnline'] = $row['OtpBannerIsOnline'] != '0' ? true : false;
			if (isset($row['IsAllowedInternationally']))
                $Results[$key]['IsAllowedInternationally'] = $row['IsAllowedInternationally'] != '0' ? true : false;
			if (isset($row['OptBannerIsVideo']))
                $Results[$key]['OptBannerIsVideo'] = $row['OptBannerIsVideo'] != '0' ? true : false;	
			if (isset($row['VoDCategoryId']))
                $Results[$key]['VoDCategoryId'] = (int) $row['VoDCategoryId'];
            if (isset($row['VODTabId']))
                $Results[$key]['VODTabId'] = (int) $row['VODTabId'];
            if (isset($row['VODCategoryId']))
                $Results[$key]['VODCategoryId'] = (int) $row['VODCategoryId'];
            if (isset($row['VideoCategoryId']))
                $Results[$key]['VideoCategoryId'] = (int) $row['VideoCategoryId'];
            if (isset($row['VideoEpisodeNo']))
                $Results[$key]['VideoEpisodeNo'] = (int) $row['VideoEpisodeNo'];
            if (isset($row['VODCategoryTotalVideos']))
                $Results[$key]['VODCategoryTotalVideos'] = (int) $row['VODCategoryTotalVideos'];
            if (isset($row['VODParentCategoryId']))
                $Results[$key]['VODParentCategoryId'] = (int) $row['VODParentCategoryId'];
            if (isset($row['VideoChannelId']))
                $Results[$key]['VideoChannelId'] = (int) $row['VideoChannelId'];
            if (isset($row['VideoSeasonNo']))
                $Results[$key]['VideoSeasonNo'] = (int) $row['VideoSeasonNo'];
            if (isset($row['VideoType']))
                $Results[$key]['VideoType'] = (int) $row['VideoType'];
            if (isset($row['VideoParentCategoryId']))
                $Results[$key]['VideoParentCategoryId'] = (int) $row['VideoParentCategoryId'];
            
            if (isset($row['AppSettingId']))
                $Results[$key]['AppSettingId'] = (int) $row['AppSettingId'];
            if (isset($row['AppSettingIsServiceFree']))
                $Results[$key]['AppSettingIsServiceFree'] = (int) $row['AppSettingIsServiceFree'];
            if (isset($row['AppSettingIsPremiumContentFree']))
                $Results[$key]['AppSettingIsPremiumContentFree'] = (int) $row['AppSettingIsPremiumContentFree'];
            if (isset($row['AppSettingShowAdMobAds']))
                $Results[$key]['AppSettingShowAdMobAds'] = (int) $row['AppSettingShowAdMobAds'];
            if (isset($row['AppSettingShowAdspirationAds']))
                $Results[$key]['AppSettingShowAdspirationAds'] = (int) $row['AppSettingShowAdspirationAds'];
            if (isset($row['AppSettingShowBannerAd']))
                $Results[$key]['AppSettingShowBannerAd'] = (int) $row['AppSettingShowBannerAd'];
            if (isset($row['AppSettingShowPreRoll']))
                $Results[$key]['AppSettingShowPreRoll'] = (int) $row['AppSettingShowPreRoll'];
            if (isset($row['AppSettingShowMidRoll']))
                $Results[$key]['AppSettingShowMidRoll'] = (int) $row['AppSettingShowMidRoll'];
            if (isset($row['AppSettingShowPostRoll']))
                $Results[$key]['AppSettingShowPostRoll'] = (int) $row['AppSettingShowPostRoll'];
            if (isset($row['AppSettingShowPostRollAttempts']))
                $Results[$key]['AppSettingShowPostRollAttempts'] = (int) $row['AppSettingShowPostRollAttempts'];
            if (isset($row['AppSettingShowBannerOnPlayer']))
                $Results[$key]['AppSettingShowBannerOnPlayer'] = (int) $row['AppSettingShowBannerOnPlayer'];
            if (isset($row['AppSettingShowInterstitial']))
                $Results[$key]['AppSettingShowInterstitial'] = (int) $row['AppSettingShowInterstitial'];
            if (isset($row['AppSettingInterstitialRefreshRate']))
                $Results[$key]['AppSettingInterstitialRefreshRate'] = (int) $row['AppSettingInterstitialRefreshRate'];
            if (isset($row['AppSettingIsAccountVerificationNeeded']))
                $Results[$key]['AppSettingIsAccountVerificationNeeded'] = (int) $row['AppSettingIsAccountVerificationNeeded'];
            if (isset($row['AppSettingIsRegisterationNeeded']))
                $Results[$key]['AppSettingIsRegisterationNeeded'] = (int) $row['AppSettingIsRegisterationNeeded'];
            if (isset($row['AppSettingPlayerBannerRefreshRate']))
                $Results[$key]['AppSettingPlayerBannerRefreshRate'] = (int) $row['AppSettingPlayerBannerRefreshRate'];
            if (isset($row['AppSettingPlayerBannerVisibilityTime']))
                $Results[$key]['AppSettingPlayerBannerVisibilityTime'] = (int) $row['AppSettingPlayerBannerVisibilityTime'];
            if (isset($row['AppSettingNonPlayerBannerRefreshRate']))
                $Results[$key]['AppSettingNonPlayerBannerRefreshRate'] = (int) $row['AppSettingNonPlayerBannerRefreshRate'];
            if (isset($row['AppSettingShowPreRollAfterDuration']))
                $Results[$key]['AppSettingShowPreRollAfterDuration'] = (int) $row['AppSettingShowPreRollAfterDuration'];
            if (isset($row['AppSettingShowPreRollTillDuration']))
                $Results[$key]['AppSettingShowPreRollTillDuration'] = (int) $row['AppSettingShowPreRollTillDuration'];
            if (isset($row['AppSettingPopupTime']))
                $Results[$key]['AppSettingPopupTime'] = (int) $row['AppSettingPopupTime'];
            if (isset($row['AppSettingIsFeaturedPageRandom']))
                $Results[$key]['AppSettingIsFeaturedPageRandom'] = $row['AppSettingIsFeaturedPageRandom'] != '0' ? true : false;
            if (isset($row['AppSettingIsRefreshClickBased']))
                $Results[$key]['AppSettingIsRefreshClickBased'] = $row['AppSettingIsRefreshClickBased'] != '0' ? true : false;
            if (isset($row['AppSettingShowAdx']))
                $Results[$key]['AppSettingShowAdx'] = $row['AppSettingShowAdx'] != '0' ? true : false;
            if (isset($row['AppSettingShowFeaturedScreenBanner']))
                $Results[$key]['AppSettingShowFeaturedScreenBanner'] = $row['AppSettingShowFeaturedScreenBanner'] != '0' ? true : false;
            if (isset($row['AppSettingShowDetailScreenBanner']))
                $Results[$key]['AppSettingShowDetailScreenBanner'] = $row['AppSettingShowDetailScreenBanner'] != '0' ? true : false;
            if (isset($row['AppSettingShowPlayerScreenBanner']))
                $Results[$key]['AppSettingShowPlayerScreenBanner'] = $row['AppSettingShowPlayerScreenBanner'] != '0' ? true : false;
            
            if (isset($row['AdvertisementAgencyId']))
                $Results[$key]['AdvertisementAgencyId'] = (int) $row['AdvertisementAgencyId'];
            if (isset($row['AdvertisementClientId']))
                $Results[$key]['AdvertisementClientId'] = (int) $row['AdvertisementClientId'];
            if (isset($row['AdvertisementCampaignId']))
                $Results[$key]['AdvertisementCampaignId'] = (int) $row['AdvertisementCampaignId'];
            if (isset($row['AdvertisementId']))
                $Results[$key]['AdvertisementId'] = (int) $row['AdvertisementId'];
            if (isset($row['AdvertisementViewsDone']))
                $Results[$key]['AdvertisementViewsDone'] = (int) $row['AdvertisementViewsDone'];
            if (isset($row['AdvertisementTargetViews']))
                $Results[$key]['AdvertisementTargetViews'] = (int) $row['AdvertisementTargetViews'];
            if (isset($row['AdvertisementMinAdsPerDay']))
                $Results[$key]['AdvertisementMinAdsPerDay'] = (int) $row['AdvertisementMinAdsPerDay'];
            if (isset($row['AdvertisementTodayCount']))
                $Results[$key]['AdvertisementTodayCount'] = (int) $row['AdvertisementTodayCount'];
            if (isset($row['AdvertisementCpmRate']))
                $Results[$key]['AdvertisementCpmRate'] = (int) $row['AdvertisementCpmRate'];
            if (isset($row['AdvertisementShowSkipAfter']))
                $Results[$key]['AdvertisementShowSkipAfter'] = (int) $row['AdvertisementShowSkipAfter'];
            if (isset($row['AdvertisementTypeId']))
                $Results[$key]['AdvertisementTypeId'] = (int) $row['AdvertisementTypeId'];
            if (isset($row['RandomPriority']))
                $Results[$key]['RandomPriority'] = (int) $row['RandomPriority'];
            
            if (isset($row['TempUserId']))
                $Results[$key]['TempUserId'] = (int) $row['TempUserId'];
            if (isset($row['TempUserIsRestricted']))
                $Results[$key]['TempUserIsRestricted'] = (int) $row['TempUserIsRestricted'];
            if (isset($row['TempUserTrialUsed']))
                $Results[$key]['TempUserTrialUsed'] = (int) $row['TempUserTrialUsed'];
            if (isset($row['UserId']))
                $Results[$key]['UserId'] = (int) $row['UserId'];
            if (isset($row['UsersId']))
                $Results[$key]['UsersId'] = (int) $row['UsersId'];
            if (isset($row['UserIsFree']))
                $Results[$key]['UserIsFree'] = (int) $row['UserIsFree'];
            if (isset($row['UserIsActive']))
                $Results[$key]['UserIsActive'] = (int) $row['UserIsActive'];
            if (isset($row['UserIsPublisher']))
                $Results[$key]['UserIsPublisher'] = (int) $row['UserIsPublisher'];
            if (isset($row['UserTypeId']))
                $Results[$key]['UserTypeId'] = (int) $row['UserTypeId'];
            if (isset($row['UserIsPassChanged']))
                $Results[$key]['UserIsPassChanged'] = (int) $row['UserIsPassChanged'];
            if (isset($row['UserSubscriptionPackageId']))
                $Results[$key]['UserSubscriptionPackageId'] = (int) $row['UserSubscriptionPackageId'];
            if (isset($row['UserSubscriptionMaxConcurrentConnections']))
                $Results[$key]['UserSubscriptionMaxConcurrentConnections'] = (int) $row['UserSubscriptionMaxConcurrentConnections'];
            if (isset($row['UserSubscriptionAutoRenew']))
                $Results[$key]['UserSubscriptionAutoRenew'] = (int) $row['UserSubscriptionAutoRenew'];
            if (isset($row['UserSubscriptionId']))
                $Results[$key]['UserSubscriptionId'] = (int) $row['UserSubscriptionId'];
            if (isset($row['UserSubscriptionUserId']))
                $Results[$key]['UserSubscriptionUserId'] = (int) $row['UserSubscriptionUserId'];
            if (isset($row['UserSubscriptionIsTempUser']))
                $Results[$key]['UserSubscriptionIsTempUser'] = (int) $row['UserSubscriptionIsTempUser'];
            if (isset($row['UserPackageType']))
                $Results[$key]['UserPackageType'] = (int) $row['UserPackageType'];
			if (isset($row['UserActivePackageType']))
                $Results[$key]['UserActivePackageType'] = (int) $row['UserActivePackageType'];
            if (isset($row['UserPackageIsRecurring']))
                $Results[$key]['UserPackageIsRecurring'] = (int) $row['UserPackageIsRecurring'];
            if (isset($row['UserPackageType']))
                $Results[$key]['UserPackageType'] = (int) $row['UserPackageType'];
            if (isset($row['UserTVPackageIsRecurring']))
                $Results[$key]['UserTVPackageIsRecurring'] = (int) $row['UserTVPackageIsRecurring'];
            
            if (isset($row['SeasonCategoryId']))
                $Results[$key]['SeasonCategoryId'] = (int) $row['SeasonCategoryId'];
            if (isset($row['SeasonNo']))
                $Results[$key]['SeasonNo'] = (int) $row['SeasonNo'];
            
			if (isset($row['SeasonId']))
                $Results[$key]['SeasonId'] = (int) $row['SeasonId'];
			
			if (isset($row['IsSeason']))
                $Results[$key]['IsSeason'] = $row['IsSeason'] != '0' ? true : false;
			
            if (isset($row['FOUND_ROWS()']))
                $Results[$key]['FOUND_ROWS()'] = (int) $row['FOUND_ROWS()'];
            
            if (isset($row['IsVideoOnline']))
                $Results[$key]['IsVideoOnline'] = $row['IsVideoOnline'] != '0' ? true : false;
            if (isset($row['IsVideoChannel']))
                $Results[$key]['IsVideoChannel'] = $row['IsVideoChannel'] != '0' ? true : false;
			if (isset($row['IsChannel']))
                $Results[$key]['IsChannel'] = $row['IsChannel'] != '0' ? true : false;
            if (isset($row['IsVideoFree']))
                $Results[$key]['IsVideoFree'] = $row['IsVideoFree'] != '0' ? true : false;
			if ($row['IsVideoFree']==1){
				unset($Results[$key]['PackageName']);   
				unset($Results[$key]['PackageIsFree']);   
				unset($Results[$key]['PackageProduct']);  	
				unset($Results[$key]['PackagePrice']);  
			}
			if ($row['IsCategoryFree']==1){
				unset($Results[$key]['PackageName']);   
				unset($Results[$key]['PackageIsFree']);   
				unset($Results[$key]['PackageProduct']);  	
				unset($Results[$key]['PackagePrice']);	
			}
			
			if($Results[$key]['PackageName']=='Local Packages')
			{
				$Results[$key]['PackageName']="Premium";
			}
			
			
			if (isset($row['IsCategoryFree']))
                $Results[$key]['IsCategoryFree'] = $row['IsCategoryFree'] != '0' ? true : false;
            if (isset($row['IsVideoDVR']))
                $Results[$key]['IsVideoDVR'] = $row['IsVideoDVR'] != '0' ? true : false;
            if (isset($row['VideoIsAllowedInternationally']))
                $Results[$key]['VideoIsAllowedInternationally'] = $row['VideoIsAllowedInternationally'] != '0' ? true : false;
            if (isset($row['VideoAllowCountryCodeList']))
                $Results[$key]['VideoAllowCountryCodeList'] = $row['VideoAllowCountryCodeList'] != '0' ? true : false;
            if (isset($row['VODCategoryHasSeasons']))
                $Results[$key]['VODCategoryHasSeasons'] = $row['VODCategoryHasSeasons'] != '0' ? true : false;
            if (isset($row['IsSectionMore']))
                $Results[$key]['IsSectionMore'] = $row['IsSectionMore'] != '0' ? true : false;
			if (isset($row['ZongBucket']))
                $Results[$key]['ZongBucket'] = $row['ZongBucket'] != '0' ? "onZong" : "offZong";            
             if (isset($row['MobilinkBucket']))
                $Results[$key]['MobilinkBucket'] = $row['MobilinkBucket'] != '0' ? "onMobilink" : "offMobilink";               
             if (isset($row['TelenorBucket']))
                $Results[$key]['TelenorBucket'] = $row['TelenorBucket'] != '0' ? "onTelenor" : "offTelenor";   
            if (isset($row['VODCategoryIsOnline']))
                $Results[$key]['VODCategoryIsOnline'] = $row['VODCategoryIsOnline'] != '0' ? true : false;
            if (isset($row['VideoCategoryIsOnline']))
                $Results[$key]['VideoCategoryIsOnline'] = $row['VideoCategoryIsOnline'] != '0' ? true : false;
            if (isset($row['IsPosterVideo']))
                $Results[$key]['IsPosterVideo'] = $row['IsPosterVideo'] != '0' ? true : false;
            if (isset($row['ShowFakeLogo']))
                $Results[$key]['ShowFakeLogo'] = $row['ShowFakeLogo'] != '0' ? true : false;
            if (isset($row['SeasonIsOnline']))
                $Results[$key]['SeasonIsOnline'] = $row['SeasonIsOnline'] != '0' ? true : false;
			
            if (isset($row['IsAllowSkipAd']))
                $Results[$key]['IsAllowSkipAd'] = $row['IsAllowSkipAd'] != '0' ? true : false;
            if (isset($row['IsAllowOnNonPlayer']))
                $Results[$key]['IsAllowOnNonPlayer'] = $row['IsAllowOnNonPlayer'] != '0' ? true : false;
            if (isset($row['IsVast']))
                $Results[$key]['IsVast'] = $row['IsVast'] != '0' ? true : false;
            if (isset($row['IsJavascriptTag']))
                $Results[$key]['IsJavascriptTag'] = $row['IsJavascriptTag'] != '0' ? true : false;
            
			
            if (isset($row['UserPaymentStatus']))
                $Results[$key]['UserPaymentStatus'] = (int) $row['UserPaymentStatus'];
            if (isset($row['UserPaymentIsRecurring']))
                $Results[$key]['UserPaymentIsRecurring'] = $row['UserPaymentIsRecurring'] != '0' ? true : false;
            if (isset($row['UserSubscriptionIsExpired']))
                $Results[$key]['UserSubscriptionIsExpired'] = $row['UserSubscriptionIsExpired'] != '0' ? true : false;
            
            if (isset($row['AppSettingForceUpdateChannels']))
                $Results[$key]['AppSettingForceUpdateChannels'] = $row['AppSettingForceUpdateChannels'] != '0' ? true : false;
            if (isset($row['AppSettingForceUpdateMovies']))
                $Results[$key]['AppSettingForceUpdateMovies'] = $row['AppSettingForceUpdateMovies'] != '0' ? true : false;
            if (isset($row['AppSettingForceUpdateCatchupTV']))
                $Results[$key]['AppSettingForceUpdateCatchupTV'] = $row['AppSettingForceUpdateCatchupTV'] != '0' ? true : false;
            
            if (isset($row['VideoDuration'])) {
                if (isset($Type)) {
                    if ($Type === 1) {
                        $Results[$key]['VideoDuration'] = $row['VideoDuration'];
                    } else {
                        $Results[$key]['VideoDuration'] = $row['VideoDuration'];
                    }
                } else {
                    $Results[$key]['VideoDuration'] = $row['VideoDuration'];
                }
            }
            if (isset($row['VideoAddedDate'])) {
                if (isset($Type)) {
                    if ($Type === 1) {
                        $Results[$key]['VideoAddedDate'] = $row['VideoAddedDate'];
                    } else {
                        $Results[$key]['VideoAddedDate'] = Format::getDate($row['VideoAddedDate']);
                    }
                } else {
                    $Results[$key]['VideoAddedDate'] = Format::getDate($row['VideoAddedDate']);
                }
            }
            if (isset($row['VODCategoryAddedDate'])) {
                if (isset($Type)) {
                    if ($Type === 1) {
                        $Results[$key]['VODCategoryAddedDate'] = $row['VODCategoryAddedDate'];
                    } else {
                        $Results[$key]['VODCategoryAddedDate'] = Format::getDate($row['VODCategoryAddedDate']);
                    }
                } else {
                    $Results[$key]['VODCategoryAddedDate'] = Format::getDate($row['VODCategoryAddedDate']);
                }
            }
        }
    }
}