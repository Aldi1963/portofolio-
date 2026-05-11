<?php
/**
 * Admin - Change Password
 */
if (!hasRole('admin')) {
    setFlash('error', 'Access denied. Admin only.');
    redirect(baseUrl('admin/dashboard'));
}

$adminPage = 'settings';
$adminTitle = 'Change Password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/change-password'));
    }
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        setFlash('error', 'All fields are required.');
        redirect(baseUrl('admin/change-password'));
    }
    
    if (strlen($newPassword) < 8) {
        setFlash('error', 'New password must be at least 8 characters.');
        redirect(baseUrl('admin/change-password'));
    }
    
    if ($newPassword !== $confirmPassword) {
        setFlash('error', 'New password and confirmation do not match.');
        redirect(baseUrl('admin/change-password'));
    }
    
    // Verify current password
    $user = currentUser();
    if (!$user || !verifyPassword($currentPassword, $user['password'])) {
        setFlash('error', 'Current password is incorrect.');
        redirect(baseUrl('admin/change-password'));
    }
    
    // Update password
    try {
        db()->update('users', [
            'password' => hashPassword($newPassword)
        ], 'id = ?', [$user['id']]);
        
        setFlash('success', 'Password changed successfully!');
        redirect(baseUrl('admin/change-password'));
    } catch (Exception $e) {
        setFlash('error', 'Failed to update password. Please try again.');
        redirect(baseUrl('admin/change-password'));
    }
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <a href="<?= baseUrl('admin/settings') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Settings
    </a>
</div>

<div class="dashboard-card" style="max-width: 500px;">
    <div class="card-header">
        <h3><i class="fas fa-key"></i> Change Password</h3>
    </div>
    <div class="card-body">
        <div class="settings-info-box" style="display:flex;gap:12px;padding:14px 18px;background:rgba(0,102,255,0.05);border:1px solid rgba(0,102,255,0.15);border-radius:8px;margin-bottom:20px;font-size:0.85rem;color:#a0a0b0;">
            <i class="fas fa-info-circle" style="color:#0066ff;margin-top:2px;"></i>
            <div>
                <strong style="color:#fff;display:block;margin-bottom:4px;">Password Requirements</strong>
                <p style="margin:0;line-height:1.6;">Minimum 8 characters. Use a mix of letters, numbers, and symbols for best security.</p>
            </div>
        </div>

        <form method="POST" class="admin-form">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label for="current_password">Current Password *</label>
                <div class="input-password-wrapper" style="position:relative;">
                    <input type="password" id="current_password" name="current_password" required placeholder="Enter your current password" style="padding-right:44px;">
                    <button type="button" class="toggle-password-btn" onclick="toggleSettingPassword(this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b6b80;cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password *</label>
                <div class="input-password-wrapper" style="position:relative;">
                    <input type="password" id="new_password" name="new_password" required placeholder="Enter new password (min 8 chars)" minlength="8" style="padding-right:44px;">
                    <button type="button" class="toggle-password-btn" onclick="toggleSettingPassword(this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b6b80;cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <div class="input-password-wrapper" style="position:relative;">
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password" minlength="8" style="padding-right:44px;">
                    <button type="button" class="toggle-password-btn" onclick="toggleSettingPassword(this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b6b80;cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSettingPassword(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fas fa-eye'; }
}
</script>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
