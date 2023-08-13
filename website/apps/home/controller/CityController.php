<?php
/**
 * @author 云趣科技
 *  分站列表控制器     
 */
namespace app\home\controller;

use core\basic\Controller;
use core\basic\Url;

class CityController extends Controller
{

    protected $parser;

    protected $htmldir;

    protected $model;

    public function __construct()
    {
        $this->parser = new ParserController();
        $this->htmldir = $this->config('tpl_html_dir') ? $this->config('tpl_html_dir') . '/' : '';
    }

    // 分站列表
    public function index()
    {
        $content = parent::parser($this->htmldir . 'city.html'); // 框架标签解析
        $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
        $content = str_replace('{pboot:pagetitle}', '城市分站-{pboot:sitetitle}-{pboot:sitesubtitle}', $content);
        $content = $this->parser->parserPositionLabel($content, 0, '城市分站', Url::home('city',true)); // CMS当前位置标签解析
        $content = $this->parser->parserSpecialPageSortLabel($content, - 2, '城市分站', Url::home('city')); // 解析分类标签
        $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
        $this->cache($content, true);
    }
}