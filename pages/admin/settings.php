<?php
/**
 * Admin - Settings
 * Dynamic configuration management from dashboard
 * Groups: general, profile, social, home, contact, mail, integration, security
 */
$adminPage = 'settings';
$adminTitle = 'Settings';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid token.');
        redirect(baseUrl('admin/settings'));
    }
    
    $settingsToUpdate = $_POST['settings'] ?? [];
    
    try {
        foreach ($settingsToUpdate as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                db()->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
            } else {
                // Auto-create if not exists (for new settings added via form)
                $group = $_POST['_setting_group'][$key] ?? 'general';
                $type = $_POST['_setting_type'][$key] ?? 'text';
                db()->insert('settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'setting_group' => $group,
                    'type' => $type
                ]);
            }
        }
        
        // Handle file uploads (avatar, logo, CV, favicon)
        $fileFields = ['owner_avatar', 'site_logo', 'site_favicon', 'owner_cv'];
        foreach ($fileFields as $field) {
            if (!empty($_FILES[$field]['tmp_name'])) {
                $allowedTypes = $field === 'owner_cv' ? ['pdf'] : ['jpg','jpeg','png','gif','webp','svg'];
                $upload = uploadFile($_FILES[$field], 'settings', $allowedTypes);
                if ($upload['success']) {
                    $existing = db()->fetch("SELECT id, setting_value FROM settings WHERE setting_key = ?", [$field]);
                    if ($existing) {
                        if ($existing['setting_value']) deleteFile($existing['setting_value']);
                        db()->update('settings', ['setting_value' => $upload['path']], 'setting_key = ?', [$field]);
                    }
                }
            }
        }
        
        setFlash('success', 'Settings saved successfully! Changes will take effect immediately.');
    } catch (Exception $e) {
        setFlash('error', 'Failed to save settings: ' . (APP_DEBUG ? $e->getMessage() : 'Please try again.'));
    }
    redirect(baseUrl('admin/settings'));
}

// Get all settings grouped
try {
    $allSettings = db()->fetchAll("SELECT * FROM settings ORDER BY setting_group, id");
    $settingsGrouped = [];
    foreach ($allSettings as $s) {
        $settingsGrouped[$s['setting_group']][] = $s;
    }
} catch (Exception $e) {
    $settingsGrouped = [];
}

// Define group icons and labels
$groupConfig = [
    'general' => ['icon' => 'cog', 'label' => 'General', 'desc' => 'Basic website configuration'],
    'profile' => ['icon' => 'user', 'label' => 'Profile', 'desc' => 'Your personal information'],
    'social' => ['icon' => 'share-alt', 'label' => 'Social Media', 'desc' => 'Social media links'],
    'home' => ['icon' => 'home', 'label' => 'Homepage', 'desc' => 'Hero section & statistics'],
    'contact' => ['icon' => 'envelope', 'label' => 'Contact', 'desc' => 'Contact page configuration'],
    'mail' => ['icon' => 'paper-plane', 'label' => 'Email/SMTP', 'desc' => 'SMTP email configuration for sending messages'],
    'integration' => ['icon' => 'plug', 'label' => 'Integrations', 'desc' => 'Third-party services (Analytics, reCAPTCHA, WhatsApp)'],
    'security' => ['icon' => 'shield-alt', 'label' => 'Security', 'desc' => 'Security & session settings'],
];

