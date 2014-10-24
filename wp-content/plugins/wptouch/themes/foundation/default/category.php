<?php get_header();
$category = get_category( get_query_var( 'cat' ) );

if( $category->slug == 'boys-of-vine' || $category->slug == 'nickelodeon-news' ) {
    $cat_id = $category->term_id;
    $categories = get_categories(
        $args = array(
            'child_of' => (int)$cat_id,
            'hide_empty' => 0
        )
    ); ?>
    <div>
        <div id="menu" class="wptouch-menu">
            <ul class="menu-tree parent-links">
   <?php
    foreach( $categories as $category ) {?>

                <li>
                    <img src="http://karingrande.com/wp-content/plugins/wptouch/resources/icons/elegant/Paper.png" alt="menu-icon">
                    <a href="<?php echo get_category_link( (int)$category->term_id )?>"><?php echo $category->name ?></a>
                </li>
 <?php }
    echo '</ul></div></div>';
    get_footer();
    die;
}
 ?>

    <div id="content">

        <?php if ( wptouch_have_posts() ) {
            while ( wptouch_have_posts() ) {
                if ( $i % 3 == 0 ) {
                    responsive_ads_unit();
                }?>
                <?php wptouch_the_post(); ?>
                <div class="<?php wptouch_post_classes(); ?>">
                    <?php get_template_part( 'post-loop' ); ?>
                </div> <!-- post classes -->
                <?php $i++;
            }
        } else { ?>
            <!-- no posts -->
        <?php } ?>

        <?php if ( foundation_is_theme_using_module( 'infinite-scroll' ) ) { ?>

            <?php if ( get_next_posts_link() ) { ?>
                <!-- hidden in css, needed to add js -->
                <a class="infinite-link" href="#" rel="<?php echo get_next_posts_page_link(); ?>"></a>
            <?php } ?>

        <?php } elseif ( foundation_is_theme_using_module( 'load-more' ) ) { ?>

            <!-- show the load more if we have more posts/pages -->
            <?php if ( get_next_posts_link() ) { ?>
                <a class="load-more-link tappable no-ajax" href="javascript:return false;" rel="<?php echo get_next_posts_page_link(); ?>">
                    <?php wptouch_fdn_archive_load_more_text(); ?>&hellip;
                </a>
            <?php } ?>

        <?php } else { ?>

            <div class="posts-nav">
                <?php posts_nav_link( ' | ', '&lsaquo; ' . __( 'newer posts', 'wptouch-pro' ), __( 'older posts', 'wptouch-pro' ) . ' &rsaquo;' ); ?>
            </div>

        <?php } ?>

    </div><!-- #content -->

<?php get_footer(); ?>