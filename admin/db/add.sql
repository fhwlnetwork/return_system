use ts_returnsystem;
--
-- 学生发布信息中心
--
--
DROP TABLE IF EXISTS `stu_pub_center`;
CREATE TABLE IF NOT EXISTS `stu_pub_center`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
  `stu_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '学生id',
  `stu_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '学生名称',
  `title` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '发布标题',
  `desc` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '描述',
  `content` TEXT COMMENT '发布内容',
  `pic` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布状态0待审核1通过2未通过',
  `remark` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '备注',
  `mid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作者id',
  `operator` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作者',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '反馈时间',
  `utime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  INDEX index_title(title),
  INDEX index_status(status),
  INDEX index_ctime(ctime),
  INDEX index_operator(operator)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '学生发布中心';
-- 创建学生工作表
--
DROP TABLE IF EXISTS `stu_works`;
CREATE TABLE IF  NOT EXISTS `stu_works`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
  `stu_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '学生id',
  `stu_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '学生名称',
  `company_name` VARCHAR(64) NOT NULL DEFAULT 0 COMMENT '公司名称',
  `salary` FLOAT(9,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '薪水',
  `major_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '专业id',
  `major_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称',
  `stime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '开始时间',
  `stop_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '结束时间',
  `is_end` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否至今，0不是1是',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '反馈时间',
  `utime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  KEY index_major(major_id),
  KEY index_name(major_name),
  KEY index_ctime(ctime),
  KEY index_salary(salary),
  KEY index_com(`company_name`),
  KEY index_username(stu_name)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '创建学生工作表';

--
-- 学生工作信息记录表
--
DROP TABLE IF EXISTS `stu_works_now`;
CREATE TABLE IF  NOT EXISTS `stu_works_now`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
  `stu_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '学生id',
  `stu_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '学生名称',
  `group_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户组',
  `company_name` VARCHAR(64) NOT NULL DEFAULT 0 COMMENT '公司名称',
  `salary` FLOAT(9,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '薪水',
  `major_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '专业id',
  `major_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称',
  `stime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '开始时间',
  `stop_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '结束时间',
  `is_end` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否至今，0不是1是',
  `is_same` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否转专业0不是1是',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '反馈时间',
  `utime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  KEY  index_major(major_id),
  KEY index_name(major_name),
  KEY index_ctime(ctime),
  KEY index_salary(salary),
  KEY index_com(`company_name`),
  KEY index_username(stu_name),
  KEY index_same(`is_same`),
  KEY index_group(`group_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '创建学生工作表';
--
-- 创建学生基本信息表
--
/*DROP TABLE IF EXISTS `student_bacic`;
CREATE TABLE IF NOT EXISTS `student_basic`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '写生基本信息表';
*/
--
-- 工作记录表
--
DROP TABLE IF EXISTS `work_history`;
CREATE TABLE IF NOT EXISTS `work_history`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
  `m_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联管理员id',
  `company_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '企业名称',
  `company_belong` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '公司行业属性',
  `start_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '工作应聘时间',
  KEY index_mid(`m_id`),
  KEY index_start_time(`start_time`),
  KEY index_company_belong(`company_belong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '工作记录表';
--
-- 创建专业表
--
CREATE TABLE IF NOT EXISTS `major`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
  `major_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT  '创建时间',
  KEY index_name(`major_name`),
  KEY `ctime`(`ctime`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '专业表';


--
-- 创建专业-工作性质对应表
--
CREATE TABLE IF NOT EXISTS `major_work_relation`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
  `work_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '工作名称',
  `major_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称',
  `major_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '专业id',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT  '创建时间',
  KEY index_name(`major_name`),
  KEY index_work_name(`work_name`),
  KEY `ctime`(`ctime`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '专业-工作性质对应表';

--
-- 发布招聘信息表
--
DROP TABLE IF EXISTS `work_information`;
CREATE TABLE IF NOT EXISTS `work_information`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '公司名称',
  `major_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '专业id',
  `work_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '工作id',
  `work_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '工作名称',
  `major_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称',
  `salary` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '薪资范围',
  `desc` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '工作描述',
  `position` TEXT COMMENT '岗位职责',
  `require` TEXT COMMENT '岗位要求',
  `mid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布者id',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布时间',
  `utime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  KEY index_ctime(ctime),
  KEY index_work(`work_id`),
  KEY index_major(`major_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '招聘信息表';
--
-- 创建发布信息表
--
DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '新闻标题',
  `desc` VARCHAR(90) NOT NULL DEFAULT '' COMMENT '文章描述',
  `pic` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '封面图片',
  `type` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '新闻类别1校内新闻2大赛新闻3行业新闻',
  `content` TEXT COMMENT '发布内容',
  `mid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布者id',
  `click_rate` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '点击率',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布时间',
  `utime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  KEY index_ctime(ctime)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '发布新闻表';

--
-- 创建评论表
--
DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message`(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论用户id',
  `username` VARCHAR(64) NOT NULL DEFAULT 0 COMMENT '评论用户名称',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论状态0待审核1通过2未通过',
  `message` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '评论内容',
  `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注',
  `ctime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布时间',
  `utime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  `mid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作者id',
  `operator` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作者',
  KEY index_ctime(ctime),
  KEY index_status(`status`),
  KEY index_uid(`uid`),
  KEY index_username(`username`),
  KEY index_operator(`operator`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '文章评论表';
