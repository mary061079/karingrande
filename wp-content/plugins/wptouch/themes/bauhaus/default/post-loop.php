<div class="<?php foundation_sharing_classes(); ?>">
	<a class="facebook-btn no-ajax" href="//www.facebook.com/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" target="_blank"><?php _e( 'Share', 'wptouch-pro' ); ?></a>
	<a class="twitter-btn no-ajax" href="//twitter.com/intent/tweet?source=wptouchpro3&text=<?php echo htmlspecialchars( urlencode( html_entity_decode( get_the_title() . ' - ' ) ) ); ?>&url=<?php echo urlencode( get_permalink() ); ?>" target="_blank"><?php _e( 'Tweet', 'wptouch-pro' ); ?></a>
	<a class="google-btn no-ajax" href="//plus.google.com/share?url=<?php echo urlencode( get_permalink() ); ?>" target="_blank">+ 1</a>
	<a class="email-btn no-ajax" href="mailto:?subject=<?php echo rawurlencode( get_the_title() ); ?>&body=<?php echo rawurlencode( get_permalink() ); ?>"><?php  _e( 'Mail', 'wptouch-pro' ); ?></a>
</div>
<?php foundation_handle_share_links( $content, $top_share = false ) ?>
<a href="<?php wptouch_the_permalink(); ?>" class="loop-link tappable clearfix <?php if ( !bauhaus_should_show_thumbnail() ) { echo 'no-thumbs'; } ?>">

	<?php if ( wptouch_get_comment_count() > 0 && comments_open() ) { ?>
		<div class="comments">
			<span><?php comments_number( '0', '1', '%' ); ?></span>
		</div>
	<?php } ?>
    <div class="data-wrapper">
	<?php if ( bauhaus_should_show_thumbnail() && wptouch_has_post_thumbnail() ) { ?>
		<img src="<?php wptouch_the_post_thumbnail( 'medium' ); ?>" alt="<?php the_title() ?>" title="<?php the_title() ?>" class="post-thumbnail wp-post-image" />
	<?php } else if ( bauhaus_should_show_thumbnail() && !wptouch_has_post_thumbnail() ) { ?>

            <div class="date-circle">
                <span class="month"><?php wptouch_the_time( 'M' ); ?></span>
                <span class="day"><?php wptouch_the_time( 'j' ); ?></span>
            </div>


	<?php } ?>

	<?php if ( bauhaus_should_show_date() || bauhaus_should_show_author() ) { ?>
		<span class="post-date-author body-font">
			<?php if ( bauhaus_should_show_date() ) { wptouch_the_time(); } ?>
		 	<?php if ( bauhaus_should_show_author() ) { ?>
		 		<?php if ( bauhaus_should_show_date() ) echo '&bull;'; ?> <?php _e( 'by', 'wptouch-pro' ); ?> <?php the_author(); ?>
		 	<?php } ?>
		 </span>
	 <?php } ?>

	<h2 class="post-title heading-font"><?php the_title(); ?></h2>
    </div>

	<?php if ( wptouch_should_load_rtl() ) { ?>
		<i class="arrow icon-angle-left"></i>
	<?php } else { ?>
		<i class="arrow icon-angle-right"></i>
	<?php } ?>
    <!--span class="bottom-border"><!--css border--></span-->
</a>
<div class="post-content body-font">
    <?php wptouch_the_excerpt(); ?>
</div>
