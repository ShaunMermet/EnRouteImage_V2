<?php

/**
 * ja_JP
 *
 * JP Japanese (日本語) message token translations for the 'site' sprinkle.
 *
 * @package 
 * @link 
 * @author 
 */

return [

    "ADMIN" => [
        "PANEL" => "管理画面"
    ],
    "HOME"  => "ホーム",
    "LOGOUT" => "ログアウト",
    "PORTAL" => [
        "WELCOME" => "ようこそ!",
        "HEADER1" => [
            "@TRANSLATION" =>   "チュートリアル - アノテーションを始めよう！",
            "CONTENT" => "このサイトでは簡単に機械学習のための教師データ（アノテーション）を作成できます"
        ],
        "STEP1" => [
            "@TRANSLATION" =>   "Step 1 - 画像の取得",
            "CONTENT"      => "アノテーションエリアでは自動的に画像が出てきます。出てこない場合はこのボタンをクリック"
        ],
        "STEP2" => [
            "@TRANSLATION" =>   "Step 2 - カテゴリーを選択する",
            "CONTENT1" => "アノテーションエリアの左側にこのようなカテゴリー選択ボックスがあります",
            "CONTENT2" => "この中からアノテーションしたいカテゴリーを選んでください"
        ],
        "STEP3" => [
            "@TRANSLATION" =>   "Step 3 - イメージへの書き込み",
            "CONTENT1" => "アノテーションエリアの画像上で、クリック&ドラッグで、選んだカテゴリーのオブジェクトをボックスで囲んでください。全てのオブジェクトを囲んでから、次のイメージボタンをクリックしますと、新しいイメージが出てきます"
        ],
        "NOTES" => [
            "@TRANSLATION" =>   "補足",
            "CONTENT1" => "一枚のイメージにおいて、複数のカテゴリーのボックスを書き込む事ができます",
            "CONTENT2" => "作業が完了していなくても、次のイメージボタンをクリックするとそれまでのアノテーションが自動保存されます",
            "CONTENT3a" => "ボックスを消したい時はこのボタン",
            "CONTENT3b" => "をクリックしてから消したいボックスをクリック。その後、書込ボタン",
            "CONTENT3c" => "をクリックして新しいボックスの書き込みを再開できます",
        ],
        "DEMO" => [
            "@TRANSLATION" =>   "デモ",
        ],
        "HEADER2" => [
            "@TRANSLATION" =>   "アノテーションしたデータはどうなるの ?",
            "CONTENT1" => "添削",
            "CONTENT2" => "アノテーションしたデータは専門家によって添削されたあと、機械学習用のデータベースへ保存されます",
            "CONTENT3" => "サインアップしょう !",
            "CONTENT4" => "ログインしてアノテーションをしますと、貢献度が記録されます!"
        ],
    ],
    "BUTTON"              => [
        "MORE_IMAGE" => "ロード イメージ",
        "NEXT_IMAGE" => "次のイメージ",
        "DELETE" => "削除",
        "DRAW" => "書込",
        "SIGN_UP" => "サインアップ",
        "RESET" => "リセット",
        "SELECTALL"=>"全て選択"
    ],
    "FEEDBACK"              => [
        "NO_IMAGE" => "イメージがありません",
    ],
    "LEGEND"              => [
        "LABEL" =>[
            "NBR_BBOX" => "バウンドボックス : ",
            "NBR_SELECTED_BBOX" => "選択済 : ",
            "SWITCH" => "バウンドボックスタイプお見せる",
        ],
        "SEG" =>[
            "NBR_AREA" => "エリア : ",
        ],
        "EDIT" => "Edit",
        "EDIT_NBR_IMAGE" => "イメージの数 : ",
        "SRECT" =>[
            "SWITCH" => "選択したものを表示",
        ],
        "URECT" =>[
            "SWITCH" => "選択していないものを表示",
        ],
    ],
    "LABEL"              => [
        "@TRANSLATION" => "ラベル",
    ],
    "VALIDATE"           => "添削",
    
    "LABEL_description"  => "カテゴリーを選んでアノテーションを始めよう !",

    "ACCOUNT" => [

        "SETTINGS" => [
            "@TRANSLATION"  => "アカウントの設定",
            "DESCRIPTION"   => "Email、ユーザー名、パスワードを設定します",
            "UPDATED"       => "アカウントの設定が更新されました",
            "STATS" => [
                "@TRANSLATION"     => "アカウントの貢献",
                "SUBMITTED"        => "添削待ちのイメージ",
                "REJECTED"         => "リジェクトされたイメージ",
                "VALIDATED"        => "アクセプトされたイメージ",
                "SUCCESS_RATE"     =>"貢献度
>90% は大変良く出来ました！
>75% はよく出来ました
<75% もう少しがんばりましょう",
            ],
            "BBOX_STATS"     =>"バウンドボックスの貢献度",
            "SEG_STATS"     =>"セグメンテーションの貢献度"
        ],
        "MY"                => "マイアカウント",
        "@TRANSLATION" => "アカウント",

        "ACCESS_DENIED" => "この操作のパーミッションがありません",

        "DISABLED" => "このアカウントは停止されました、管理員へご連絡ください",

        "EMAIL_UPDATED" => "アカウントのEmailを更新する",

        "INVALID" => "このアカウントは見つかりません、消去された可能性があります、管理員へご連絡ください",

        "MASTER_NOT_EXISTS" => "マスターアカウントを作成するまでアカウントの作成ができません",
        
        "SESSION_COMPROMISED"       => "他のデバイスからログインされています、一旦ログアウトしてから再度ログインしてください",
        "SESSION_COMPROMISED_TITLE" => "このアカウントは盗用された可能性があります",
        "SESSION_EXPIRED"       => "セッションがタイムアウトしました、再度ログインしてください",

        "TOOLS" => "アカウントツール",

        "UNVERIFIED" => "このアカウントまた認証されていません、Emailをチェックしてアクティベーションしてください",

        "VERIFICATION" => [
            "NEW_LINK_SENT"     => "認証用のリンクをこちらへ発送しました{{email}}. Emailをチェックしてください",
            "RESEND"            => "認証用のEmailを再発送する",
            "COMPLETE"          => "正しく認証しました、ログインできます",
            "EMAIL"             => "認証用のEmailアドレスを入力してください",
            "PAGE"              => "新しいアカウントを認証するためのEmailを入力してください",
            "SEND"              => "アカウントするための認証Emailを入力します",
            "TOKEN_NOT_FOUND"   => "このアカウントはすでに認証されています",
        ]
    ],


    "GALLERY"               =>"ギャラリー",
    "UPLOAD" =>[
        "@TRANSLATION" => "アップロード",
        "TITLE" => [
            "EXPORT"        =>"エクスポート",
            "CATEGORY"      =>"カテゴリーの編集",
            "UPLOAD"        =>"イメージのアップロード",
            "FOLDER"        =>"イメージのリスト",
            "SETEDITOR"        =>"画像セットの編集",
        ]
    ],
    "BB_UPLOAD"               =>"バウンドボックス",
    "SEG_UPLOAD"               =>"セグメンテーション",
    "BB_LABEL"               =>"バウンドボックス",
    "BB_LABEL_FULL"               =>"バウンドボックス",
    "BB_VALIDATE"               =>"バウンドボックス添削",
    "SEG_LABEL"               =>"セグメンテーション",
    "SEG_VALIDATE"               =>"セグメンテーション添削",
    "SEG_LABEL_description" =>"カテゴリーを選んでセグメンテーションを始めよう !",
    "PASSWORD" => [
        "BETWEEN"   => "パスワードの文字数 {{min}}-{{max}} "
    ],
    "GROUP" => [
        "BB_CPRS_RATE"        => "バウンドボックスモードのイメージ圧縮率",
        "BB_CPRS_RATE_INFO" => "(0-100) 0 は高圧縮率, 100 は高画質",
        "SEG_CPRS_RATE"            => "セグメンテーションモードのイメージ圧縮率",
        "SEG_CPRS_RATE_INFO"    => "(0-100) 0 は高圧縮率, 100 は高画質"
    ],
    "ERROR" => [
        "IMG_OUTDATED"        => "タイムアウトになりました、新しいイメージをロードします",
    ],
    "EMAIL" => [
        "INVALID"               => "このEmailのアカウントが見つかりません <strong>{{email}}</strong>.",
        "IN_USE"                => "Email <strong>{{email}}</strong> はすでに使われています",
        "VERIFICATION_REQUIRED" => "Email (認証が必要です、本当のEmailを入力してください!)"
    ],

    "EMAIL_OR_USERNAME" => "ユーザー名もしくはEmail",

    "FIRST_NAME" => "名",

    "HEADER_MESSAGE_ROOT" => "ルートユーザーとしてログインしています",

    "LAST_NAME" => "姓",

    "LOCALE" => [
        "SAVE" => "ja_JP",
        "ACCOUNT" => "アカウントで使う言語",
        "INVALID" => "<strong>{{locale}}</strong> このロケーションは正しくありません"
    ],

    "LOGIN" => [
        "@TRANSLATION"      => "ログイン",
        "ALREADY_COMPLETE"  => "すでにログインしています",
        "SOCIAL"            => "またはこちらでログイン出来ます",
        "REQUIRED"          => "ログインが必要です"
    ],

    
    "NAME" => "名前",

    "NAME_AND_EMAIL" => "名前とEmail",

    "PAGE" => [
        "LOGIN" => [
            "DESCRIPTION"   => "{{site_name}}へログインもしくはアカウントを作成してください",
            "SUBTITLE"      => "無償でアカウントを作成するかログインしてください",
            "TITLE"         => "始めよう！",
        ]
    ],

    "PASSWORD" => [
        "@TRANSLATION" => "パスワード",

        "BETWEEN"   => "{{min}}-{{max}}文字の間で設定してください",

        "CONFIRM"               => "パスワードの再確認",
        "CONFIRM_CURRENT"       => "現在のパスワードをご確認ください",
        "CONFIRM_NEW"           => "新しいパスワードをご確認ください",
        "CONFIRM_NEW_EXPLAIN"   => "新しいパスワードをもう一度入力してください",
        "CONFIRM_NEW_HELP"      => "新しいパスワードを設定する時のみ必要です",
        "CURRENT"               => "現在のパスワード",
        "CURRENT_EXPLAIN"       => "現在のパスワードの入力が必要です",

        "FORGOTTEN" => "パスワードを忘れた？",
        "FORGET" => [
            "@TRANSLATION" => "パスワードを忘れました",

            "COULD_NOT_UPDATE"  => "パスワードを更新出来ません",
            "EMAIL"             => "Emailを入力してください、パスワードリセット用のEmailを送ります",
            "EMAIL_SEND"        => "パスワードリセット用のEmailを発送します",
            "INVALID"           => "このパスワードリセットのリクエストは見つかりません、有効期間を過ぎた可能性があります、こちらから再度操作してください<a href=\"{{url}}\">resubmitting your request<a>.",
            "PAGE"              => "パスワードリセット用のリンクを取得します",
            "REQUEST_CANNED"    => "パスワードリセットのリクエストをキャンセルします",
            "REQUEST_SENT"      => "パスワードリセットのリンクをこちらへ送りました <strong>{{email}}</strong>."
        ],

        "RESET" => [
            "@TRANSLATION"      => "パスワードのリセット",
            "CHOOSE"            => "パスワードを入力してください",
            "PAGE"              => "新しいパスワードを設定します",
            "SEND"              => "新しいパスワードを入力してログインしてください"
        ],

        "HASH_FAILED"       => "パスワードの正確性を確認出来ません、管理員へご連絡ください",
        "INVALID"           => "現在のパスワードと記録されたパスワードが一致しません",
        "NEW"               => "新しいパスワード",
        "NOTHING_TO_UPDATE" => "現在と同じパスワードを設定出来ません",
        "UPDATED"           => "アカウントのパスワードを更新しました"
    ],

    "PROFILE"       => [
        "SETTINGS"  => "プロフィールの設定",
        "UPDATED"   => "プロフィールの設定を更新しました"
    ],

    "REGISTER"      => "サインアップ",
    "REGISTER_ME"   => "サインアップします",

    "REGISTRATION" => [
        "BROKEN"            => "アカウントの登録にエラーが発生しました、管理員へご連絡ください",
        "COMPLETE_TYPE1"    => "サインアップが正しく出来ました、ログインしてください",
        "COMPLETE_TYPE2"    => "サインアップが正しく出来ました、アクティベーション用のリンクを<strong>{{email}}</strong>へ送信しました、アクティベーションを行ってください",
        "DISABLED"          => "アカウントの作成が停止されています",
        "LOGOUT"            => "アカウントの作成をするにはログアウトしてください",
        "WELCOME"           => "サインアップは簡単です"
    ],

    "RATE_LIMIT_EXCEEDED"       => "この操縦は回数制限をこえました、{{delay}}秒お待ちください",
    "REMEMBER_ME"               => "ログイン情報を覚える",
    "REMEMBER_ME_ON_COMPUTER"   => "ログイン情報このPCに記録する (共用PCで使わないでください)",

    "SIGNIN"                => "ログイン",
    "SIGNIN_OR_REGISTER"    => "ログインもしくはサインアップ",
    "SIGNUP"                => "サインアップ",

    "TOS"           => "使用条件",
    "TOS_AGREEMENT" => "{{site_title}}のアカウントを作成し, <a {{link_attributes | raw}}>使用条件</a>に同意しました",
    "TOS_FOR"       => "{{title}}の使用条件",

    "USERNAME" => [
        "@TRANSLATION" => "ユーザー名",

        "CHOOSE"        => "ユーザー名を選択してください",
        "INVALID"       => "ユーザー名が正しくありません",
        "IN_USE"        => "<strong>{{user_name}}</strong>はすでに使われています",
        "NOT_AVAILABLE" => "Username <strong>{{user_name}}</strong>はすでに使われています,ほかのユーザー名を選択するか、'おまかせ'をクリックしてください."
    ],

    "USER_ID_INVALID"       => "指定したユーザー名が見つかりません",
    "USER_OR_EMAIL_INVALID" => "ユーザー名もしくはEmailが見つかりません",
    "USER_OR_PASS_INVALID"  => "ユーザー名もしくはパスワードが正しくありません",

    "WELCOME" => "おかえりなさい, {{first_name}}",
    "COMBOTITLE" => [
        "CATEGORY"        => "イメージカテゴリー",
        "GROUP"        => "イメージグループ",
        "SET"        => "画像セット",
    ],
    "COMBOPLACEHOLDER" => [
        "NONE"        => "なし
",
    ]
];