// Friendly labels for setting keys
$friendlyLabels = [
    'mail_host' => 'SMTP Host',
    'mail_port' => 'SMTP Port',
    'mail_username' => 'SMTP Username / Email',
    'mail_password' => 'SMTP Password / App Password',
    'mail_from' => 'From Email Address',
    'mail_from_name' => 'From Name',
    'mail_encryption' => 'Encryption (tls/ssl)',
    'recaptcha_site_key' => 'reCAPTCHA Site Key',
    'recaptcha_secret_key' => 'reCAPTCHA Secret Key',
    'ga_tracking_id' => 'Google Analytics Tracking ID',
    'whatsapp_number' => 'WhatsApp Number (with country code)',
    'whatsapp_message' => 'Default WhatsApp Message',
    'tawk_to_id' => 'Tawk.to Widget ID (Live Chat)',
    'facebook_pixel_id' => 'Facebook Pixel ID',
    'session_lifetime' => 'Session Lifetime (seconds)',
    'csrf_token_lifetime' => 'CSRF Token Lifetime (seconds)',
    'login_max_attempts' => 'Max Login Attempts',
    'login_lockout_time' => 'Lockout Time (seconds)',
    'force_https' => 'Force HTTPS',
    'maintenance_mode' => 'Maintenance Mode',
    'site_name' => 'Site Name',
    'site_tagline' => 'Site Tagline',
    'site_description' => 'Site Description',
    'site_keywords' => 'SEO Keywords',
    'site_logo' => 'Site Logo',
    'site_favicon' => 'Favicon',
    'owner_name' => 'Your Name',
    'owner_title' => 'Professional Title',
    'owner_email' => 'Email Address',
    'owner_phone' => 'Phone Number',
    'owner_address' => 'Address / Location',
    'owner_bio' => 'Biography',
    'owner_avatar' => 'Profile Photo',
    'owner_cv' => 'CV / Resume (PDF)',
    'hero_title' => 'Hero Title',
    'hero_subtitle' => 'Hero Subtitle',
    'hero_description' => 'Hero Description',
    'hero_typing_texts' => 'Typing Animation Texts (comma-separated)',
    'stats_projects' => 'Total Projects',
    'stats_clients' => 'Total Clients',
    'stats_experience' => 'Years of Experience',
    'stats_awards' => 'Awards/Certifications',
    'footer_text' => 'Footer Copyright Text',
    'google_maps_embed' => 'Google Maps Embed Code',
    'social_github' => 'GitHub URL',
    'social_linkedin' => 'LinkedIn URL',
    'social_twitter' => 'Twitter/X URL',
    'social_instagram' => 'Instagram URL',
    'social_dribbble' => 'Dribbble URL',
    'social_youtube' => 'YouTube URL',
];

// Placeholders / help text
$helpTexts = [
    'mail_host' => 'e.g. smtp.gmail.com, smtp.mail.yahoo.com',
    'mail_port' => 'Usually 587 for TLS, 465 for SSL',
    'mail_username' => 'Your full email address',
    'mail_password' => 'App password (not your regular password)',
    'mail_encryption' => 'tls or ssl',
    'recaptcha_site_key' => 'Get from google.com/recaptcha/admin',
    'recaptcha_secret_key' => 'Keep this secret!',
    'ga_tracking_id' => 'e.g. G-XXXXXXXXXX or UA-XXXXXXX-X',
    'whatsapp_number' => 'e.g. 6281234567890 (no + or spaces)',
    'tawk_to_id' => 'Get from tawk.to dashboard',
    'facebook_pixel_id' => 'e.g. 123456789012345',
    'session_lifetime' => 'Default: 3600 (1 hour)',
    'login_max_attempts' => 'Default: 5 attempts',
    'login_lockout_time' => 'Default: 900 (15 minutes)',
];

include TEMPLATES_PATH . '/admin-header.php';
?>

