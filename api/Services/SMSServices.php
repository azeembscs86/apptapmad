<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Respect\Validation\Validator as v;

/**
 * Class to Handle all Services Related to SMS
 *
 * @author SAIF UD DIN
 *        
 */
class SMSServices extends Config
{

    /**
     * Function to Send SMS Using Mobile Number
     *
     * @param Request $request
     * @param Response $response
     */
    public static function sendSMS(Request $request, Response $response)
    {
        $Version = $request->getAttribute('Version');
        $Language = $request->getAttribute('Language');
        parent::setConfig($Language);
        $Platform = $request->getAttribute('Platform');
        $Array['Mobile'] = $request->getAttribute('Mobile');
        
        try {
            $db = parent::getDataBase();
            switch ($Version) {
                case 'v1':
                case 'V1':
                    switch ($Platform) {
                        case 'Android':
                        case 'android':
                        case 'ANDROID':
                            // Activation Code $results [0] ['UserActivationCode'])
                            
                            // Your Account SID and Auth Token from twilio.com/console
                            $sid = 'AC5a629f87dce71f0d1c7323f801fcf749';
                            $token = 'c5b22451e785535e89e71a68eedb4a4e';
                            $client = new Client($sid, $token);
                            
                            // Use the client to do fun stuff like send text messages!
                            try {
                                $client->messages->create(
                                    // the number you'd like to send the message to
                                    '+92' . ltrim($Array['Mobile'], '0'), [
                                        // the body of the text message you'd like to send
                                        "body" => "Dear User,\n\nYou've successfully registered for tapmad TV. Your verification code is : " . $Results[0]['UserActivationCode'] . ".\n\ntapmad TV",
                                        // A Twilio phone number you purchased at twilio.com/console
                                        "from" => '+12314361240'
                                    ]);
                                // On US phone numbers, you could send an image as well!
                                // 'mediaUrl' => $imageUrl
                                
                                $Result = new stdClass();
                                $Result->QuickSMSResult = 'Message sent to ' . $Array['Mobile'];
                                return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getSMSSuccessMessage($Result))));
                            } catch (TwilioException $e) {
                                $Result = new stdClass();
                                $Result->QuickSMSResult = 'Could not send SMS notification.' . ' Twilio replied with: ' . $e;
                                return General::getResponse($response->write(SuccessObject::getSuccessObject(Message::getSMSErrorMessage($Result))));
                            }
                            break;
                        default:
                            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_PLATFORM'))));
                            break;
                    }
                    break;
                case 'v2':
                case 'V2':
                    return General::getResponse($response->write(ErrorObject::getErrorObject(array(
                        'In Process.'
                    ))));
                    break;
                default:
                    return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getMessage('E_INVALID_SERVICE_VERSION'))));
                    break;
            }
        } catch (PDOException $e) {
            return General::getResponse($response->write(ErrorObject::getErrorObject(Message::getPDOMessage($e))));
        } finally {
            $db = null;
        }
    }
}