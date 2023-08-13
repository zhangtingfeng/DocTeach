<?php
/**
 *  分站模型类
 */
namespace app\admin\model\system;

use core\basic\Model;

class CityModel extends Model{
    // 获取分站列表  -- 前端缓存城市用
    public function getAllList(){
        return parent::table('ay_city')->where('status=1')->order('sorting asc')->select(1);
    }

    // 获取分站列表
    public function getList($pid=0){
        $field = array(
            'a.*',
            "(select count(b.id) from ay_city b where b.pid=a.id) as count"
        );
        $where['pid']=$pid;
        $result = parent::table('ay_city a')->field($field)->where($where)->order('a.sorting asc')->select();
        return $result;
    }

    // 检查分站
    public function checkName($where){
        return parent::table('ay_city')->field('title,etitle')
            ->where($where)
            ->find();
    }

    // 添加分站
    public function addCity($data){
        return parent::table('ay_city')->insert($data);
    }

    // 查询分站
    public function findCity($id){
        return parent::table('ay_city')->where("id={$id}")->find();
    }

    // 删除分站
    public function delCity($id){
        return parent::table('ay_city')->where("id='$id' OR pid='$id'")->delete();
    }

    // 批量删除分站
    public function delCityList($ids){
        foreach($ids as $id){
            $this->delCity($id);
        }
        return true;
        //return parent::table('ay_city')->delete($ids);
    }

    // 修改分站资料
    public function modCity($id, $data){
        return parent::table('ay_city')->where("id='$id'")->update($data);
    }

}
