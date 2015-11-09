<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Models\Session as SessionModel;

class BaseController extends Controller {

	protected function _check_session( $token = "", $id = "" ) {
	    $result = false;
	    if ( !empty( $token ) ) {
	        $SessionModel   = new SessionModel();
	        $session        = $SessionModel->find( $token );
	 
	        if ( !empty( ( array ) $session ) ) {
	            if ( !empty( $id ) ) {
	                if ( $session->user_id == $id ) {
	                    $result = $session;
	                }
	            } else {
	                $result     = $session;
	            }
	        }
	    }
	 
	    return $result;
	}
 
	protected function _response( $result ) {
	    if ( is_object( $result ) && property_exists( $result, "status" ) ) {
	        return response()->json( $result, $result->status );
	    } else {
	        return response()->json( $result );
	    }
	}

}
