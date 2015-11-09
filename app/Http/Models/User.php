<?php namespace App\Http\Models;
 
use App\Http\Models\Base as Model;
 
class User extends Model {
 
    private $_col   = "users";
 
    private $_error = null;
 
    public function get( $where ) {
    	if ( is_array( $where ) ) {
		    return $this->_findOne( $this->_col, $where );
		} else {
		    $this->_where( '_id', $where );
		    return $this->_findOne( $this->_col );
		}
    }
 
    public function get_error() {
    	return $this->_error;
    }
 
    public function create( $user ) {
    	if ( is_array( $user ) ) {
		    $user   = ( object ) $user;
		}
		$this->_where( '$or', array(
		        array(
		            "email"     => $user->email
		        ),
		        array(
		            "mobile"    => $user->mobile
		        )
		    )
		);
		$existing   = $this->_findOne( $this->_col );
		 
		if ( empty( ( array ) $existing ) ) {
		    $user   = $this->_insert( $this->_col, $user );
		} else {
		    $user   = $existing;
		}
		 
		$user->_id  = ( string ) $user->_id;
		 
		return $user;
    }
 
    public function remove( $id ) {
    	$this->_where( '_id', $id );
		$user   = $this->_findOne( $this->_col );
		 
		if ( empty( ( array ) $user ) ) {
		    $this->_error       = "ERROR_INVALID_ID";
		    return false;
		} else {
		    $this->_where( '_id', $id );
		    if ( !$this->_remove( $this->_col ) ) {
		        $this->_error   = "ERROR_REMOVING_USER";
		        return false;
		    }
		}
		 
		return $user;
    }
 
    public function retrieve( $id, $distance, $limit = 9999, $page = 1 ) {
    	if ( !empty( $id ) && !empty( $distance ) ) {
		    $this->_where( '_id', $id );
		    $this->_select( 'location' );
		    $user   = $this->_findOne( $this->_col );
 
		    if ( empty( ( array ) $user ) ) {
		        $this->_error   = "ERROR_INVALID_USER";
		        return false;
		    }
		 
    		$this->_where( '$and', array(
            array(
                '_id'       => array( '$ne' => new \MongoId( $id ) )
            ),
            array(
                'location'  => array(
                    '$nearSphere'       => array(
                        '$geometry'     => array(
                            'type'          => "Point",
                            'coordinates'   => $user->location['coordinates']
                        ),
                        '$maxDistance'  => ( float ) $distance
                    )
                )
	            )
	        ) );
		}
		 
		$this->_limit( $limit, ( $limit * --$page ) );
		return $this->_find( $this->_col );
    }
 
    public function update( $id, $data ) {
    	if ( is_array( $data ) ) {
		    $data   = ( object ) $data;
		}
		if ( isset( $data->email ) || isset( $data->mobile ) ) {
		    $this->_where( '$and', array(
		        array(
		             '_id'       => array( '$ne' => new \MongoId( $id ) )
		             ),
		        array(
		             '$or'       => array(
                		array(
		             		'email'     => ( isset( $data->email ) ) ? $data->email : ""
	                   	),
                		array(
                	        'mobile'    => ( isset( $data->mobile ) ) ? $data->mobile : ""
		    	           )
		                )
		            )
		        )
		    );

		    $existing   = $this->_findOne( $this->_col );
		    if ( !empty( ( array ) $existing ) && $existing->_id != $id ) {
		        $this->_error   = "ERROR_EXISTING_USER";
		        return false;
		    }
		}
		 
		$this->_where( '_id', $id );
		return $this->_update( $this->_col, ( array ) $data );
    }
}