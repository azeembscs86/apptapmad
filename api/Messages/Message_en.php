<?php

/**
 * Class To Handle All Messages in English
 * 
 * @author SAIF UD DIN
 *
 */
class Message
{

    const W_NO_CONTENT_CODE = 'W_NO_CONTENT';

    const W_NO_CONTEND_MSG = "No Content Found.";

    const E_CODE_MISMATCH_CODE = 'E_CODE_MISMATCH';

    const E_CODE_MISMATCH_MSG = "Incorrect Activation Code. Please Try Again!";

    const E_ALREADY_ACTIVATED_CODE = 'E_ALREADY_ACTIVATED';

    const E_ALREADY_ACTIVATED_MSG = "User Account Already Activated Or Doesn't Exists.";

    const E_SUCCESS_CODE = 'E_SUCCESS';

    const E_SUCCESS_MSG = "Success.";

    const E_EMPTY_SEARCH_CODE = 'E_EMPTY_SEARCH';

    const E_EMPTY_SEARCH_MSG = "Empty Search String.";

    const E_NO_LOGIN_CODE = 'E_NO_LOGIN';

    const E_NO_LOGIN_MSG = "Incorrect Username or Password.";

    const E_WRONG_PASS_CODE = 'E_WRONG_PASS';

    const E_WRONG_PASS_MSG = "Incorrect Password.";

    const E_WRONG_PASS_SIGNUP_CODE = 'E_WRONG_PASS_SIGNUP';

    const E_WRONG_PASS_SIGNUP_MSG = "User Already Exists And Password Is Incorrect.";

    const E_WRONG_FB_CODE = 'E_WRONG_FB';

    const E_WRONG_FB_MSG = "Incorrect Facebook ID.";

    const E_WRONG_FB_SIGNUP_CODE = 'E_WRONG_FB_SIGNUP';

    const E_WRONG_FB_SIGNUP_MSG = "Incorrect Facebook ID.";

    const E_EXISTS_CODE = 'E_EXISTS';

    const E_EXISTS_MSG = "Already Exists.";

    const E_USER_EXIST_CODE = 'E_USER_EXIST';

    const E_USER_EXIST_MSG = "User Already Exists.";

    const E_DEVICE_EXIST_CODE = 'E_DEVICE_EXIST';

    const E_DEVICE_EXIST_MSG = "Device Already Registered With Tapmad TV.";

    const E_NO_INSERT_CODE = 'E_NO_INSERT';

    const E_NO_INSERT_MSG = "Data Not Inserted.";

    const E_NO_DELETE_CODE = 'E_NO_DELETE';

    const E_NO_DELETE_MSG = "Data Is Not Deleted.";

    const E_NO_UPDATE_CODE = 'E_NO_UPDATE';

    const E_NO_UPDATE_MSG = "Data Is Not Updated.";

    const E_CORRUPT_DATA_CODE = 'E_CORRUPT_DATA';

    const E_CORRUPT_DATA_MSG = "Data Is Corrupted.";

    const E_RESTRICT_USER_CODE = 'E_RESTRICT_USER';

    const E_RESTRICT_USER_MSG = "User Is Restricted.";

    const E_WRONG_CURRENT_PASS_CODE = 'E_WRONG_CURRENT_PASS';

    const E_WRONG_CURRENT_PASS_MSG = "Current Password Is Wrong.";

    const E_SAME_PASS_CODE = 'E_SAME_PASS';

    const E_SAME_PASS_MSG = "Same Current & New Password.";

    const E_PASS_MISMATCH_CODE = 'E_PASS_MISMATCH';

    const E_PASS_MISMATCH_MSG = "New Password Mismatch.";

    const E_MAIL_NOTSENT_CODE = 'E_MAIL_NOTSENT';

    const E_MAIL_NOTSENT_MSG = "Email Not Sent.";

    const E_INVALID_USER_CODE = 'E_INVALID_USER';

    const E_INVALID_USER_MSG = "Invalid Username Provided.";

    const E_INVALID_SERVICE_VERSION_CODE = 'E_INVALID_SERVICE_VERSION';

    const E_INVALID_SERVICE_VERSION_MSG = "Invalid Service Version.";

