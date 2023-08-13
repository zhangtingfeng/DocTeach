-- ----------------------------
-- Mysql数据库升级脚本
-- 适用于PbootCMS 3.2.1
-- ----------------------------

--
-- 后台新增图片清理菜单
-- 建议自行在后台Menu/index下添加菜单，该sql仅提供参数用作参考
--

INSERT INTO ay_menu ( `mcode`, `pcode`, `name`, `url`, `sorting`, `status`, `shortcut`, `ico`, `create_user`, `update_user`, `create_time`, `update_time`) VALUES ('M161', 'M1101', '图片清理', '/admin/ImageExt/index', 908, '1', '1', 'fa-trash', 'admin', 'admin', '2022-09-19 13:44:59', '2022-09-19 13:44:59');
--
-- 
--
