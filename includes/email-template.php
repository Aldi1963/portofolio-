<?php
/**
 * HTML Email Template
 * Used for contact form notifications and auto-replies
 */

/**
 * Generate HTML email for new contact notification (to admin)
 */
function emailTemplateContact($data) {
    $siteName = getSetting('site_name', APP_NAME);
    $siteUrl = APP_URL;
    $year = date('Y');
    
    $name = htmlspecialchars($data['name']);
    $email = htmlspecialchars($data['email']);
    $phone = htmlspecialchars($data['phone'] ?? '-');
    $subject = htmlspecialchars($data['subject']);
    $message = nl2br(htmlspecialchars($data['message']));
    $date = date('d M Y, H:i');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#0a0a0f;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0a0a0f;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#12121a;border-radius:12px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0066ff,#0044cc);padding:30px 40px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">New Contact Message</h1>
                            <p style="margin:8px 0 0;color:rgba(255,255,255,0.8);font-size:14px;">Someone reached out via your portfolio</p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 40px;">
                            <!-- Contact Info Table -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="padding:12px 16px;background:#1a1a2e;border-radius:8px 8px 0 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                                        <span style="color:#6b6b80;font-size:12px;text-transform:uppercase;letter-spacing:1px;">From</span><br>
                                        <span style="color:#ffffff;font-size:15px;font-weight:600;">{$name}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;background:#1a1a2e;border-bottom:1px solid rgba(255,255,255,0.05);">
                                        <span style="color:#6b6b80;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Email</span><br>
                                        <a href="mailto:{$email}" style="color:#0066ff;font-size:14px;text-decoration:none;">{$email}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;background:#1a1a2e;border-bottom:1px solid rgba(255,255,255,0.05);">
                                        <span style="color:#6b6b80;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Phone</span><br>
                                        <span style="color:#a0a0b0;font-size:14px;">{$phone}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;background:#1a1a2e;border-bottom:1px solid rgba(255,255,255,0.05);">
                                        <span style="color:#6b6b80;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Subject</span><br>
                                        <span style="color:#ffffff;font-size:14px;font-weight:600;">{$subject}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;background:#1a1a2e;border-radius:0 0 8px 8px;">
                                        <span style="color:#6b6b80;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Date</span><br>
                                        <span style="color:#a0a0b0;font-size:14px;">{$date}</span>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Message -->
                            <div style="background:#1a1a2e;border-radius:8px;padding:20px;border-left:4px solid #0066ff;">
                                <p style="margin:0 0 8px;color:#6b6b80;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Message</p>
                                <p style="margin:0;color:#e0e0e0;font-size:14px;line-height:1.8;">{$message}</p>
                            </div>
                            
                            <!-- Action Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px;">
                                <tr>
                                    <td align="center">
                                        <a href="mailto:{$email}?subject=Re: {$subject}" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#0066ff,#0044cc);color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:8px;">
                                            Reply to {$name}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
                            <p style="margin:0;color:#6b6b80;font-size:12px;">
                                This email was sent from <a href="{$siteUrl}" style="color:#0066ff;text-decoration:none;">{$siteName}</a> contact form.
                            </p>
                            <p style="margin:6px 0 0;color:#4a4a5a;font-size:11px;">&copy; {$year} {$siteName}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Generate HTML auto-reply email (to the sender)
 */
function emailTemplateAutoReply($data) {
    $siteName = getSetting('site_name', APP_NAME);
    $siteUrl = APP_URL;
    $ownerName = getSetting('owner_name', 'Admin');
    $year = date('Y');
    
    $name = htmlspecialchars($data['name']);
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#0a0a0f;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0a0a0f;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#12121a;border-radius:12px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0066ff,#0044cc);padding:30px 40px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">Thank You!</h1>
                            <p style="margin:8px 0 0;color:rgba(255,255,255,0.8);font-size:14px;">Your message has been received</p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="color:#ffffff;font-size:16px;font-weight:600;margin:0 0 16px;">Hi {$name},</p>
                            
                            <p style="color:#a0a0b0;font-size:14px;line-height:1.8;margin:0 0 16px;">
                                Thank you for reaching out! I've received your message and will get back to you within <strong style="color:#ffffff;">24 hours</strong>.
                            </p>
                            
                            <p style="color:#a0a0b0;font-size:14px;line-height:1.8;margin:0 0 24px;">
                                In the meantime, feel free to check out my latest work on my portfolio or connect with me on social media.
                            </p>
                            
                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{$siteUrl}/portfolio" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#0066ff,#0044cc);color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:8px;">
                                            View My Portfolio
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color:#a0a0b0;font-size:14px;line-height:1.8;margin:24px 0 0;">
                                Best regards,<br>
                                <strong style="color:#ffffff;">{$ownerName}</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
                            <p style="margin:0;color:#6b6b80;font-size:12px;">
                                <a href="{$siteUrl}" style="color:#0066ff;text-decoration:none;">{$siteName}</a>
                            </p>
                            <p style="margin:6px 0 0;color:#4a4a5a;font-size:11px;">&copy; {$year} {$siteName}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Send HTML email using mail() with proper headers
 */
function sendHtmlEmail($to, $subject, $htmlBody, $replyTo = '') {
    $fromEmail = config('mail_from') ?: getSetting('owner_email', 'noreply@example.com');
    $fromName = config('mail_from_name') ?: getSetting('site_name', APP_NAME);
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    if (!empty($replyTo)) {
        $headers .= "Reply-To: {$replyTo}\r\n";
    }
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    return @mail($to, $subject, $htmlBody, $headers);
}
