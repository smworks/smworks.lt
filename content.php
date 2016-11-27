<?php

if (!defined('PATH')) exit('Direct access to script is not allowed.');

/**
 * Date: 2016 01 19
 * Desc: loads content and replaces various information.
 */

define('TITLE', 'SMWorks.lt');
require_once 'facebook.php';
require_once CORE . 'image.php';

class Content extends Singleton
{

    public function loadTemplate($filename, $ignoreTemplatePath = false)
    {
        return file_get_contents(($ignoreTemplatePath ? '' : TEMPLATES) . $filename);
    }

    public function display()
    {
        if (isset($_GET['image']) && $_GET['image'] && isset($_GET['imageId']) && isset($_FILES['file'])) {
            echo Image::getInstance()->save($_FILES['file']);
            exit;
        }

        if (isset($_GET['page']) && $_GET['page'] && isset($_GET['pageId'])) {
            $this->handleRest($_GET['pageId'], $_SERVER['REQUEST_METHOD']);
            exit;
        }

        if (isset($_GET['error']) && !empty($_GET['error']) && isset($_GET['message'])) {
            $this->displayErrorPage($_GET['error'], $_GET['message']);
            exit;
        }

        if (isset($_GET['admin']) && $_GET['admin']) {
            User::getInstance()->requireAuthentication(function () {
                $this->displayErrorPage(401, 'Unauthorized access');
            });
            exit;
        }

        if (isset($_GET['editor']) && $_GET['editor'] && isset($_GET['pageId'])) {
            User::getInstance()->requireAuthentication(function () {
                $this->displayErrorPage(401, 'Unauthorized access');
            });
            $this->displayEditorWindow($_GET['pageId']);
            exit;
        }
        $this->displayHome();
    }

    private function handleRest($pageId, $method)
    {
        switch ($method) {
            case 'GET':
                $content = DB::getInstance()->getFirstColumn(
                    'SELECT content FROM pages WHERE id=' . $_GET['pageId']);
                echo $this->replaceTags(Utils::decodeHTML($content));
                break;
            case 'PUT':
                parse_str(file_get_contents('php://input'), $_PUT);
                $_PUT = Utils::encodeHtml($_PUT);
                if (DB::getInstance()->update('pages SET content=?, summary=?, title=?, thumbnail=?, category=?, visible=? WHERE id=?',
                    $_PUT['content'], $_PUT['summary'], $_PUT['title'], $_PUT['thumbnail'], $_PUT['category'], $_PUT['visible'], $_GET['pageId'])
                ) {
                    echo 'Updated';
                } else {
                    echo 'No rows were updated';
                    http_response_code(500);
                }
                break;
            case 'POST':
                if (DB::getInstance()->insert('INSERT INTO pages (content, summary, title, thumbnail, category, visible) VALUES (?, ?, ?, ?, ?, ?)',
                    $_POST['content'], $_POST['summary'], $_POST['title'], $_POST['thumbnail'], $_POST['category'], $_POST['visible'])
                ) {
                    echo DB::getInstance()->getLastInsertId();
                } else {
                    echo 'No rows were inserted';
                    http_response_code(500);
                }
                break;
            case 'DELETE':
                if (DB::getInstance()->delete('DELETE FROM pages WHERE id=?', $pageId)) {
                    echo 'Deleted';
                } else {
                    echo 'Failed to delete';
                    http_response_code(500);
                }
                break;
            default:
                http_response_code(400);
                break;
        }
    }

    private function displayHome()
    {
        $page = $this->loadTemplate('index.html');
        $fbAddress = 'http://' . $_SERVER['HTTP_HOST'];
        $fbType = 'website';

        if (isset($_GET['pageId']) && ctype_digit($_GET['pageId'])) {
            $fbAddress = 'http://' . $_SERVER['HTTP_HOST'] . '/pages/' . $_GET['pageId'];
            $fbType = 'article';
            Facebook::getInstance()->setImage(Utils::decodeHTML(DB::getInstance()->getFirstColumn(
                'SELECT thumbnail FROM pages WHERE id=' . $_GET['pageId'])));
        }
        Facebook::getInstance()->setUrl($fbAddress)->setType($fbType);
        echo str_replace('{CATEGORIES}', $this->getCategoriesSelectHtml(), $this->replaceTags($page));
    }

