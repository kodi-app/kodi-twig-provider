<?php
/**
 * Created by PhpStorm.
 * User: nagyatka
 * Date: 2017. 09. 10.
 * Time: 18:20
 */

namespace KodiTwigProvider\ContentProvider;


class PageTitleProvider extends ContentProvider
{
    public function getValue()
    {
        return $this->getConfiguration()["title"];
    }
}