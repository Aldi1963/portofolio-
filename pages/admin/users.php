<?php
/**
 * Admin - User Management
 * List users, create editors, toggle active status
 */
if (!hasRole('admin')) {
    setFlash('error', 'Access denied. Admin only.');
    redirect(baseUrl('admin/dashboard'));
}

$adminPage = 'users';
$adminTitle = 'User Management';

// Handle create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/users'));
    }
    
    if ($_POST['action'] === 'create') {
        $username = post('username');
        $email = post('email');
        $fullName = post('full_name');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
            setFlash('error', 'All fields are required.');
            redirect(baseUrl('admin/users'));
        }
        
        if (strlen($password) < 8) {
            setFlash('error', 'Password must be at least 8 characters.');
            redirect(baseUrl('admin/users'));
        }
        
        if (!isValidEmail($email)) {
            setFlash('error', 'Invalid email address.');
            redirect(baseUrl('admin/users'));
        }
        
        // Check duplicates
        try {
            $existing = db()->fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            if ($existing) {
                setFlash('error', 'Username or email already exists.');
                redirect(baseUrl('admin/users'));
            }
            
            db()->insert('users', [
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName,
                'password' => hashPassword($password),
                'role' => 'editor',
                'is_active' => 1,
            ]);
            
            logActivity('create_user', 'Created editor user: ' . $username);
            setFlash('success', 'Editor user created successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to create user: ' . (APP_DEBUG ? $e->getMessage() : 'Please try again.'));
        }
        redirect(baseUrl('admin/users'));
    }
    
    if ($_POST['action'] === 'toggle_active') {
        $userId = (int)($_POST['user_id'] ?? 0);
        
        if ($userId && $userId !== $_SESSION['user_id']) {
            try {
                $targetUser = db()->fetch("SELECT id, is_active, username FROM users WHERE id = ?", [$userId]);
                if ($targetUser) {
                    $newStatus = $targetUser['is_active'] ? 0 : 1;
                    db()->update('users', ['is_active' => $newStatus], 'id = ?', [$userId]);
                    $statusText = $newStatus ? 'activated' : 'deactivated';
                    logActivity('toggle_user', "User {$targetUser['username']} {$statusText}");
                    setFlash('success', "User {$targetUser['username']} has been {$statusText}.");
                }
            } catch (Exception $e) {
                setFlash('error', 'Failed to update user status.');
            }
        } else {
            setFlash('error', 'Cannot change your own status.');
        }
        redirect(baseUrl('admin/users'));
    }
}

// Get all users
try {
    $users = db()->fetchAll("SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC");
} catch (Exception $e) {
    $users = [];
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <p style="margin:0;font-size:0.85rem;color:var(--text-muted);">
        <i class="fas fa-info-circle"></i> Manage user accounts. Editors can manage content but cannot access system settings.
    </p>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('create-user-modal').style.display='flex'">
        <i class="fas fa-plus"></i> Add Editor
    </button>
</div>

<!-- Users Table -->
<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-users"></i> All Users</h3>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-users"></i>
            <h3>No users found</h3>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= xssClean($u['full_name']) ?></strong><br><small style="color:var(--text-muted);">@<?= xssClean($u['username']) ?></small></td>
                        <td><?= xssClean($u['email']) ?></td>
                        <td>
                            <span style="padding:3px 8px;border-radius:4px;font-size:0.75rem;background:<?= $u['role'] === 'admin' ? '#0066ff15' : '#10b98115' ?>;color:<?= $u['role'] === 'admin' ? '#0066ff' : '#10b981' ?>;">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span style="padding:3px 8px;border-radius:4px;font-size:0.75rem;background:<?= $u['is_active'] ? '#10b98115' : '#ef444415' ?>;color:<?= $u['is_active'] ? '#10b981' : '#ef4444' ?>;">
                                <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><span style="font-size:0.8rem;"><?= $u['last_login'] ? timeAgo($u['last_login']) : 'Never' ?></span></td>
                        <td>
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-outline" style="font-size:0.75rem;padding:4px 10px;" 
                                        onclick="return confirm('<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this user?')">
                                    <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                                    <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                            <?php else: ?>
                            <span style="font-size:0.8rem;color:var(--text-muted);">Current user</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create User Modal -->
<div id="create-user-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;">
    <div class="dashboard-card" style="max-width:480px;width:90%;margin:auto;">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3><i class="fas fa-user-plus"></i> Create Editor User</h3>
            <button type="button" onclick="document.getElementById('create-user-modal').style.display='none'" style="background:none;border:none;color:var(--text-muted);font-size:1.2rem;cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form method="POST" class="admin-form">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Enter full name">
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required placeholder="Enter username" pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required placeholder="Enter email address">
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required placeholder="Minimum 8 characters" minlength="8">
                </div>
                
                <div style="padding:12px 16px;background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15);border-radius:8px;margin-bottom:16px;font-size:0.83rem;color:var(--text-secondary);">
                    <i class="fas fa-info-circle" style="color:#10b981;"></i>
                    This user will be created with the <strong>Editor</strong> role. Editors can manage content (projects, blog, services) but cannot access Settings, Backup, or User Management.
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('create-user-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
