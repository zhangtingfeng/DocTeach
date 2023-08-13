<?php
/**
 *  城市分站控制器
 */
namespace app\admin\controller\system;

use core\basic\Controller;
use app\admin\model\system\CityModel;

class CityController extends Controller
{

    private $count;

    private $blank;

    private $outData = array();

    private $model;

    public function __construct()
    {
        $this->model = new CityModel();
        $lockFile = ROOT_PATH . '/data/city.lock';
        //如果不存在插件锁，则创建数据表
        if(!file_exists($lockFile)){
            $this->createTabkle();
        }
    }

    // 创建数据表
    public function createTabkle(){
        if (get_db_type() == 'sqlite') {
            $sql = file_get_contents(ROOT_PATH . '/city_sqlite_update.sql');
            $result = $this->model->amd($sql);
        } else {
            $sql = file_get_contents(ROOT_PATH . '/city_mysql_update.sql');
            //分割sql语句
            $sqlArr = explode(';', $sql);
            foreach ($sqlArr as $v) {
                if( $v!=='' ){
                    $this->model->amd($v);
                }
            }
            $result = true;
        }
        if($result){
            file_put_contents(ROOT_PATH . '/data/city.lock', get_datetime());
        }
    }

    // 地区列表
    public function index1()
    {
        $this->assign('list', true);
        $pid = 0;
        $cur_city = '顶级';
        if( get('pid') ){
            $pid = get('pid');
            $cur_city = $this->model->findCity($pid)->title;
        }
        $this->assign('pid', $pid);
        $this->assign('cur_city', $cur_city);
        $lists = $this->model->getList($pid);
        $this->assign('lists', $lists);
        $city_select = $lists;
        $this->assign('city_select', $city_select);
        $this->display('system/city.html');
    }

    // 内容栏目增加
    public function add()
    {
        // 修改操作
        if ($_POST) {
            if (! ! $mutititle = post('mutititle')) {
                $mutititle = str_replace('，', ',', $mutititle);    //批量城市名
                $mutietitle = post('mutietitle');
                $mutietitle = str_replace('，', ',', $mutietitle);
                $pid = post('pid', 'int');
                if (! $mutititle) {
                    alert_back('城市名称不能为空！');
                }
                if (! $mutietitle) {
                    alert_back('英文名称不能为空！');
                }
                $titles = explode(',', $mutititle);
                $etitles = explode(',', $mutietitle);
                foreach ($titles as $key => $value) {
                    // 检查名称
                    if ($this->model->checkName("title='$value' or etitle='$etitles[$key]'")) {
                        alert_back('城市名称已经存在，不能再使用！');
                    }
                    $data[] = array(
                        'pid' => $pid,
                        'title' => $value,
                        'etitle' => $etitles[$key],
                        'isurl' => '',
                        'status' => 1,
                        'sorting' => 255,
                        'istop' => 0,
                        'seo_title' => '',
                        'seo_keywords' => '',
                        'seo_description' => '',
                        'contact' => '',
                        'mobile' => '',
                        'phone' => '',
                        'fax' => '',
                        'email' => '',
                        'qq' => '',
                        'address' => '',
                    );
                }
            }else{
                // 获取数据
                $title = post('title');
                $etitle = post('etitle');
                $isurl = post('isurl');
                $pid = post('pid', 'int');
                $status = post('status', 'int');
                $istop = post('istop', 'int', '', '', 0);
                $seo_title = post('seo_title');
                $seo_keywords = post('seo_keywords');
                $seo_description = post('seo_description');

                $contact = post('contact');
                $mobile = post('mobile');
                $phone = post('phone');
                $fax = post('fax');
                $email = post('email');
                $qq = post('qq');
                $address = post('address');

                if (! $title) {
                    alert_back('名称不能为空！');
                }
                if (! $etitle) {
                    alert_back('英文名不能为空！');
                }
                // 检查名称
                if ($this->model->checkName("title='$title' or etitle='$etitle'")) {
                    alert_back('名称已经存在，不能再使用！');
                }
                // 构建数据
                $data = array(
                    'pid' => $pid,
                    'title' => $title,
                    'etitle' => $etitle,
                    'isurl' => $isurl,
                    'status' => $status,
                    'sorting' => 255,
                    'istop' => $istop,
                    'seo_title' => $seo_title,
                    'seo_keywords' => $seo_keywords,
                    'seo_description' => $seo_description,
                    'contact' => $contact,
                    'mobile' => $mobile,
                    'phone' => $phone,
                    'fax' => $fax,
                    'email' => $email,
                    'qq' => $qq,
                    'address' => $address
                );
            }
            // 执行添加
            if ($this->model->addCity($data)) {
                $this->log('新增地区' . $id . '成功！');
                $url = $pid?'/pid/'.$pid:'';
                success('新增成功！', url('/admin/City/index'.$url));
            } else {
                location(- 1);
            }
        }
    }

