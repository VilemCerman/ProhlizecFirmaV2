<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDetailPage extends BasePage
{
    private $employee;
    private $room;
    private $keys;

    protected function prepare(): void
    {
        parent::prepare();
        //získat data z GET
        $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        //najít zaměstnance v databázi
        $this->employee = Employee::findByID($employeeId);
        if (!$this->employee)
            throw new NotFoundException();

        //místnost
        $stmt = PDOProvider::get()->prepare("SELECT `room_id`, `name`, `phone` FROM `room` WHERE `room_id`= :roomId");
        $stmt->execute(['roomId' => $this->employee->room]);
        $this->room = $stmt->fetch();

        //klíče
        $stmt = PDOProvider::get()->prepare("SELECT `key`.key_id AS key_id, `key`.room AS room_id, `room`.name AS room_name FROM `key` INNER JOIN `room` ON `key`.employee = :employeeId AND `key`.room = `room`.room_id");
        $stmt->execute(['employeeId' => $employeeId]);
        $this->keys = $stmt->fetchAll();

        $this->title = "{$this->employee->name} {$this->employee->surname}";

    }

    protected function pageBody()
    {
        //prezentovat data
        return MustacheProvider::get()->render(
            'employeeDetail',
            ['employee' => $this->employee, 'theRoom' => $this->room, 'keys' => $this->keys]
        );
    }

}

$page = new EmployeeDetailPage();
$page->render();

?>