    const E_INVALID_PLATFORM_CODE = 'E_INVALID_PLATFORM';

    const E_INVALID_PLATFORM_MSG = "Invalid Platform.";

    const E_RECORD_UPDATE_CODE = 'E_RECORD_UPDATE';

    const E_RECORD_UPDATE_MSG = "Record Not Updated.";

    const E_NO_INSERT_DEVICE_INFO_CODE = 'E_NO_INSERT_DEVICE_INFO';

    const E_NO_INSERT_DEVICE_INFO_MSG = "Device Information Is Not Added.";

    const E_NO_UPDATE_DEVICE_INFO_CODE = 'E_NO_UPDATE_DEVICE_INFO';

    const E_NO_UPDATE_DEVICE_INFO_MSG = "Device Information Is Not Updated.";

    const E_NO_STATUS_PARAM_CODE = 'E_NO_STATUS_PARAM';

    const E_NO_STATUS_PARAM_MSG = "Status Parameter Missing.";

    const E_NO_TRANS_OBG_CODE = 'E_NO_TRANS_OBG';

    const E_NO_TRANS_OBG_MSG = "Transaction Object Missing.";

    const E_NO_TRANS_STATUS_PARAM_CODE = 'E_NO_TRANS_STATUS_PARAM';

    const E_NO_TRANS_STATUS_PARAM_MSG = "Transaction Status Parameter Missing.";

    const E_FAILED_TRANS_CODE = 'E_FAILED_TRANS';

    const E_FAILED_TRANS_MSG = "Failed Transaction ID Provided.";

    const E_NO_USER_CODE = 'E_NO_USER';

    const E_NO_USER_MSG = "User Not Found.";

    const E_INVALID_PRODUCT_CODE = 'E_INVALID_PRODUCT';

    const E_INVALID_PRODUCT_MSG = "Invalid Product.";

    const E_INVALID_PARAMS_CODE = 'E_INVALID_PARAMS';

    const E_INVALID_PARAMS_MSG = "Invalid Parameters (Bad Request).";

    const E_EXCEPTION_FORBIDDEN_CODE = 'E_EXCEPTION_FORBIDDEN';

    const E_EXCEPTION_FORBIDDEN_MSG = "Exception (Forbidden).";

    const E_NO_PAYMENT_CODE = 'E_NO_PAYMENT';

    const E_NO_PAYMENT_MSG = "Sorry! Payment Not Processed.";

    const E_NO_OPERATOR_CODE = 'E_NO_OPERATOR';

    const E_NO_OPERATOR_MSG = "Sorry! Operator Not Supported.";

    const E_ALREADY_CHAT_ID_CODE = 'E_ALREADY_CHAT_ID';

    const E_ALREADY_CHAT_ID_MSG = "Sorry! Chat Id Already Taken.";

    const M_INSERT_CODE = 'M_INSERT';

    const M_INSERT_MSG = "Data Inserted Successfully.";

    const M_LOGIN_FB_CODE = 'M_LOGIN_FB';

    const M_LOGIN_FB_MSG = "Logged In Successfully Using Facebook.";

    const M_LOGIN_FB_SIGNUP_CODE = 'M_LOGIN_FB_SIGNUP';

    const M_LOGIN_FB_SIGNUP_MSG = "Logged In Successfully Using Facebook.";

    const M_LOGIN_CODE = 'M_LOGIN';

    const M_LOGIN_MSG = "Logged In Successfully.";

    const M_LOGIN_SIGNUP_CODE = 'M_LOGIN_SIGNUP';

    const M_LOGIN_SIGNUP_MSG = "User Already Exists And Logged In Successfully.";

    const M_DATA_CODE = 'M_DATA';

    const M_DATA_MSG = "Data Returned Successfully.";

    const M_DELETE_CODE = 'M_DELETE';

    const M_DELETE_MSG = "Data Deleted Successfully.";

    const M_UPDATE_CODE = 'M_UPDATE';

    const M_UPDATE_MSG = "Data Updated Successfully.";

    const M_UPDATE_TEMP_USER_CODE = 'M_UPDATE_TEMP_USER';

