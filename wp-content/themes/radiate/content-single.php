<?php
/**
 * The template used for displaying page content in single.php
 *
 * @package ThemeGrill
 * @subpackage Radiate
 * @since Radiate 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemtype="http://schema.org/Article">
	<header class="entry-header">
		<h1 class="entry-title" itemprop="name"><?php the_title(); ?></h1>

		<div class="entry-meta" >
			<?php radiate_posted_on(); ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'radiate' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
		<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
			<?php
				/* translators: used between list items, there is a space after the comma */
				$categories_list = get_the_category_list( __( ', ', 'radiate' ) );
				if ( $categories_list && radiate_categorized_blog() ) :
			?>
			<span class="cat-links">
				<?php echo $categories_list; ?>
			</span>
			<?php endif; // End if categories ?>

			<?php
				/* translators: used between list items, there is a space after the comma */
				$tags_list = get_the_tag_list( '', __( ', ', 'radiate' ) );
				if ( $tags_list ) :
			?>
			<span class="tags-links">
				<?php echo $tags_list; ?>
			</span>
			<?php endif; // End if $tags_list ?>
		<?php endif; // End if 'post' == get_post_type() ?>

		<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
		<span class="comments-link">
                    <a href="<?php the_permalink() ?>#comments"><?php comments_number( __( 'Leave a comment', 'radiate' ), __( '1 Comment', 'radiate' ), __( '% Comments', 'radiate' ) ) 
                    //comments_popup_link( __( 'Leave a comment', 'radiate' ), __( '1 Comment', 'radiate' ), __( '% Comments', 'radiate' ) ); ?></a></span>
		<?php endif; ?>

		<?php edit_post_link( __( 'Edit', 'radiate' ), '<span class="edit-link">', '</span>' ); ?>
                <div class="social_wrapper"> 
                    <?php show_share_buttons() ?>
                    <div class="addthis_sharing_toolbox"></div>
                </div>
                <?php radiate_post_nav(); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
