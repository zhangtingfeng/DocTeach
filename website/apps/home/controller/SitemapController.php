<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2018年7月15日
 *  生成sitemap文件
 */
namespace app\home\controller;

use core\basic\Controller;
use app\home\model\SitemapModel;
use core\basic\Url;

class SitemapController extends Controller{

    protected $model;

    public function __construct(){
        $this->model = new SitemapModel();
        cookie('city',''); //防止生成的链接叠加当前城市
    }

    public function index()
    {
        header("Content-type:text/xml;charset=utf-8");
        $str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        //$str .= '<urlset>' . "\n";
        $str .= '<urlset xmlns= "http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n" ;
        $str .= $this->makeNode('', date('Y-m-d'), '1.00', 'always'); // 根目录
        
        $sorts = $this->model->getSorts();
        $Parser = new ParserController();

        $wildcard = $this->config('wildcard');  //泛域名支持状态
        $city_suffix = $this->config('city_suffix');  //城市后缀
        $city_suffix = $city_suffix ? '.html' : '/';
        $model = model('admin.system.City');
        $city = $model->getAllList();
        $domain = get_http_host();  //当前域名
        $cur_city = array_filter($city, function($t) use ($domain) { return $t['isurl'] == $domain; });    //当前城市绑定了域名
        if( $wildcard || !empty($cur_city) ){
            foreach ($sorts as $value) {
                if ($value->outlink) {
                    continue;
                } elseif ($value->type == 1) {
                    $link = $Parser->parserLink(1, $value->urlname, 'about', $value->scode, $value->filename);
                    $str .= $this->makeNode($link, date('Y-m-d'), '0.80', 'daily');
                } else {
                    $link = $Parser->parserLink(2, $value->urlname, 'list', $value->scode, $value->filename);
                    $str .= $this->makeNode($link, date('Y-m-d'), '0.80', 'daily');
                    $contents = $this->model->getSortContent($value->scode);
                    foreach ($contents as $value2) {
                        if ($value2->outlink) { // 外链
                            continue;
                        } else {
                            $link = $Parser->parserLink(2, $value2->urlname, 'content', $value2->scode, $value2->sortfilename, $value2->id, $value2->filename);
                        }
                        $str .= $this->makeNode($link, date('Y-m-d', strtotime($value2->date)), '0.60', 'daily');
                    }
                }
            }
        }else{
            cookie('city',''); //防止生成的链接叠加当前城市
            foreach ($city as $c) {
                $str .= $this->makeNode( '/'.$c['etitle'] . $city_suffix, date('Y-m-d'), '1.00', 'always'); // 根目录
            }
            if( $this->config('open_citymap') ){
                array_unshift($city,['etitle'=>'','isurl'=>'']); // 添加主站
            }else{
                $city = ['etitle'=>'','isurl'=>''];
            }
            foreach( $city as $item ){
                if( $item['isurl'] !=='' ){
                    continue;
                }
                $url = $item['etitle'] ? '/'.$item['etitle'] : '';
                //首页，上面生成过了，这里不再生成
                // if( $url!=='' ){
                //     $str .= $this->makeNode( $url . $city_suffix, date('Y-m-d'), '1.00', 'always'); // 根目录
                // }
                foreach ($sorts as $value) {
                    if ($value->outlink) {
                        continue;
                    } elseif ($value->type == 1) {
                        $link = $Parser->parserLink(1, $value->urlname, 'about', $value->scode, $value->filename);
                        $str .= $this->makeNode( $url . $link, date('Y-m-d'), '0.80', 'daily');
                    } else {
                        $link = $Parser->parserLink(2, $value->urlname, 'list', $value->scode, $value->filename);
                        $str .= $this->makeNode( $url . $link, date('Y-m-d'), '0.80', 'daily');
                        $contents = $this->model->getSortContent($value->scode);
                        foreach ($contents as $value2) {
                            if ($value2->outlink) { // 外链
                                continue;
                            } else {
                                $link = $Parser->parserLink(2, $value2->urlname, 'content', $value2->scode, $value2->sortfilename, $value2->id, $value2->filename);
                            }
                            $str .= $this->makeNode( $url . $link, date('Y-m-d', strtotime($value2->date)), '0.60', 'daily');
                        }
                    }
                }
            }
        }
        echo $str . "\n</urlset>";
    }

