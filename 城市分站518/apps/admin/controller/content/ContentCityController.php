<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2017年12月26日
 *  内容栏目控制器
 */
namespace app\admin\controller\content;

use core\basic\Controller;
use app\admin\model\content\ContentCityModel;

class ContentCityController extends Controller
{

    private $count;

    private $blank;

    private $outData = array();

    private $model;

    public function __construct()
    {
        $this->model = new ContentCityModel();
    }

    // 内容栏目列表
    public function index()
    {
        $this->assign('list', true);
        $tree = $this->model->getList();
        $citys = $this->makeCityList($tree);
        $this->assign('citys', $citys);
        
        // 内容模型
        $models = model('admin.content.Model');
        $this->assign('allmodels', $models->getSelectAll());
        $this->assign('models', $models->getSelect());
        
        // 内容栏目下拉表
        $city_tree = $this->model->getSelect();
        $city_select = $this->makeCitySelect($city_tree);
        $this->assign('city_select', $city_select);
        
        // 模板文件
        $htmldir = $this->config('tpl_html_dir') ? '/' . $this->config('tpl_html_dir') : '';
        $this->assign('tpls', file_list(ROOT_PATH . current($this->config('tpl_dir')) . '/' . $this->model->getTheme() . $htmldir));
        
        // 前端地址连接符判断
        $url_break_char = $this->config('url_break_char') ?: '_';
        $this->assign('url_break_char', $url_break_char);
        $this->assign('sitemap_close', $this->config('sitemap_close'));
        $this->assign('sitemap_type', $this->config('sitemap_type'));
        // 获取会员分组
        $this->assign('groups', model('admin.member.MemberGroup')->getSelect());
        
        $this->display('content/contentcity.html');
    }

    // 生成无限级内容栏目列表
    private function makeCityList($tree)
    {
        // 循环生成
        foreach ($tree as $value) {
            $this->count ++;
            $this->outData[$this->count] = new \stdClass();
            foreach ($value as $k => $v) {
                $this->outData[$this->count]->$k = $v;
            }
            
            if ($value->son) {
                $this->outData[$this->count]->son = true;
            } else {
                $this->outData[$this->count]->son = false;
            }
            
            // 子菜单处理
            if ($value->son) {
                $this->blank .= '　　';
                $this->makeCityList($value->son);
            }
        }
        
        // 循环完后回归缩进位置
        $this->blank = substr($this->blank, 6);
        return $this->outData;
    }

