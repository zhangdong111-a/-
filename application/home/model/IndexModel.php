<?php

namespace app\home\model;
use think\Model;
use think\Db;

class IndexModel extends Model
{

    /**
     * [getAllArticle 获取文章总数]
     
     */
	public function getByCount($map)
	{
		return Db::name('article')->where($map)->count();
	}


	
    /**
     * [getAllArticle 获取全部文章]
     
     */
    public function getArticleByWhere($map, $nowpage, $limits)
    {
        return Db::name('article')->field('think_article.*,name')
            ->join('think_article_cate', 'think_article.cate_id = think_article_cate.id')
            ->where($map)
            ->page($nowpage, $limits)
            ->order('id desc')->select();
    }

}