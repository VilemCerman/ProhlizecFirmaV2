<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeUpdatePage extends CRUDPage
{
    private ?Employee $employee;
    private ?array $errors = [];
    private int $state;
    private $keys;
    private $allKeys;
    private $rooms;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Upravit zaměstnance";

        //když chce formulář
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
            if (!$employeeId)
                throw new BadRequestException();

            //jdi dál
            $this->employee = Employee::findByID($employeeId);
            if (!$this->employee)
                throw new NotFoundException();

            $stmt = PDOProvider::get()->prepare("SELECT `key`.key_id AS key_id, `key`.room AS room, `room`.name AS room_name FROM `key` INNER JOIN `room` ON `key`.employee = :employeeId AND `key`.room = `room`.room_id ORDER BY room ASC");
            $stmt->execute(['employeeId' => $employeeId]);
            //$this->keys = $stmt->fetchAll();

            $stmtRoom = PDOProvider::get()->prepare("SELECT room_id, no, name FROM room ORDER BY room_id ASC");
            $stmtRoom->execute([]);

            $keysAvailable = $key = $stmt->fetch();
            while ($room = $stmtRoom->fetch())
            {
                $active = false;
                if($keysAvailable && $key->room == $room->room_id){
                    $active = true;
                    $keysAvailable = $key = $stmt->fetch();
                }
                $this->rooms[] = [
                    'room_id' => $room->room_id,
                    'no' => $room->no,
                    'name' => $room->name,
                    'isActive' => $active
                ];
            }
        }

        //když poslal data
        elseif($this->state === self::STATE_DATA_SENT) {
            //načti je
            $this->employee = Employee::readPost();
            $this->keys = filter_input(INPUT_POST, 'keys',FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            //dump($this->keys);

            //zkontroluj je, jinak formulář
            $this->errors = [];
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {
                //dump($_POST);
                $employeeId = $this->employee->employee_id;
                //ulož je

                $stmtUserKeys = PDOProvider::get()->prepare("SELECT room, key_id FROM `key` WHERE `employee` = :employeeId ORDER BY room ASC");
                $stmtUserKeys->execute(['employeeId' => $this->employee->employee_id]);
                //$userKeys = $stmtUserKeys->fetchAll();
                while($key = $stmtUserKeys ->fetch()){
                    $userKeys[$key->room] = $key->key_id;
                }

                $stmt = PDOProvider::get()->prepare("SELECT * FROM `key` INNER JOIN `room` ON `key`.employee = :employeeId AND `key`.room = `room`.room_id ORDER BY room ASC");
                //$stmt->execute(['employeeId' => $employeeId]);

                $allRooms = Room::getAll();

                foreach ($allRooms AS $room){
                    if(isset($userKeys[$room->room_id]) && !isset($this->keys[$room->room_id])){
                        Key::deleteByID($userKeys[$room->room_id]);
                    }
                    else if(!isset($userKeys[$room->room_id]) && isset($this->keys[$room->room_id])){
                        $key = new Key(null, $room->room_id, $this->employee->employee_id);
                        $key->insert();
                    }
                }

                $success = $this->employee->update();

                //přesměruj
                $this->redirect(self::ACTION_UPDATE, $success);
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
                'header' => $this->title.' '.$this->employee->name.' '.$this->employee->surname
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

$page = new EmployeeUpdatePage();
$page->render();

?>
