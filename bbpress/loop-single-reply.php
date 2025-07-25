<?php

/**
 * Replies Loop - Single Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '<div style="border: 1px solid #ff0000; position: relative;">';
	echo '<div style="position: absolute; top: -16px; left: 0; color: rgb(255, 0, 0, 0.5); font-size: 8px; font-weight: bold; z-index: 1000;">loop-single-reply.php</div>';
}

?>

<div id="post-<?php bbp_reply_id(); ?>" class="bbp-reply-header">
	<div class="bbp-meta">
		<span class="bbp-reply-post-date"><?php bbp_reply_post_date(); ?></span>

		<?php if (bbp_is_single_user_replies()) : ?>

			<span class="bbp-header">
				<?php esc_html_e('in reply to: ', 'bbpress'); ?>
				<a class="bbp-topic-permalink" href="<?php bbp_topic_permalink(bbp_get_reply_topic_id()); ?>"><?php bbp_topic_title(bbp_get_reply_topic_id()); ?></a>
			</span>

		<?php endif; ?>

		<a href="<?php bbp_reply_url(); ?>" class="bbp-reply-permalink">#<?php bbp_reply_id(); ?></a>

		<?php do_action('bbp_theme_before_reply_admin_links'); ?>

		<?php bbp_reply_admin_links(); ?>

		<?php do_action('bbp_theme_after_reply_admin_links'); ?>

	</div><!-- .bbp-meta -->
</div><!-- #post-<?php bbp_reply_id(); ?> -->

<div <?php bbp_reply_class(); ?>>
	<div class="bbp-reply-author">

		<?php do_action('bbp_theme_before_reply_author_details'); ?>

		<?php bbp_reply_author_link(array('show_role' => true)); ?>

		<?php if (current_user_can('moderate', bbp_get_reply_id())) : ?>

			<?php do_action('bbp_theme_before_reply_author_admin_details'); ?>

			<div class="bbp-reply-ip"><?php bbp_author_ip(bbp_get_reply_id()); ?></div>

			<?php do_action('bbp_theme_after_reply_author_admin_details'); ?>

		<?php endif; ?>

		<?php do_action('bbp_theme_after_reply_author_details'); ?>

	</div><!-- .bbp-reply-author -->

	<div class="bbp-reply-content">

		<?php do_action('bbp_theme_before_reply_content'); ?>

		<?php bbp_reply_content(); ?>

		<?php do_action('bbp_theme_after_reply_content'); ?>

	</div><!-- .bbp-reply-content -->
</div><!-- .reply -->

<?php
if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '</div>';
}
?>