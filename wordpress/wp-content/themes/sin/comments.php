<?php
/**
 * The template for displaying comments
 *
 * @package sin
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
  return;
}
?>

<div id="comments" class="comments-area">

  <?php
  // You can start editing here -- including this comment!
  if ( have_comments() ) : ?>
    <h2 class="comments-title">
      <?php
      $comment_count = get_comments_number();
      if ( 1 === $comment_count ) {
        echo esc_html_e( 'One Comment', 'sin' );
      } else {
        printf( // WPCS: XSS OK.
          /* translators: 1: comment count number, 2: title. */
          esc_html( _nx( '%1$s Comment', '%1$s Comments', $comment_count, 'comments title', 'sin' ) ),
          number_format_i18n( $comment_count )
        );
      }
      ?>
    </h2><!-- .comments-title -->

    <ol class="comment-list">
      <?php
        wp_list_comments( array(
          'callback'=>'sin_list_comments'
        ) );
      ?>
    </ol><!-- .comment-list -->

    <?php sin_comments_navigation();

    // If comments are closed and there are comments, let's leave a little note, shall we?
    if ( ! comments_open() ) : ?>
      <p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'sin' ); ?></p>
    <?php
    endif;

  endif; // Check for have_comments().

  comment_form();
  ?>

</div><!-- #comments -->