    const M_UPDATE_TEMP_USER_MSG = "Temp User Already Inserted And Access Allowed.";

    const M_INSERT_TEMP_USER_CODE = 'M_INSERT_TEMP_USER';

    const M_INSERT_TEMP_USER_MSG = "Temp User Inserted and Access Allowed.";

    const M_RESTRICT_TEMP_USER_CODE = 'M_RESTRICT_TEMP_USER';

    const M_RESTRICT_TEMP_USER_MSG = "Temp User Already Inserted And Access Denied.";

    const M_TRIAL_UPDATED_CODE = 'M_TRIAL_UPDATED';

    const M_TRIAL_UPDATED_MSG = "Temp User Trial Time Updated.";

    const M_PASS_CHANGED_CODE = 'M_PASS_CHANGED';

    const M_PASS_CHANGED_MSG = "Password Changed Successfully.";

    const M_MAIL_SENT_CODE = 'M_MAIL_SENT';

    const M_MAIL_SENT_MSG = "Mail Sent Successfully.";

    const M_RECORD_UPDATE_CODE = 'M_RECORD_UPDATE';

    const M_RECORD_UPDATE_MSG = "Record Updated Successfully.";

    const M_INSERT_DEVICE_INFO_CODE = 'M_INSERT_DEVICE_INFO';

    const M_INSERT_DEVICE_INFO_MSG = "Device Information Added Successfully.";

    const M_UPDATE_DEVICE_INFO_CODE = 'M_UPDATE_DEVICE_INFO';

    const M_UPDATE_DEVICE_INFO_MSG = "Device Information Updated Successfully.";

    const M_ACOUNT_ACTIVATED_CODE = 'M_ACOUNT_ACTIVATED';

    const M_ACOUNT_ACTIVATED_MSG = "User Account Activated Successfully.";

    const M_ACTIVATION_CODE_SENT_CODE = 'M_ACTIVATION_CODE_SENT';

    const M_ACTIVATION_CODE_SENT_MSG = "Activation Code Sent Successfully.";

    const M_RECURSION_DISABLED_CODE = 'M_RECURSION_DISABLED';

    const M_RECURSION_DISABLED_MSG = "Recursion Disable Successfully.";

    const M_PAYMENT_CODE = 'M_PAYMENT';

    const M_PAYMENT_MSG = "Success! Payment Processed.";

    const M_OTP_SEND_CODE = 'M_OTP_SEND';

    const M_OTP_SEND_MSG = "Activation Code sent Successfully.";

    const M_OTP_VERIFIED_CODE = 'M_OTP_VERIFIED';

    const M_OTP_VERIFIED_MSG = "Activation Code Verified Successfully.";

    const E_LIMIT_REACHED_CODE = "E_LIMIT_REACHED";

    const E_LIMIT_REACHED_MSG = "Maximum Limit Exceeded";

    const E_RECORD_NOT_FOUND_CODE = "E_RECORD_NOT_FOUND";

    const E_RECORD_NOT_FOUND_MSG = "Record Not Found!";

    const E_OTP_EXPIRE_CODE = "E_OTP_EXPIRE";

    const E_OTP_EXPIRE_MSG = "Code Expired! Please Try again.";
	
	const E_OTP_EXPIRE_AFTER_TWO_MINUTES_CODE = "E_OTP_EXPIRE_AFTER_TWO_MINUTES";

    const E_OTP_EXPIRE_AFTER_TWO_MINUTES_MSG = "Code Expired! Please Try again.";

    const E_NO_INSERT_MOBILE_NUMBER_CODE = 'E_NO_INSERT_MOBILE_NUMBER';

    const E_NO_INSERT_MOBILE_NUMBER_MSG = "Mobile Number Is Not Enter.";

    const E_MISSING_PARAMETER_CODE = 'E_MISSING_PARAMETER';

    const E_MISSING_PARAMETER_MSG = "Missing parameters in your request";

    const E_INVALID_MOBILE_NUMBER_CODE = 'E_INVALID_MOBILE_NUMBER';

    const E_INVALID_MOBILE_NUMBER_MSG = "Please Enter Valid Mobile Number.";

    const E_INVALID_OTP_CODE_CODE = 'E_INVALID_OTP_CODE';

