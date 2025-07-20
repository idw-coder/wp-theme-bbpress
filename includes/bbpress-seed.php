<?php

if (!defined('ABSPATH')) {
    exit;
}

// wp_loadedフックでシード処理実行
add_action('wp_loaded', function () {
    if (!current_user_can('administrator')) return;
    if (!function_exists('bbp_insert_forum')) return;

    // 明示的な全削除
    // http://localhost:8080/wp-admin/?delete_all_bbp
    if (isset($_GET['delete_all_bbp'])) {
        delete_all_bbpress_content();
        delete_option('bbpress_demo_seeded');
        error_log('[SEED] 全ての bbPress データを削除しました。');
        echo '<a href="?run_bbp_seed">再投入</a> | ';
        echo '<a href="?">戻る</a>';
        wp_die('全ての bbPress データを削除しました。');
    }

    // 明示的な再投入
    // http://localhost:8080/wp-admin/?run_bbp_seed
    if (isset($_GET['run_bbp_seed'])) {
        delete_option('bbpress_demo_seeded');
        seed_bbpress_forums();
        error_log('[SEED] シードデータ作成完了');
        echo '<a href="?">戻る</a>';
        wp_die('bbPressシードデータの作成が完了しました。');
    }

    // 通常の初回1回だけ自動投入
    // if (!get_option('bbpress_demo_seeded')) {
    //     error_log('[SEED] シードデータ自動投入');
    //     seed_bbpress_forums();
    // }
});

// シード処理本体
function seed_bbpress_forums()
{
    // 重複防止：既にシードされている場合は終了
    if (get_option('bbpress_demo_seeded')) {
        error_log('[SEED] 既にシード済みのため処理をスキップ');
        return;
    }

    $structure_path = trailingslashit(get_stylesheet_directory()) . 'includes/bbpress-structure.php';
    if (!file_exists($structure_path)) {
        error_log('[SEED] 構造ファイルが存在しません: ' . $structure_path);
        return;
    }

    $structure = require $structure_path;

    $user_ids = wp_list_pluck(get_users(['fields' => ['ID']]), 'ID');
    error_log('[SEED] 投稿者ユーザー: ' . implode(', ', $user_ids));


    foreach ($structure as $cat_title => $forums) {
        // 投稿者ランダム決定（カテゴリ）
        $author_id = $user_ids[array_rand($user_ids)];

        // カテゴリ重複チェック（WP_Query使用）
        $existing_cat = new WP_Query([
            'post_type' => 'forum',
            'title' => $cat_title,
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);
        if ($existing_cat->have_posts()) {
            error_log("[SEED] カテゴリ '{$cat_title}' は既に存在するためスキップ");
            wp_reset_postdata();
            continue;
        }

        $cat_id = bbp_insert_forum([
            'post_title'   => $cat_title,
            'post_content' => $cat_title . ' に関するカテゴリです。',
            'post_status'  => 'publish',
            'post_parent'  => 0,
            'post_author'  => $author_id,
        ]);
        if (!$cat_id || is_wp_error($cat_id)) {
            error_log("[SEED] カテゴリ作成失敗: $cat_title");
            if (is_wp_error($cat_id)) {
                error_log("[SEED] エラー: " . $cat_id->get_error_message());
            }
            continue;
        }

        // フォーラムタイプをカテゴリに設定（関数の存在チェック）
        if (function_exists('bbp_update_forum_type')) {
            bbp_update_forum_type($cat_id, 'category');
        } else {
            update_post_meta($cat_id, '_bbp_forum_type', 'category');
        }

        foreach ($forums as $forum_title => $topics) {
            // 投稿者ランダム決定（フォーラム）
            $author_id = $user_ids[array_rand($user_ids)];

            // フォーラム重複チェック（WP_Query使用）
            $existing_forum = new WP_Query([
                'post_type' => 'forum',
                'title' => $forum_title,
                'posts_per_page' => 1,
                'fields' => 'ids'
            ]);
            if ($existing_forum->have_posts()) {
                error_log("[SEED] フォーラム '{$forum_title}' は既に存在するためスキップ");
                wp_reset_postdata();
                continue;
            }

            $forum_id = bbp_insert_forum([
                'post_title'   => $forum_title,
                'post_content' => $forum_title . ' に関するフォーラムです。',
                'post_status'  => 'publish',
                'post_parent'  => $cat_id,
                'post_author'  => $author_id,
            ]);
            if (!$forum_id || is_wp_error($forum_id)) continue;

            foreach ($topics as $topic_title => $replies) {
                // 投稿者ランダム決定（トピック）
                $author_id = $user_ids[array_rand($user_ids)];

                $topic_id = bbp_insert_topic([
                    'post_title'   => $topic_title,
                    'post_content' => $topic_title . ' について議論しましょう。',
                    'post_status'  => 'publish',
                    'post_parent'  => $forum_id,
                    'post_author'  => $author_id,
                ]);
                if (!$topic_id || is_wp_error($topic_id)) continue;

                foreach ($replies as $reply_content) {
                    // 投稿者ランダム決定（返信）
                    $author_id = $user_ids[array_rand($user_ids)];

                    bbp_insert_reply([
                        'post_content' => $reply_content,
                        'post_status'  => 'publish',
                        'post_parent'  => $topic_id,
                        'post_author'  => $author_id,
                    ]);
                }
            }
            // bbp_update_forum($forum_id); は、
            // wp_posts とは別の wp_postmeta テーブルに統計情報を保存 → 一覧場面のトピック数などを更新
            bbp_update_forum_topic_count($forum_id);
            bbp_update_forum_reply_count($forum_id);
            bbp_update_forum_subforum_count($forum_id);
            bbp_update_forum_last_topic_id($forum_id);
            bbp_update_forum_last_reply_id($forum_id);

            error_log('[SEED] フォーラム統計再構築完了: ' . $forum_id);
        }
    }

    update_option('bbpress_demo_seeded', 1);
}

// 全削除関数
function delete_all_bbpress_content()
{
    // フォーラム、トピック、返信を全削除
    $forums = get_posts(['post_type' => 'forum', 'numberposts' => -1, 'post_status' => 'any']);
    $topics = get_posts(['post_type' => 'topic', 'numberposts' => -1, 'post_status' => 'any']);
    $replies = get_posts(['post_type' => 'reply', 'numberposts' => -1, 'post_status' => 'any']);

    foreach ($forums as $forum) {
        wp_delete_post($forum->ID, true);
    }
    foreach ($topics as $topic) {
        wp_delete_post($topic->ID, true);
    }
    foreach ($replies as $reply) {
        wp_delete_post($reply->ID, true);
    }

    error_log('[SEED] 全データ削除完了');
}
