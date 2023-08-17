<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2020年06月26日
 *  会员前台模型
 */
namespace app\home\model;

use core\basic\Model;
use core\basic\Config;

class StudentModel extends Model
{



    // 会员注册
    public function register($data)
    {
        return parent::table('ay_diy_parent_askmessage')->insert($data);
    }


}