    // 内容栏目增加
    public function add()
    {
        if ($_POST) {
            if (! ! $multiplename = post('multiplename')) {
                $multiplename = str_replace('，', ',', $multiplename);
                $pcode = post('pcode', 'var');
                $type = post('type');
                $mcode = post('mcode');

                $status = post('status');
                
                if (! $pcode) { // 父编码默认为0
                    $pcode = 0;
                }
                
                if (! $mcode) {
                    alert_back('栏目模型必须选择！');
                }
                
                if (! $type) {
                    alert_back('栏目类型不能为空！');
                }
                
                $names = explode(',', $multiplename);
                $lastcode = $this->model->getLastCode();
                $scode = get_auto_code($lastcode);
                foreach ($names as $key => $value) {
                    $data[] = array(
                        'acode' => session('acode'),
                        'pcode' => $pcode,
                        'scode' => $scode,
                        'name' => $value,
                        'mcode' => $mcode,

                        'status' => $status,
                        'gid' => 0,
                        'gtype' => 4,
            'gsname' => '',
'address' => '',
'postcode' => '',
'contact' => '',
'mobile' => '',
'phone' => '',
'fax' => '',
'email' => '',
'qq' => '',
                        'filename' => $this->encode($value, 'all'),
                        'title' => '',
                        'keywords' => '',
                        'description' => '',
                        'sorting' => 255,
                        'create_user' => session('username'),
                        'update_user' => session('username')
                    );
                    $scode = get_auto_code($scode);
                }
            } else {
                // 获取数据
                $scode = get_auto_code($this->model->getLastCode()); // 自动编码;
                $pcode = post('pcode', 'var');
                $name = post('name');
                $type = post('type');
                $mcode = post('mcode');

                $status = post('status');
      
                $filename = trim(post('filename'), '/');
                $title = post('title');
                $keywords = post('keywords');
                $description = post('description');
                
                $gid = post('gid', 'int') ?: 0;
                $gtype = post('gtype', 'int') ?: 4;
$gsname = post('gsname');
$address = post('address');
$postcode = post('postcode');           
$contact = post('contact');
$mobile = post('mobile');
$phone = post('phone');
$fax = post('fax');
$email = post('email');
$qq = post('qq');
                 
                
                if (! $scode) {
                    alert_back('编码不能为空！');
                }
                
                if (! $pcode) { // 父编码默认为0
                    $pcode = 0;
                }
                
                if (! $name) {
                    alert_back('栏目名不能为空！');
                }
                
                if (! $mcode) {
                    alert_back('栏目模型必须选择！');
                }
                
                if (! $type) {
                    alert_back('栏目类型不能为空！');
                }
                
                if ($filename && ! preg_match('/^[a-zA-Z0-9\-\/]+$/', $filename)) {
                    alert_back('URL名称只允许字母、数字、横线、斜线组成!');
                }
                
                if ($filename && $this->model->checkUrlname($filename)) {
                    alert_back('URL名称与模型URL名称冲突，请换一个名称！');
                }
                
    
                
                // 检查编码
                if ($this->model->checkCity("scode='$scode'")) {
                    alert_back('该内容栏目编号已经存在，不能再使用！');
                }
                
                // 检查自定义URL名称
                if ($filename) {
                    while ($this->model->checkFilename($filename)) {
                        $filename = $filename . '_' . mt_rand(1, 20);
                    }
                }
                
                // 构建数据
                $data = array(
                    'acode' => session('acode'),
                    'pcode' => $pcode,
                    'scode' => $scode,
                    'name' => $name,
                    'mcode' => $mcode,

                    'status' => $status,
                    'gid' => $gid,
                    'gtype' => $gtype,

'gsname' => $gsname,
'address' => $address,
'postcode' => $postcode,
'contact' => $contact,
'mobile' => $mobile,
'phone' => $phone,
'fax' => $fax,
'email' => $email,
'qq' => $qq,
         
                    'filename' => $filename,
                    'title' => $title,
                    'keywords' => $keywords,
                    'description' => $description,
                    'sorting' => 255,
                    'create_user' => session('username'),
                    'update_user' => session('username')
                );
            }
            
            // 执行添加
            if ($this->model->addCity($data)) {
      
                $this->log('新增数据内容栏目' . $scode . '成功！');
                success('新增成功！', url('/admin/ContentCity/index'));
            } else {
                $this->log('新增数据内容栏目' . $scode . '失败！');
                error('新增失败！', - 1);
            }
        }
    }

        public function configCity()
    {
        if ($_POST) {
               $sitemap_close = post('sitemap_close');
               $sitemap_type = post('sitemap_type');
 
 $this->model->modValue('sitemap_close', $sitemap_close);
 $this->model->modValue('sitemap_type', $sitemap_type);
$this->log('修改城市参数成功！');
path_delete(RUN_PATH . '/config');
success('新增成功！', url('/admin/ContentCity/index'));
       
            }  
    }


    // 生成内容栏目下拉选择
    private function makeCitySelect($tree, $selectid = null)
    {
        $list_html = '';
        foreach ($tree as $value) {
            // 默认选择项
            if ($selectid == $value->scode) {
                $select = "selected='selected'";
            } else {
                $select = '';
            }
            if (get('scode') != $value->scode) { // 不显示本身，避免出现自身为自己的父节点
                $list_html .= "<option value='{$value->scode}' $select>{$this->blank}{$value->name}</option>";
            }
            // 子菜单处理
            if ($value->son) {
                $this->blank .= '　　';
                $list_html .= $this->makeCitySelect($value->son, $selectid);
            }
        }
        // 循环完后回归位置
        $this->blank = substr($this->blank, 0, - 6);
        return $list_html;
    }

    // 内容栏目删除
    public function del()
    {
        // 执行批量删除
        if ($_POST) {
            if (! ! $list = post('list')) {
                if ($this->model->delCityList($list)) {
                    $this->log('批量删除栏目成功！');
                    success('批量删除成功！', - 1);
                } else {
                    $this->log('批量删除栏目失败！');
                    error('批量删除失败！', - 1);
                }
            } else {
                alert_back('请选择要删除的栏目！');
            }
        }
        
        if (! $scode = get('scode', 'var')) {
            error('传递的参数值错误！', - 1);
        }
        if ($this->model->delCity($scode)) {
            $this->log('删除数据内容栏目' . $scode . '成功！');
            success('删除成功！', - 1);
        } else {
            $this->log('删除数据内容栏目' . $scode . '失败！');
            error('删除失败！', - 1);
        }
    }

