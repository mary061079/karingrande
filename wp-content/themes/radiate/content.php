<?php
/**
 * @package ThemeGrill
 * @subpackage Radiate
 * @since Radiate 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemtype="http://schema.org/Article">
    <header class="entry-header">
        <h1 class="entry-title"  itemprop="name"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>

        <?php if ('post' == get_post_type()) : ?>
            <div class="entry-meta">
                <?php radiate_posted_on(); ?>
            </div><!-- .entry-meta -->
        <?php endif; ?>
    </header><!-- .entry-header -->

    <?php if (is_search()) : // Only display Excerpts for Search ?>

        <div class="entry-summary" itemscope>
            <?php the_excerpt(); ?>
        </div><!-- .entry-summary -->
    <?php else : ?>       	
        <?php //the_post_thumbnail( 'thumbnail', array( 'itemprop'=>'image' )) ?>
        <div class="entry-content" itemprop="desc"> 
            <?php the_content(__('Read more <span class="meta-nav">&rarr;</span>', 'radiate')); ?>
            <?php
            wp_link_pages(array(
                'before' => '<div class="page-links">' . __('Pages:', 'radiate'),
                'after' => '</div>',
            ));
            ?>
        </div><!-- .entry-content -->
    <?php endif; ?>

    <footer class="entry-meta" itemprop="footer">
        <?php if ('post' == get_post_type()) : // Hide category and tag text for pages on Search ?>
            <?php
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list(__(', ', 'radiate'));
            if ($categories_list && radiate_categorized_blog()) :
                ?>
                <span class="cat-links">
                    <?php echo $categories_list; ?>
                </span>
            <?php endif; // End if categories ?>

            <?php
            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list('', __(', ', 'radiate'));
            if ($tags_list) :
                ?>
                <span class="tags-links">
                    <?php echo $tags_list; ?>
                </span>
            <?php endif; // End if $tags_list ?>
        <?php endif; // End if 'post' == get_post_type() ?>

        <?php if (!post_password_required() && ( comments_open() || '0' != get_comments_number() )) : ?>
            <span class="comments-link"><?php comments_popup_link(__('Leave a comment', 'radiate'), __('1 Comment', 'radiate'), __('% Comments', 'radiate')); ?></span>
        <?php endif; ?>

        <?php edit_post_link(__('Edit', 'radiate'), '<span class="edit-link">', '</span>'); ?>  
    </footer><!-- .entry-meta -->
    <div class="social_wrapper"> 
        <!-- Go to www.addthis.com/dashboard to customize your tools -->
        <?php show_share_buttons() ?>




    </div> 
</article><!-- #post-## -->       