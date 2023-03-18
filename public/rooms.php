<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class RoomsPage extends BasePage
{
    public function __construct()
    {
        $this->title = "Výpis místností";
    }

    protected function pageBody()
    {
        //získat data
        $rooms = Room::getAll(['name' => 'ASC']);

        //prezentovat data
        return MustacheProvider::get()->render('roomList',['rooms' => $rooms]);
    }

}

$page = new RoomsPage();
$page->render();

?>
