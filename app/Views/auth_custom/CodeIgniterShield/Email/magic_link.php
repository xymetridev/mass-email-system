<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Login ke Mass Email System</title>
    <style type="text/css" rel="stylesheet" media="all">
        /* Base */
        body {
            width: 100% !important;
            height: 100%;
            margin: 0;
            line-height: 1.4;
            background-color: #F8FAFC;
            color: #475569;
            -webkit-text-size-adjust: none;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #F8FAFC;
            padding-bottom: 40px;
        }
        .content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            padding: 30px;
            text-align: center;
            background-color: #206bc4;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .body {
            padding: 40px 30px;
        }
        .body h2 {
            color: #1e293b;
            font-size: 20px;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .body p {
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #206bc4;
            color: #ffffff !important;
            padding: 14px 32px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        .footer {
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            background-color: #F8FAFC;
        }
        .footer p {
            margin: 5px 0;
        }
        .divider {
            border-top: 1px solid #e2e8f0;
            margin: 30px 0;
        }
        .small-text {
            font-size: 14px;
            color: #64748b;
        }
        .link-alt {
            word-break: break-all;
            color: #206bc4;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="content">
                    <div class="header">
                        <h1>Mass Email System</h1>
                    </div>
                    <div class="body">
                        <h2>Halo, <?= esc($user->username) ?>!</h2>
                        <p>Anda menerima email ini karena ada permintaan akses masuk (Login) ke akun Anda menggunakan Magic Link.</p>
                        
                        <div class="button-container">
                            <a href="<?= url_to('verify-magic-link') ?>?token=<?= $token ?>" class="button">Masuk ke Dashboard</a>
                        </div>

                        <p class="small-text">Link ini hanya berlaku selama <b>1 jam</b> dan hanya bisa digunakan satu kali.</p>
                        
                        <div class="divider"></div>
                        
                        <p class="small-text">Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:</p>
                        <p class="link-alt small-text"><?= url_to('verify-magic-link') ?>?token=<?= $token ?></p>
                    </div>
                    <div class="footer">
                        <p>&copy; <?= date('Y') ?> Mass Email System. All rights reserved.</p>
                        <p>Jika Anda tidak merasa meminta email ini, abaikan saja pesan ini.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
