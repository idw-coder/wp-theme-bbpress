<?php

/**
 * Single Topic Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '<div style="border: 1px solid #ff0000; position: relative;">';
	echo '<div style="position: absolute; top: -16px; left: 0; color: rgb(255, 0, 0, 0.5); font-size: 8px; font-weight: bold; z-index: 1000;">content-single-topic.php</div>';
}
?>

<div id="bbpress-forums" class="bbpress-wrapper">

	<?php bbp_breadcrumb(); ?>

	<?php bbp_topic_subscription_link(); ?>

	<?php bbp_topic_favorite_link(); ?>

	<?php do_action('bbp_template_before_single_topic'); ?>

	<?php if (post_password_required()) : ?>

		<?php bbp_get_template_part('form', 'protected'); ?>

	<?php else : ?>

		<?php bbp_topic_tag_list(); ?>

		<?php bbp_single_topic_description(); ?>

		<?php if (bbp_show_lead_topic()) : ?>

			<?php bbp_get_template_part('content', 'single-topic-lead'); ?>

		<?php endif; ?>

		<?php if (bbp_has_replies()) : ?>

			<?php bbp_get_template_part('pagination', 'replies'); ?>

			<?php bbp_get_template_part('loop',       'replies'); ?>

			<?php bbp_get_template_part('pagination', 'replies'); ?>

		<?php endif; ?>

		<?php bbp_get_template_part('form', 'reply'); ?>

	<?php endif; ?>

	<?php bbp_get_template_part('alert', 'topic-lock'); ?>

	<?php do_action('bbp_template_after_single_topic'); ?>

</div>

<?php
if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '</div>';
}
?>