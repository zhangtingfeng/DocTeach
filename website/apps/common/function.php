<?php
/**
 * @ copyright (C)2016-2099 Hnaoyun Inc.
 * @ author XingMeng
 * @ email hnxsh@foxmail.com
 * @ date 2017年4月14日
 * 公共处理函数
 */
use core\basic\Config;

// 获取字符串型自动编码
function get_auto_code($string, $start = '1')
{
    if (! $string)
        return $start;
    if (is_numeric($string)) { // 如果纯数字则直接加1
        return sprintf('%0' . strlen($string) . 's', $string + 1);
    } else { // 非纯数字则先分拆
        $reg = '/^([a-zA-Z-_]+)([0-9]+)$/';
        $str = preg_replace($reg, '$1', $string); // 字母部分
        $num = preg_replace($reg, '$2', $string); // 数字部分
        return $str . sprintf('%0' . (strlen($string) - strlen($str)) . 's', $num + 1);
    }
}

// 获取指定分类列表
function get_type($tcode)
{
    $type_model = model('admin.system.Type');
    if (! ! $result = $type_model->getItem($tcode)) {
        return $result;
    } else {
        return array();
    }
}

// 生成区域选择
function make_area_Select($tree, $selectid = null)
{
    $list_html = '';
    global $blank;
    foreach ($tree as $values) {
        // 默认选择项
        if ($selectid == $values->acode) {
            $select = "selected='selected'";
        } else {
            $select = '';
        }
        
        // 禁用父栏目选择功能
        if ($values->son) {
            $disabled = "disabled='disabled'";
        } else {
            $disabled = '';
        }
        $list_html .= "<option value='{$values->acode}' $select $disabled>{$blank}{$values->acode} {$values->name}";
        
        // 子菜单处理
        if ($values->son) {
            $blank .= '　　';
            $list_html .= make_area_Select($values->son, $selectid);
        }
    }
    // 循环完后回归位置
    $blank = substr($blank, 0, - 6);
    return $list_html;
}

// 检测指定的方法是否拥有权限
function check_level($btnAction, $isPath = false)
{
    $user_level = session('levels');
    if ($isPath) {
        if (in_array($btnAction, $user_level)) {
            return true;
        }
    } else {
        if (in_array('/' . M . '/' . C . '/' . $btnAction, $user_level) || session('id') == 1) {
            return true;
        }
    }
}

// 获取返回按钮
function get_btn_back($btnName = '返 回')
{
    if (! ! $backurl = get('backurl')) {
        $url = base64_decode($backurl);
    } elseif (isset($_SERVER["HTTP_REFERER"])) {
        $url = $_SERVER["HTTP_REFERER"];
    } else {
        $url = url('/' . M . '/' . C . '/index');
    }
    
    $btn_html = "<a href='" . $url . "' class='layui-btn layui-btn-primary'>$btnName</a>";
    return $btn_html;
}

// 获取新增按钮
function get_btn_add($btnName = '新 增')
{
    $user_level = session('levels');
    if (! in_array('/' . M . '/' . C . '/add', $user_level) && session('id') != 1)
        return;
    $btn_html = "<a href='" . url("/" . M . '/' . C . "/add") . get_btn_qs() . "' class='layui-btn layui-btn-primary'>$btnName</a>";
    return $btn_html;
}

// 获取更多按钮
function get_btn_more($idValue, $id = 'id', $btnName = '详情')
{
    $btn_html = "<a href='" . url("/" . M . '/' . C . "/index/$id/$idValue") . "' class='layui-btn layui-btn-xs layui-btn-primary' title='$btnName'>$btnName</a>";
    return $btn_html;
}

// 获取删除按钮
function get_btn_del($idValue, $id = 'id', $btnName = '删除')
{
    $user_level = session('levels');
    if (! in_array('/' . M . '/' . C . '/del', $user_level) && session('id') != 1)
        return;
    $btn_html = "<a href='" . url('/' . M . '/' . C . "/del/$id/$idValue") . "' onclick='return confirm(\"您确定要删除么？\")' class='layui-btn layui-btn-xs layui-btn-danger' title='$btnName'>$btnName</a>";
    return $btn_html;
}

// 获取修改按钮
function get_btn_mod($idValue, $id = 'id', $btnName = '修改')
{
    $user_level = session('levels');
    if (! in_array('/' . M . '/' . C . '/mod', $user_level) && session('id') != 1)
        return;
    $btn_html = "<a href='" . url("/" . M . '/' . C . "/mod/$id/$idValue") . get_btn_qs() . "'  class='layui-btn layui-btn-xs'>$btnName</a>";
    return $btn_html;
}

// 获取其它按钮
function get_btn($btnName, $theme, $btnAction, $idValue, $id = 'id')
{
    $user_level = session('levels');
    if (! in_array('/' . M . '/' . C . '/' . $btnAction, $user_level) && session('id') != 1)
        return;
    $btn_html = "<a href='" . url("/" . M . '/' . C . "/$btnAction/$id/$idValue") . get_btn_qs() . "'  class='layui-btn layui-btn-xs $theme'>$btnName</a>";
    return $btn_html;
}

