<?php
namespace app\home\controller;

use core\basic\Controller;
use app\home\model\ContributionModel;
use core\basic\Url;

class CartController extends Controller
{

    protected $parser;

    protected $model;

    protected $htmldir;

    public function __construct()
    {
        // 未登录时跳转到用户登录
        if (! session('pboot_uid')) {
            location(Url::home('member/login'));
        }
        $this->model = new ContributionModel();
        $this->parser = new ParserController();
        $this->htmldir = $this->config('tpl_html_dir') ? $this->config('tpl_html_dir') . '/' : '';
    }

    public function index(){
        $content = parent::parser($this->htmldir . 'member/article_list.html'); // 框架标签解析
        $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
        $content = str_replace('{pboot:pagetitle}', '我的文章列表', $content);
        $content = $this->parser->parserPositionLabel($content, 0, '我的文章列表', Url::home('member/index')); // CMS当前位置标签解析
        $content = $this->parser->parserSpecialPageSortLabel($content, - 4, '我的文章列表', Url::home('member/index')); // 解析分类标签
        $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
        echo $content;
        exit();
    }
    
    //其他功能自行补充，本文主要讲的是如何创建功能模块
}