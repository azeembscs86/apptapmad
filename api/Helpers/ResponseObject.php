<?php

/**
 * Class To Handle Response Objects
 *
 * @author SAIF UD DIN
 *
 */
class ResponseObject
{

    /**
     * Function To Get General Response As JSON Object
     *
     * @param ARRAY $Data
     * @return ARRAY JSON
     */
    public static function getResponseObject($Data)
    {
        $ResponseArray['DateTime'] = array(
            'CurrentDateTime' => date('Y-m-d H:i:s')
        );
        
        if ($Data['Response']) {
            $ResponseArray['Response'] = $Data['Response'];
        }
        
        if ($Data['RamadanBanner']) {
            $ResponseArray['RamadanBanner'] = $Data['RamadanBanner'];
        } else {
            $ResponseArray['RamadanBanner'] = null;
        }
        
        if ($Data['AdVideo']) {
            $ResponseArray['Video'] = array_slice($Data['AdVideo'], 0, 6);
            if (count(array_slice($Data['AdVideo'], 6)) > 0) {
                $ResponseArray['Ad'] = array_slice($Data['AdVideo'], 6);
            } else {
                $ResponseArray['Ad'] = null;
            }
        } else {
            $ResponseArray['Video'] = null;
            $ResponseArray['Ad'] = null;
        }
        
        if ($Data['Midrolls']) {
            $ResponseArray['Midrolls'] = $Data['Midrolls'];
        } else {
            $ResponseArray['Midrolls'] = array();
        }
        
        if ($Data['Videos']) {
            $ResponseArray['Videos'] = $Data['Videos'];
        } else {
            $ResponseArray['Videos'] = array();
        }
        
        return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}