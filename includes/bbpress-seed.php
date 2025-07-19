<?php

if (!defined('ABSPATH')) {
    exit;
}

// bbPressのAPIを使用するため bbPressが完全にロードされた後に実行
add_action('bbp_loaded', function () {
    if (current_user_can('administrator') && !get_option('bbpress_demo_seeded')) {
        seed_bbpress_forums();
    }
});

// URL叩き用の再実行フック（任意）
add_action('admin_init', function () {
    if (!current_user_can('administrator')) return;

    if (isset($_GET['run_bbp_seed'])) {
        delete_option('bbpress_demo_seeded');
        seed_bbpress_forums();
        wp_die('bbPressシードデータの作成が完了しました。');
    }
});

function seed_bbpress_forums()
{
    $users = get_users(['fields' => 'all_with_meta']);
    if (empty($users)) {
        error_log('[SEED] ユーザーが存在しないため中断');
        return;
    }

    $forum_id = bbp_insert_forum([
        'post_title'   => 'テストフォーラム',
        'post_content' => 'テスト用のフォーラムです。',
        'post_status'  => 'publish',
        'post_parent'  => 0,
        'post_author'  => $users[0]->ID,
    ]);
    if (!$forum_id || is_wp_error($forum_id)) {
        error_log('[SEED] forum error: ' . (is_wp_error($forum_id) ? $forum_id->get_error_message() : 'unknown'));
        return;
    }

    $topic_id = bbp_insert_topic([
        'post_title'   => 'テストトピック',
        'post_content' => 'これはテストトピックの内容です。',
        'post_status'  => 'publish',
        'post_parent'  => $forum_id,
        'post_author'  => $users[0]->ID,
    ]);
    if (!$topic_id || is_wp_error($topic_id)) {
        error_log('[SEED] topic error: ' . (is_wp_error($topic_id) ? $topic_id->get_error_message() : 'unknown'));
        return;
    }

    $reply_id = bbp_insert_reply([
        'post_content' => 'これはテスト返信です。',
        'post_status'  => 'publish',
        'post_parent'  => $topic_id,
        'post_author'  => $users[0]->ID,
    ]);
    if (!$reply_id || is_wp_error($reply_id)) {
        error_log('[SEED] reply error: ' . (is_wp_error($reply_id) ? $reply_id->get_error_message() : 'unknown'));
        return;
    }

    update_option('bbpress_demo_seeded', 1);
    error_log('[SEED] 完了');
}
