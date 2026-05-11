<?php
/**
 * Admin - Database Backup
 * Export database as SQL file download
 */
if (!hasRole('admin')) {
    setFlash('error', 'Access denied. Admin only.');
    redirect(baseUrl('admin/dashboard'));
}

$adminPage = 'backup';
$adminTitle = 'Database Backup';

// Handle backup download
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    if (!verifyCsrfToken($_GET['token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/backup'));
    }
    
    try {
        $tables = db()->fetchAll("SHOW TABLES");
        $dbName = DB_NAME;
        $filename = 'backup_' . $dbName . '_' . date('Y-m-d_His') . '.sql';
        
        // Start output
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = "-- =============================================\n";
        $output .= "-- Database Backup: {$dbName}\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- PHP Portfolio Website\n";
        $output .= "-- =============================================\n\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+07:00\";\n\n";
        
        echo $output;
        
        foreach ($tables as $tableRow) {
            $table = array_values($tableRow)[0];
            
            // Get CREATE TABLE statement
            $createTable = db()->fetch("SHOW CREATE TABLE `{$table}`");
            $createSql = $createTable['Create Table'] ?? '';
            
            echo "\n-- =============================================\n";
            echo "-- Table: {$table}\n";
            echo "-- =============================================\n";
            echo "DROP TABLE IF EXISTS `{$table}`;\n";
            echo $createSql . ";\n\n";
            
            // Get table data
            $rows = db()->fetchAll("SELECT * FROM `{$table}`");
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                echo "-- Data for table `{$table}`\n";
                
                // Insert in chunks of 100
                $chunks = array_chunk($rows, 100);
                foreach ($chunks as $chunk) {
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    echo "INSERT INTO `{$table}` ({$columnList}) VALUES\n" . implode(",\n", $values) . ";\n\n";
                }
            }
        }
        
        echo "\nCOMMIT;\n";
        exit;
        
    } catch (Exception $e) {
        setFlash('error', 'Backup failed: ' . (APP_DEBUG ? $e->getMessage() : 'Please try again.'));
        redirect(baseUrl('admin/backup'));
    }
}

// Get database info
try {
    $tables = db()->fetchAll("SHOW TABLE STATUS");
    $totalSize = 0;
    $totalRows = 0;
    foreach ($tables as $t) {
        $totalSize += ($t['Data_length'] ?? 0) + ($t['Index_length'] ?? 0);
        $totalRows += $t['Rows'] ?? 0;
    }
} catch (Exception $e) {
    $tables = [];
    $totalSize = 0;
    $totalRows = 0;
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
    <!-- Backup Card -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-download"></i> Export Backup</h3>
        </div>
        <div class="card-body" style="text-align:center;padding:40px 20px;">
            <div style="font-size:3rem;color:var(--accent);margin-bottom:16px;">
                <i class="fas fa-database"></i>
            </div>
            <h3 style="margin-bottom:8px;">Download Database Backup</h3>
            <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:24px;">
                Export all tables and data as a .sql file.<br>
                Use this to restore your website or migrate to another server.
            </p>
            
            <div style="display:flex;justify-content:center;gap:24px;margin-bottom:24px;">
                <div style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary);"><?= count($tables) ?></div>
                    <div style="font-size:0.8rem;color:var(--text-muted);">Tables</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary);"><?= number_format($totalRows) ?></div>
                    <div style="font-size:0.8rem;color:var(--text-muted);">Total Rows</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary);"><?= round($totalSize / 1024, 1) ?> KB</div>
                    <div style="font-size:0.8rem;color:var(--text-muted);">DB Size</div>
                </div>
            </div>
            
            <a href="<?= baseUrl('admin/backup?action=download&token=' . generateCsrfToken()) ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-download"></i> Download SQL Backup
            </a>
            
            <p style="color:var(--text-muted);font-size:0.78rem;margin-top:16px;">
                <i class="fas fa-info-circle"></i> Backup includes all tables, data, and structure.
            </p>
        </div>
    </div>
    
    <!-- Database Info -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-table"></i> Database Tables</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Rows</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $t): ?>
                        <tr>
                            <td><strong><?= $t['Name'] ?></strong></td>
                            <td><?= number_format($t['Rows'] ?? 0) ?></td>
                            <td><?= round((($t['Data_length'] ?? 0) + ($t['Index_length'] ?? 0)) / 1024, 1) ?> KB</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:700;">
                            <td>Total</td>
                            <td><?= number_format($totalRows) ?></td>
                            <td><?= round($totalSize / 1024, 1) ?> KB</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tips -->
<div class="dashboard-card" style="margin-top:20px;">
    <div class="card-body">
        <div style="display:flex;gap:12px;padding:4px;font-size:0.85rem;color:var(--text-secondary);">
            <i class="fas fa-lightbulb" style="color:var(--warning);margin-top:2px;"></i>
            <div>
                <strong style="color:var(--text-primary);display:block;margin-bottom:4px;">Backup Tips</strong>
                <ul style="margin:0;padding-left:18px;line-height:2;">
                    <li>Create backups regularly, especially before major updates</li>
                    <li>Store backup files in a safe location (cloud storage, external drive)</li>
                    <li>To restore: import the .sql file via phpMyAdmin or mysql command</li>
                    <li>Command: <code style="background:var(--bg-tertiary);padding:2px 6px;border-radius:3px;">mysql -u user -p database_name &lt; backup_file.sql</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
