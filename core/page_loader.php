<?php

/**
 * Date: 2016 01 19
 * Desc: loads content and replaces various information.
 */

define('TITLE', 'SMWorks.lt');

class PageLoader extends Singleton
{

    public function loadTemplate($filename)
    {
        return file_get_contents(TEMPLATES . $filename);
    }

    public function display()
    {
        switch ($_GET['id']) {
            case 1:
                $this->displayHome();
                break;
            case 2:
                $this->displayTutorial($_GET['page']);
                break;
            default:
                $this->displayPageNotFound();
        }
    }

    private function displayHome()
    {
        $page = $this->loadTemplate('index.html');
        $page = $this->replaceTags($page);
        echo $page;
    }

    private function displayTutorial($tutorialId)
    {
        echo 'SELECT content FROM tutorials WHERE id=' . $tutorialId;
        $page = WebDB::getInstance()->getFirstColumn('SELECT content FROM tutorials WHERE id=' . $tutorialId);

        $page = $this->replaceTags($page);
        echo $page;
    }

    private function displayPageNotFound()
    {
        $page = $this->loadTemplate('page_not_found.html');
        $page = $this->replaceTags($page);
        echo $page;
    }

    private function replaceTags($page)
    {
        $search = array(
            '{TITLE}',
            '{EMAIL}',
            '{ASSETS}'
        );
        $replace = array(
            TITLE,
            User::getInstance()->getEmail(),
            'assets'
        );

        $page = str_replace($search, $replace, $page);
        $page = preg_replace_callback('/{TEMPLATE:(.*?)}/', function ($match) {
            if ($match[1] == 'tutorials') {
                $content = $this->tutorials();
            } else {
                $content = file_get_contents(TEMPLATES . $match[1] . '.html');
            }
            return $content;
        }, $page);

        return str_replace('{LOAD_TIME}', round(microtime(true) - START_TIME, 3), $page);
    }

    private function tutorials()
    {
        $tutorialColumn = $this->loadTemplate('page_column.html');
        $tutorialRow = $this->loadTemplate('page_row.html');
        $arr = DB::getInstance()->selectObjectArray('SELECT id, thumbnail, summary FROM tutorials');

        $rows = '';
        for ($i = 0; $i < count($arr); $i += 3) {
            $columns = $this->getColumn($arr[$i], $tutorialColumn);
            if ($i % 3 == 1) {
                $columns .= $this->getColum($arr[$i + 1], $tutorialColumn);
            } else if ($i % 3 == 2) {
                $columns .= $this->getColumn($arr[$i + 2], $tutorialColumn);
            }
            $rows .= str_replace('{COLUMNS}', $columns, $tutorialRow);
        }
        return $this->replaceTags($rows);
    }

    private function getColumn($obj, $tutorialColumn)
    {
        return str_replace(
            array('{ID}', '{ADDRESS}', '{SUMMARY}'),
            array($obj->id, $obj->thumbnail, $obj->summary),
            $tutorialColumn);
    }

}