<form method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrfField() ?>
    
    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <?php 
        $orderedGroups = ['general', 'profile', 'social', 'home', 'contact', 'mail', 'integration', 'security'];
        $first = true;
        foreach ($orderedGroups as $group): 
            if (!isset($settingsGrouped[$group])) continue;
            $gc = $groupConfig[$group] ?? ['icon' => 'cog', 'label' => ucfirst($group), 'desc' => ''];
        ?>
        <button type="button" class="settings-tab <?= $first ? 'active' : '' ?>" data-tab="<?= $group ?>">
            <i class="fas fa-<?= $gc['icon'] ?>"></i>
            <?= $gc['label'] ?>
        </button>
        <?php $first = false; endforeach; ?>
    </div>

    <!-- Settings Panels -->
    <?php 
    $first = true;
    foreach ($orderedGroups as $group): 
        if (!isset($settingsGrouped[$group])) continue;
        $settings = $settingsGrouped[$group];
        $gc = $groupConfig[$group] ?? ['icon' => 'cog', 'label' => ucfirst($group), 'desc' => ''];
    ?>
    <div class="settings-panel dashboard-card <?= $first ? 'active' : '' ?>" id="panel-<?= $group ?>">
        <div class="card-header">
            <h3><i class="fas fa-<?= $gc['icon'] ?>"></i> <?= $gc['label'] ?> Settings</h3>
            <span class="card-desc"><?= $gc['desc'] ?></span>
        </div>
        <div class="card-body">
            
            <?php if ($group === 'mail'): ?>
            <div class="settings-info-box">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Email SMTP Configuration</strong>
                    <p>Configure SMTP settings to send emails from contact form. For Gmail, use App Password (not your regular password). Enable 2FA first, then create App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a>.</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($group === 'integration'): ?>
            <div class="settings-info-box">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Third-Party Integrations</strong>
                    <p>Connect external services. Leave blank to disable any integration. All changes take effect immediately.</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($group === 'security'): ?>
            <div class="settings-info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Security Settings</strong>
                    <p>Be careful modifying these values. Incorrect settings may lock you out or affect site performance.</p>
                </div>
            </div>
            <?php endif; ?>

            <?php foreach ($settings as $setting): 
                $label = $friendlyLabels[$setting['setting_key']] ?? ucwords(str_replace('_', ' ', $setting['setting_key']));
                $help = $helpTexts[$setting['setting_key']] ?? '';
                $type = $setting['type'];
                $value = $setting['setting_value'] ?? '';
            ?>
            <div class="form-group">
                <label for="setting-<?= $setting['setting_key'] ?>">
                    <?= $label ?>
                    <?php if ($type === 'password'): ?>
                    <span class="label-badge sensitive"><i class="fas fa-lock"></i> Sensitive</span>
                    <?php endif; ?>
                </label>
                
                <?php if ($type === 'textarea'): ?>
                <textarea name="settings[<?= $setting['setting_key'] ?>]" id="setting-<?= $setting['setting_key'] ?>" rows="3" placeholder="<?= $help ?>"><?= xssClean($value) ?></textarea>
                
                <?php elseif ($type === 'boolean'): ?>
                <div class="toggle-switch-wrapper">
                    <label class="toggle-switch">
                        <input type="hidden" name="settings[<?= $setting['setting_key'] ?>]" value="0">
                        <input type="checkbox" name="settings[<?= $setting['setting_key'] ?>]" value="1" <?= $value == '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label"><?= $value == '1' ? 'Enabled' : 'Disabled' ?></span>
                </div>
                
                <?php elseif ($type === 'image'): ?>
                <input type="file" name="<?= $setting['setting_key'] ?>" accept="image/*" class="file-input" id="setting-<?= $setting['setting_key'] ?>">
                <?php if (!empty($value)): ?>
                <div class="current-file-preview">
                    <img src="<?= uploadUrl($value) ?>" alt="Current">
                    <span class="file-name"><?= basename($value) ?></span>
                </div>
                <?php endif; ?>
                
                <?php elseif ($type === 'number'): ?>
                <input type="number" name="settings[<?= $setting['setting_key'] ?>]" id="setting-<?= $setting['setting_key'] ?>" value="<?= xssClean($value) ?>" placeholder="<?= $help ?>">
                
                <?php elseif ($type === 'password'): ?>
                <div class="input-password-wrapper">
                    <input type="password" name="settings[<?= $setting['setting_key'] ?>]" id="setting-<?= $setting['setting_key'] ?>" value="<?= xssClean($value) ?>" placeholder="<?= $help ?>" autocomplete="off">
                    <button type="button" class="toggle-password-btn" onclick="toggleSettingPassword(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <?php else: ?>
                <input type="text" name="settings[<?= $setting['setting_key'] ?>]" id="setting-<?= $setting['setting_key'] ?>" value="<?= xssClean($value) ?>" placeholder="<?= $help ?>">
                <?php endif; ?>
                
                <?php if ($help && $type !== 'password'): ?>
                <small class="form-help"><i class="fas fa-question-circle"></i> <?= $help ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php $first = false; endforeach; ?>
    
    <!-- Save Button (Sticky) -->
    <div class="settings-save-bar">
        <div class="save-bar-inner">
            <span class="save-bar-info"><i class="fas fa-info-circle"></i> Changes are saved to database and take effect immediately.</span>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </div>
    </div>