    // 内容栏目修改
    public function mod()
    {
        if (! ! $submit = post('submit')) {
            switch ($submit) {
                case 'sorting': // 修改列表排序
                    $listall = post('listall');
                    if ($listall) {
                        $sorting = post('sorting');
                        foreach ($listall as $key => $value) {
                            if ($sorting[$key] === '' || ! is_numeric($sorting[$key]))
                                $sorting[$key] = 255;
                            $this->model->modCitySorting($value, "sorting=" . $sorting[$key]);
                        }
                        $this->log('批量修改栏目排序成功！');
                        success('修改成功！', - 1);
                    } else {
                        alert_back('排序失败，无任何内容！');
                    }
                    break;
            }
        }
        
        if (! $scode = get('scode', 'var')) {
            error('传递的参数值错误！', - 1);
        }
        
        // 单独修改状态
        if (($field = get('field', 'var')) && ! is_null($value = get('value', 'var'))) {
            if ($this->model->modCity($scode, "$field='$value',update_user='" . session('username') . "'")) {
                $this->log('修改数据内容栏目' . $scode . '状态' . $value . '成功！');
                location(- 1);
            } else {
                $this->log('修改数据内容栏目' . $scode . '状态' . $value . '失败！');
                alert_back('修改失败！');
            }
        }
        
        // 修改操作
        if ($_POST) {
            
            // 获取数据
            $pcode = post('pcode', 'var');
            $name = post('name');
            $mcode = post('mcode');
            $type = post('type');
 
            $status = post('status');
       
            $filename = trim(post('filename'), '/');
        

            $title = post('title');
            $keywords = post('keywords');
            $description = post('description');
            $modsub = post('modsub', 'int');
            
            $gid = post('gid', 'int') ?: 0;
            $gtype = post('gtype', 'int') ?: 4;

$gsname = post('gsname');
$address = post('address');
$postcode = post('postcode');

 $contact = post('contact');
$mobile = post('mobile');
$phone = post('phone');
$fax = post('fax');
$email = post('email');
$qq = post('qq');
                 
            
            if (! $pcode) { // 父编码默认为0
                $pcode = 0;
            }
            
            if (! $name) {
                alert_back('栏目名不能为空！');
            }
            
            if (! $mcode) {
                alert_back('栏目模型必须选择！');
            }
            
            if (! $type) {
                alert_back('栏目类型不能为空！');
            }
            
            if ($filename && ! preg_match('/^[a-zA-Z0-9\-\/]+$/', $filename)) {
                alert_back('URL名称只允许字母、数字、横线、斜线组成!');
            }
            
            if ($filename && $this->model->checkUrlname($filename)) {
                alert_back('URL名称与模型URL名称冲突，请换一个名称！');
            }
            

            
            if ($filename) {
                while ($this->model->checkFilename($filename, "scode<>'$scode'")) {
                    $filename = $filename . '-' . mt_rand(1, 20);
                }
            }
            
            // 构建数据
            $data = array(
                'pcode' => $pcode,
                'name' => $name,
                'mcode' => $mcode,

'gsname' => $gsname,
'address' => $address,
'postcode' => $postcode,
'contact' => $contact,
'mobile' => $mobile,
'phone' => $phone,
'fax' => $fax,
'email' => $email,
'qq' => $qq,


                'status' => $status,
                'gid' => $gid,
                'gtype' => $gtype, 
                'filename' => $filename,
                'title' => $title,
                'keywords' => $keywords,
                'description' => $description,
                'update_user' => session('username')
            );
            
            // 执行添加
            if ($this->model->modCity($scode, $data, $modsub)) {

                
                $this->log('修改数据内容栏目' . $scode . '成功！');
                success('修改成功！', url('/admin/ContentCity/index'));
            } else {
                location(- 1);
            }
        } else { // 调取修改内容
            $this->assign('mod', true);
            
            $city = $this->model->getCity($scode);
            if (! $city) {
                error('编辑的内容已经不存在！', - 1);
            }
            $this->assign('city', $city);
            
            // 父编码下拉选择
            $city_tree = $this->model->getSelect();
            $city_select = $this->makeCitySelect($city_tree, $city->pcode);
            $this->assign('city_select', $city_select);
            
            // 模板文件
            $htmldir = $this->config('tpl_html_dir') ? '/' . $this->config('tpl_html_dir') : '';
            $this->assign('tpls', file_list(ROOT_PATH . current($this->config('tpl_dir')) . '/' . $this->model->getTheme() . $htmldir));
            
            // 内容模型
            $models = model('admin.content.Model');
            $this->assign('models', $models->getSelect());
            
            // 获取会员分组
            $this->assign('groups', model('admin.member.MemberGroup')->getSelect());
            
            $this->display('content/contentcity.html');
        }
    }

