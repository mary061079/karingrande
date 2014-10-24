<?php get_header(); ?>
<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

	   <header class="page-header">
				<h1 class="page-title">
					<?php
						if ( is_category() ) :
							single_cat_title();

						elseif ( is_tag() ) :
							single_tag_title();

						elseif ( is_author() ) :
							/* Queue the first post, that way we know
							 * what author we're dealing with (if that is the case).
							*/
							the_post();
							printf( __( 'Author: %s', 'radiate' ), '<span class="vcard">' . get_the_author() . '</span>' );
							/* Since we called the_post() above, we need to
							 * rewind the loop back to the beginning that way
							 * we can run the loop properly, in full.
							 */
							rewind_posts();

						elseif ( is_day() ) :
							printf( __( 'Day: %s', 'radiate' ), '<span>' . get_the_date() . '</span>' );

						elseif ( is_month() ) :
							printf( __( 'Month: %s', 'radiate' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );

						elseif ( is_year() ) :
							printf( __( 'Year: %s', 'radiate' ), '<span>' . get_the_date( 'Y' ) . '</span>' );

						elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
							_e( 'Asides', 'radiate' );

						elseif ( is_tax( 'post_format', 'post-format-image' ) ) :
							_e( 'Images', 'radiate');

						elseif ( is_tax( 'post_format', 'post-format-video' ) ) :
							_e( 'Videos', 'radiate' );

						elseif ( is_tax( 'post_format', 'post-format-quote' ) ) :
							_e( 'Quotes', 'radiate' );

						elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
							_e( 'Links', 'radiate' );

						else :
							_e( 'Archives', 'radiate' );

						endif;
					?>
				</h1>
				<?php
					// Show an optional term description.
					$term_description = term_description();
					if ( ! empty( $term_description ) ) :
						printf( '<div class="taxonomy-description">%s</div>', $term_description );
					endif;
				?>
			</header><!-- .page-header -->
			          <?php $category = get_category( get_query_var( 'cat' ) );
                      $cat_id = $category->cat_ID;
			                $categories = get_categories(
                    $args = array( 'child_of' => $cat_id,
                                   'hide_empty' => 0
                                   )
                ); 
                ?>
                <article class="post type-post status-publish format-standard hentry category-boys-of-vine category-nash-grier">
	                   <header class="entry-header"> 
                <?php foreach( $categories as $category ) {   ?>
                   
                         <h1 class="entry-title">
                     <?php echo '<a href="' . get_category_link( (int)$category->term_id ) . '">' . $category->name . '</a>'; ?>
                         </h1>
                  
              <?php   } ?>
                
                 	</header><!-- .entry-header -->
                </article><!-- #post-## -->    
					</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>