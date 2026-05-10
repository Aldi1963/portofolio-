<?php
/**
 * Admin - Settings
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
            }
        }
        
        // Handle file uploads (avatar, logo, CV)
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
        
        setFlash('success', 'Settings saved successfully!');
    } catch (Exception $e) {
        setFlash('error', 'Failed to save settings.');
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

include TEMPLATES_PATH . '/admin-header.php';
?>

<form method="POST" enctype="multipart/form-data" class="admin-form">
    <?= csrfField() ?>
    
    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <?php $groups = array_keys($settingsGrouped); ?>
        <?php foreach ($groups as $index => $group): ?>
        <button type="button" class="settings-tab <?= $index === 0 ? 'active' : '' ?>" data-tab="<?= $group ?>">
            <i class="fas fa-<?= $group === 'general' ? 'cog' : ($group === 'profile' ? 'user' : ($group === 'social' ? 'share-alt' : ($group === 'home' ? 'home' : 'envelope'))) ?>"></i>
            <?= ucfirst($group) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Settings Content -->
    <?php foreach ($settingsGrouped as $group => $settings): ?>
    <div class="settings-panel dashboard-card <?= $group === array_key_first($settingsGrouped) ? 'active' : '' ?>" id="panel-<?= $group ?>">
        <div class="card-header"><h3><?= ucfirst($group) ?> Settings</h3></div>
        <div class="card-body">
            <?php foreach ($settings as $setting): ?>
            <div class="form-group">
                <label><?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?></label>
                <?php if ($setting['type'] === 'textarea'): ?>
                <textarea name="settings[<?= $setting['setting_key'] ?>]" rows="3"><?= xssClean($setting['setting_value'] ?? '') ?></textarea>
                <?php elseif ($setting['type'] === 'boolean'): ?>
                <select name="settings[<?= $setting['setting_key'] ?>]">
                    <option value="0" <?= $setting['setting_value'] == '0' ? 'selected' : '' ?>>Disabled</option>
                    <option value="1" <?= $setting['setting_value'] == '1' ? 'selected' : '' ?>>Enabled</option>
                </select>
                <?php elseif ($setting['type'] === 'image'): ?>
                <input type="file" name="<?= $setting['setting_key'] ?>" accept="image/*" class="file-input">
                <?php if (!empty($setting['setting_value'])): ?>
                <img src="<?= uploadUrl($setting['setting_value']) ?>" style="max-height:60px;margin-top:5px;border-radius:4px;">
                <?php endif; ?>
                <?php elseif ($setting['type'] === 'number'): ?>
                <input type="number" name="settings[<?= $setting['setting_key'] ?>]" value="<?= xssClean($setting['setting_value'] ?? '') ?>">
                <?php else: ?>
                <input type="text" name="settings[<?= $setting['setting_key'] ?>]" value="<?= xssClean($setting['setting_value'] ?? '') ?>">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
    </div>
</form>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
