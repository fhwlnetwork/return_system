use ts_returnsystem;

--
-- 学生表增加字段
--
ALTER TABLE manager ADD major_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '专业id';
ALTER TABLE manager ADD major_name VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称';
ALTER TABLE manager ADD begin_time INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '入校时间';
ALTER TABLE manager ADD stop_time INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '毕业时间';
ALTER TABLE manager ADD INDEX index_major(`major_name`);


ALTER TABLE manager ADD person_name VARCHAR(15) NOT NULL DEFAULT '' COMMENT '用户姓名';
ALTER TABLE manager ADD nation VARCHAR(30) NOT NULL DEFAULT '' COMMENT '民族';
ALTER TABLE manager ADD sex VARCHAR(12) NOT NULL DEFAULT '' COMMENT '性别';
ALTER TABLE manager ADD id_number VARCHAR(20) NOT NULL DEFAULT '' COMMENT '身份证';


--
-- 工作增加工作名称
--

ALTER TABLE stu_works ADD work_name VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称';
ALTER TABLE stu_works_now ADD work_name VARCHAR(64) NOT NULL DEFAULT '' COMMENT '专业名称';