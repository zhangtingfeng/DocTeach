<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2020年06月26日
 *  会员前台控制器
 */
namespace app\home\controller;

use core\basic\Controller;
use app\home\model\StudentModel;
use core\basic\Url;

class StudentController extends Controller
{

    protected $parser;

    protected $model;

    protected $htmldir;

    public function __construct()
    {
        $this->model = new StudentModel();
        $this->parser = new ParserController();
        $this->htmldir = $this->config('tpl_html_dir') ? $this->config('tpl_html_dir') . '/' : '';
    }



    // 会员注册页面
    public function Fastregister()
    {
        // 已经登录时跳转到用户中心
        if (session('pboot_uid')) {
            location(Url::home('member/ucenter'));
        }
        
        // 执行注册
        if ($_POST) {
            if ($this->config('register_status') === '0') {
                error('系统已经关闭注册功能，请到后台开启再试！');
            }
            
            if (time() - session('lastreg') < 10) {
                alert_back('您注册太频繁了，请稍后再试！');
            }
            
            // 验证码验证
            $checkcode = strtolower(post('checkcode', 'var'));
            if ($this->config('register_check_code') !== '0') {
                if (! $checkcode) {
                    alert_back('验证码不能为空！');
                }
                
                if ($checkcode != session('checkcode')) {
                    alert_back('验证码错误！');
                }
            }

            $teachType = post('teachType');
            $name = post('name');
            $phoneNumber = post('phoneNumber');
            $WXname = post('WXname');




            if (!$name) {
                alert_back('您的姓名不能为空，请输入注册的邮箱账号！');
            }
            if (! $phoneNumber && !$WXname) {
                alert_back('您的联系电话和微信号不能同时为空！');
            }

           if ($phoneNumber){
               if (! preg_match('/^1[0-9]{10}$/', $phoneNumber)) {
                   alert_back('手机号码格式不正确，请输入正确的手机号码！');
               }
           }

            // 构建数据
            $data = array(
                'teachType' => $teachType,
                'parentName' => $name,
                'Mobile' => $phoneNumber,
                'WXNumber' => $WXname
              );

            $status = 1; // 默认需要审核
            // 执行注册
            if ($this->model->register($data)) {
                session('lastreg', time()); // 记录最后提交时间
                alert_location('预约成功！', Url::home('/student/registerDetail/'), 1);

            } else {
                error('家长快速注册失败！', - 1);
            }
        } else {
            $content = parent::parser($this->htmldir . 'student/fastregister.html'); // 框架标签解析
            $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
            $content = str_replace('{pboot:pagetitle}', $this->config('register_title') ?: '家长快速注册-{pboot:sitetitle}-{pboot:sitesubtitle}', $content);
            $content = $this->parser->parserPositionLabel($content, 0, '家长快速注册', Url::home('student/fastregister')); // CMS当前位置标签解析
            $content = $this->parser->parserSpecialPageSortLabel($content, - 3, '家长快速注册', Url::home('student/fastregister')); // 解析分类标签
            $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
            echo $content;
            exit();
        }
    }

// 会员注册页面
    public function registerDetail()
    {
        // 已经登录时跳转到用户中心
        if (session('pboot_uid')) {
            location(Url::home('member/ucenter'));
        }

        // 执行注册
        if ($_POST) {
            if ($this->config('register_status') === '0') {
                error('系统已经关闭注册功能，请到后台开启再试！');
            }

            if (time() - session('lastreg') < 10) {
                alert_back('您注册太频繁了，请稍后再试！');
            }

            // 验证码验证
            $checkcode = strtolower(post('checkcode', 'var'));
            if ($this->config('register_check_code') !== '0') {
                if (! $checkcode) {
                    alert_back('验证码不能为空！');
                }

                if ($checkcode != session('checkcode')) {
                    alert_back('验证码错误！');
                }
            }

            $teachType = post('teachType');
            $name = post('name');
            $phoneNumber = post('phoneNumber');
            $WXname = post('WXname');




            if (!$name) {
                alert_back('您的姓名不能为空，请输入注册的邮箱账号！');
            }
            if (! $phoneNumber && !$WXname) {
                alert_back('您的联系电话和微信号不能同时为空！');
            }

            if ($phoneNumber){
                if (! preg_match('/^1[0-9]{10}$/', $phoneNumber)) {
                    alert_back('手机号码格式不正确，请输入正确的手机号码！');
                }
            }

            // 构建数据
            $data = array(
                'teachType' => $teachType,
                'parentName' => $name,
                'Mobile' => $phoneNumber,
                'WXNumber' => $WXname
            );

            $status = 1; // 默认需要审核
            // 执行注册
            if ($this->model->register($data)) {
                session('lastreg', time()); // 记录最后提交时间
                alert_location('注册成功！', Url::home('/'), 1);

            } else {
                error('家长快速注册失败！', - 1);
            }
        } else {
            $content = parent::parser($this->htmldir . 'student/registerDetail.html'); // 框架标签解析
            $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
            $content = str_replace('{pboot:pagetitle}', $this->config('register_title') ?: '家长快速注册-{pboot:sitetitle}-{pboot:sitesubtitle}', $content);
            $content = $this->parser->parserPositionLabel($content, 0, '家长注册', Url::home('student/registerDetail')); // CMS当前位置标签解析
            $content = $this->parser->parserSpecialPageSortLabel($content, - 3, '家长注册', Url::home('student/registerDetail')); // 解析分类标签
            $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
            echo $content;
            exit();
        }
    }

    // 家长快速注册用户中心
    public function ucenter()
    {
        // 未登录时跳转到用户登录
        if (! session('pboot_uid')) {
            location(Url::home('student/login'));
        }
        
        $content = parent::parser($this->htmldir . 'student/ucenter.html'); // 框架标签解析
        $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
        $content = str_replace('{pboot:pagetitle}', $this->config('ucenter_title') ?: '个人中心-{pboot:sitetitle}-{pboot:sitesubtitle}', $content);
        $content = $this->parser->parserPositionLabel($content, 0, '个人中心', Url::home('student/ucenter')); // CMS当前位置标签解析
        $content = $this->parser->parserSpecialPageSortLabel($content, - 4, '个人中心', Url::home('student/ucenter')); // 解析分类标签
        $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
        echo $content;
        exit();
    }



    public function _empty()
    {
        _404('您访问的地址不存在，请核对再试！');
    }
}