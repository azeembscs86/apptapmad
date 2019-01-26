<?php
// TODO : Implement Authentication
// TODO : Request Logging
// TODO : Add Server Side Validation
date_default_timezone_set ( 'Asia/Karachi' );

require 'vendor/autoload.php';

// use \Psr\Http\Message\ServerRequestInterface as Request;
// use \Psr\Http\Message\ResponseInterface as Response;
// use Slim\Middleware\JwtAuthentication;

$app = new \Slim\App ( [ 
		'settings' => [ 
				'displayErrorDetails' => false 
		] 
] );

// Including Config PHP Files.
$routeFiles = ( array ) glob ( 'Config/*.php' );
foreach ( $routeFiles as $routeFile ) {
	require $routeFile;
}

// Including Services PHP Files.
$routeFiles = ( array ) glob ( 'Services/*.php' );
foreach ( $routeFiles as $routeFile ) {
	require $routeFile;
}

// User Related Services Call
$app->post ( '/NewsignUpORSignInUsingMobileNo', '\NewSignInService:NewsignUpORSignInUsingMobileNo' );
$app->post ( '/NewsignUpORSignInUsingACR', '\NewSignInService:NewsignUpORSignInUsingACR' );
$app->post ( '/signUpORSignInUsingACR', '\UserServices:signUpORSignInUsingACR' );
$app->post ( '/signUpORSignInUsingMobileNo', '\UserServices:signUpORSignInUsingMobileNo' );
$app->post ( '/signUpORSignInUsingMobileNoNew', '\UsersService:signUpORSignInUsingMobileNoNew' );
$app->post ( '/signUpORSignInUsingMobileNo1', '\UserServices:signUpORSignInUsingMobileNo1' );
$app->post ( '/signUpORSignInUsingMobileNumber', '\SignInUserServices:signUpORSignInUsingMobileNumber' );
$app->post ( '/signUpORSignInUsingACRCode', '\SignInUserServices:signUpORSignInUsingACRCode' );
$app->get ( '/checkAndRegisterTempUser/{Version}/{Language}/{Platform}/{DeviceID}/{MACAddress}', '\UserServices:registerTempUser' );
$app->get ( '/signUp/{Version}/{Language}/{Platform}/{MobileNo}/{Username}/{Password}[/{IsRecurring}]', '\UserServices:signUp' );
$app->post ( '/signUp', '\UserServices:signUpPOST' );
$app->post ( '/signUpUsingFacebook', '\UserServices:signUpUsingFacebook' );
$app->post ( '/getAllUsersAgainstMobileNo/{Version}/{Language}/{Platform}', '\UserServices:getAllUsersAgainstMobileNo' );
$app->get ( '/getUserIdAgainstUsername/{Version}/{Language}/{Platform}/{Username}', '\UserServices:getUserIdAgainstUsername' );
$app->get ( '/forgetPasswordEmail/{Version}/{Language}/{Platform}/{Username}', '\UserServices:forgetPasswordEmail' );
$app->get ( '/resetPassword/{Version}/{Language}/{Platform}/{MobileNo}/{CurrentPassword}/{NewPassword}/{ConfirmPassword}', '\UserServices:resetPassword' );
$app->get ( '/logIn/{Version}/{Language}/{Platform}/{Username}/{Password}', '\UserServices:logIn' );
$app->post ( '/logIn', '\UserServices:logInPOST' );
$app->get ( '/logInUsingFacebook/{Version}/{Language}/{Platform}/{FacebookId}', '\UserServices:logInUsingFacebook' );
$app->get ( '/updateTrialPeriod/{Version}/{Language}/{Platform}/{DeviceID}/{MACAddress}', '\UserServices:updateTrialPeriod' );
$app->get ( '/saveOrUpdateDeviceInfo/{Version}/{Language}/{Platform}/{DeviceID}/{DeviceMAC}/{DeviceToken}/{DeviceBrand}/{DeviceName}/{DeviceModel}/{DeviceManufacturer}/{DeviceProduct}', '\UserServices:saveOrUpdateDeviceInfo' );
$app->post ( '/editUserProfile', '\UserServices:editUserProfile' );
$app->get ( '/forgetPasswordMobile/{Version}/{Language}/{Platform}/{MobileNo}', '\UserServices:forgetPasswordMobile' );
$app->get ( '/sendPushNotifications/{Version}/{Language}/{Platform}/{MobileNo}', '\UserServices:sendPushNotifications' );
$app->get ( '/activateUserAccount/{Version}/{Language}/{Platform}/{Mobile}/{ActivationCode}', '\UserServices:activateUserAccount' );
$app->get ( '/sendActivationCodeMobile/{Version}/{Language}/{Platform}/{Mobile}', '\UserServices:sendActivationCodeMobile' );
$app->get ( '/saveUserMobileNo/{Version}/{Language}/{Platform}/{MobileNo}', '\UserServices:saveUserMobileNo' );
$app->get ( '/getUserSubscription/{Version}/{Language}/{Platform}/{UserId}', '\UserServices:getUserSubscription' );
$app->post ( '/validateUser/{Version}/{Language}/{Platform}', '\UserServices:validateUser' );
$app->post ( '/saveUserActivity/{Version}/{Language}/{Platform}', '\UserServices:saveUserActivity' );
$app->post ( '/sendOTP/{Version}/{Language}/{Platform}', '\UserServices:sendOTP' );
$app->post ( '/verifyOTP/{Version}/{Language}/{Platform}', '\UserServices:verifyOTP' );

