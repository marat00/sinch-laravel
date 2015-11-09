<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;

use App\Http\Models\Session as SessionModel;
use App\Http\Models\User as UserModel;

class UserController extends BaseController {

	private $_model = null;
 
	public function __construct() {
	    $this->_model   = new UserModel();
	}
	 
	public function create( Request $request ) {
		$email      = $request->get( 'email' );
		$fbId       = $request->get( 'fbId' );
		$gender     = $request->get( 'gender' );
		$location   = $request->get( 'location' );
		$mobile     = $request->get( 'mobile' );
		$name       = $request->get( 'name' );
		 
		if ( gettype( $location ) == "string" ) {
		    $location   = json_decode( $location );
		}
		$locObj                 = new \stdClass();
		$locObj->type           = "Point";
		$locObj->coordinates    = array( $location->lon, $location->lat );
		 
		$result     = new \stdClass();
		if ( empty( $name ) || empty( ( array ) $location ) || empty( $fbId ) || empty( $gender ) || ( empty( $email ) && empty( $mobile ) ) ) {
		    $result->error  = "ERROR_INVALID_PARAMETERS";
		    $result->status = 403;
		} else {
		    $user       = array(
		        "email"     => $email,
		        "fbId"      => $fbId,
		        "gender"    => $gender,
		        "location"  => $locObj,
		        "mobile"    => $mobile,
		        "name"      => $name
		    );
		    $result     = $this->_model->create( $user );
		}
		 
		return $this->_response( $result );
	}
	 
	public function get( Request $request, $id ) {
		$token  = $request->get( 'token' );
 
		$result = new \stdClass();
		if ( !$this->_check_session( $token ) ) {
		    $result->error  = "PERMISSION_DENIED";
		    $result->status = 403;
		} else {
		    $result = $this->_model->get( $id );
		}
		 
		return $this->_response( $result );
	}
	 
	public function remove( Request $request, $id ) {
		$token  = $request->get( 'token' );
		$result = new \stdClass();
	 
		if ( !$this->_check_session( $token, $id ) ) {
		    $result->error  = "PERMISSION_DENIED";
		    $result->status = 403;
		} else {
		    $result = $this->_model->remove( $id );
		    if ( !$result ) {
		        $result         = new \stdClass();
		        $result->error  = $this->_model->get_error();
		        $result->status = 403;
		    }
		}
		 
		return $this->_response( $result );
	}
	 
	public function retrieve( Request $request ) {
		$token      = $request->get( 'token' );
		$distance   = $request->get( 'distance' );
		 
		$session    = $this->_check_session( $token );
		$result     = $this->_model->retrieve( ( isset( $session->user_id ) ? $session->user_id : "" ), $distance, $request->get( 'limit' ), $request->get( 'page' ) );
		if ( !is_array( $result ) && !$result ) {
		    $result         = new \stdClass();
		    $result->error  = $this->_model->get_error();
		    $result->status = 403;
		}
 
		return $this->_response( $result );
	}
	 
	public function update( Request $request, $id ) {
		$token      = $request->get( 'token' );
		$data       = new \stdClass();
		if ( !empty( $email = $request->get( 'email' ) ) ) {
		    $data->email    = $email;
		}
		if ( !empty( $fbId = $request->get( 'fbId' ) ) ) {
		    $data->fbId     = $fbId;
		}
		if ( !empty( $gender = $request->get( 'gender' ) ) ) {
		    $data->gender   = $gender;
		}
		if ( !empty( $location = $request->get( 'location' ) ) ) {
		    if ( gettype( $location ) == "string" ) {
		        $location   = json_decode( $location );
		    }
		    $locObj                 = new \stdClass();
		    $locObj->type           = "Point";
		    $locObj->coordinates    = array( $location->lon, $location->lat );
		        
		    $data->location = $locObj;
		}
		if ( !empty( $mobile = $request->get( 'mobile' ) ) ) {
		    $data->mobile   = $mobile;
		}
		if ( !empty( $name = $request->get( 'name' ) ) ) {
		    $data->name     = $name;
		}
		 
		$result     = new \stdClass();
		if ( !$this->_check_session( $token, $id ) ) {
		    $result->error  = "PERMISSION_DENIED";
		    $result->status = 403;
		} else {
		    $result = $this->_model->update( $id, $data );
		    if ( !$result ) {
		        $result         = new \stdClass();
		        $result->error  = $this->_model->get_error();
		        $result->status = 403;
		    }
		}
		 
		return $this->_response( $result );
	}
}
