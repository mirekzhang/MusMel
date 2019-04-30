<?php
/**
 * Custom template tags for this theme
 *
 * @package sin
 */

if (!function_exists('sin_entry_header')) {
  function sin_entry_header() {
    $show_categories = get_theme_mod('blog_show_categories', 1);

    echo '<header class="entry-header clearfix">';

    if ($show_categories) {
      if ( 'post' === get_post_type() ) {
        echo '<div class="entry-meta clearfix">';

        if ($show_categories) {
          $categories_list = get_the_category_list( esc_html__( ', ', 'sin' ) );
          if ( $categories_list ) {
            echo '<span class="cat-links">' . $categories_list . '</span>';
          }
        }

        echo '</div><!-- .entry-meta -->';
      }
    }
    if ( is_singular() ) {
      the_title( '<h1 class="entry-title">', '</h1>' );
    } else {
      the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
    }

    echo '</header><!-- .entry-header -->';
  }
}

if (!function_exists('sin_entry_footer')) {
  function sin_entry_footer() {
    $show_date = get_theme_mod('blog_show_date', 1);
    $show_author = get_theme_mod('blog_show_author', 1);
    $show_tags = get_theme_mod('blog_show_tags', 1);

    if ($show_date||$show_author||$show_tags) {
      if ( 'post' === get_post_type() ) {
        echo '<footer class="entry-footer clearfix">';
          echo '<div class="meta-info">';
          if($show_date){
            $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
            if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
              $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
            }
            $time_string = sprintf( $time_string,
              esc_attr( get_the_date( 'c' ) ),
              esc_html( get_the_date() ),
              esc_attr( get_the_modified_date( 'c' ) ),
              esc_html( get_the_modified_date() )
            );
            $posted_on = '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>';
            echo '<span class="posted-on">' . $posted_on . '</span>';
          }
          if($show_author){
            $byline = sprintf(
              esc_html_x( 'by %s', 'post author', 'sin' ),
              '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
            );
            echo '<span class="byline">' . $byline . '</span>';
          }
          echo '</div>';
          
          if(is_single()){
            if($show_tags){
              $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'sin' ) );
              if ( $tags_list ) {
                printf( '<div class="tags-links">' . esc_html__( 'Tagged: %1$s', 'sin' ) . '</div>', $tags_list );
              }
            }
          }
        echo '</footer><!-- .entry-footer -->';
      }
    }
  }
}

if (!function_exists('sin_posts_navigation')) {
  function sin_posts_navigation() {
    the_posts_navigation(array(
      'prev_text' => '<i class="fa fa-caret-left"></i> '.esc_html__('Older posts','sin'),
      'next_text'  => esc_html__('Newer posts','sin').' <i class="fa fa-caret-right"></i>'      
    ));
  }
}

if (!function_exists('sin_post_navigation')) {
  function sin_post_navigation(){
    the_post_navigation( array(
          'prev_text' => '<i class="fa fa-caret-left"></i> %title',
          'next_text' => '%title <i class="fa fa-caret-right"></i>'
    ) );
  }
}

if (!function_exists('sin_comments_navigation')) {
  function sin_comments_navigation(){
    the_comments_navigation(array(
      'prev_text' => '<i class="fa fa-caret-left"></i> '.esc_html__( 'Older comments' ,'sin'),
      'next_text' => esc_html__( 'Newer comments' ,'sin').' <i class="fa fa-caret-right"></i>'
    ));
  }
}

if (!function_exists('sin_posts_pagination')) {
  function sin_posts_pagination(){
    the_posts_pagination(array(
      'prev_text' => '<i class="fa fa-caret-left"></i>',
      'next_text' => '<i class="fa fa-caret-right"></i>'
    ));
  }
}

if (!function_exists('sin_about_the_author')) {
  function sin_about_the_author() {
    $author_ID = get_the_author_meta('ID');
    $author_email = get_the_author_meta('user_email');
    $author_display_name = get_the_author_meta('display_name');
    $author_posts_url = get_author_posts_url($author_ID);
    ?>
    <div class="about-author clearfix">
      <div class="about-author-avatar">
        <a href="<?php echo esc_url($author_posts_url); ?>">
          <?php echo get_avatar($author_email, '60', '', esc_attr($author_display_name)); ?>
        </a>
      </div>
      <div class="about-author-bio-wrap">
        <div class="about-author-name">
          <?php the_author_posts_link(); ?>
          <span>(<?php the_author_posts(); esc_html_e(' Posts', 'sin'); ?>)</span>
        </div>
        <div class="about-author-bio">
          <?php the_author_meta('description'); ?>
        </div>
        <a href="<?php echo esc_url($author_posts_url); ?>" class="about-author-link">
          <?php esc_html_e('View all author&rsquo;s posts', 'sin'); ?><i class="fa fa-caret-right"></i>
        </a>
      </div>
    </div>
    <?php
  }
}
