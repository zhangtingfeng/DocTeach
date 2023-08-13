<?php
/**
 * @copyright (C)2020-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2020年3月8日
 *  个人扩展标签可编写到本类中，升级不会覆盖
 */
namespace app\home\controller;

use core\basic\Controller;
use core\basic\Config;

class ExtLabelController
{

    protected $content;

    /* 必备启动函数 */
    public function run($content)
    {
        // 接收数据
        $this->content = $content;
        
        // 执行个人自定义标签函数
        $this->test();

        // 城市分站标签
        $this->citytag();
        
        // 返回数据
        return $this->content;
    }

    // 测试扩展单个标签
    private function test()
    {
        $this->content = str_replace('{pboot:userip}', get_user_ip(), $this->content);
    }

    // 城市分站标签
    private function citytag(){
        $cur_city = cookie('city');
        if( !! $cur_city ){
            $citys = Config::get('citys');
            $city = $citys[$cur_city];
            $this->content = str_replace('{xx}', $city['title'], $this->content);
            $this->content = str_replace('{citylink}', $city['etitle'], $this->content);
        }else{
            $this->content = str_replace('{xx}', '', $this->content);
            $this->content = str_replace('{citylink}', '', $this->content);
        }
    }


}