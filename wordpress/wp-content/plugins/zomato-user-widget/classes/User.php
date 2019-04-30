<?php namespace Zomato;

include( plugin_dir_path( __FILE__ ) . '/Repo.php');
use Zomato\Repo as Repo;

class User {

  private $user_data;

  public function __construct( $user_id, $user_lang )
  {
    $user_repo = new Repo();
    $this->user_data = $user_repo->getUser( $user_id, $user_lang );
  }

  public function getProfileLink()
  {
    return $this->user_data['profile_link'];
  }

  public function getProfileImageUrl()
  {
    return $this->user_data['profile_image_link'];
  }

  public function getBlurredImageUrl()
  {
    return $this->user_data['profile_image_blurred'];
  }

  public function getUserName()
  {
    return $this->user_data['user_name'];
  }

  public function getUserRank()
  {
    return $this->user_data['user_rating'];
  }

  public function getUserBio()
  {
    return $this->user_data['user_bio'];
  }

  public function getUserLevel()
  {
    return $this->user_data['user_status'];
  }

  public function getReviewCount()
  {
    return $this->user_data['num_of_reviews'];
  }

  public function getPhotoCount()
  {
    return $this->user_data['num_of_photos'];
  }

  public function getFollowerCount()
  {
    return $this->user_data['num_of_followers'];
  }

  public function getReviewUrl()
  {
    return $this->user_data['user_review_url'];
  }

  public function getPhotoUrl()
  {
    return $this->user_data['user_photo_url'];
  }

  public function getNetworkUrl()
  {
    return $this->user_data['user_network_url'];
  }

  public function getReviewText()
  {
    return $this->user_data['review_text'];
  }

  public function getPhotoText()
  {
    return $this->user_data['photo_text'];
  }

  public function getFollowerText()
  {
    return $this->user_data['follower_text'];
  }

  public function hasBio()
  {
    return ( empty( $this->user_data['user_bio'] ) ) ? false : true;
  }

} // end class

?>
