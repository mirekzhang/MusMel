<div class="zuw">

  <div id="zuw__overlay"></div>
  <div id="zuw__blurred-bg" style="background-image: url('<?php echo $user->getBlurredImageUrl(); ?>');"></div>

  <a href="<?php echo $user->getProfileLink(); ?>" class="zuw__profile-link">

    <div id="zuw__profile-pic" style="background-image: url('<?php echo $user->getProfileImageURL(); ?>')"></div>

    <p id="zuw__name">
      <?php echo $user->getUserName(); ?>
    </p>

    <p id="zuw__level">
      <span class="zuw__level-icon zuw__level-icon--l<?php echo $user->getUserRank(); ?>"><?php echo $user->getUserRank(); ?></span>
      <?php echo $user->getUserLevel(); ?>
    </p>

    <?php if( $user->hasBio() ): ?>
      <p id="zuw__bio">
        <?php echo $user->getUserBio(); ?>
      </p>
    <?php endif; ?>

  </a>

  <ul id="zuw-stats" class="cf">

    <li class="zuw-stat">
      <a target="_blank" href="<?php echo $user->getReviewUrl(); ?>">
        <span class="zuw-stat--number"><?php echo $user->getReviewCount(); ?></span>
        <span class="zuw-stat--label"><?php echo $user->getReviewText(); ?></span>
      </a>
    </li>

    <li class="zuw-stat">
      <a target="_blank" href="<?php echo $user->getPhotoUrl(); ?>">
        <span class="zuw-stat--number"><?php echo $user->getPhotoCount(); ?></span>
        <span class="zuw-stat--label"><?php echo $user->getPhotoText(); ?></span>
      </a>
    </li>

    <li class="zuw-stat">
      <a target="_blank" href="<?php echo $user->getNetworkUrl(); ?>">
        <span class="zuw-stat--number"><?php echo $user->getFollowerCount(); ?></span>
        <span class="zuw-stat--label"><?php echo $user->getFollowerText(); ?></span>
      </a>
    </li>

  </ul>
  <!-- end zuw-stats -->

</div>
<!-- end zuw -->
