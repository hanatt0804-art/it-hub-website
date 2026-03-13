<?php
// 文字化け対策
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// 管理者の受信先メールアドレス（IT HUBの連絡先）
$to = "info@it-hub.biz";

// フォームからデータを受け取る
$name       = isset($_POST['name']) ? $_POST['name'] : '';
$company    = isset($_POST['company']) ? $_POST['company'] : '';
$phone      = isset($_POST['phone']) ? $_POST['phone'] : '';
$email      = isset($_POST['email']) ? $_POST['email'] : '';
$inquiry    = isset($_POST['inquiry']) ? $_POST['inquiry'] : '';
$spam_check = isset($_POST['spam_check']) ? $_POST['spam_check'] : '';

// 簡易的なスパムチェック（「ITHUB」と入力されていない場合は弾く）
if ($spam_check !== 'ITHUB') {
    die("スパム対策キーワードが正しくありません。戻ってやり直してください。");
}

// === 管理者用通知メールの内容 ===
$admin_subject = "【IT HUB】Webサイトからお問い合わせがありました";
$admin_body = <<<EOM
Webサイトのお問い合わせフォームより、以下の内容で送信がありました。

■お名前
{$name}

■事業所名
{$company}

■電話番号
{$phone}

■メールアドレス
{$email}

■お問い合わせ内容
{$inquiry}
EOM;

// === お客様用自動返信メールの内容 ===
$user_subject = "【IT HUB】お問い合わせありがとうございます（自動返信）";
$user_body = <<<EOM
{$name} 様
（※{$company} 様）

この度は、IT HUBへお問い合わせいただき誠にありがとうございます。
以下の内容でお問い合わせを受け付けました。

確認次第、担当者より折り返しご連絡させていただきますので、
今しばらくお待ちくださいますようお願い申し上げます。

--------------------------------------------------
【送信内容】
■お名前： {$name}
■事業所名： {$company}
■電話番号： {$phone}
■メールアドレス： {$email}
■お問い合わせ内容：
{$inquiry}
--------------------------------------------------

※本メールはシステムからの自動返信です。
※ご返信には数営業日いただく場合がございます。

==================================================
IT HUB
URL： https://it-hub.biz/
Mail： info@it-hub.biz
==================================================
EOM;

// メールヘッダー情報（管理者通知用）
$admin_headers = "From: " . mb_encode_mimeheader("IT HUB") . " <" . $to . ">\r\n";
$admin_headers .= "Reply-To: " . $email . "\r\n";
$admin_headers .= "Content-Type: text/plain; charset=UTF-8";

// メールヘッダー情報（お客様自動返信用）
$user_headers = "From: " . mb_encode_mimeheader("IT HUB") . " <" . $to . ">\r\n";
$user_headers .= "Reply-To: " . $to . "\r\n";
$user_headers .= "Content-Type: text/plain; charset=UTF-8";

// メールの送信処理
// 第5引数に -f オプションを指定することで、Lolipop等での到達率を向上させます
$admin_send_result = mb_send_mail($to, $admin_subject, $admin_body, $admin_headers, "-f " . $to);
$user_send_result = mb_send_mail($email, $user_subject, $user_body, $user_headers, "-f " . $to);

// 送信成功時にサンキューページへリダイレクト
if ($admin_send_result && $user_send_result) {
    header("Location: thanks.html");
    exit();
} else {
    // サーバーエラー等で送信できなかった場合
    die("エラーが発生しました。メールを送信できませんでした。時間をおいて再度お試しください。");
}
?>