    const E_INVALID_OTP_CODE_MSG = "Please Enter Valid Pin Code.";

    const E_INCORRECT_OR_MISSMATCHED_MOBILE_NO_CODE = 'E_INCORRECT_OR_MISSMATCHED_MOBILE_NO';

    const E_INCORRECT_OR_MISSMATCHED_MOBILE_NO_MSG = "incorrect/mismatched Mobile number";
	
	const E_OTP_CODE_MESSAGE_NOT_SEND_CODE = 'E_OTP_CODE_MESSAGE_NOT_SEND';

    const E_OTP_CODE_MESSAGE_NOT_SEND_MSG = "Something went wrong, Please Try again later.";
	
	const E_USER_SUBSCRIBED_CODE = 'E_USER_SUBSCRIBED';

    const E_USER_SUBSCRIBED_MSG = "User Already Unsubscribed.";

    public static function getWarningMessage($Message)
    {
        return array(
            'responseCode' => 2,
            'status' => 'Warning',
            'message' => $Message
        );
    }

    public static function getSuccessMessage($Message)
    {
        return array(
            'responseCode' => 1,
            'status' => 'Success',
            'message' => $Message
        );
    }

    public static function getErrorMessage($Message)
    {
        return array(
            'responseCode' => 0,
            'status' => 'Error',
            'message' => $Message
        );
    }
    
    public static function getErrorMessageAndCode($Message, $Code)
    {
        return array(
            'responseCode' => $Code,
            'status' => 'Error',
            'message' => $Message
        );
    }

