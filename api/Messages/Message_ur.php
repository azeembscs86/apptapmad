<?php
function getErrorMessage($type) {
	switch ($type) {
		case 'E_NO_CONTENT' :
			return json_encode ( array (
					'responseCode' => "1",
					'status' => 'ErrorUrdu',
					'message' => "No Content Found." 
			) );
	}
	return "";
}
?>