    // 生成结点信息
    private function makeNode($link, $date, $priority = 0.60, $changefreq = 'always')
    {
        $node = '
<url>
    <loc>' . get_http_url() . $link . '</loc>
    <priority>' . $priority . '</priority>
    <lastmod>' . $date . '</lastmod>
    <changefreq>' . $changefreq . '</changefreq>
</url>';
        return $node;
    }

    // 文本格式
    public function linkTxt()
    {
        header("Content-type:text/plain;charset=utf-8");
        $sorts = $this->model->getSorts();
        $Parser = new ParserController();
        $str = get_http_url() . "\n";

        $wildcard = $this->config('wildcard');  //泛域名支持状态
        $city_suffix = $this->config('city_suffix');  //城市后缀
        $city_suffix = $city_suffix ? '.html' : '/';
        $model = model('admin.system.City');
        $city = $model->getAllList();
        $domain = get_http_host();  //当前域名
        $cur_city = array_filter($city, function($t) use ($domain) { return $t['isurl'] == $domain; });    //当前城市绑定了域名
        if( $wildcard || !empty($cur_city) ){
            foreach ($sorts as $value) {
                if ($value->outlink) {
                    continue;
                } elseif ($value->type == 1) {
                    $link = $Parser->parserLink(1, $value->urlname, 'about', $value->scode, $value->filename);
                    $str .= get_http_url() . $link . "\n";
                } else {
                    $link = $Parser->parserLink(2, $value->urlname, 'list', $value->scode, $value->filename);
                    $str .= get_http_url() . $link . "\n";
                    $contents = $this->model->getSortContent($value->scode);
                    foreach ($contents as $value2) {
                        if ($value2->outlink) { // 外链
                            continue;
                        } else {
                            $link = $Parser->parserLink(2, $value2->urlname, 'content', $value2->scode, $value2->sortfilename, $value2->id, $value2->filename);
                        }
                        $str .= get_http_url() . $link . "\n";
                    }
                }
            }
        }else{
            cookie('city',''); //防止生成的链接叠加当前城市
            foreach ($city as $c) {
                $str .= get_http_url() .'/'. $c['etitle'] . $city_suffix . "\n"; // 根目录
            }
            if( $this->config('open_citymap') ){
                array_unshift($city,['etitle'=>'','isurl'=>'']); // 添加主站
            }else{
                $city = ['etitle'=>'','isurl'=>''];
            }
            foreach( $city as $item ){
                if( $item['isurl'] !=='' ){
                    continue;
                }
                $url = $item['etitle'] ? '/'.$item['etitle'] : '';
                //$str .= get_http_url() . $url . $city_suffix . "\n";
                foreach ($sorts as $value) {
                    if ($value->outlink) {
                        continue;
                    } elseif ($value->type == 1) {
                        $link = $Parser->parserLink(1, $value->urlname, 'about', $value->scode, $value->filename);
                        $str .= get_http_url() . $url . $link . "\n";
                    } else {
                        $link = $Parser->parserLink(2, $value->urlname, 'list', $value->scode, $value->filename);
                        $str .= get_http_url() . $url . $link . "\n";
                        $contents = $this->model->getSortContent($value->scode);
                        foreach ($contents as $value2) {
                            if ($value2->outlink) { // 外链
                                continue;
                            } else {
                                $link = $Parser->parserLink(2, $value2->urlname, 'content', $value2->scode, $value2->sortfilename, $value2->id, $value2->filename);
                            }
                            $str .= get_http_url() . $url . $link . "\n";
                        }
                    }
                }
            }
        }
        echo $str;
    }
}