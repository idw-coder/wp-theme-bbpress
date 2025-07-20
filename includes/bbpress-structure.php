<?php

// bbPress 用のシード構造データ
return [
    '開発カテゴリ' => [
        'PHP' => [
            '文字化けが発生する' => [
                'Content-Type ヘッダーを確認してください。',
                'データベースの照合順序が utf8mb4_general_ci になっているか？',
            ],
            'パフォーマンスが悪い' => [
                'OPcache は有効ですか？',
                '不必要なループを減らしてください。',
            ]
        ],
        'JavaScript' => [
            'イベントが発火しない' => [
                'addEventListener の対象要素を確認。',
                'DOMContentLoaded イベント内に書いていますか？',
            ]
        ]
    ],
    '運用カテゴリ' => [
        'サーバー' => [
            '502 Bad Gateway が出る' => [
                'nginx の upstream 設定が正しいか確認。',
                'PHP-FPM が死んでいないか？',
            ]
        ]
    ]
];
