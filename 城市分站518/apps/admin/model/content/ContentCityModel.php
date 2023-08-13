<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2017年12月26日
 *  内容栏目模型类
 */
namespace app\admin\model\content;

use core\basic\Model;

class ContentCityModel extends Model
{

    // 存储分类及子编码
    protected $scodes = array();

    // 获取内容栏目列表
    public function getList()
    {
        $field = array(
            'a.*',
            'b.type',
            'b.urlname',
            '(select count(*) from ay_content c where c.scode=a.scode) wzcount'
        );
        $join = array(
            'ay_model b',
            'a.mcode=b.mcode',
            'LEFT'
        );
        $result = parent::table('ay_content_city a')->field($field)
            ->where("a.acode='" . session('acode') . "'")
            ->join($join)
            ->order('a.pcode,a.sorting,a.id')
            ->select();
        $tree = get_tree($result, 0, 'scode', 'pcode');
        return $tree;
    }

    // 获取内容栏目选择列表
    public function getSelect()
    {
        $result = parent::table('ay_content_city')->field('pcode,scode,name')
            ->where("acode='" . session('acode') . "'")
            ->order('pcode,sorting,id')
            ->select();
        $tree = get_tree($result, 0, 'scode', 'pcode');
        return $tree;
    }

    // 获取单页内容栏目选择列表
    public function getSingleSelect()
    {
        $field = array(
            'a.pcode',
            'a.scode',
            'a.name',
            'a.outlink'
        );
        $join = array(
            'ay_model b',
            'a.mcode=b.mcode',
            'LEFT'
        );
        $result = parent::table('ay_content_city a')->field($field)
            ->where('b.type=1')
            ->where("a.outlink=''")
            ->where("a.acode='" . session('acode') . "'")
            ->notIn('a.scode', 'select scode from ay_content')
            ->join($join)
            ->order('a.pcode,a.sorting,a.id')
            ->select();
        return $result;
    }

    // 获取列表内容栏目选择列表
    public function getListSelect($mcode)
    {
        $field = array(
            'a.pcode',
            'a.scode',
            'a.name',
            'a.outlink'
        );
        $join = array(
            'ay_model b',
            'a.mcode=b.mcode',
            'LEFT'
        );
        $result = parent::table('ay_content_city a')->field($field)
            ->where('b.type=2')
            ->where("a.outlink=''")
            ->where("a.mcode='$mcode'")
            ->where("a.acode='" . session('acode') . "'")
            ->join($join)
            ->order('a.pcode,a.sorting,a.id')
            ->select();
        $tree = get_tree($result, 0, 'scode', 'pcode');
        // 对于父栏目非列表的栏目进行追加到后面
        foreach ($result as $value) {
            if ($value->pcode != 0 && result_value_search($value->pcode, $result, 'scode') === false) {
                $value->son = get_tree($result, $value->scode, 'scode', 'pcode');
                $tree[] = $value;
            }
        }
        return $tree;
    }

    // 检查内容栏目
    public function checkCity($where)
    {
        return parent::table('ay_content_city')->field('id')
            ->where($where)
            ->find();
    }

    // 获取内容栏目详情
    public function getCity($scode)
    {
        $field = array(
            'a.*',
            'b.type'
        );
        $join = array(
            'ay_model b',
            'a.mcode=b.mcode',
            'LEFT'
        );
        return parent::table('ay_content_city a')->field($field)
            ->where("a.scode='$scode'")
            ->where("a.acode='" . session('acode') . "'")
            ->join($join)
            ->find();
    }

    // 获取最后一个code
    public function getLastCode()
    {
        return parent::table('ay_content_city')->order('id DESC')->value('scode');
    }

    // 添加内容栏目
    public function addCity(array $data)
    {
        return parent::table('ay_content_city')->autoTime()->insert($data);
    }

    // 删除内容栏目及内容
    public function delCity($scode)
    {
        $this->scodes = array(); // 先清空
        $scodes = $this->getSubScodes($scode); // 获取全部子类
        
        return parent::table('ay_content_city')->in('scode', $scodes)
            ->where("acode='" . session('acode') . "'")
            ->delete();
    }

    // 批量删除栏目及内容
    public function delCityList($scodes)
    {
        $this->scodes = array(); // 先清空
        foreach ($scodes as $value) {
            $allscode = $this->getSubScodes($value); // 获取全部子类
        }
        return parent::table('ay_content_city')->in('scode', $allscode)
            ->where("acode='" . session('acode') . "'")
            ->delete();
    }

    // 修改内容栏目资料
    public function modCity($scode, $data, $modsub = false)
    {
 
        $result = parent::table('ay_content_city')->autoTime()
            ->where("scode='$scode'")
            ->where("acode='" . session('acode') . "'")
            ->update($data);
        return $result;
    }

    // 修改内容栏目排序
    public function modCitySorting($id, $data)
    {
        $result = parent::table('ay_content_city')->autoTime()
            ->where("id='$id'")
            ->where("acode='" . session('acode') . "'")
            ->update($data);
        return $result;
    }



    // 分类子类集
    private function getSubScodes($scode)
    {
        if (! $scode) {
            return;
        }
        $this->scodes[] = $scode;
        $subs = parent::table('ay_content_city')->where("pcode='$scode'")->column('scode');
        if ($subs) {
            foreach ($subs as $value) {
                $this->getSubScodes($value);
            }
        }
        return $this->scodes;
    }

    // 检查自定义URL名称
    public function checkFilename($filename, $where = array())
    {
        return parent::table('ay_content_city')->field('id')
            ->where("filename='$filename'")
            ->where($where)
            ->find();
    }

    // 检查URL名字冲突
    public function checkUrlname($filename)
    {
        return parent::table('ay_model')->field('id')
            ->where("urlname='$filename'")
            ->find();
    }

    // 获取当前主题
    public function getTheme()
    {
        return parent::table('ay_site')->where("acode='" . session('acode') . "'")->value('theme');
    }
  
    // 修改应用配置值
    public function modValue($name, $value)
    {
        return parent::table('ay_config')->where("name='$name'")->update("value='$value'");
    }

  
    public function addCitySql()
    {
        $menu_data=[
      "mcode"=>'M161',
      "pcode"=>'M157', 
      "name"=>'城市分站', 
      "url"=>'/admin/ContentCity/index', 
      "sorting"=>255, 
      "status"=>'1', 
      "shortcut"=>'0', 
      "ico"=>'fa-bars', 
       "create_user"=>'admin', 
       "update_user"=>'admin', 
        ];

 

        return parent::table('ay_menu')->autoTime()->insert($menu_data);
    }

    public function addConfig(array $data)
    {
        return parent::table('ay_config')->insert($data);
    }


}