// User Related Services Call
$app->get ( '/sendSMS/{Version}/{Language}/{Platform}/{Mobile}', '\SMSServices:sendSMS' );

// App Settings Related Services Call
$app->get ( '/getHomePageDetail/{Version}/{Language}/{Platform}', '\AppSettings:getHomePageDetail' );
$app->get ( '/getHomePageDetail2/{Version}/{Language}/{Platform}', '\AppSettings:getHomePageDetail2' );
$app->get ( '/getHomePageDetail3/{Version}/{Language}/{Platform}', '\AppSettings:getHomePageDetail3' );
$app->get ( '/getHomePageDetailWithPackages/{Version}/{Language}/{Platform}', '\AppSettings:getHomePageDetailWithPackages' );
$app->get ( '/getHomePageWithBucketPackages/{Version}/{Language}/{Platform}', '\AppSettings:getHomePageWithBucketPackages' );
$app->get ( '/getHomePageDetailWithPackages2/{Version}/{Language}/{Platform}', '\AppSettings:getHomePageDetailWithPackages2' );
$app->get ( '/getHomeDetailWithPackagesMobilinkAdds/{Version}/{Language}/{Platform}', '\AppSettings:getHomeDetailWithPackagesMobilinkAdds' );
$app->get ( '/getUserIpAddress', '\AppSettings:getUserIpAddress' );
$app->get ( '/getSeasonVodByCategoryId/{Version}/{Language}/{Platform}/{CategoryId}', '\AppSettings:getSeasonVodByCategoryId' ); //get Season and Video By Category Id
$app->get ( '/getVodsCategoryIdWithPackages/{Version}/{Language}/{Platform}/{CategoryId}', '\AppSettings:getVodsCategoryIdWithPackages' ); //get Season and Video By Category Id

