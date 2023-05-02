<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeesPage extends CRUDPage
{

    public function __construct()
    {
        $this->title = "Výpis zaměstnanců";
    }

    protected function pageBody()
    {
        $html = "";
        //zobrazit alert
        if ($this->alert) {
            $html .= MustacheProvider::get()->render('crudResult', $this->alert);
        }

        //získat data
        $stmt = PDOProvider::get()->prepare("SELECT `employee`.employee_id, `employee`.name, `employee`.surname, `employee`.job, `employee`.wage, `employee`.room, `room`.name AS room_name FROM `employee` INNER JOIN `room` ON `employee`.room = `room`.room_id");
        $stmt->execute();
        $employees = $stmt->fetchAll();
        //$employees = Employee::getAll(['name' => 'ASC']);
        //prezentovat data
        $html .= MustacheProvider::get()->render('employeeList',['employees' => $employees, 'notAdmin' => !$this->user->admin]);

        return $html;
    }

}

$page = new EmployeesPage();
$page->render();

?>
