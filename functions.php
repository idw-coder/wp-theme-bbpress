<?php
function enqueue_parent_theme_styles()
{
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        filemtime(get_template_directory() . '/style.css')
    );
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/assetes/style.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assetes/style.css')
    );
}
add_action('wp_enqueue_scripts', 'enqueue_parent_theme_styles');

/**
 * bbPressシードデータファイルを読み込み
 */
add_action('wp_loaded', function () {
    error_log('[TEST] wp_loaded実行されました');
});
// add_action('after_setup_theme', function () {
//     $path = get_stylesheet_directory() . '/includes/bbpress-seed.php';
//     error_log('[DEBUG] シードファイルパス: ' . $path);
//     error_log('[DEBUG] ファイル存在: ' . (file_exists($path) ? 'YES' : 'NO'));

//     if (file_exists($path)) {
//         require_once $path;
//         error_log('[DEBUG] シードファイル読み込み完了');
//     } else {
//         error_log('[DEBUG] シードファイルが見つかりません');
//     }
// });
add_action('after_setup_theme', function () {
    // bbPressシード処理を遅延読み込み
    require_once get_stylesheet_directory() . '/includes/bbpress-seed.php';
});
// functions.phpの最下部に追加してテスト
add_action('init', function () {
    // error_log('bbPress存在チェック: ' . (function_exists('bbp_is_forum') ? 'YES' : 'NO'));
});
/**
 * テンプレートファイル内のハードコードされた文字列も変更
 */
function custom_bbp_text_strings($translated_text, $text, $domain)
{
    if ($domain === 'bbpress') {
        switch ($text) {
            case 'Forum':
                return '質問掲示板';
            case 'Forums':
                return '質問掲示板';
            case 'Forum:':
                return '質問掲示板:';
            case 'Create New Forum':
                return '新しい質問掲示板を作成';
            case 'Create New Forum in "%s"':
                return '「%s」に新しい質問掲示板を作成';
            case 'Now Editing "%s"':
                return '「%s」を編集中';
            case 'Forum Name (Maximum Length: %d):':
                return '質問掲示板名（最大文字数: %d）:';
            case 'Forum Type:':
                return '質問掲示板タイプ:';
            case 'Forum Moderators:':
                return '質問掲示板モデレーター:';
            case 'This group does not currently have a forum.':
                return 'このグループには現在質問掲示板がありません。';
        }
    }
    return $translated_text;
}
add_filter('gettext', 'custom_bbp_text_strings', 20, 3);


/**
 * ===========================================================
 *  bbPress いいね 
 *  ===========================================================
 */

/* ────────────────────────────────────────────────
   1. 投稿者のメタ（total_likes）を +1 するユーティリティ
   ──────────────────────────────────────────────── */
function my_add_like_to_user($user_id)
{
    $likes = (int) get_user_meta($user_id, 'total_likes', true);
    update_user_meta($user_id, 'total_likes', $likes + 1);
}

/* ────────────────────────────────────────────────
   2. bbPress の返信本文に “♡いいね” リンクを差し込む
   ──────────────────────────────────────────────── */
add_filter('bbp_get_reply_content', 'my_bbp_append_like_link', 10, 2);
function my_bbp_append_like_link($content, $reply_id)
{

    // ログインしていなければ何もしない
    if (! is_user_logged_in()) {
        return $content;
    }

    // 投稿者
    $author_id = bbp_get_reply_author_id($reply_id);

    // 自分の投稿にはいいねさせない（やりたければ外す）
    if (get_current_user_id() === $author_id) {
        return $content;
    }

    // いいね用 URL（クエリ引数方式：超・簡易）
    $like_url = wp_nonce_url(
        add_query_arg('like_reply', $reply_id),
        'like_reply_' . $reply_id
    );

    // 末尾にリンク追加
    $content .= sprintf(
        '<br><a href="%s" class="bbp-like-link" style="font-size:0.9em;">♡ いいね</a>',
        esc_url($like_url)
    );

    return $content;
}

/* ────────────────────────────────────────────────
   3. “♡いいね” が押されたときの処理
   ──────────────────────────────────────────────── */
add_action('template_redirect', 'my_bbp_handle_like_click');
function my_bbp_handle_like_click()
{

    // パラメータが無ければスルー
    if (empty($_GET['like_reply'])) {
        return;
    }

    $reply_id = absint($_GET['like_reply']);

    // ノンス確認（CSRF 対策：最低限）
    if (! wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', 'like_reply_' . $reply_id)) {
        wp_die('Nonce verification failed.');
    }

    // 返信が存在しない・ゴミ箱等なら蹴る
    if (! bbp_get_reply($reply_id)) {
        wp_die('Reply not found.');
    }

    // 投稿者
    $author_id = bbp_get_reply_author_id($reply_id);

    // 自己いいね禁止
    if (get_current_user_id() === $author_id) {
        wp_safe_redirect(remove_query_arg(['like_reply', '_wpnonce']));
        exit;
    }

    /**
     * ★★★ 重複いいね防止について ★★★
     * 　本当は ( user_id, reply_id ) で専用テーブルを作り UNIQUE 制約を張るべき。
     * 　今回は「参考実装」なので省略。必要なら自力で実装してください。
     */

    // メタを +1
    my_add_like_to_user($author_id);

    // リダイレクト (F5連打対策)
    wp_safe_redirect(remove_query_arg(['like_reply', '_wpnonce']));
    exit;
}

/* ────────────────────────────────────────────────
   4. テンプレートで使うヘルパー：♡合計取得
   ──────────────────────────────────────────────── */
function my_get_user_like_count($user_id = null)
{
    $user_id = $user_id ?: get_current_user_id();
    return (int) get_user_meta($user_id, 'total_likes', true);
}

/* ────────────────────────────────────────────────
   5. （おまけ）ユーザープロフィール欄に表示
   ──────────────────────────────────────────────── */
add_action('show_user_profile', 'my_show_like_count_on_profile');
add_action('edit_user_profile', 'my_show_like_count_on_profile');
function my_show_like_count_on_profile($user)
{
?>
    <h2>いいね♡統計</h2>
    <table class="form-table">
        <tr>
            <th><label>累計いいね数</label></th>
            <td><strong><?php echo number_format_i18n(my_get_user_like_count($user->ID)); ?></strong> 回</td>
        </tr>
    </table>
<?php
}

/**
 * bbPressフォーラムのページタイトルを「質問掲示板」に変更
 */
function custom_bbp_forum_labels($labels)
{
    $labels['name'] = '質問掲示板';
    $labels['singular_name'] = '質問掲示板';
    $labels['menu_name'] = '質問掲示板';
    $labels['all_items'] = 'すべての質問掲示板';
    $labels['add_new_item'] = '新しい質問掲示板を作成';
    $labels['edit_item'] = '質問掲示板を編集';
    $labels['new_item'] = '新しい質問掲示板';
    $labels['view_item'] = '質問掲示板を表示';
    $labels['view_items'] = '質問掲示板を表示';
    $labels['search_items'] = '質問掲示板を検索';
    $labels['not_found'] = '質問掲示板が見つかりません';
    $labels['not_found_in_trash'] = 'ゴミ箱に質問掲示板が見つかりません';
    $labels['archives'] = '質問掲示板';

    return $labels;
}
add_filter('bbp_get_forum_post_type_labels', 'custom_bbp_forum_labels');