    public static function getMessage($type)
    {
        switch ($type) {
            case self::W_NO_CONTENT_CODE:
                return array(
                    'responseCode' => 2,
                    'status' => 'Warning',
                    'message' => self::W_NO_CONTEND_MSG
                );
            case self::M_DATA_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_DATA_MSG
                );
            case self::E_SUCCESS_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Error',
                    'message' => self::E_SUCCESS_MSG
                );
            case self::E_EMPTY_SEARCH_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_EMPTY_SEARCH_MSG
                );
            case self::E_NO_LOGIN_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_LOGIN_MSG
                );
            case self::E_WRONG_PASS_CODE:
                return array(
                    'responseCode' => 2,
                    'status' => 'Error',
                    'message' => self::E_WRONG_PASS_MSG
                );
            case self::E_WRONG_PASS_SIGNUP_CODE:
                return array(
                    'responseCode' => 12,
                    'status' => 'Error',
                    'message' => self::E_WRONG_PASS_SIGNUP_MSG
                );
            case self::E_WRONG_FB_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_WRONG_FB_MSG
                );
            case self::E_WRONG_FB_SIGNUP_CODE:
                return array(
                    'responseCode' => 12,
                    'status' => 'Error',
                    'message' => self::E_WRONG_FB_SIGNUP_MSG
                );
            case self::E_USER_EXIST_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_USER_EXIST_MSG
                );
            case self::E_DEVICE_EXIST_CODE:
                return array(
                    'responseCode' => 3,
                    'status' => 'Error',
                    'message' => self::E_DEVICE_EXIST_MSG
                );
            case self::E_EXISTS_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_EXISTS_MSG
                );
            case self::E_NO_INSERT_CODE:
                return array(
                    'responseCode' => 2,
                    'status' => 'Error',
                    'message' => self::E_NO_INSERT_MSG
                );
            case self::M_INSERT_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_INSERT_MSG
                );
            case self::M_LOGIN_FB_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_LOGIN_FB_MSG
                );
            case self::M_LOGIN_FB_SIGNUP_CODE:
                return array(
                    'responseCode' => 11,
                    'status' => 'Success',
                    'message' => self::M_LOGIN_FB_SIGNUP_MSG
                );
            case self::M_LOGIN_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_LOGIN_MSG
                );
            case self::M_LOGIN_SIGNUP_CODE:
                return array(
                    'responseCode' => 11,
                    'status' => 'Success',
                    'message' => self::M_LOGIN_SIGNUP_MSG
                );
            case self::M_UPDATE_TEMP_USER_CODE:
                return array(
                    'responseCode' => 2,
                    'status' => 'Success',
                    'message' => self::M_UPDATE_TEMP_USER_MSG
                );
            case self::M_INSERT_TEMP_USER_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_INSERT_TEMP_USER_MSG
                );
            case self::M_TRIAL_UPDATED_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_TRIAL_UPDATED_MSG
                );
            case self::E_CORRUPT_DATA_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_CORRUPT_DATA_MSG
                );
            case self::E_RESTRICT_USER_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_RESTRICT_USER_MSG
                );
            case self::M_RESTRICT_TEMP_USER_CODE:
                return array(
                    'responseCode' => 3,
                    'status' => 'Error',
                    'message' => self::M_RESTRICT_TEMP_USER_MSG
                );
            case self::E_WRONG_CURRENT_PASS_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_WRONG_CURRENT_PASS_MSG
                );
            case self::E_PASS_MISMATCH_CODE:
                return array(
                    'responseCode' => 2,
                    'status' => 'Error',
                    'message' => self::E_PASS_MISMATCH_MSG
                );
            case self::M_PASS_CHANGED_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_PASS_CHANGED_MSG
                );
            case self::E_SAME_PASS_CODE:
                return array(
                    'responseCode' => 3,
                    'status' => 'Error',
                    'message' => self::E_SAME_PASS_MSG
                );
            case self::E_MAIL_NOTSENT_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_MAIL_NOTSENT_MSG
                );
            case self::M_MAIL_SENT_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_MAIL_SENT_MSG
                );
            case self::E_INVALID_USER_CODE:
                return array(
                    'responseCode' => 2,
                    'status' => 'Error',
                    'message' => self::E_INVALID_USER_MSG
                );
            case self::E_INVALID_SERVICE_VERSION_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_INVALID_SERVICE_VERSION_MSG
                );
            case self::E_INVALID_PLATFORM_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_INVALID_PLATFORM_MSG
                );
            case self::M_RECORD_UPDATE_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_RECORD_UPDATE_MSG
                );
            case self::E_RECORD_UPDATE_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_RECORD_UPDATE_MSG
                );
            case self::M_INSERT_DEVICE_INFO_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_INSERT_DEVICE_INFO_MSG
                );
            case self::E_NO_INSERT_DEVICE_INFO_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_INSERT_DEVICE_INFO_MSG
                );
            case self::M_UPDATE_DEVICE_INFO_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_UPDATE_DEVICE_INFO_MSG
                );
            case self::E_NO_UPDATE_DEVICE_INFO_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_UPDATE_DEVICE_INFO_MSG
                );
            case self::M_DELETE_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_DELETE_MSG
                );
            case self::E_NO_DELETE_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_DELETE_MSG
                );
            case self::M_UPDATE_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_UPDATE_MSG
                );
            case self::E_NO_UPDATE_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_UPDATE_MSG
                );
            case self::E_ALREADY_ACTIVATED_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_ALREADY_ACTIVATED_MSG
                );
            case self::E_CODE_MISMATCH_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_CODE_MISMATCH_MSG
                );
            case self::M_ACOUNT_ACTIVATED_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_ACOUNT_ACTIVATED_MSG
                );
            case self::M_ACTIVATION_CODE_SENT_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_ACTIVATION_CODE_SENT_MSG
                );
            case self::E_NO_STATUS_PARAM_CODE:
                return array(
                    'responseCode' => 3,
                    'status' => 'Error',
                    'message' => self::E_NO_STATUS_PARAM_MSG
                );
            case self::E_NO_TRANS_OBG_CODE:
                return array(
                    'responseCode' => 4,
                    'status' => 'Error',
                    'message' => self::E_NO_TRANS_OBG_MSG
                );
            case self::E_NO_TRANS_STATUS_PARAM_CODE:
                return array(
                    'responseCode' => 5,
                    'status' => 'Error',
                    'message' => self::E_NO_TRANS_STATUS_PARAM_MSG
                );
            case self::E_FAILED_TRANS_CODE:
                return array(
                    'responseCode' => 6,
                    'status' => 'Error',
                    'message' => self::E_FAILED_TRANS_MSG
                );
            case self::M_RECURSION_DISABLED_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_RECURSION_DISABLED_MSG
                );
            case self::E_NO_USER_CODE:
                return array(
                    'responseCode' => 3,
                    'status' => 'Error',
                    'message' => self::E_NO_USER_MSG
                );
            case self::E_INVALID_PRODUCT_CODE:
                return array(
                    'responseCode' => 4,
                    'status' => 'Error',
                    'message' => self::E_INVALID_PRODUCT_MSG
                );
            case self::E_INVALID_PARAMS_CODE:
                return array(
                    'responseCode' => 5,
                    'status' => 'Error',
                    'message' => self::E_INVALID_PARAMS_MSG
                );
            case self::E_EXCEPTION_FORBIDDEN_CODE:
                return array(
                    'responseCode' => 6,
                    'status' => 'Error',
                    'message' => self::E_EXCEPTION_FORBIDDEN_MSG
                );
            case self::E_NO_PAYMENT_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_PAYMENT_MSG
                );
            case self::M_PAYMENT_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_PAYMENT_MSG
                );
            case self::E_NO_OPERATOR_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_OPERATOR_MSG
                );
            case self::E_ALREADY_CHAT_ID_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_ALREADY_CHAT_ID_MSG
                );
            case self::M_OTP_SEND_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_OTP_SEND_MSG
                );
            case self::M_OTP_VERIFIED_CODE:
                return array(
                    'responseCode' => 1,
                    'status' => 'Success',
                    'message' => self::M_OTP_VERIFIED_MSG
                );
            case self::E_LIMIT_REACHED_CODE:
                return array(
                    'responseCode' => 4,
                    'status' => 'Error',
                    'message' => self::E_LIMIT_REACHED_MSG
                );
            case self::E_RECORD_NOT_FOUND_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_RECORD_NOT_FOUND_MSG
                );
            case self::E_OTP_EXPIRE_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_OTP_EXPIRE_MSG
                );
			case self::E_OTP_EXPIRE_AFTER_TWO_MINUTES_CODE:
                return array(
                    'responseCode' => 2,
                    'status' => 'Error',
                    'message' => self::E_OTP_EXPIRE_AFTER_TWO_MINUTES_MSG
                );
            case self::E_NO_INSERT_MOBILE_NUMBER_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_NO_INSERT_MOBILE_NUMBER_MSG
                );
            case self::E_MISSING_PARAMETER_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_MISSING_PARAMETER_MSG
                );
            case self::E_INVALID_MOBILE_NUMBER_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_INVALID_MOBILE_NUMBER_MSG
                );
            case self::E_INVALID_OTP_CODE_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_INVALID_OTP_CODE_MSG
                );
            case self::E_INCORRECT_OR_MISSMATCHED_MOBILE_NO_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Error',
                    'message' => self::E_INCORRECT_OR_MISSMATCHED_MOBILE_NO_MSG
                );
			case self::E_OTP_CODE_MESSAGE_NOT_SEND_CODE:
                return array(
                    'responseCode' => 0,
                    'status' => 'Balance 0',
                    'message' => self::E_OTP_CODE_MESSAGE_NOT_SEND_MSG
                );	
				
		    case self::E_USER_SUBSCRIBED_CODE:
                return array(
                    'responseCode' => 5,
                    'status' => 'Already Unsubscribed',
                    'message' => self::E_USER_SUBSCRIBED_MSG
                );		
        }
        return "";
    }

    public function getPDOMessage($e)
    {
        return array(
            'responseCode' => 0,
            'status' => 'Error',
            'message' => $e->getMessage()
        );
    }

    public static function getSMSSuccessMessage($Result)
    {
        return array(
            'responseCode' => 1,
            'status' => 'Success',
            'message' => $Result->QuickSMSResult
        );
    }

    public static function getSMSErrorMessage($Result)
    {
        return array(
            'responseCode' => 0,
            'status' => 'Error',
            'message' => $Result->QuickSMSResult
        );
    }

    public static function getSMSWarningMessage($Result)
    {
        return array(
            'responseCode' => 2,
            'status' => 'Warning',
            'message' => $Result->QuickSMSResult
        );
    }
}
?>