$app->get ( '/getContentDetailWithAd/{Version}/{Language}/{Platform}/{VideoType}/{VideoEntityId}/{PackageId}', '\AppSettings:getContentDetailWithAd' );
$app->get ( '/getContentDetailWithAd/{Version}/{Language}/{Platform}/{VideoType}/{VideoEntityId}', '\AppSettings:getContentDetailWithAd' );
$app->get ( '/getRelatedContent/{Version}/{Language}/{Platform}/{IsChannel}/{ChannelOrVODId}', '\AppSettings:getRelatedContent' );
$app->get ( '/getSectionAndDetail/{Version}/{Language}/{Platform}', '\AppSettings:getSectionAndDetail' );
$app->get ( '/getSectionMoreInfo/{Version}/{Language}/{Platform}/{SectionId}/{OffSet}', '\AppSettings:getSectionMoreInfo' );
$app->get ( '/getAppSettings/{Version}/{Language}/{Platform}', '\AppSettings:getAppSettings' );
$app->get ( '/updateAppDownload/{Version}/{Language}/{Platform}', '\AppSettings:updateAppDownload' );
$app->get ( '/getCategoriesSectionMoreInfo/{Version}/{Language}/{Platform}/{SectionId}/{IsCategory}/{OffSet}', '\AppSettings:getCategoriesSectionMoreInfo' );
$app->get ( '/updateUserPackageByUserId', '\AppSettings:updateUserPackageByUserId' );

// Channels And VOD Related Services Call
$app->get ( '/getAllChannelsWithCategories/{Version}/{Language}/{Platform}[/{DateTime}]', '\ChannelServices:getAllChannelsWithCategories' );
$app->get ( '/getAllChannelsWithCategories2/{Version}/{Language}/{Platform}[/{DateTime}]', '\ChannelServices:getAllChannelsWithCategories2' );
$app->get ( '/searchAllChannelsWithCategories/{Version}/{Language}/{Platform}/{SearchString}/{OffSet}', '\ChannelServices:searchAllChannelsWithCategories' );
$app->get ( '/getAllVODCategories/{Version}/{Language}/{Platform}[/{DateTime}]', '\ChannelServices:getAllVODCategories' );
$app->get ( '/getVODsByCategory/{Version}/{Language}/{Platform}/{CategoryId}/{OffSet}', '\ChannelServices:getVODsByCategory' );
$app->get ( '/getVODsBySeason/{Version}/{Language}/{Platform}/{CategoryId}/{SeasonNo}/{OffSet}', '\ChannelServices:getVODsBySeason' );
$app->get ( '/getAllMoviesCategories/{Version}/{Language}/{Platform}', '\ChannelServices:getAllMoviesCategories' );
$app->get ( '/getMoviesByCategory/{Version}/{Language}/{Platform}/{CategoryId}/{OffSet}', '\ChannelServices:getVODsByCategory' );
$app->get ( '/getRelatedChannelsOrVODs/{Version}/{Language}/{Platform}/{ChannelOrVODId}/{IsChannel}', '\ChannelServices:getRelatedChannelsOrVODs' );
$app->get ( '/getRelatedMoreInfo/{Version}/{Language}/{Platform}/{CategoryId}/{IsChannel}/{OffSet}', '\ChannelServices:getRelatedMoreInfo' );
$app->get ( '/getChannelOrVODUrl/{Version}/{Language}/{Platform}/{ChannelOrVODId}/{IsChannel}', '\ChannelServices:getChannelOrVODUrl' );
$app->get ( '/updateChannelOrVODViews/{Version}/{Language}/{Platform}/{IsChannel}/{ChannelOrVODId}', '\ChannelServices:updateChannelOrVODViews' );
$app->get ( '/getAllVODsWithCategories/{Version}/{Language}/{Platform}', '\ChannelServices:getAllVODsWithCategories' );
$app->get ( '/getAllMoviesWithCategories/{Version}/{Language}/{Platform}[/{DateTime}]', '\ChannelServices:getAllMoviesWithCategories' );
$app->get ( '/getMoviesByPage/{Version}/{Language}/{Platform}/{CategoryId}/{PageNumber}', '\ChannelServices:getVODsByPage' );
$app->get ( '/getVODsByPage/{Version}/{Language}/{Platform}/{CategoryId}/{PageNumber}', '\ChannelServices:getVODsByPage' );
$app->get ( '/searchInAllContent/{Version}/{Language}/{Platform}/{SearchString}/{PageNumber}', '\ChannelServices:searchInAllContent' );
$app->get ( '/getVODsByCategoryWithOutLimit/{Version}/{Language}/{Platform}/{CategoryId}[/{DateTime}]', '\ChannelServices:getVODsByCategoryWithOutLimit' );
$app->get ( '/getVideoDetail/{Version}/{Language}/{Platform}/{VideoId}/{IsChannel}', '\ChannelServices:getVideoDetail' );
$app->get ( '/getChannelsRssFeed/{Version}/{Language}/{Platform}', '\ChannelServices:getChannelsRssFeed' );
$app->get ( '/getIphoneBucketStatus', '\PaymentServices:getIphoneBucketStatus' );
$app->get ( '/getAndroidBucketStatus', '\PaymentServices:getAndroidBucketStatus' );
$app->get ( '/getBucketStatus', '\PaymentServices:getBucketStatus' );
$app->get ( '/getAndroidBucket', '\PaymentServices:getAndroidBucket' );
$app->get ( '/getIphoneBucket', '\PaymentServices:getIphoneBucket' );


