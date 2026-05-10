<?php
/**
 * Admin Logout
 */
logoutUser();
session_start();
setFlash('success', 'You have been logged out successfully.');
redirect(baseUrl('admin/login'));
