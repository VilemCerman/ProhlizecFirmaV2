<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class IndexPage extends CRUDPage
{
    public function __construct()
    {
        $this->title = "Prohlížeč databáze firmy";
    }

    protected function pageBody()
    {
        $html = '';
        //zobrazit alert
        if ($this->alert) {
            $html = MustacheProvider::get()->render('crudResult', $this->alert);
        }
        return $html;
    }

}

$page = new IndexPage();
$page->render();

?>