<?php

/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">

		<?php if (have_posts()) : ?>

			<?php if (is_home() && ! is_front_page()) : ?>
				<header>
					<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
				</header>
			<?php endif; ?>

		<?php
			// Start the loop.
			while (have_posts()) :
				the_post();

				/*
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that
				 * will be used instead.
				 */
				get_template_part('template-parts/content', get_post_format());

			// End the loop.
			endwhile;

			// Previous/next page navigation.
			the_posts_pagination(
				array(
					'prev_text'          => __('Previous page', 'twentysixteen'),
					'next_text'          => __('Next page', 'twentysixteen'),
					/* translators: Hidden accessibility text. */
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'twentysixteen') . ' </span>',
				)
			);

		// If no content, include the "No posts found" template.
		else :
			get_template_part('template-parts/content', 'none');

		endif;
		?>

	</main><!-- .site-main -->
</div><!-- .content-area -->

<?php // get_sidebar(); 
?>
<?php // get_footer(); 
?>