    // 修改
    public function mod()
    {
        // 批量修改排序
        if (! ! $submit = post('submit')) {
            switch ($submit) {
                case 'sorting': // 修改列表排序
                    $listall = post('listall');
                    if ($listall) {
                        $sorting = post('sorting');
                        foreach ($listall as $key => $value) {
                            if ($sorting[$key] === '' || ! is_numeric($sorting[$key]))
                                $sorting[$key] = 255;
                            $this->model->modCity($value, "sorting=" . $sorting[$key]);
                        }
                        $this->log('批量修改排序成功！');
                        success('修改成功！', - 1);
                    } else {
                        alert_back('排序失败，无任何内容！');
                    }
                    break;
            }
        }
        if (! $id = get('id', 'int')) {
            error('传递的参数值错误！', - 1);
        }
        // 单独修改状态
        if (($field = get('field', 'var')) && ! is_null($value = get('value', 'var'))) {
            if ($this->model->modCity($id, "$field='$value'")) {
                $this->log('修改地区' . $id . '状态' . $value . '成功！');
                location(- 1);
            } else {
                $this->log('修改地区' . $id . '状态' . $value . '失败！');
                alert_back('修改失败！');
            }
        }
        // 修改操作
        if ($_POST) {
            // 获取数据
            $title = post('title');
            $etitle = post('etitle');
            $isurl = post('isurl');
            $pid = post('pid', 'int');
            $status = post('status', 'int');
            $istop = post('istop', 'int', '', '', 0);
            $seo_title = post('seo_title');
            $seo_keywords = post('seo_keywords');
            $seo_description = post('seo_description');

            $contact = post('contact');
            $mobile = post('mobile');
            $phone = post('phone');
            $fax = post('fax');
            $email = post('email');
            $qq = post('qq');
            $address = post('address');

            if($id==$pid){
                alert_back('区域混乱啦！');
            }
            if (! $title) {
                alert_back('名称不能为空！');
            }
            if (! $etitle) {
                alert_back('英文名不能为空！');
            }
            // 构建数据
            $data = array(
                'pid' => $pid,
                'title' => $title,
                'etitle' => $etitle,
                'isurl' => $isurl,
                'status' => $status,
                'istop' => $istop,
                'seo_title' => $seo_title,
                'seo_keywords' => $seo_keywords,
                'seo_description' => $seo_description,
                'contact' => $contact,
                'mobile' => $mobile,
                'phone' => $phone,
                'fax' => $fax,
                'email' => $email,
                'qq' => $qq,
                'address' => $address
            );
            // 执行添加
            if ($this->model->modCity($id, $data)) {
                $this->log('修改地区' . $id . '成功！');
                $url = $pid?'/pid/'.$pid:'';
                success('修改成功！', url('/admin/City/index'.$url));
            } else {
                location(- 1);
            }
        } else { // 调取修改内容
            $this->assign('mod', true);
            $city = $this->model->findCity($id);
            if (! $city) {
                error('编辑的内容已经不存在！', - 1);
            }
            if( $city->pid==0 ){
                $pid = 0;
                $cur_city = '顶级';
            }else{
                $parent_city = $this->model->findCity($city->pid);
                $pid = $parent_city->pid;
                $cur_city = $parent_city->title;
            }
            $this->assign('pid', $pid);
            $this->assign('cur_city', $cur_city);
            $city_select = $this->model->getList($parent_city->pid);
            $this->assign('city_select', $city_select);
            $this->assign('city', $city);
            $this->display('system/city.html');
        }
    }

    // 地区删除
    public function del(){
        // 执行批量删除
        if ($_POST) {
            if (! ! $list = post('list')) {
                if ($this->model->delCityList($list)) {
                    $this->log('批量删除地区成功！');
                    success('批量删除成功！', - 1);
                } else {
                    $this->log('批量删除地区失败！');
                    error('批量删除失败！', - 1);
                }
            } else {
                alert_back('请选择要删除的内容！');
            }
        }
        if (! $id = get('id', 'int')) {
            error('传递的参数值错误！', - 1);
        }
        if ($this->model->delCity($id)) {
            $this->log('删除地区' . $id . '成功！');
            success('删除成功！', - 1);
        } else {
            $this->log('删除地区' . $id . '失败！');
            error('删除失败！', - 1);
        }
    }


}
