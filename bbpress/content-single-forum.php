<?php

/**
 * Single Forum Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// デバッグ用ボーダー開始
if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '<div style="border: 1px solid #ff0000; position: relative;">';
	echo '<div style="position: absolute; top: -16px; left: 0; color: rgb(255, 0, 0, 0.5); font-size: 8px; font-weight: bold; z-index: 1000;">content-single-forum.php</div>';
}
?>

<div id="bbpress-forums" class="bbpress-wrapper">

	<?php bbp_breadcrumb(); ?>

	<?php bbp_forum_subscription_link(); ?>

	<?php do_action('bbp_template_before_single_forum'); ?>

	<?php if (post_password_required()) : ?>

		<?php bbp_get_template_part('form', 'protected'); ?>

	<?php else : ?>

		<?php // bbp_single_forum_description(); 
		?>

		<?php if (bbp_has_forums()) : ?>

			<?php bbp_get_template_part('loop', 'forums'); ?>

		<?php endif; ?>

		<?php if (! bbp_is_forum_category() && bbp_has_topics()) : ?>

			<?php bbp_get_template_part('pagination', 'topics'); ?>

			<?php bbp_get_template_part('loop',       'topics'); ?>

			<?php bbp_get_template_part('pagination', 'topics'); ?>

			<?php bbp_get_template_part('form',       'topic'); ?>

		<?php elseif (! bbp_is_forum_category()) : ?>

			<?php bbp_get_template_part('feedback',   'no-topics'); ?>

			<?php bbp_get_template_part('form',       'topic'); ?>

		<?php endif; ?>

	<?php endif; ?>

	<?php do_action('bbp_template_after_single_forum'); ?>

</div>
<?php
// デバッグ用ボーダー終了
if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {
	echo '</div>';
}