// Advertisement Related Services Call
$app->get ( '/getVODOrChannelURLWithAd/{Version}/{Language}/{Platform}/{IsChannel}/{PackageId}/{ChannelOrVODId}/{Gender}/{Age}/{AdType}', '\AdsServices:getVODOrChannelURLWithAd' );
$app->get ( '/updateAdViewAndStats/{Version}/{Language}/{Platform}/{AgencyId}/{ClientId}/{CampaignId}/{AdId}/{UserId}/{IsTempUser}/{IsChannel}/{PackageId}/{ChannelOrVODId}/{AdType}/{DeviceId}/{Source}/{VideoSourceUrl}/{StatisticType}', '\AdsServices:updateAdViewAndStats' );
$app->get ( '/updateAdClickAndStats/{Version}/{Language}/{Platform}/{AgencyId}/{ClientId}/{CampaignId}/{AdId}/{UserId}/{IsTempUser}/{ChannelOrVODId}/{IsChannel}/{PackageId}/{AdType}/{DeviceType}/{DeviceId}/{Source}/{VideoSourceUrl}/{StatisticType}', '\AdsServices:updateAdClickAndStats' );
$app->get ( '/getAdURL/{Version}/{Language}/{Platform}/{Gender}/{Age}/{AdType}', '\AdsServices:getAdURL' );
$app->get ( '/getVastAd/{Version}/{Language}/{Platform}/{AdID}', '\AdsServices:getVastAd' );
$app->get ( '/getRamadanTiming/{Version}/{Language}/{Platform}', '\AdsServices:getRamadanTiming' );

// Catchup TV Related Services Call
$app->get ( '/getCatchupTVURL/{Version}/{Language}/{Platform}/{VideoEntityId}', '\CatchupTVServices:getCatchupTVURL' );
$app->get ( '/getCatchupTVURLWithAd/{Version}/{Language}/{Platform}/{IsChannel}/{PackageId}/{ChannelOrVODId}/{Gender}/{Age}/{AdType}', '\CatchupTVServices:getCatchupTVURLWithAd' );
$app->get ( '/getCatchupTV/{Version}/{Language}/{Platform}[/{DateTime}]', '\CatchupTVServices:getCatchupTV' );
$app->get ( '/getRelatedCatchup/{Version}/{Language}/{Platform}/{CatchUpCategoryID}/{CatchUpChannelID}', '\CatchupTVServices:getRelatedCatchup' );

// Favorite & Playlist Related Service Call
$app->get ( '/getAllFavouriteList/{Version}/{Language}/{Platform}/{UserID}', '\PlaylistServices:getAllFavouriteList' );
$app->get ( '/getFavouriteListing/{Version}/{Language}/{Platform}/{UserID}/{FavouriteName}', '\PlaylistServices:getFavouriteListing' );
$app->get ( '/addFavouriteListing/{Version}/{Language}/{Platform}/{UserID}/{FavouriteName}/{ContentId}/{IsChannel}', '\PlaylistServices:addFavouriteListing' );
$app->get ( '/deleteFavouriteListing/{Version}/{Language}/{Platform}/{UserID}/{FavouriteName}/{ContentId}/{IsChannel}', '\PlaylistServices:deleteFavouriteListing' );

