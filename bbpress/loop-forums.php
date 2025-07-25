<?php

/**
 * Forums Loop
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '<div style="border: 1px solid #ff0000; position: relative;">';
	echo '<div style="position: absolute; top: -16px; left: 0; color: rgb(255, 0, 0, 0.5); font-size: 8px; font-weight: bold; z-index: 1000;">loop-forums.php</div>';
}

do_action('bbp_template_before_forums_loop'); ?>

<ul id="forums-list-<?php bbp_forum_id(); ?>" class="bbp-forums">

	<li class="bbp-header">

		<ul class="forum-titles">
			<li class="bbp-forum-info"><?php esc_html_e('Forum', 'bbpress'); ?></li>
			<li class="bbp-forum-topic-count"><?php esc_html_e('Topics', 'bbpress'); ?></li>
			<li class="bbp-forum-reply-count"><?php bbp_show_lead_topic()
													? esc_html_e('Replies', 'bbpress')
													: esc_html_e('Posts',   'bbpress');
												?></li>
			<li class="bbp-forum-freshness"><?php esc_html_e('Last Post', 'bbpress'); ?></li>
		</ul>

	</li><!-- .bbp-header -->

	<li class="bbp-body">

		<?php while (bbp_forums()) : bbp_the_forum(); ?>

			<?php bbp_get_template_part('loop', 'single-forum'); ?>

		<?php endwhile; ?>

	</li><!-- .bbp-body -->

	<li class="bbp-footer">

		<div class="tr">
			<p class="td colspan4">&nbsp;</p>
		</div><!-- .tr -->

	</li><!-- .bbp-footer -->

</ul><!-- .forums-directory -->

<?php do_action('bbp_template_after_forums_loop');

if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '</div>';
}
?>