// 获取按钮返回参数
function get_btn_qs()
{
    if (isset($_SERVER["QUERY_STRING"]) && ! ! $qs = $_SERVER["QUERY_STRING"]) {
        return "&backurl=" . base64_encode(URL);
    } else {
        return "?backurl=" . base64_encode(URL);
    }
}

// 获取返回URL
function get_backurl()
{
    if (! ! $backurl = get('backurl')) {
        if (isset($_SERVER["QUERY_STRING"]) && ! ! get('p')) {
            return "&backurl=" . $backurl;
        } else {
            return "?backurl=" . $backurl;
        }
    } else {
        return;
    }
}

// 获取返回tab跳转地址
function get_tab($tid)
{
    if (isset($_SERVER["QUERY_STRING"]) && ! ! get('p')) {
        return "&#tab=" . $tid;
    } else {
        return "?#tab=" . $tid;
    }
}

// 缓存语言信息
function cache_config($refresh = false)
{
    // 系统配置缓存
    $config_cache = RUN_PATH . '/config/' . md5('config') . '.php';
    if (! file_exists($config_cache) || $refresh) {
        $model = model('admin.system.Config');
        $config = $model->getConfig();
        unset($config['sn']);
        unset($config['sn_user']);
        Config::set(md5('config'), $config, false, true);
    }
    
    // 多语言缓存
    $lg_cache = RUN_PATH . '/config/' . md5('area') . '.php';
    if (! file_exists($lg_cache) || $refresh) {
        $model = model('admin.system.Config');
        $area = $model->getAreaTheme(); // 获取所有语言
        $map = array();
        foreach ($area as $key => $value) {
            $map[$value['acode']] = $value;
        }
        if (! $map) {
            error('系统没有任何可用区域，请核对后再试！');
        }
        $lgs['lgs'] = $map;
        Config::set(md5('area'), $lgs, false, true);
    }

   // 分站缓存
    $city_cache = RUN_PATH . '/config/' . md5('city') . '.php';
    if ( file_exists(ROOT_PATH . '/data/city.lock') && ( ! file_exists($city_cache) || $refresh ) ) {
        $model = model('admin.system.City');
        $city = $model->getAllList(); // 获取城市分站
        $map = array();
        foreach ($city as $key => $value) {
            $map[$value['etitle']] = $value;
        }
        $citys['citys'] = $map;
        Config::set(md5('city'), $citys, false, true);
    }
}

/**
 * 生成分站URL分站参数
 * @param string $etitle 分站英文名称
 * @param string $isurl 分站绑定链接
 * @return string $path 追加URL参数
 */
function create_city_url( $etitle, $isurl, $path=false ){
    $main_domain = Config::get('main_domain');  //主域名
    $wildcard = Config::get('wildcard');    //泛域名支持
    $city_suffix = Config::get('city_suffix')?'.html':'/';   //分站后缀
    $http = 'http://';
    if( is_https() ){
        $http = 'https://';
    }
    if( $wildcard ){
        if( $main_domain=='' ){
            error('请到后台全局配置->参数配置->基本配置中填入「网站主域名」');
        }
        //生成泛域名
        $domain = create_domain($main_domain,$etitle);
        if( $path ){
            $url = $http . $domain.'/'.PATH;
        }
        $url = $http . $domain;
    }else{
        if( $isurl ){
            if( $path ){
                $url = $http . $isurl.'/'.PATH;
            }else{
                $url = $http . $isurl;
            }
        }else{
            if( $path ){
                $url = ($main_domain ? $http . $main_domain : '') . '/'.$etitle.'/'.PATH;
            }else{
                $url = ($main_domain ? $http . $main_domain : '') . '/'.$etitle.$city_suffix;
            }
        }
    }
    return $url;
}

// 生成新域名 @cms88
function create_domain( $domain, $etitle ){
    $domain_first = explode('.', $domain)[0];
    $new_domain = str_replace($domain_first, $etitle, $domain);
    return $new_domain;
}

// 获取默认语言
function get_default_lg()
{
    $default = current(Config::get('lgs'));
    return $default['acode'];
}

// 获取当前语言并进行安全处理
function get_lg()
{
    $lg = cookie('lg');
    if (! $lg || ! preg_match('/^[\w\-]+$/', $lg)) {
        $lg = get_default_lg();
        cookie('lg', $lg);
    }
    return $lg;
}

// 获取当前语言主题
function get_theme()
{
    $lgs = Config::get('lgs');
    $lg = get_lg();
    return $lgs[$lg]['theme'];
}

// 推送百度
function post_baidu($api, $urls)
{
    $ch = curl_init();
    $options = array(
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_URL => $api,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: text/plain'
        )
    );
    curl_setopt_array($ch, $options);
    $result = json_decode(curl_exec($ch));
    return $result;
}




