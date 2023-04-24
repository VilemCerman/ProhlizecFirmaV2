<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeCreatePage extends CRUDPage
{
    private ?Employee $employee;
    private ?array $errors = [];
    private int $state;
    private $keys;
    private $rooms;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Zapsat nového zaměstnance";

        $this->employee = new Employee();
        $stmtRoom = PDOProvider::get()->prepare("SELECT room_id, no, name FROM room ORDER BY no ASC");
        $stmtRoom->execute([]);
        while ($room = $stmtRoom->fetch())
        {
            $this->rooms[] = [
                'room_id' => $room->room_id,
                'no' => $room->no,
                'name' => $room->name,
                'selected' => $room->room_id == $this->employee->room
            ];
        }
        //když chce formulář
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
        }

        //když poslal data
        elseif($this->state === self::STATE_DATA_SENT) {
            //načti je
            $this->employee = Employee::readPost();
            $this->keys = filter_input(INPUT_POST, 'keys',FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            //zkontroluj je, jinak formulář
            $this->errors = [];
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {
                //ulož je
                $success = $this->employee->insert();
                if($this->keys !== null){
                    foreach ($this->keys AS $room_id){
                        $key = new Key($room_id, $this->employee->employee_id);
                        $key->insert();
                    }
                }

                //přesměruj
               $this->redirect(self::ACTION_INSERT, $success);
            }
        }
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'employeeForm',
            [
                'employee' => $this->employee,
                'errors' => $this->errors,
                'rooms' => $this->rooms,
                'create' => true
            ]
        );
    }

    private function findState() : void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $this->state = self::STATE_DATA_SENT;
        else
            $this->state = self::STATE_FORM_REQUESTED;
    }

}

$page = new EmployeeCreatePage();
$page->render();

?>