</form>

<style>
/* Additional Settings Page Styles */
.settings-info-box {
    display: flex;
    gap: 12px;
    padding: 16px 20px;
    background: rgba(0, 102, 255, 0.05);
    border: 1px solid rgba(0, 102, 255, 0.15);
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 0.85rem;
    color: var(--text-secondary, #a0a0b0);
}
.settings-info-box i { color: #0066ff; font-size: 1.1rem; margin-top: 2px; }
.settings-info-box strong { display: block; color: var(--text-primary, #fff); margin-bottom: 4px; }
.settings-info-box p { margin: 0; line-height: 1.6; }
.settings-info-box p a { color: #0066ff; }
.settings-info-box.warning { background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2); }
.settings-info-box.warning i { color: #f59e0b; }

.card-desc { font-size: 0.8rem; color: var(--text-muted, #6b6b80); }

.label-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; margin-left: 8px; }
.label-badge.sensitive { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.input-password-wrapper { position: relative; }
.input-password-wrapper input { padding-right: 44px; }
.toggle-password-btn { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted, #6b6b80); cursor: pointer; padding: 4px; }
.toggle-password-btn:hover { color: #0066ff; }

.toggle-switch-wrapper { display: flex; align-items: center; gap: 12px; }
.toggle-switch { position: relative; display: inline-block; width: 48px; height: 26px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background: var(--bg-tertiary, #1a1a2e); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 26px; transition: 0.3s; }
.toggle-slider::before { content: ''; position: absolute; height: 20px; width: 20px; left: 2px; bottom: 2px; background: #fff; border-radius: 50%; transition: 0.3s; }
.toggle-switch input:checked + .toggle-slider { background: #0066ff; border-color: #0066ff; }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(22px); }
.toggle-label { font-size: 0.85rem; color: var(--text-secondary, #a0a0b0); }

.current-file-preview { display: flex; align-items: center; gap: 10px; margin-top: 8px; padding: 8px 12px; background: var(--bg-tertiary, #1a1a2e); border-radius: 6px; }
.current-file-preview img { height: 40px; width: auto; border-radius: 4px; }
.current-file-preview .file-name { font-size: 0.8rem; color: var(--text-muted, #6b6b80); }

.settings-save-bar { position: sticky; bottom: 0; margin: 24px -24px -24px; padding: 16px 24px; background: rgba(10,10,15,0.95); backdrop-filter: blur(10px); border-top: 1px solid var(--border, rgba(255,255,255,0.08)); z-index: 10; }
.save-bar-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.save-bar-info { font-size: 0.8rem; color: var(--text-muted, #6b6b80); display: flex; align-items: center; gap: 6px; }

.form-help { display: flex; align-items: center; gap: 4px; margin-top: 4px; font-size: 0.78rem; color: var(--text-muted, #6b6b80); }

@media (max-width: 768px) {
    .settings-save-bar { margin: 24px -16px -16px; padding: 12px 16px; }
    .save-bar-inner { flex-direction: column; gap: 10px; }
    .save-bar-info { display: none; }
}
</style>

<script>
function toggleSettingPassword(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