    // 添加栏目时执行单页内容增加
    public function addSingle($scode, $title)
    {
        // 构建数据
        $data = array(
            'acode' => session('acode'),
            'scode' => $scode,
            'subscode' => '',
            'title' => $title,
            'titlecolor' => '#333333',
            'subtitle' => '',
            'filename' => '',
            'author' => session('realname'),
            'source' => '本站',
    
            'date' => date('Y-m-d H:i:s'),
            'content' => '',
            'tags' => '',
            'enclosure' => '',
            'keywords' => '',
            'description' => '',
            'sorting' => 255,
            'status' => 1,
            'istop' => 0,
            'isrecommend' => 0,
            'isheadline' => 0,
            'gid' => 0,
            'gtype' => 4,
      
            'visits' => 0,
            'likes' => 0,
            'oppose' => 0,
            'create_user' => session('username'),
            'update_user' => session('username')
        );
        
        // 执行添加
        if ($this->model->addSingle($data)) {
            return true;
        } else {
            return false;
        }
    }


private $_aMaps = array(
        'a' => -20319, 'ai' => -20317, 'an' => -20304, 'ang' => -20295, 'ao' => -20292,
        'ba' => -20283, 'bai' => -20265, 'ban' => -20257, 'bang' => -20242, 'bao' => -20230, 'bei' => -20051, 'ben' => -20036,
        'beng' => -20032, 'bi' => -20026, 'bian' => -20002, 'biao' => -19990, 'bie' => -19986, 'bin' => -19982, 'bing' => -19976,
        'bo' => -19805, 'bu' => -19784, 'ca' => -19775, 'cai' => -19774, 'can' => -19763, 'cang' => -19756, 'cao' => -19751,
        'ce' => -19746, 'ceng' => -19741, 'cha' => -19739, 'chai' => -19728, 'chan' => -19725, 'chang' => -19715, 'chao' => -19540,
        'che' => -19531, 'chen' => -19525, 'cheng' => -19515, 'chi' => -19500, 'chong' => -19484, 'chou' => -19479, 'chu' => -19467,
        'chuai' => -19289, 'chuan' => -19288, 'chuang' => -19281, 'chui' => -19275, 'chun' => -19270, 'chuo' => -19263, 'ci' => -19261,
        'cong' => -19249, 'cou' => -19243, 'cu' => -19242, 'cuan' => -19238, 'cui' => -19235, 'cun' => -19227, 'cuo' => -19224,
        'da' => -19218, 'dai' => -19212, 'dan' => -19038, 'dang' => -19023, 'dao' => -19018, 'de' => -19006, 'deng' => -19003, 'di' => -18996,
        'dian' => -18977, 'diao' => -18961, 'die' => -18952, 'ding' => -18783, 'diu' => -18774, 'dong' => -18773, 'dou' => -18763,
        'du' => -18756, 'duan' => -18741, 'dui' => -18735, 'dun' => -18731, 'duo' => -18722, 'e' => -18710, 'en' => -18697, 'er' => -18696,
        'fa' => -18526, 'fan' => -18518, 'fang' => -18501, 'fei' => -18490, 'fen' => -18478, 'feng' => -18463, 'fo' => -18448,
        'fou' => -18447, 'fu' => -18446, 'ga' => -18239, 'gai' => -18237, 'gan' => -18231, 'gang' => -18220, 'gao' => -18211,
        'ge' => -18201, 'gei' => -18184, 'gen' => -18183, 'geng' => -18181, 'gong' => -18012, 'gou' => -17997, 'gu' => -17988,
        'gua' => -17970, 'guai' => -17964, 'guan' => -17961, 'guang' => -17950, 'gui' => -17947, 'gun' => -17931, 'guo' => -17928,
        'ha' => -17922, 'hai' => -17759, 'han' => -17752, 'hang' => -17733, 'hao' => -17730, 'he' => -17721, 'hei' => -17703,
        'hen' => -17701, 'heng' => -17697, 'hong' => -17692, 'hou' => -17683, 'hu' => -17676, 'hua' => -17496, 'huai' => -17487,
        'huan' => -17482, 'huang' => -17468, 'hui' => -17454, 'hun' => -17433, 'huo' => -17427, 'ji' => -17417, 'jia' => -17202,
        'jian' => -17185, 'jiang' => -16983, 'jiao' => -16970, 'jie' => -16942, 'jin' => -16915, 'jing' => -16733, 'jiong' => -16708,
        'jiu' => -16706, 'ju' => -16689, 'juan' => -16664, 'jue' => -16657, 'jun' => -16647, 'ka' => -16474, 'kai' => -16470,
        'kan' => -16465, 'kang' => -16459, 'kao' => -16452, 'ke' => -16448, 'ken' => -16433, 'keng' => -16429, 'kong' => -16427,
        'kou' => -16423, 'ku' => -16419, 'kua' => -16412, 'kuai' => -16407, 'kuan' => -16403, 'kuang' => -16401, 'kui' => -16393,
        'kun' => -16220, 'kuo' => -16216, 'la' => -16212, 'lai' => -16205, 'lan' => -16202, 'lang' => -16187, 'lao' => -16180,
        'le' => -16171, 'lei' => -16169, 'leng' => -16158, 'li' => -16155, 'lia' => -15959, 'lian' => -15958, 'liang' => -15944,
        'liao' => -15933, 'lie' => -15920, 'lin' => -15915, 'ling' => -15903, 'liu' => -15889, 'long' => -15878, 'lou' => -15707,
        'lu' => -15701, 'lv' => -15681, 'luan' => -15667, 'lue' => -15661, 'lun' => -15659, 'luo' => -15652, 'ma' => -15640, 'mai' => -15631,
        'man' => -15625, 'mang' => -15454, 'mao' => -15448, 'me' => -15436, 'mei' => -15435, 'men' => -15419, 'meng' => -15416,
        'mi' => -15408, 'mian' => -15394, 'miao' => -15385, 'mie' => -15377, 'min' => -15375, 'ming' => -15369, 'miu' => -15363,
        'mo' => -15362, 'mou' => -15183, 'mu' => -15180, 'na' => -15165, 'nai' => -15158, 'nan' => -15153, 'nang' => -15150, 'nao' => -15149,
        'ne' => -15144, 'nei' => -15143, 'nen' => -15141, 'neng' => -15140, 'ni' => -15139, 'nian' => -15128, 'niang' => -15121,
        'niao' => -15119, 'nie' => -15117, 'nin' => -15110, 'ning' => -15109, 'niu' => -14941, 'nong' => -14937, 'nu' => -14933,
        'nv' => -14930, 'nuan' => -14929, 'nue' => -14928, 'nuo' => -14926, 'o' => -14922, 'ou' => -14921, 'pa' => -14914, 'pai' => -14908,
        'pan' => -14902, 'pang' => -14894, 'pao' => -14889, 'pei' => -14882, 'pen' => -14873, 'peng' => -14871, 'pi' => -14857,
        'pian' => -14678, 'piao' => -14674, 'pie' => -14670, 'pin' => -14668, 'ping' => -14663, 'po' => -14654, 'pu' => -14645,
        'qi' => -14630, 'qia' => -14594, 'qian' => -14429, 'qiang' => -14407, 'qiao' => -14399, 'qie' => -14384, 'qin' => -14379,
        'qing' => -14368, 'qiong' => -14355, 'qiu' => -14353, 'qu' => -14345, 'quan' => -14170, 'que' => -14159, 'qun' => -14151,
        'ran' => -14149, 'rang' => -14145, 'rao' => -14140, 're' => -14137, 'ren' => -14135, 'reng' => -14125, 'ri' => -14123,
        'rong' => -14122, 'rou' => -14112, 'ru' => -14109, 'ruan' => -14099, 'rui' => -14097, 'run' => -14094, 'ruo' => -14092,
        'sa' => -14090, 'sai' => -14087, 'san' => -14083, 'sang' => -13917, 'sao' => -13914, 'se' => -13910, 'sen' => -13907,
        'seng' => -13906, 'sha' => -13905, 'shai' => -13896, 'shan' => -13894, 'shang' => -13878, 'shao' => -13870, 'she' => -13859,
        'shen' => -13847, 'sheng' => -13831, 'shi' => -13658, 'shou' => -13611, 'shu' => -13601, 'shua' => -13406, 'shuai' => -13404,
        'shuan' => -13400, 'shuang' => -13398, 'shui' => -13395, 'shun' => -13391, 'shuo' => -13387, 'si' => -13383, 'song' => -13367,
        'sou' => -13359, 'su' => -13356, 'suan' => -13343, 'sui' => -13340, 'sun' => -13329, 'suo' => -13326, 'ta' => -13318,
        'tai' => -13147, 'tan' => -13138, 'tang' => -13120, 'tao' => -13107, 'te' => -13096, 'teng' => -13095, 'ti' => -13091,
        'tian' => -13076, 'tiao' => -13068, 'tie' => -13063, 'ting' => -13060, 'tong' => -12888, 'tou' => -12875, 'tu' => -12871,
        'tuan' => -12860, 'tui' => -12858, 'tun' => -12852, 'tuo' => -12849, 'wa' => -12838, 'wai' => -12831, 'wan' => -12829,
        'wang' => -12812, 'wei' => -12802, 'wen' => -12607, 'weng' => -12597, 'wo' => -12594, 'wu' => -12585, 'xi' => -12556,
        'xia' => -12359, 'xian' => -12346, 'xiang' => -12320, 'xiao' => -12300, 'xie' => -12120, 'xin' => -12099, 'xing' => -12089,
        'xiong' => -12074, 'xiu' => -12067, 'xu' => -12058, 'xuan' => -12039, 'xue' => -11867, 'xun' => -11861,
        'ya' => -11847, 'yan' => -11831, 'yang' => -11798, 'yao' => -11781, 'ye' => -11604, 'yi' => -11589, 'yin' => -11536,
        'ying' => -11358, 'yo' => -11340, 'yong' => -11339, 'you' => -11324, 'yu' => -11303, 'yuan' => -11097, 'yue' => -11077, 'yun' => -11067,
        'za' => -11055, 'zai' => -11052, 'zan' => -11045, 'zang' => -11041, 'zao' => -11038, 'ze' => -11024, 'zei' => -11020,
        'zen' => -11019, 'zeng' => -11018, 'zha' => -11014, 'zhai' => -10838, 'zhan' => -10832, 'zhang' => -10815, 'zhao' => -10800,
        'zhe' => -10790, 'zhen' => -10780, 'zheng' => -10764, 'zhi' => -10587, 'zhong' => -10544, 'zhou' => -10533, 'zhu' => -10519,
        'zhua' => -10331, 'zhuai' => -10329, 'zhuan' => -10328, 'zhuang' => -10322, 'zhui' => -10315, 'zhun' => -10309, 'zhuo' => -10307,
        'zi' => -10296, 'zong' => -10281, 'zou' => -10274, 'zu' => -10270, 'zuan' => -10262, 'zui' => -10260, 'zun' => -10256, 'zuo' => -10254
    );
 
