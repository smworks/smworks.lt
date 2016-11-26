<?php

/**
 * Created by PhpStorm.
 * User: Martynas
 * Date: 2016-10-30
 * Time: 16:51 PM
 */
define('FB_URL', '{FB_URL}');
define('FB_TITLE', '{FB_TITLE}');
define('FB_DESCRIPTION', '{FB_DESCRIPTION}');
define('FB_IMAGE', '{FB_IMAGE}');
define('FB_APP_ID', '{FB_APP_ID}');
define('FB_TYPE', '{FB_TYPE}');

class Facebook extends Singleton
{

    private $tags = array(
        FB_URL => 'http://smworks.lt',
        FB_TITLE => 'SMWorks.lt',
        FB_DESCRIPTION => 'Personal website',
        FB_IMAGE => 'http://smworks.lt/assets/img/smworks.png',
        FB_APP_ID => '227461151000174',
        FB_TYPE => 'website'
    );

    public function setUrl($url)
    {
        $this->tags[FB_URL] = $url;
        return $this;
    }

    public function setTitle($title)
    {
        $this->tags[FB_TITLE] = $title;
        return $this;
    }

    public function setDescription($description)
    {
        $this->tags[FB_DESCRIPTION] = $description;
        return $this;
    }

    public function setImage($image)
    {
        $this->tags[FB_IMAGE] = $image;
        return $this;
    }

    public function setType($type)
    {
        $this->tags[FB_TYPE] = $type;
        return $this;
    }

    /**
     * This method takes html content and replaces all facebook specific tags with appropriate values.
     * @param $content - html content
     * @return mixed Html content with replaced facebook tags
     */
    public function getMetaTagHtml($content)
    {
        return str_replace(array_keys($this->tags), array_values($this->tags), $content);
    }
}