// Custom Service Call
$app->get ( '/insertStoreData/{Version}/{Language}/{Platform}/{CustomerName}/{CustomerPhone}/{CustomerEmail}/{CustomerAddress}/{CustomerCity}/{CustomerCountry}/{CustomerProduct}/{CustomerProductPrice}', '\CustomServices:insertStoreData' );
$app->post ( '/insertStoreDataPOST', '\CustomServices:insertStoreDataPOST' );
$app->post ( '/sendEmail', '\CustomServices:sendEmail' );
$app->get ( '/getAppStoreData/{Version}/{Language}/{Platform}', '\CustomServices:getAppStoreData' );

// Payment Services
$app->post ( '/getAdyenAvailablePayment', '\PaymentServices:getAdyenAvailablePayment' );
$app->post ( '/savePaymentTransaction', '\PaymentServices:savePaymentTransaction' );
$app->post ( '/successfulPaymentTransaction', '\PaymentServices:successfulPaymentTransaction' );
$app->post ( '/failedPaymentTransaction', '\PaymentServices:failedPaymentTransaction' );
$app->post ( '/unsubscribePaymentTransaction', '\PaymentServices:unsubscribePaymentTransaction' );
$app->post ( '/unsubscribePaymentTransaction2', '\PaymentServices:unsubscribePaymentTransaction2' );
$app->post ( '/getUserPaymentHistory', '\PaymentServices:getUserPaymentHistory' );
$app->post ( '/successfulPaymentTransactionTest', '\PaymentServices:successfulPaymentTransactionTest' );
$app->post ( '/failedPaymentTransactionTest', '\PaymentServices:failedPaymentTransactionTest' );
$app->post ( '/responsePaymentTransactionTest', '\PaymentServices:responsePaymentTransactionTest' );
$app->post ( '/makePaymentTransaction', '\PaymentServices:makePaymentTransaction' );
$app->post ( '/responsePaymentTransaction', '\PaymentServices:responsePaymentTransaction' );
$app->post ( '/failedPaymentTransactions', '\PaymentServices:failedPaymentTransactions' );
$app->post ( '/makePaymentTransactionTest', '\PaymentServices:makePaymentTransactionTest' );
$app->post ( '/makePaymentsTransactions', '\PaymentSolutions:makePaymentsTransactions' );


//---------------------------test routes for payments-----------------------------------//
$app->post ( '/savePaymentTransaction1', '\PaymentServicesTest:savePaymentTransaction' );
$app->post ( '/successfulPaymentTransaction1', '\PaymentServicesTest:successfulPaymentTransaction' );
$app->post ( '/failedPaymentTransaction1', '\PaymentServicesTest:failedPaymentTransaction' );
$app->post ( '/unsubscribePaymentTransaction1', '\PaymentServicesTest:unsubscribePaymentTransaction' );
$app->post ( '/unsubscribePaymentTransaction21', '\PaymentServicesTest:unsubscribePaymentTransaction2' );
$app->post ( '/getUserPaymentHistory1', '\PaymentServicesTest:getUserPaymentHistory' );
$app->post ( '/successfulPaymentTransactionTest1', '\PaymentServicesTest:successfulPaymentTransactionTest' );
$app->post ( '/failedPaymentTransactionTest1', '\PaymentServicesTest:failedPaymentTransactionTest' );
$app->post ( '/responsePaymentTransactionTest1', '\PaymentServicesTest:responsePaymentTransactionTest' );
$app->post ( '/makePaymentTransaction1', '\PaymentServicesTest:makePaymentTransaction' );
$app->post ( '/responsePaymentTransaction1', '\PaymentServicesTest:responsePaymentTransaction' );
$app->post ( '/failedPaymentTransactions1', '\PaymentServicesTest:failedPaymentTransactions' );
$app->post ( '/unsubscribePackagePaymentTransaction', '\PaymentServices:unsubscribePackagePaymentTransaction' );
$app->post ( '/deletePaymentUser', '\PaymentServices:deletePaymentUser' );
$app->get ( '/testQuery', '\AppSettings:testQuery' ); //get Season and Video By Category Id
$app->get ( '/testQuery2', '\AppSettings:testQuery2' ); //get Season and Video By Category Id
$app->get ( '/testQuery3', '\ChannelServices:testQuery3' ); //get Season and Video By Category Id
$app->get ( '/testQuery4', '\ChannelServices:testQuery4' ); //get Season and Video By Category Id