    /**
     * 将中文编码成拼音
     * @param string $utf8Data utf8字符集数据
     * @param string $sRetFormat 返回格式 [head:首字母|all:全拼音]
     * @return string
     */
    public function encode($utf8Data, $sRetFormat = 'head')
    {
        $sGBK = iconv('UTF-8', 'GBK', $utf8Data);
        $aBuf = array();
        for ($i = 0, $iLoop = strlen($sGBK); $i < $iLoop; $i++) {
            $iChr = ord($sGBK{$i});
            if ($iChr > 160)
                $iChr = ($iChr << 8) + ord($sGBK{++$i}) - 65536;
            if ('head' === $sRetFormat)
                $aBuf[] = substr($this->zh2py($iChr), 0, 1);
            else
                $aBuf[] = $this->zh2py($iChr);
        }
        if ('head' === $sRetFormat)
            return implode('', $aBuf);
        else
            return implode('', $aBuf);
    }
  /**
     * 中文转换到拼音(每次处理一个字符)
     * @param number $iWORD 待处理字符双字节
     * @return string 拼音
     */
    private function zh2py($iWORD)
    {
        if ($iWORD > 0 && $iWORD < 160) {
            return chr($iWORD);
        } elseif ($iWORD < -20319 || $iWORD > -10247) {
            return '';
        } else {
            foreach ($this->_aMaps as $py => $code) {
                if ($code > $iWORD) break;
                $result = $py;
            }
            return $result;
        }
    }
 

}
