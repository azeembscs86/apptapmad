<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class to Handle all Services Related to User
 *
 * @author SAIF UD DIN
 *        
 */
class CustomServices extends Config
{

    public static function insertStoreDataPOST(Request $request, Response $response)
    {
        $Version = filter_var($request->getParsedBody()['Version'], FILTER_SANITIZE_STRING);
        $Language = filter_var($request->getParsedBody()['Language'], FILTER_SANITIZE_STRING);
        parent::setConfig($Language);
        $Platform = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);
        $orderArray = array();
        $orderArray['CustomerName'] = filter_var($request->getParsedBody()['customer_name'], FILTER_SANITIZE_STRING);
        $orderArray['CustomerPhone'] = filter_var($request->getParsedBody()['mobile_number'], FILTER_SANITIZE_STRING);
        $orderArray['CustomerEmail'] = filter_var($request->getParsedBody()['customer_email'], FILTER_SANITIZE_STRING);
        $orderArray['CustomerAddress'] = filter_var($request->getParsedBody()['customer_address'], FILTER_SANITIZE_STRING);
        $orderArray['CustomerCity'] = filter_var($request->getParsedBody()['customer_city'], FILTER_SANITIZE_STRING);
        $orderArray['CustomerCountry'] = filter_var($request->getParsedBody()['customer_country'], FILTER_SANITIZE_STRING);
        $orderArray['CustomerProduct'] = $request->getParsedBody()['products'];
        $orderArray['CustomerPlatform'] = filter_var($request->getParsedBody()['Platform'], FILTER_SANITIZE_STRING);
        
        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Android':
                case 'android':
                case 'ANDROID':
                    $Count = 0;
                    foreach ($orderArray['CustomerProduct'] as $Product) {
                        $insert = array(
                            "CustomerName" => isset($orderArray['CustomerName']) ? $orderArray['CustomerName'] : null,
                            "CustomerPhone" => isset($orderArray['CustomerPhone']) ? $orderArray['CustomerPhone'] : null,
                            "CustomerEmail" => isset($orderArray['CustomerEmail']) ? $orderArray['CustomerEmail'] : null,
                            "CustomerAddress" => isset($orderArray['CustomerAddress']) ? $orderArray['CustomerAddress'] : null,
                            "CustomerCity" => isset($orderArray['CustomerCity']) ? $orderArray['CustomerCity'] : null,
                            "CustomerCountry" => isset($orderArray['CustomerCountry']) ? $orderArray['CustomerCountry'] : null,
                            "CustomerPlatform" => isset($orderArray['CustomerPlatform']) ? $orderArray['CustomerPlatform'] : null,
                            "CustomerProduct" => isset($Product['product_id']) ? filter_var($Product['product_id'], FILTER_SANITIZE_STRING) : null,
                            "CustomerProductPrice" => isset($Product['product_price']) ? filter_var($Product['product_price'], FILTER_SANITIZE_STRING) : null
                        );
                        if ((int) $db->insert("qgproductorders", $insert)) {
                            $Count ++;
                        }
                    }
                    if ($Count) {
                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_INSERT'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_INSERT'))));
                    }
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

    public static function insertStoreData(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        
        $orderArray = array();
        $orderArray['CustomerName'] = $request->getAttribute('CustomerName');
        $orderArray['CustomerPhone'] = $request->getAttribute('CustomerPhone');
        $orderArray['CustomerEmail'] = $request->getAttribute('CustomerEmail');
        $orderArray['CustomerAddress'] = $request->getAttribute('CustomerAddress');
        $orderArray['CustomerCity'] = $request->getAttribute('CustomerCity');
        $orderArray['CustomerCountry'] = $request->getAttribute('CustomerCountry');
        $orderArray['CustomerProduct'] = $request->getAttribute('CustomerProduct');
        $orderArray['CustomerProductPrice'] = $request->getAttribute('CustomerProductPrice');
        $orderArray['CustomerPlatform'] = $request->getAttribute('Platform');
        
        try {
            $db = parent::getDataBase();
            switch ($Platform) {
                case 'Android':
                case 'android':
                case 'ANDROID':
                    $insert = array(
                        "CustomerName" => isset($orderArray['CustomerName']) ? $orderArray['CustomerName'] : null,
                        "CustomerPhone" => isset($orderArray['CustomerPhone']) ? $orderArray['CustomerPhone'] : null,
                        "CustomerEmail" => isset($orderArray['CustomerEmail']) ? $orderArray['CustomerEmail'] : null,
                        "CustomerAddress" => isset($orderArray['CustomerAddress']) ? $orderArray['CustomerAddress'] : null,
                        "CustomerCity" => isset($orderArray['CustomerCity']) ? $orderArray['CustomerCity'] : null,
                        "CustomerCountry" => isset($orderArray['CustomerCountry']) ? $orderArray['CustomerCountry'] : null,
                        "CustomerProduct" => isset($orderArray['CustomerProduct']) ? $orderArray['CustomerProduct'] : null,
                        "CustomerProductPrice" => isset($orderArray['CustomerProductPrice']) ? $orderArray['CustomerProductPrice'] : null,
                        "CustomerPlatform" => isset($orderArray['CustomerPlatform']) ? $orderArray['CustomerPlatform'] : null
                    );
                    
                    if ((int) $db->insert("qgproductorders", $insert)) {
                        return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_INSERT'))));
                    } else {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_NO_INSERT'))));
                    }
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

    public static function sendEmail(Request $request, Response $response)
    {
        $Version = "V1";
        $Language = "en";
        parent::setConfig($Language);
        $Platform = "web";
        
        $Name = filter_var($request->getParsedBody()['Name'], FILTER_SANITIZE_STRING);
        $Email = filter_var($request->getParsedBody()['Email'], FILTER_SANITIZE_STRING);
        $Phone = filter_var($request->getParsedBody()['Phone'], FILTER_SANITIZE_STRING);
        $Message = filter_var($request->getParsedBody()['Message'], FILTER_SANITIZE_STRING);
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    
                    $mail = new PHPMailer();
                    
                    // $mail->SMTPDebug = 3; // Enable verbose debug output
                    $mail->isSMTP(); // Set mailer to use SMTP
                    $mail->Host = 'smtpout.secureserver.net'; // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true; // Enable SMTP authentication
                    $mail->Username = 'support@pitelevision.com'; // SMTP username
                    $mail->Password = 'sup&3450'; // SMTP password
                                                  // $mail->SMTPSecure = 'tls'; // Enable TLS encryption, ssl also accepted
                    $mail->Port = 25; // TCP port to connect to
                    
                    $mail->From = $Email;
                    $mail->FromName = 'Sim Paisa Contact Us';
                    $mail->addAddress('salim.karim@publishexsolutions.com');
                    $mail->isHTML(true); // Set email format to HTML
                    
                    $mail->Subject = 'Query From ' . $Name;
                    $mail->Body = "<b>Name : </b> " . $Name . "<br/><br/>
										<b>Email : </b> " . $Email . "<br/><br/>
										<b>Phone Number : </b> " . $Phone . "<br/><br/>
										<b>Message : </b> " . $Message . "<br/><br/>
										<b>Thank You</b><br/>";
                    $mail->AltBody = "<b>Name : </b> " . $Name . "<br/><br/>
										<b>Email : </b> " . $Email . "<br/><br/>
										<b>Phone Number : </b> " . $Phone . "<br/><br/>
										<b>Message : </b> " . $Message . "<br/><br/>
										<b>Thank You</b><br/>";
                    
                    if (! $mail->send()) {
                        return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_MAIL_NOTSENT'))));
                    }
                    return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getMessage('M_MAIL_SENT'))));
                    break;
                case 'v2':
                case 'V2': // Local/International Filter Disabled
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getUserErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }

    public static function getAppStoreData(Request $request, Response $response)
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
                case 'Android':
                case 'android':
                case 'ANDROID':
                    $ResultArray = array(
                        array(
                            'CategoryID' => 1,
                            'CategoryName' => 'MEN',
                            'CategoryImage' => '',
                            'SubCategories' => array(
                                array(
                                    'SubCategoryID' => 1,
                                    'SubCategoryName' => 'Shirts',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'White Contrast Black Shirt',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/shirt-for-men/shirt-white-contrast-black.png',
                                            'ItemPrice' => 1790
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Pink Shirt',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/shirt-for-men/shirt-pink.png',
                                            'ItemPrice' => 1890
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Blue Contrast Shirt',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/shirt-for-men/shirt-blue-contrast.png',
                                            'ItemPrice' => 1700
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 2,
                                    'SubCategoryName' => 'Trousers',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Men Short 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/short%20for%20men/short%20men%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Men Short 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/short%20for%20men/short%20men%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Men Short 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/short%20for%20men/short%20men%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 3,
                                    'SubCategoryName' => 'Shalwar Kameez',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Shalwar Kameez Red',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/shalwar%20kameez%20for%20men/shalwar%20kameez%20red.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Shalwar Kameez Blue',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/shalwar%20kameez%20for%20men/shalwar%20kameez%20blue.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Shalwar Kameez Green',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/shalwar%20kameez%20for%20men/shalwar%20kameez%20green.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 4,
                                    'SubCategoryName' => 'Ties, belts and Cufflinks',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Men Tie 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/ties%20for%20men/ties%20for%20men%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Men Tie 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/ties%20for%20men/ties%20for%20men%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Men Belt 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/belt%20man/belt%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Men Belt 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/belt%20man/belt%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 5,
                                            'ItemName' => 'Men Cough Link 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/cough%20links/cough%20links%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 6,
                                            'ItemName' => 'Men Cough Link 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/cough%20links/cough%20links%201.png',
                                            'ItemPrice' => 2100
                                        )
                                    
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 5,
                                    'SubCategoryName' => 'Watches',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Men Watch 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/watches%20for%20men/watches%20for%20men%203.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Men Watch 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/watches%20for%20men/watches%20for%20men%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Men Watch 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/watches%20for%20men/watches%20for%20men%201.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 6,
                                    'SubCategoryName' => 'Formal',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Men Formal Pent 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/formal%20pant/formal%20pent%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Men Formal Pent 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/formal%20pant/formal%20pent%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Men Formal Pent 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/formal%20pant/formal%20pent%203.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Men Formal Shoe 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/formal%20shoe/formal%20shoes%20men%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 5,
                                            'ItemName' => 'Men Formal Shoe 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/formal%20shoe/formal%20shoes%20men%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 6,
                                            'ItemName' => 'Men Formal Shoe 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/formal%20shoe/formal%20shoes%20men%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 7,
                                    'SubCategoryName' => 'Semi Formal',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Men Semi Formal Shoe 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/semi%20formal%20shoes%20for%20men/semi%20formal%20shoes%20for%20men%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Men Semi Formal Shoe 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/semi%20formal%20shoes%20for%20men/semi%20formal%20shoes%20for%20men%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Men Semi Formal Shoe 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/semi%20formal%20shoes%20for%20men/semi%20formal%20shoes%20for%20men%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 8,
                                    'SubCategoryName' => 'Casual',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Men Casual Shoe 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/casual%20shoe/casual%20shoe%20for%20men%203.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Men Casual Shoe 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/casual%20shoe/casual%20shoe%20for%20men%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Men Casual Shoe 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/casual%20shoe/casual%20shoe%20for%20men%201.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 9,
                                    'SubCategoryName' => 'Slippers',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Men Sandal 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/sandals%20for%20men/sandals%20for%20men%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Men Sandal 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/sandals%20for%20men/sandals%20for%20men%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Men Sandal 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Men/sandals%20for%20men/sandals%20for%20men%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                )
                            )
                        ),
                        array(
                            'CategoryID' => 2,
                            'CategoryName' => 'WOMEN',
                            'CategoryImage' => '',
                            'SubCategories' => array(
                                array(
                                    'SubCategoryID' => 1,
                                    'SubCategoryName' => 'Kurti',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Kurti 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/kurti/kurti%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Kurti 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/kurti/kurti%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Kurti 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/kurti/kurti%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 2,
                                    'SubCategoryName' => 'Trousers and Tights',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Trouser 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/trouser-for-women/trouser%20for%20women%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Trouser 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/trouser-for-women/trouser%20for%20women%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Trouser 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/trouser-for-women/trouser%20for%20women%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 3,
                                    'SubCategoryName' => 'Saris',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Saree 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/saris/saris%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Saree 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/saris/saris%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Saree 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/saris/saris%203.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Women Saree 4',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/saris/saris%204.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 4,
                                    'SubCategoryName' => 'Shalwar Kameez',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Shalwar Kameez 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/shalwar-kameez-for-women/shalwar%20kameez%20for%20women%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Shalwar Kameez 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/shalwar-kameez-for-women/shalwar%20kameez%20for%20women%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Shalwar Kameez 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/shalwar-kameez-for-women/shalwar%20kameez%20for%20women%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 5,
                                    'SubCategoryName' => 'T-Shirts',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women T-Shirt 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/ladies-tshirt/ladies%20t%20shirt%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women T-Shirt 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/ladies-tshirt/ladies%20t%20shirt%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women T-Shirt 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/ladies-tshirt/ladies%20t%20shirt%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 6,
                                    'SubCategoryName' => 'Watches',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Watch 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/ladies-watches/ladies%20watches%201.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Watch 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/ladies-watches/ladies%20watches%202.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Watch 3',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/ladies-watches/ladies%20watches%203.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 7,
                                    'SubCategoryName' => 'Jewelry',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Blue Ring',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Jewelry/blue%20ring.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Butterfly Necklace',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Jewelry/Butterfly%20necklace.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Golden Bracelet',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Jewelry/Golden%20bracelet.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Women Moon Ring',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Jewelry/Moon%20ring.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 5,
                                            'ItemName' => 'Women Silver Bracelet',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Jewelry/Silve%20bracelet.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 6,
                                            'ItemName' => 'Women Silver Necklace',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Jewelry/Silver%20Necklace.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 7,
                                            'ItemName' => 'Women Silver Ring',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Jewelry/Silver%20Ring.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 8,
                                    'SubCategoryName' => 'Bags and Clutches',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Black Bag',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/Black%20Bag.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Black Clutch',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/Black%20Clutch.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Grey Bag',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/Gray%20bag.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Women Maroon Clutch',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/Maroon%20Clutch.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 5,
                                            'ItemName' => 'Women Pink Bag',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/Pink%20Bag.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 6,
                                            'ItemName' => 'Women Purple Clutch',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/Purple%20Clutch.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 7,
                                            'ItemName' => 'Women Red Bag',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/Red%20Bag.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 8,
                                            'ItemName' => 'Women Clutch',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Bags-Clutches/White%20Clutch.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 9,
                                    'SubCategoryName' => 'Pumps',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Black Pump',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Pumps/Black%20Pumps.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Blue Pump',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Pumps/Blue%20Heel%20Pumps.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women High Pump',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Pumps/Pink%20High%20Pumps.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Women Pink Pump',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Pumps/pink%20Pumps.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 10,
                                    'SubCategoryName' => 'Heels',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Black Heel 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Heels/Black%202%20Heel.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Black Heel 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Heels/Black%20Heels.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Grey Heel',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Heels/Grey%20Heels.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Women Pink Heel',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Heels/Pink%20Heels.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                ),
                                array(
                                    'SubCategoryID' => 11,
                                    'SubCategoryName' => 'Sandals',
                                    'SubCategoryImage' => '',
                                    'Items' => array(
                                        array(
                                            'ItemID' => 1,
                                            'ItemName' => 'Women Black Sandal 1',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Sandals/Balck%20Sandal.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 2,
                                            'ItemName' => 'Women Black Sandal 2',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Sandals/Black%202%20Sandal.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 3,
                                            'ItemName' => 'Women Brown Sandal',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Sandals/Brown%20Sandal.png',
                                            'ItemPrice' => 2100
                                        ),
                                        array(
                                            'ItemID' => 4,
                                            'ItemName' => 'Women High Sandal',
                                            'ItemImage' => 'http://tapmad.com/appstoreimages/Categories/Categories/Women/Sandals/high%20sandal.png',
                                            'ItemPrice' => 2100
                                        )
                                    )
                                )
                            )
                        ),
                        array(
                            'CategoryID' => 3,
                            'CategoryName' => 'SPORTS & FITNESS',
                            'CategoryImage' => '',
                            'SubCategories' => array(
                                array(
                                    'SubCategoryID' => 1,
                                    'SubCategoryName' => 'Wall Clocks',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 2,
                                    'SubCategoryName' => 'Wall Art',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 3,
                                    'SubCategoryName' => 'Candles and lamps',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 4,
                                    'SubCategoryName' => 'Accessories',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 5,
                                    'SubCategoryName' => 'Bedsheets',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 6,
                                    'SubCategoryName' => 'Blankets',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 7,
                                    'SubCategoryName' => 'towels',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                )
                            
                            )
                        ),
                        array(
                            'CategoryID' => 4,
                            'CategoryName' => 'HOME & LIVING',
                            'CategoryImage' => '',
                            'SubCategories' => array(
                                array(
                                    'SubCategoryID' => 1,
                                    'SubCategoryName' => 'Supplements',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 2,
                                    'SubCategoryName' => 'Cricket',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 3,
                                    'SubCategoryName' => 'Badminton',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 4,
                                    'SubCategoryName' => 'Table Tennis',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 5,
                                    'SubCategoryName' => 'Tennis',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 6,
                                    'SubCategoryName' => 'Football',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                ),
                                array(
                                    'SubCategoryID' => 7,
                                    'SubCategoryName' => 'Basketball',
                                    'SubCategoryImage' => '',
                                    'Items' => array()
                                )
                            )
                        )
                    );
                    
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