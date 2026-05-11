<?php
/**
 * Admin Logout
 */
logActivity('logout', 'User logged out');
logoutUser();
session_start();
setFlash('success', 'You have been logged out successfully.');
redirect(baseUrl('admin/login'));
