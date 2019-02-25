<?php
/**
 * User: Mac <1767575253@qq.com>
 * Date: 2019/02/22
 * Ke searcher
 */

namespace macwu;

use QL\Contracts\PluginContract;
use QL\QueryList;

class Ke implements PluginContract
{
    protected $ql;
    protected $keyword;
    protected $pageNumber = 30;
    protected $httpOpt = [];
    const API = 'https://nj.zu.ke.com/zufang/';
    const RULES = [
      'apartment' => ['.content__list--item--brand','text'],
    ];
    const RANGE = '.content__list';

    public function __construct(QueryList $ql, $pageNumber)
    {
        $this->ql = $ql->rules(self::RULES)->range(self::RANGE);
        $this->pageNumber = $pageNumber;
    }

    public static function install(QueryList $queryList, ...$opt)
    {
        $name = $opt[0] ?? 'ke';
        $queryList->bind($name,function ($pageNumber = 30){
            return new Ke($this,$pageNumber);
        });
    }

    public function setHttpOpt(array $httpOpt = [])
    {
        $this->httpOpt = $httpOpt;
        return $this;
    }

    public function search($keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    public function page($page = 1)
    {
        return $this->query($page)->query()->getData();
    }

    public function getCount()
    {
        $count = 0;
        $text =  $this->query(1)->find('.content__title--hl')->text();
        return (int)$text;
    }

    public function getCountPage()
    {
        $count = $this->getCount();
        $countPage = ceil($count / $this->pageNumber);
        return $countPage;
    }

    protected function query($page = 1)
    {
        if(empty($this->keyword))
            $qu = '';
        else{
            if($page == 1)
                $qu = '/rs'.$this->keyword.'/';
            else
                $qu = 'pg'.$page.'rs'.$this->keyword.'/';
        }
        $this->ql->get(self::API.$qu,[],$this->httpOpt);
        return $this->ql;
    }

}