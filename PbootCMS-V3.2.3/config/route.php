<?php
// =======可用于二开时自定义路由，升级不覆盖============
return array(
    
    'url_route' => array(
        // URL地址路由，如后台站点信息控制器：'admin/Site' => 'admin/content.Site',
        'home/city.html' => 'home/City/index', // 分站首页
        
        'admin/City' => 'admin/system.City',    // 后台分站管理
    )
);