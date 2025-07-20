<?php

/**
 * bbPress デバッグ機能
 * wp-content/themes/twentysixteen-child/includes/bbpress-debug.php
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

// グローバル変数でテンプレート情報を保存
global $bbp_debug_templates;
$bbp_debug_templates = array();

/**
 * bbPressテンプレート読み込み時にファイル名を記録
 */
add_filter('bbp_locate_template', 'bbp_debug_template_path_fixed', 10, 3);
function bbp_debug_template_path_fixed($template, $template_names, $load)
{
    global $bbp_debug_templates;

    if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {

        // テンプレートファイル名を取得
        $template_name = basename($template);
        $template_dir = str_replace(ABSPATH, '', dirname($template));

        // 配列に記録（重複を避ける）
        $bbp_debug_templates[$template_name] = array(
            'name' => $template_name,
            'path' => $template_dir,
            'full_path' => $template
        );

        error_log('Template recorded: ' . $template_name);
    }

    return $template;
}

/**
 * ページ表示時にデバッグ情報を出力
 */
add_action('wp_head', 'bbp_debug_output_fixed');
function bbp_debug_output_fixed()
{
    global $bbp_debug_templates;

    if (defined('WP_DEBUG') && WP_DEBUG && is_user_logged_in() && current_user_can('administrator')) {

        error_log('bbp_debug_output_fixed called');

        // ページタイプを判定
        // https://codex.bbpress.org/themes/amending-bbpress-templates/
        $page_type = '';
        if (function_exists('bbp_is_forum_archive') && bbp_is_forum_archive()) {
            $page_type = 'フォーラム一覧ページ';
        } elseif (function_exists('bbp_is_single_forum') && bbp_is_single_forum()) {
            $page_type = 'フォーラム個別ページ';
        } elseif (function_exists('bbp_is_topic_archive') && bbp_is_topic_archive()) {
            $page_type = 'トピック一覧ページ';
        } elseif (function_exists('bbp_is_single_topic') && bbp_is_single_topic()) {
            $page_type = 'トピック個別ページ';
        } elseif (function_exists('bbp_is_topic_edit') && bbp_is_topic_edit()) {
            $page_type = 'トピック編集ページ';
        } elseif (function_exists('bbp_is_reply_edit') && bbp_is_reply_edit()) {
            $page_type = '返信編集ページ';
        } elseif (function_exists('bbp_is_topic_tag') && bbp_is_topic_tag()) {
            $page_type = 'トピックタグページ';
        } elseif (function_exists('bbp_is_single_user') && bbp_is_single_user()) {
            $page_type = 'ユーザープロフィールページ';
        } elseif (function_exists('bbp_is_search') && bbp_is_search()) {
            $page_type = '検索結果ページ';
        } else {
            $page_type = 'その他のページ';
        }

?>
        <style>
            .bbp-debug-info {
                position: fixed;
                top: 10%;
                right: 10px;
                z-index: 99999;
                font-size: 12px;
                line-height: 1.4;
                max-width: 350px;
                min-width: 250px;
                overflow: hidden;
            }

            .bbp-debug-page {
                background: linear-gradient(135deg, rgb(102, 126, 234, 0.5) 0%, rgb(118, 75, 162, 0.5) 100%);
                color: white;
                padding: 10px 12px;
                font-weight: bold;
            }

            .bbp-debug-template {
                background: rgba(231, 76, 60, 0.5);
                color: white;
                padding: 8px 12px;
            }

            .bbp-debug-template:last-child {
                border-bottom: none;
            }

            .bbp-debug-file {
                font-weight: bold;
                margin-bottom: 2px;
            }

            .bbp-debug-path {
                font-size: 10px;
                word-break: break-all;
            }

            .bbp-debug-close {
                position: absolute;
                top: 8px;
                right: 8px;
                color: white;
                cursor: pointer;
                font-size: 14px;
                font-weight: bold;
                opacity: 0.7;
            }

            .bbp-debug-toggle {
                position: fixed;
                top: 10%;
                right: 10px;
                background: rgb(44, 62, 80, 0.5);
                color: white;
                border: none;
                padding: 8px 12px;
                cursor: pointer;
                font-size: 11px;
                z-index: 99998;
                display: none;
            }
        </style>

        <button class="bbp-debug-toggle" onclick="document.querySelector('.bbp-debug-info').style.display='block'; this.style.display='none';">DEBUG</button>

        <div class="bbp-debug-info">
            <div class="bbp-debug-close" onclick="this.parentElement.style.display='none'; document.querySelector('.bbp-debug-toggle').style.display='block';">×</div>
            <div class="bbp-debug-page"><?php echo esc_html($page_type); ?></div>
            <?php if (!empty($bbp_debug_templates)): ?>
                <?php foreach ($bbp_debug_templates as $template): ?>
                    <div class="bbp-debug-template">
                        <div class="bbp-debug-file"><?php echo esc_html($template['name']); ?></div>
                        <div class="bbp-debug-path"><?php echo esc_html($template['path']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bbp-debug-template">
                    <div class="bbp-debug-file">テンプレート未検出</div>
                </div>
            <?php endif; ?>
        </div>

        <script>
            console.log('bbPress Debug Info:');
            console.log('Page Type: <?php echo esc_js($page_type); ?>');
            <?php if (!empty($bbp_debug_templates)): ?>
                <?php foreach ($bbp_debug_templates as $template): ?>
                    console.log('Template: <?php echo esc_js($template["name"]); ?> (<?php echo esc_js($template["path"]); ?>)');
                <?php endforeach; ?>
            <?php endif; ?>
        </script>
<?php
    }
}
