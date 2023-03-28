<?php
//
////namespace models;
//
//class Key
//{
//    public const DB_TABLE = "employee";
//
//    public ?int $employee_id;
//    public ?string $name;
//    public ?string $surname;
//    public ?string $job;
//    public ?int $wage;
//    public ?int $room;
//    public ?string $login;
//    public ?string $pass;
//    public ?bool $admin;
//
//    /**
//     * @param int|null $employee_id
//     * @param string|null $name
//     * @param string|null $surname;
//     * @param string|null $job;
//     * @param int|null $wage;
//     * @param int|null $room;
//     * @param string|null $login;
//     * @param string|null $pass;
//     * @param bool|null $admin;
//     */
//    public function __construct(?int $employee_id = null, ?string $name = null, ?string $surname = null, ?string $job = null, ?int $wage = null, ?int $room = null, ?string $login = null, ?string $pass = null, ?bool $admin = false)
//    {
//        $this->employee_id = $employee_id;
//        $this->name = $name;
//        $this->surname = $surname;
//        $this->job = $job;
//        $this->wage = $wage;
//        $this->room = $room;
//        $this->login = $login;
//        $this->pass = $pass;
//        $this->admin = $admin;
//    }
//
//    public static function findByID(int $id) : ?self
//    {
//        $pdo = PDOProvider::get();
//        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."` WHERE `employee_id`= :employeeId");
//        $stmt->execute(['employeeId' => $id]);
//
//        if ($stmt->rowCount() < 1)
//            return null;
//
//        $employee = new self();
//        $employee->hydrate($stmt->fetch());
//        return $employee;
//    }
//
//    /**
//     * @return Employee[]
//     */
//
//    public static function getAll($sorting = []) : array
//    {
//        $sortSQL = "";
//        if (count($sorting))
//        {
//            $SQLchunks = [];
//            foreach ($sorting as $field => $direction)
//                $SQLchunks[] = "`{$field}` {$direction}";
//
//            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
//        }
//
//        $pdo = PDOProvider::get();
//        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."`" . $sortSQL);
//        $stmt->execute([]);
//
//        $employees = [];
//        while ($employeeData = $stmt->fetch())
//        {
//            $key = new Employee();
//            $key->hydrate($employeeData);
//            $keys[] = $key;
//        }
//
//        return $employees;
//    }
//
//    private function hydrate(array|object $data)
//    {
//        $fields = ['employee_id', 'name', 'surname', 'job', 'wage', 'room', 'login', 'pass', 'admin'];
//        if (is_array($data))
//        {
//            foreach ($fields as $field)
//            {
//                if (array_key_exists($field, $data))
//                    $this->{$field} = $data[$field];
//            }
//        }
//        else
//        {
//            foreach ($fields as $field)
//            {
//                if (property_exists($data, $field))
//                    $this->{$field} = $data->{$field};
//            }
//        }
//    }
//
//    public function insert() : bool
//    {
//        $query = "INSERT INTO ".self::DB_TABLE." (`name`, `surname`, `job`, `wage`, `room`, `login`, `password`, `admin`) VALUES (:name, :surname, :job, :wage, :room, : login, :pass, :admin)";
//        $stmt = PDOProvider::get()->prepare($query);
//        $result = $stmt->execute(['name'=>$this->name, 'surname'=>$this->surname, 'job'=>$this->job, 'wage'=>$this->wage, 'room'=>$this->room, 'login'=>$this->login, 'password'=>$this->password, 'admin'=>$this->admin]);
//        if (!$result)
//            return false;
//
//        $this->employee_id = PDOProvider::get()->lastInsertId();
//        return true;
//    }
//
//    public function update() : bool
//    {
//        if (!isset($this->employee_id) || !$this->employee_id)
//            throw new Exception("Cannot update model without ID");
//
//        $query = "UPDATE ".self::DB_TABLE." SET `name` = :name, `surname` = :surname, `job` = :job, `wage` = :wage, `room` = :room, `login` = :login, `password` = :password, `admin` = :admin WHERE `employee_id` = :employeeId";
//        $stmt = PDOProvider::get()->prepare($query);
//        return $stmt->execute(['employeeId'=>$this->employee_id, 'name'=>$this->name, 'surname'=>$this->surname, 'job'=>$this->job, 'wage'=>$this->wage, 'room'=>$this->room, 'login'=>$this->login, 'password'=>$this->password, 'admin'=>$this->admin]);
//    }
//
//    public function delete() : bool
//    {
//        return self::deleteByID($this->employee_id);
//    }
//
//    public static function deleteByID(int $employeeId) : bool
//    {
//        $query = "DELETE FROM `".self::DB_TABLE."` WHERE `employee_id` = :employeeId";
//        $stmt = PDOProvider::get()->prepare($query);
//        return $stmt->execute(['employeeId'=>$employeeId]);
//    }
//
//    public function validate(&$errors = []) : bool
//    {
//        if (!isset($this->name) || (!$this->name))
//            $errors['name'] = 'Jméno nesmí být prázdné';
//
//        if (!isset($this->surname) || (!$this->surname))
//            $errors['surname'] = 'Příjmení musí být vyplněno';
//
//        if (!isset($this->job) || (!$this->job))
//            $errors['job'] = 'Pozice musí být vyplněna';
//
//        if (!isset($this->wage) || (!$this->wage))
//            $errors['wage'] = 'Plat musí být vyplněn';
//
//        if (!isset($this->room) || (!$this->room))
//            $errors['room'] = 'Číslo místnosti musí být vyplněno';
//
////        if (!isset($this->login) || (!$this->login))
////            $errors['login'] = 'Číslo musí být vyplněno';
////
////        if (!isset($this->pass) || (!$this->pass))
////            $errors['pass'] = 'Číslo musí být vyplněno';
////
////        if (!isset($this->admin) || (!$this->admin))
////            $errors['admin'] = 'Číslo musí být vyplněno';
//
//        return count($errors) === 0;
//    }
//
//    public static function readPost() : self
//    {
//        $employee = new Employee();
//        $employee->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
//
//        $employee->name = filter_input(INPUT_POST, 'name');
//        if ($employee->name)
//            $employee->name = trim($employee->name);
//
//        $employee->no = filter_input(INPUT_POST, 'no');
//        if ($employee->no)
//            $employee->no = trim($employee->no);
//
//        $employee->phone = filter_input(INPUT_POST, 'phone');
//        if ($employee->phone)
//            $employee->phone = trim($employee->phone);
//        if (!$employee->phone)
//            $employee->null;
//
//        return $employee;
//    }
//}
//