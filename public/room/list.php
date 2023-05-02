<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class RoomsPage extends CRUDPage
{

    public function __construct()
    {
        $this->title = "Výpis místností";
    }

    protected function pageBody()
    {
        $html = "";
        //zobrazit alert
        if ($this->alert) {
            $html .= MustacheProvider::get()->render('crudResult', $this->alert);
        }

        //získat data
        $rooms = Room::getAll(['name' => 'ASC']);
        //prezentovat data
        $html .= MustacheProvider::get()->render('roomList',['rooms' => $rooms, 'notAdmin' => !$this->user->admin]);

        return $html;
    }

}

$page = new RoomsPage();
$page->render();

?>