    private function displayErrorPage($httpCode, $error)
    {
        $page = $this->loadTemplate('error_page.html');
        $page = $this->replaceTags($page);
        echo str_replace(array('{CODE}', '{ERROR}'),
            array($httpCode, $error), $page);
        http_response_code($httpCode);
    }

    private function replaceTags($page)
    {
        $search = array(
            '{TITLE}'
        );
        $replace = array(
            TITLE
        );

        $page = Facebook::getInstance()->getMetaTagHtml($page);
        $page = str_replace($search, $replace, $page);
        $page = preg_replace_callback('/{TEMPLATE:(.*?)}/', function ($match) {
            if ($match[1] == 'pages') {
                $content = $this->pages();
            } else if ($match[1] == 'hello_world') {
                $files = glob('assets/templates/hello_world_*');
                $index = rand(0, count($files) - 1);
                $content = htmlspecialchars($this->loadTemplate($files[$index], true));
            } else {
                $content = $this->loadTemplate($match[1] . '.html');
            }
            return $content;
        }, $page);
        return str_replace('{GEN_TIME}', round(microtime(true) - START_TIME, 3), $page);
    }

    private function pages()
    {
        $pageColumn = $this->loadTemplate('article_thumbnail.html');
        $arr = DB::getInstance()->selectObjectArray(
            'SELECT id, thumbnail, summary, title, category, UNIX_TIMESTAMP(created) AS created FROM pages WHERE visible = 1 ORDER BY id DESC');

        $content = '';
        foreach ($arr as $obj) {
            $content .= $this->getColumn($obj, $pageColumn);
        }
        return $this->replaceTags($content) . (User::getInstance()->isAuthenticated()
            ? $this->getButton('index.php?editor=true&pageId=new', 'NEW') : '');
    }

    private function getColumn($obj, $pageColumn)
    {
        $thumbnail = strlen($obj->thumbnail) > 0
            ? Image::getInstance()->load(Utils::decodeHTML($obj->thumbnail), 200, 200)
            : 'http://placehold.it/200x200';
        return str_replace(
            array('{ID}', '{THUMBNAIL}', '{SUMMARY}', '{TITLE}', '{CATEGORY}', '{EDIT}', '{DATE}'),
            array($obj->id, $thumbnail, $obj->summary, $obj->title, $obj->category,
                User::getInstance()->isAuthenticated(), $obj->created),
            $pageColumn);
    }

    private function getButton($address, $text)
    {
        return str_replace(
            array('{ADDRESS}', '{TEXT}'),
            array($address, $text),
            $this->loadTemplate('button.html'));
    }

    private function displayEditorWindow($pageId)
    {
        if ($pageId == 'new') {
            $page = $this->loadTemplate('editor.html');
            $page = $this->replaceTags($page);
            echo str_replace(
                array('{CONTENT}', '{ID}', '{SUMMARY}', '{THUMBNAIL}', '{CATEGORIES}', '{VISIBLE}', '{ARTICLE_TITLE}'),
                array('', -1, '', '', $this->getCategoriesSelectHtml(), '', ''),
                $page);
        } else if ($obj = DB::getInstance()->getObject(
            'SELECT content, id, summary, title, thumbnail, category, visible FROM pages WHERE id=?', $pageId)
        ) {
            $page = $this->loadTemplate('editor.html');
            $page = $this->replaceTags($page);

            $content = $this->replaceTags(Utils::decodeHTML($obj->content));

            echo str_replace(
                array('{CONTENT}', '{ID}', '{SUMMARY}', '{THUMBNAIL}', '{CATEGORIES}', '{VISIBLE}', '{ARTICLE_TITLE}'),
                array($content, $obj->id, $obj->summary, $obj->thumbnail,
                    $this->getCategoriesSelectHtml($obj->category), $obj->visible ? ' checked' : '', $obj->title),
                $page);
        } else {
            $this->displayErrorPage(404, 'File not found');
        }
    }

    /**
     * @param int $categoryId - Specified selected category id, not required.
     * @return string Genereated html for category selector
     */
    private function getCategoriesSelectHtml($categoryId = -1)
    {
        $categories = DB::getInstance()->selectObjectArray('SELECT id, name FROM page_categories');
        $selectCategories = '';
        foreach ($categories as $cat) {
            $selectCategories .= '<option value="' . $cat->id . '"' . ($cat->id == $categoryId ? 'selected' : '') . '>' . $cat->name . '</option>';
        }
        return $selectCategories;
    }

}