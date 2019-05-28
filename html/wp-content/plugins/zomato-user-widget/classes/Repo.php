<?php namespace Zomato;

use Exception;

class Repo {

  private $_version  = "v1";
  private $_endpoint = "https://www.zomato.com/user-widget";


  public function getUser( $user_id, $user_lang )
  {
    return $this->_makeRequest( '/user' , $user_id, $user_lang );
  }


  protected function _makeRequest( $path, $user_id, $user_lang ) {

    $url  = $this->_endpoint;
    $url .= "/" . $this->_version;
    $url .= $path;
    $url .= "?user_id=" . urlencode($user_id);
    $url .= "&lang=" . urlencode($user_lang);

    $response = wp_remote_get( $url );

    $response_code = wp_remote_retrieve_response_code( $response );

    if ( $response_code == 400 ) {
      throw new Exception("Unable to retrieve any data. Please make sure you have entered a valid email address.");
    } elseif ( $response_code == 204 ) {
      throw new Exception("No such user exists. Please make sure you have entered the correct email id");
    } elseif ( $response_code != 200 ) {
      throw new Exception("Could not establish a connection with Zomato.");
    }

    $response_body = wp_remote_retrieve_body( $response );
    $response_body = json_decode( $response_body, true );

    return $response_body;
  }

} // end class


?>
