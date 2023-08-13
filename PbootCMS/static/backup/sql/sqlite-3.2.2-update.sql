-- ----------------------------
-- Sqlite数据库升级脚本
-- 适用于PbootCMS 3.2.2
-- ----------------------------

--
-- 后台新增首页跳转404配置项
--
--

INSERT INTO ay_config (`name`, `value`, `type`, `sorting`, `description`) VALUES ('url_index_404', '0', '2', 255, '跳转404');
--
-- 
--
