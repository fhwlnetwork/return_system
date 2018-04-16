-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2018 年 03 月 07 日 09:46
-- 服务器版本: 5.5.53
-- PHP 版本: 5.4.45

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS ts_returnsystem CHARSET utf8;

use ts_returnsystem;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `ts_returnsystem`
--

-- --------------------------------------------------------


--
-- 表的结构 `auth_assignment`
--

DROP TABLE IF EXISTS `auth_assignment`;
CREATE TABLE IF NOT EXISTS `auth_assignment` (
  `item_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `auth_assignment`
--

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
  ('root', '1', 1428575108);

-- --------------------------------------------------------

--
-- 表的结构 `auth_item`
--

DROP TABLE IF EXISTS `auth_item`;
CREATE TABLE IF NOT EXISTS `auth_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `rule_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '角色层级',
  `p_id` int(11) DEFAULT NULL COMMENT '上级ID',
  `by_id` int(11) DEFAULT NULL COMMENT '创建人',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 转存表中的数据 `auth_item`
--

INSERT INTO `auth_item` (`id`, `name`, `type`, `description`, `rule_name`, `data`, `path`, `p_id`, `by_id`, `created_at`, `updated_at`) VALUES
  (1, 'root', 1, '超级管理员', NULL, NULL, '0', 0, NULL, 1428575108, 1428575108);

-- --------------------------------------------------------

--
-- 表的结构 `auth_item_child`
--

DROP TABLE IF EXISTS `auth_item_child`;
CREATE TABLE IF NOT EXISTS `auth_item_child` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `auth_rule`
--

DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE IF NOT EXISTS `auth_rule` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `manager`;
CREATE TABLE IF NOT EXISTS `manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mobile_phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `role` smallint(6) NOT NULL DEFAULT '10',
  `status` smallint(6) NOT NULL DEFAULT '10',
  `mgr_org` text COLLATE utf8_unicode_ci COMMENT '可以管理的组织',
  `mgr_region` text COLLATE utf8_unicode_ci COMMENT '可以管理的区域',
  `mgr_product` text COLLATE utf8_unicode_ci NOT NULL COMMENT '可以管理的产品',
  `mgr_admin` text COLLATE utf8_unicode_ci NOT NULL COMMENT '可以管理的管理员',
  `mgr_portal` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '可管理的portal 页面',
  `tid` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '保存ztree默认生成的tid值',
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '结构路径',
  `pid` int(11) DEFAULT NULL COMMENT '父ID',
  `mgr_admin_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '[1=>不管理其他人，2=>只管理所勾选管理员，3=>管理勾选管理员及自己创建的管理员，4=>管理自己创建的管理员]',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `ip_area` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '允许管理员登录的ip段',
  `max_open_num` int NOT NULL DEFAULT 0 COMMENT '最大开户数',
  `expire_time` int NOT NULL DEFAULT 0 COMMENT '失效日期',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

--
-- 转存表中的数据 `manager`
--
INSERT INTO `manager` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `role`, `status`, `mgr_org`, `mgr_product`, `mgr_admin`, `tid`, `path`, `pid`, `created_at`, `updated_at`, `mgr_admin_type`) VALUES
  (1, 'admin', 'wgsmx0m7uSQ98aBEJ8W-OBowbwqozctq', '$2y$13$2xW..AD7LnU6vG7mZm6sneWuQw5x5P8vu7eE30JS/NVWpI3/JroEG', NULL, 'srun@srun.com', 10, 10, '103', '', '', '', NULL, NULL, 1429592064, 1429592064, 1);
--
-- 创建日志记录表
--
DROP TABLE IF EXISTS `log_operate`;
CREATE TABLE IF NOT EXISTS `log_operate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator` varchar(128) COLLATE utf8_unicode_ci NOT NULL COMMENT '操作人',
  `target` varchar(128) COLLATE utf8_unicode_ci NOT NULL COMMENT '操作目标',
  `action` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '操作动作',
  `action_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '动作类型',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '操作内容',
  `opt_ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '操作人ip',
  `opt_time` int(11) NOT NULL COMMENT '操作时间',
  `class` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '本日志的类，主要用户解析日志中的字段和值',
  `type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '日志类型：默认0格式化数据，1描述性日志',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- 创建管理远登录表`manager_login_log`
--
CREATE TABLE IF NOT EXISTS `manager_login_log`(
  `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT '主键id',
  `user_id` INT NOT NULL DEFAULT 0 COMMENT '管理员登录id',
  `manager_name` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT  '' COMMENT '管理员名称',
  `ip` VARCHAR(64)  COLLATE utf8_unicode_ci  NOT NULL DEFAULT '' COMMENT '登录ip',
  `login_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录时间',
  KEY `manager_name`(`manager_name`),
  KEY `login_time`(`login_time`),
  KEY `user_id`(`user_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci  AUTO_INCREMENT = 1 COMMENT='管理员登录日志表';


DROP TABLE IF EXISTS `users_group`;
CREATE TABLE IF NOT EXISTS `users_group` (
  `id` smallint (6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '组织结构名称',
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '结构路径',
  `pid` smallint (6) NOT NULL DEFAULT '1' COMMENT '父ID',
  `level` tinyint(3) NOT NULL DEFAULT '1' COMMENT '组织递进',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `tid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

--
-- 转存表中的数据 `users_group`
--

INSERT INTO `users_group` (`id`, `name`, `path`, `pid`, `level`, `status`, `tid`) VALUES
  (1, '/', '0', 0, 1, 1, '');

--
-- 表的结构 `stu_class`
--

CREATE TABLE IF NOT EXISTS `stu_class` (
  `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stu_major` varchar(8) NOT NULL DEFAULT '',
  `stu_class` varchar(12) NOT NULL DEFAULT '',
  `tea_num` char(8) NOT NULL DEFAULT '',
  `stu_years` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`stu_class`),
  KEY `tea_num` (`tea_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 转存表中的数据 `stu_class`
--

INSERT INTO `stu_class` (`stu_major`, `stu_class`, `tea_num`, `stu_years`) VALUES
('物联网工程', '物联网14101', '2017001', '2014'),
('网络工程', '网络工程14101', '2017001', '2014'),
('计算机科学与技术', '计科14101', '2017002', '2014');

-- --------------------------------------------------------

--
-- 表的结构 `stu_message_count`
--

CREATE TABLE IF NOT EXISTS `stu_message_count` (
  `id` varchar(2) NOT NULL DEFAULT '',
  `stu_date` date NOT NULL DEFAULT '0000-00-00',
  `stu_num` char(8) NOT NULL,
  `stu_byqxdm` int(4) DEFAULT NULL,
  `stu_dwzzjgdm` varchar(20) DEFAULT NULL,
  `stu_dwmc` varchar(30) DEFAULT NULL,
  `stu_dwxzdn` int(4) DEFAULT NULL,
  `stu_dwhydm` int(4) DEFAULT NULL,
  `stu_dwszdm` int(8) DEFAULT NULL,
  `stu_gzwlbdm` int(4) DEFAULT NULL,
  PRIMARY KEY (`stu_num`,`stu_date`),
  UNIQUE KEY `stu_num` (`stu_num`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `stu_message_count`
--

INSERT INTO `stu_message_count` (`id`, `stu_date`, `stu_num`, `stu_byqxdm`, `stu_dwzzjgdm`, `stu_dwmc`, `stu_dwxzdn`, `stu_dwhydm`, `stu_dwszdm`, `stu_gzwlbdm`) VALUES
('0', '2017-11-06', '40214105', 10, NULL, NULL, NULL, NULL, NULL, NULL),
('', '2017-11-23', '40214112', 10, NULL, NULL, NULL, NULL, NULL, NULL),
('1', '2017-11-07', '40214113', 1, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `super_user_table`
--

CREATE TABLE IF NOT EXISTS `super_user_table` (
  `super_user` varchar(8) NOT NULL DEFAULT '',
  `super_passwd` varchar(8) DEFAULT NULL,
  `super_qq` char(10) DEFAULT NULL,
  `super_email` varchar(12) DEFAULT NULL,
  `super_mobile` char(12) DEFAULT NULL,
  `super_power` int(11) DEFAULT NULL,
  PRIMARY KEY (`super_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `super_user_table`
--

INSERT INTO `super_user_table` (`super_user`, `super_passwd`, `super_qq`, `super_email`, `super_mobile`, `super_power`) VALUES
('admin', '123456', '123456789', '849109312@qq', '13619565979', 0);

-- --------------------------------------------------------

--
-- 表的结构 `user_stu`
--

CREATE TABLE IF NOT EXISTS `user_stu` (
  `id` varchar(2) NOT NULL,
  `stu_num` char(8) NOT NULL,
  `stu_name` varchar(6) DEFAULT NULL,
  `stu_sex` char(2) DEFAULT NULL,
  `stu_passwd` varchar(8) DEFAULT '123456' COMMENT '初始密码123456',
  `stu_age` int(2) DEFAULT NULL,
  `stu_birth` date DEFAULT NULL,
  `stu_email` varchar(10) DEFAULT NULL,
  `stu_qqnum` int(11) DEFAULT NULL,
  `stu_mobilephone` varchar(11) DEFAULT NULL,
  `stu_address` varchar(20) DEFAULT NULL,
  `stu_class` varchar(10) DEFAULT NULL,
  `stu_user_power` int(2) DEFAULT NULL,
  `stu_photo` varchar(20) DEFAULT '/upload/test.img' COMMENT '头像',
  PRIMARY KEY (`stu_num`),
  KEY `stu_class` (`stu_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user_stu`
--

INSERT INTO `user_stu` (`id`, `stu_num`, `stu_name`, `stu_sex`, `stu_passwd`, `stu_age`, `stu_birth`, `stu_email`, `stu_qqnum`, `stu_mobilephone`, `stu_address`, `stu_class`, `stu_user_power`, `stu_photo`) VALUES
('0', '40214105', '吴建华', '男', '123456', 23, '1994-01-19', '8491909312', 849109312, '13619596597', '宁夏理工学院众智云端团队', '网络工程14101', 0, '/upload/test.img'),
('2', '40214112', '张三', '男', '123456', 23, '2017-11-16', '110', 120, '119', '宁夏理工学院众智云端团队', '物联网14101', 3, '/upload/test.img'),
('1', '40214113', '王爱军', '男', '123456', 23, '2017-11-15', '112', 12, '122', '宁夏理工学院众智云端团队', '计科14101', 2, '/upload/test.img');

-- --------------------------------------------------------

--
-- 表的结构 `user_tea`
--

CREATE TABLE IF NOT EXISTS `user_tea` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `tea_num` char(8) NOT NULL,
  `tea_name` varchar(6) DEFAULT NULL,
  `tea_sex` char(2) DEFAULT NULL,
  `tea_passwd` varchar(8) DEFAULT '' COMMENT '123456',
  `tea_age` int(2) DEFAULT NULL,
  `tea_birth` date DEFAULT NULL,
  `tea_email` varchar(10) DEFAULT NULL,
  `tea_qqnum` char(11) DEFAULT NULL,
  `tea_mobilephone` char(11) DEFAULT NULL,
  `tea_address` varchar(20) DEFAULT NULL,
  `tea_department` varchar(20) DEFAULT NULL,
  `tea_user_power` char(2) DEFAULT NULL COMMENT '1',
  PRIMARY KEY (`id`,`tea_num`),
  KEY `tea_num` (`tea_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `user_tea`
--

INSERT INTO `user_tea` (`id`, `tea_num`, `tea_name`, `tea_sex`, `tea_passwd`, `tea_age`, `tea_birth`, `tea_email`, `tea_qqnum`, `tea_mobilephone`, `tea_address`, `tea_department`, `tea_user_power`) VALUES
(1, '2017001', '常会丽', '女', '123456', 1, '0000-00-00', '1', '1', '1', '1', '1', '1'),
(2, '2017002', '徐晓君', '女', '123456', 1, '2017-11-16', '1', '1', '1', '1', '1', '1'),
(3, '', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- 限制导出的表
--

--
-- 限制表 `stu_class`
--
ALTER TABLE `stu_class`
  ADD CONSTRAINT `stu_class_connet` FOREIGN KEY (`stu_class`) REFERENCES `user_stu` (`stu_class`),
  ADD CONSTRAINT `tea_class_stu` FOREIGN KEY (`tea_num`) REFERENCES `user_tea` (`tea_num`);

--
-- 限制表 `user_stu`
--
ALTER TABLE `user_stu`
  ADD CONSTRAINT `stu_conect` FOREIGN KEY (`stu_num`) REFERENCES `stu_message_count` (`stu_num`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