// Reporting Related Service Call
$app->get ( '/getTapmadStats/{Version}/{Language}/{Platform}', '\ReportingServices:getTapmadStats' );
$app->get ( '/getCampaignStats/{Version}/{Language}/{Platform}/{StartDate}/{EndDate}', '\ReportingServices:getCampaignStats' );
$app->get ( '/getCampaignTxIDs/{Version}/{Language}/{Platform}/{StartDate}/{EndDate}', '\ReportingServices:getCampaignTxIDs' );
$app->get ( '/getBucketDetails/{Version}/{Language}/{Platform}/{StartDate}/{EndDate}', '\ReportingServices:getBucketDetails' );

// $app->add(function (Request $request, Response $response, callable $next) {
// $app->log->debug('BEFORE');
// $response = $next($request, $response);
// $app->log->debug('AFTER');

// return $response;
// });

// Authorization Middleware
// TODO : Eliminate HardCoded Strings.
// TODO : Proper Response Format.
// TODO : Declare Variable in Config like secret
// $app->add ( function ($request, $response, $next) use ($container) {
// $headers = $request->getHeaders ();
// if (! isset ( $headers ['HTTP_AUTHORIZATION'] )) {
// return getErrorResponse ( $response, "Access Credentials Not Supplied" );
// } else {
// try {
// $authHeader = $headers ['HTTP_AUTHORIZATION'];
// if (preg_match ( '/Bearer\s+(.*)$/i', $authHeader [0], $matches )) {
// if ($matches [1] === 'GETTOKEN') {
// if (isset ( $headers ['HTTP_DEVICEID'] )) {
// $JWT = new JwtAuthentication ( [
// "secret" => "supersecretkeyyoushouldnotcommittogithub"
// ] );

// $response = $response->withAddedHeader ( 'AllowedToken', $JWT->encodeToken ( $headers ['HTTP_DEVICEID'] ) );
// return $next ( $request, $response );
// } else {
// return getErrorResponse ( $response, "Invalid Token Credentials" );
// }
// } else {
// $JWT = new \Slim\Middleware\JwtAuthentication ( [
// "secure" => false,
// "secret" => "supersecretkeyyoushouldnotcommittogithub",
// "error" => function ($request, $response, $next) {
// return getErrorResponse ( $response, $next ["message"] );
// },
// "callback" => function ($request, $response, $arguments) use ($container) {
// $container ["jwt"] = $arguments ["decoded"];
// }
// ] );
// $JWT->__invoke ( $request, $response, $next );
// }
// } else {
// return getErrorResponse ( $response, "Invalid Token" );
// }

// return $response;
// } catch ( Exception $e ) {
// return $response->write ( json_encode ( $e->getMessage () ) );
// }
// }
// } );

$app->run ();

// /**
// * Function To Get Response For Authorization Middleware
// *
// * @param \Psr\Http\Message\ResponseInterface $Response
// * @param STRING $Message
// */
// function getErrorResponse($Response, $Message) {
// $ResponseData ["responseCode"] = 0;
// $ResponseData ["status"] = "Error";
// $ResponseData ["message"] = $Message;
// return $Response->withHeader ( "Content-Type", "application/json" )->write ( json_encode ( $ResponseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
// }
?>