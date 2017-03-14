-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2017 at 07:02 AM
-- Server version: 5.7.14
-- PHP Version: 7.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `labelimgdb`
--
CREATE DATABASE IF NOT EXISTS `labelimgdb` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `labelimgdb`;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'An identifier used to track the type of activity.',
  `occurred_at` timestamp NULL DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `icon` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'fa fa-user' COMMENT 'The icon representing users in this group.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `slug`, `name`, `description`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'groupuser', 'GroupUser', 'standard user', '', '2017-01-31 14:33:19', '2017-02-17 05:47:44');

-- --------------------------------------------------------

--
-- Table structure for table `labelimgarea`
--

CREATE TABLE `labelimgarea` (
  `id` int(4) NOT NULL,
  `source` int(4) NOT NULL,
  `rectType` int(4) NOT NULL,
  `rectLeft` int(4) NOT NULL,
  `rectTop` int(4) NOT NULL,
  `rectRight` int(4) NOT NULL,
  `rectBottom` int(4) NOT NULL,
  `user` int(4) NOT NULL DEFAULT '0' COMMENT 'id of the user that submitted the area',
  `alive` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'when 0 considered area as deleted'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `labelimgcategories`
--

CREATE TABLE `labelimgcategories` (
  `id` int(4) NOT NULL,
  `Category` char(25) NOT NULL,
  `Color` char(7) NOT NULL DEFAULT '#FFFFFF' COMMENT 'Color associated with the category'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `labelimgexportlinks`
--

CREATE TABLE `labelimgexportlinks` (
  `id` int(4) NOT NULL,
  `token` char(50) NOT NULL,
  `archivePath` char(100) NOT NULL,
  `expires` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `labelimglinks`
--

CREATE TABLE `labelimglinks` (
  `id` int(4) NOT NULL,
  `path` char(250) NOT NULL,
  `validated` tinyint(1) NOT NULL DEFAULT '0',
  `available` tinyint(4) NOT NULL DEFAULT '1',
  `requested` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'A code that references a specific action or URI that an assignee of this permission has access to.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `conditions` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The conditions under which members of this group have access to this hook.',
  `description` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `slug`, `name`, `conditions`, `description`, `created_at`, `updated_at`) VALUES
(1, 'create_group', 'Create group', 'always()', 'Create a new group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(2, 'create_user', 'Create user', 'always()', 'Create a new user in your own group and assign default roles.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(3, 'create_user_field', 'Set new user group', 'subset(fields,[\'group\'])', 'Set the group when creating a new user.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(4, 'delete_group', 'Delete group', 'always()', 'Delete a group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(5, 'delete_user', 'Delete user', '!has_role(user.id,2) && !is_master(user.id)', 'Delete users who are not Site Administrators.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(6, 'update_account_settings', 'Edit user', 'always()', 'Edit your own account settings.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(7, 'update_group_field', 'Edit group', 'always()', 'Edit basic properties of any group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(8, 'update_user_field', 'Edit user', '!has_role(user.id,2) && subset(fields,[\'name\',\'email\',\'locale\',\'group\',\'flag_enabled\',\'flag_verified\',\'password\'])', 'Edit users who are not Site Administrators.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(9, 'update_user_field', 'Edit group user', 'equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,2) && (!has_role(user.id,3) || equals_num(self.id,user.id)) && subset(fields,[\'name\',\'email\',\'locale\',\'flag_enabled\',\'flag_verified\',\'password\',\'roles\'])', 'Edit users in your own group who are not Site or Group Administrators, except yourself.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(10, 'uri_account_settings', 'Account settings page', 'always()', 'View the account settings page.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(11, 'uri_activities', 'Activity monitor', 'always()', 'View a list of all activities for all users.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(12, 'uri_dashboard', 'Admin dashboard', 'has_role(self.id,2) || has_role(self.id,3)', 'View the administrative dashboard.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(13, 'uri_group', 'View group', 'always()', 'View the group page of any group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(14, 'uri_group', 'View own group', 'equals_num(self.group_id,group.id)', 'View the group page of your own group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(15, 'uri_groups', 'Group management page', 'always()', 'View a page containing a list of groups.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(16, 'uri_user', 'View user', 'always()', 'View the user page of any user.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(17, 'uri_user', 'View user', 'equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,2) && (!has_role(user.id,3) || equals_num(self.id,user.id))', 'View the user page of any user in your group, except the master user and Site and Group Administrators (except yourself).', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(18, 'uri_users', 'User management page', 'always()', 'View a page containing a table of users.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(19, 'view_group_field', 'View group', 'in(property,[\'name\',\'icon\',\'slug\',\'description\',\'users\'])', 'View certain properties of any group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(20, 'view_group_field', 'View group', 'equals_num(self.group_id,group.id) && in(property,[\'name\',\'icon\',\'slug\',\'description\',\'users\'])', 'View certain properties of your own group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(21, 'view_user_field', 'View user', 'in(property,[\'user_name\',\'name\',\'email\',\'locale\',\'theme\',\'roles\',\'group\',\'activities\'])', 'View certain properties of any user.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(22, 'view_user_field', 'View user', 'equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,2) && (!has_role(user.id,3) || equals_num(self.id,user.id)) && in(property,[\'user_name\',\'name\',\'email\',\'locale\',\'roles\',\'group\',\'activities\'])', 'View certain properties of any user in your own group, except the master user and Site and Group Administrators (except yourself).', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(23, 'uri_label', 'view label page', 'has_role(self.id,1) || has_role(self.id,2) || has_role(self.id,3)', 'View the page to annotate the photos', NULL, NULL),
(24, 'uri_validate', 'view validate page', 'has_role(self.id,2) || has_role(self.id,3)', 'View the page to validate the photos', NULL, NULL),
(25, 'uri_upload', 'view upload page', 'has_role(self.id,2)', 'View the page to upload the photos, manage category, export processed data.', NULL, NULL),
(26, 'uri_roles', 'access roles', 'has_role(self.id,2)', 'get the info about roles,', NULL, NULL),
(27, 'uri_export', 'export data', 'has_role(self.id,2)', 'Grant right to export data', NULL, NULL),
(28, 'uri_catEdit', 'export data', 'has_role(self.id,2)', 'Grant right to edit categories of areas', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permission_roles`
--

CREATE TABLE `permission_roles` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `permission_roles`
--

INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(2, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(2, 3, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(3, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(4, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(5, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(6, 1, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(7, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(8, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(9, 3, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(10, 1, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(11, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(12, 2, NULL, NULL),
(13, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(14, 3, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(15, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(16, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(17, 3, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(18, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(19, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(20, 3, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(21, 2, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(22, 3, '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(23, 1, '2017-02-09 07:50:42', '2017-02-09 07:50:42'),
(23, 2, '2017-02-09 07:57:07', '2017-02-09 07:57:07'),
(23, 3, '2017-02-09 07:58:25', '2017-02-09 07:58:25'),
(24, 2, '2017-02-09 07:57:07', '2017-02-09 07:57:07'),
(24, 3, '2017-02-09 07:58:25', '2017-02-09 07:58:25'),
(25, 2, '2017-02-09 07:57:07', '2017-02-09 07:57:07'),
(26, 2, NULL, NULL),
(27, 2, NULL, NULL),
(28, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `persistences`
--

CREATE TABLE `persistences` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `persistent_token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `slug`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'user', 'User', 'This role provides basic user functionality.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(2, 'site-admin', 'Site Administrator', 'This role is meant for "site administrators", who can basically do anything except create, edit, or delete other administrators.', '2017-01-31 14:33:19', '2017-01-31 14:33:19'),
(3, 'group-admin', 'Group Administrator', 'This role is meant for "group administrators", who can basically do anything with users in their own group, except other administrators of that group.', '2017-01-31 14:33:19', '2017-01-31 14:33:19');

-- --------------------------------------------------------

--
-- Table structure for table `role_users`
--

CREATE TABLE `role_users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8_unicode_ci,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `throttles`
--

CREATE TABLE `throttles` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `request_data` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(254) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en_US' COMMENT 'The language and locale to use for this user.',
  `theme` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The user theme.',
  `group_id` int(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'The id of the user group.',
  `flag_verified` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Set to 1 if the user has verified their account via email, 0 otherwise.',
  `flag_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Set to 1 if the user account is currently enabled, 0 otherwise.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.',
  `last_activity_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'The id of the last activity performed by this user.',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_name`, `email`, `first_name`, `last_name`, `locale`, `theme`, `group_id`, `flag_verified`, `flag_enabled`, `last_activity_id`, `password`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'root', 'labelimage.manager@gmail.com', 'labelimage', 'Manager', 'en_US', 'root', 1, 1, 1, 124, '$2y$10$0pqkT4iLXlNI6ak7omYPteDPen02qY80ygl1GzLIQ.C2aS1QrAscm', NULL, '2017-01-31 15:46:59', '2017-02-14 08:46:18'),
(2, 'AdminSh', 'shaun.mermet@labromance.com', 'Shaun', 'Mermet', 'en_US', NULL, 1, 1, 1, 133, '$2y$10$X40HcSTm15VBZqv0SoIiBeU1sAfOBhHxsmNijsBprZ8hF9Kl8nQii', NULL, '2017-02-14 01:27:37', '2017-02-17 05:57:04');

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `version`
--

CREATE TABLE `version` (
  `sprinkle` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `version`
--

INSERT INTO `version` (`sprinkle`, `version`, `created_at`, `updated_at`) VALUES
('core', '4.0.0-alpha', '2017-01-31 14:33:02', '2017-01-31 14:33:02'),
('account', '4.0.0-alpha', '2017-01-31 14:33:02', '2017-01-31 14:33:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activities_user_id_index` (`user_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groups_slug_unique` (`slug`),
  ADD KEY `groups_slug_index` (`slug`);

--
-- Indexes for table `labelimgarea`
--
ALTER TABLE `labelimgarea`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labelimgcategories`
--
ALTER TABLE `labelimgcategories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labelimgexportlinks`
--
ALTER TABLE `labelimgexportlinks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `labelimglinks`
--
ALTER TABLE `labelimglinks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `path` (`path`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_resets_user_id_index` (`user_id`),
  ADD KEY `password_resets_hash_index` (`hash`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permission_roles`
--
ALTER TABLE `permission_roles`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_roles_permission_id_index` (`permission_id`),
  ADD KEY `permission_roles_role_id_index` (`role_id`);

--
-- Indexes for table `persistences`
--
ALTER TABLE `persistences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `persistences_user_id_index` (`user_id`),
  ADD KEY `persistences_token_index` (`token`),
  ADD KEY `persistences_persistent_token_index` (`persistent_token`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`),
  ADD KEY `roles_slug_index` (`slug`);

--
-- Indexes for table `role_users`
--
ALTER TABLE `role_users`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_users_user_id_index` (`user_id`),
  ADD KEY `role_users_role_id_index` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD UNIQUE KEY `sessions_id_unique` (`id`);

--
-- Indexes for table `throttles`
--
ALTER TABLE `throttles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `throttles_type_index` (`type`),
  ADD KEY `throttles_ip_index` (`ip`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_user_name_unique` (`user_name`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_user_name_index` (`user_name`),
  ADD KEY `users_email_index` (`email`),
  ADD KEY `users_group_id_index` (`group_id`),
  ADD KEY `users_last_activity_id_index` (`last_activity_id`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verifications_user_id_index` (`user_id`),
  ADD KEY `verifications_hash_index` (`hash`);

--
-- Indexes for table `version`
--
ALTER TABLE `version`
  ADD UNIQUE KEY `version_sprinkle_unique` (`sprinkle`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `labelimgarea`
--
ALTER TABLE `labelimgarea`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=282;
--
-- AUTO_INCREMENT for table `labelimgcategories`
--
ALTER TABLE `labelimgcategories`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `labelimgexportlinks`
--
ALTER TABLE `labelimgexportlinks`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;
--
-- AUTO_INCREMENT for table `labelimglinks`
--
ALTER TABLE `labelimglinks`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;
--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT for table `persistences`
--
ALTER TABLE `persistences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `throttles`
--
ALTER TABLE `throttles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
DELIMITER $$
--
-- Events
--
CREATE DEFINER=`labelImgManager`@`localhost` EVENT `free images` ON SCHEDULE EVERY '60:0' MINUTE_SECOND STARTS '2017-01-29 00:00:00' ENDS '2018-02-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE labelimglinks SET available = 1 WHERE available = 0 AND requested < DATE_SUB(NOW(), INTERVAL 1 MINUTE)$$

CREATE DEFINER=`labelImgManager`@`localhost` EVENT `Clean download links` ON SCHEDULE EVERY '0 6' DAY_HOUR STARTS '2017-01-29 00:00:00' ENDS '2018-01-29 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `labelimgexportlinks` WHERE `labelimgexportlinks`.`expires`< NOW()$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--Change for Alpha 0.2.1

ALTER TABLE `labelimglinks` ADD `category` INT(1) NULL DEFAULT NULL COMMENT 'category tag on image ref : labelimgcategories' AFTER `requested`;



--Change for Alpha 0.2.2

INSERT INTO `permissions` (`id`, `slug`, `name`, `conditions`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'uri_validated', 'view validated page', 'has_role(self.id,2)', 'View the page to check validated images.', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('29', '2', NULL, NULL);
UPDATE `permissions` SET `name` = 'edit category' WHERE `permissions`.`id` = 28;
INSERT INTO `permissions` (`id`, `slug`, `name`, `conditions`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'get_area', 'Get area', 'always()', 'Grant access to request areas', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('30', '2', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('30', '3', NULL, NULL);
INSERT INTO `permissions` (`id`, `slug`, `name`, `conditions`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'create_area', 'Create area', 'always()', 'Grant access to create areas', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('31', '1', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('31', '2', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('31', '3', NULL, NULL);
INSERT INTO `permissions` (`id`, `slug`, `name`, `conditions`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'update_area', 'Update area', 'always()', 'Grant access to update areas', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('32', '2', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('32', '3', NULL, NULL);
INSERT INTO `permissions` (`id`, `slug`, `name`, `conditions`, `description`, `created_at`, `updated_at`) VALUES (NULL, 'delete_area', 'Delete area', 'always()', 'Grant access to delete areas', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('33', '2', NULL, NULL);
INSERT INTO `permission_roles` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES ('33', '3', NULL, NULL);

ALTER TABLE `labelimglinks` ADD `validated_at` DATETIME NULL DEFAULT NULL AFTER `category`;


--------------------------------
--- Changes for Alpha 0.2.3 ----
--------------------------------


--------------------------------
--- Changes for Alpha 0.2.4 ----
--------------------------------

DROP EVENT `free images`; CREATE DEFINER=`labelImgManager`@`localhost` EVENT `free images` ON SCHEDULE EVERY 3600 MINUTE_SECOND STARTS '2017-01-29 00:00:00' ENDS '2018-02-01 00:00:00' ON COMPLETION NOT PRESERVEENABLE DO UPDATE labelimglinks SET available = 1 WHERE available = 0 AND requested < DATE_SUB(NOW(), INTERVAL 1 HOUR);

INSERT INTO `role_users` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES ('2', '1', NOW(), NOW()), ('2', '2', NOW(), NOW()), ('2', '3', NOW(), NOW())
ON DUPLICATE KEY UPDATE `user_id` = `user_id`;

alter table `labelimgarea`
ADD CONSTRAINT UNIQUE(`source`,
                      `rectType`,
                      `rectLeft`,
                      `rectTop`,
                      `rectRight`,
                      `rectBottom`,
                      `alive`);

--------------------------------
--- Changes for Alpha 0.2.5 ----
--------------------------------

--------------------------------
--- Changes for Alpha 0.2.6 ----
--------------------------------

ALTER TABLE `labelimgarea` ADD `deleted_at` DATETIME NULL DEFAULT NULL AFTER `alive`;
UPDATE `labelimgarea` SET `deleted_at`= NOW() WHERE `alive` = 0;
ALTER TABLE `labelimgarea`
  DROP `alive`;
alter table `labelimgarea`
ADD CONSTRAINT UNIQUE(`source`,
                      `rectType`,
                      `rectLeft`,
                      `rectTop`,
                      `rectRight`,
                      `rectBottom`,
                      `deleted_at`);