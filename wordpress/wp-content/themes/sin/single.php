<?php
/**
 * The template for displaying all single posts
 *
 * @package sin
 */

get_header(); ?>

<div id="content" class="site-content">
  <div class="container">

<?php
$blog_layout = get_theme_mod('blog_layout', 'right_sidebar');

if ($blog_layout == 'right_sidebar') {
?>
      <div class="row">
        <div class="col-md-8 sidebar-right">
<?php
}
if ($blog_layout == 'left_sidebar') {
?>
      <div class="row">
        <div class="col-md-8 col-md-push-4 sidebar-left">
<?php
}
?>

          <div id="primary" class="content-area">
            <main id="main" class="site-main">

            <?php
            while ( have_posts() ) : the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
              <?php if (has_post_thumbnail()) {?> 
                <div class="post-media">
                  <?php the_post_thumbnail();?>
                </div>
              <?php }?>

              <div class="post-content clearfix">
                <?php sin_entry_header(); ?>

              <div class="entry-content clearfix">
                <?php
                the_content();

                wp_link_pages( array(
                  'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sin' ),
                  'after'  => '</div>',
                ) );
                ?>
              </div><!-- .entry-content -->

              <?php 
              sin_entry_footer();

              $show_about_author = get_theme_mod('single_show_about_author', 0);
              if (!post_password_required() && $show_about_author) {
                sin_about_the_author();
              }

              $show_post_nav = get_theme_mod('single_show_post_nav', 1);
              if ($show_post_nav) {
                sin_post_navigation();
              }
              ?>
                </div>
            </article><!-- #post-<?php the_ID(); ?> -->
                          
            <?php
              if ( comments_open() || get_comments_number() ) :
                comments_template();
              endif;

            endwhile; // End of the loop.
            ?>

            </main><!-- #main -->
          </div><!-- #primary -->

<?php
if ($blog_layout == 'right_sidebar') {
?>
        </div>
        <div class="col-md-4 sidebar-right">
          <?php get_sidebar();?>
        </div>
      </div>
<?php
}
if ($blog_layout == 'left_sidebar') {
?>
        </div>
        <div class="col-md-4 col-md-pull-8 sidebar-left">
          <?php get_sidebar();?>
        </div>
      </div>
<?php
}
?>

  </div>
</div><!-- #content -->

<?php
get_footer();
