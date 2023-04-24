<?php

class Employee
{
    public const DB_TABLE = "employee";

    public ?int $employee_id;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?int $room;
    public ?string $login;
    public ?string $password;
    public ?bool $admin;
    public ?string $hash;

    /**
     * @param int|null $employee_id
     * @param string|null $name
     * @param string|null $surname;
     * @param string|null $job;
     * @param int|null $wage;
     * @param int|null $room;
     * @param string|null $login;
     * @param string|null $password;
     * @param bool|null $admin;
     * @param string|null $hash;
     */
    public function __construct(?int $employee_id = null, ?string $name = null, ?string $surname = null, ?string $job = null, ?int $wage = null, ?int $room = null, ?string $login = null, ?string $password = null, ?bool $admin = false, ?string $hash = null)
    {
        $this->employee_id = $employee_id;
        $this->name = $name;
        $this->surname = $surname;
        $this->job = $job;
        $this->wage = $wage;
        $this->room = $room;
        $this->login = $login;
        $this->password = $password;
        $this->admin = $admin;
        $this->hash = $hash;
    }

    public static function findByID(int $id) : ?self
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."` WHERE `employee_id`= :employeeId");
        $stmt->execute(['employeeId' => $id]);

        if ($stmt->rowCount() < 1)
            return null;

        $employee = new self();
        $employee->hydrate($stmt->fetch());
        return $employee;
    }

    /**
     * @return Employee[]
     */

    public static function getAll($sorting = []) : array
    {
        $sortSQL = "";
        if (count($sorting))
        {
            $SQLchunks = [];
            foreach ($sorting as $field => $direction)
                $SQLchunks[] = "`{$field}` {$direction}";

            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
        }

        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."`" . $sortSQL);
        $stmt->execute([]);

        $employees = [];
        while ($employeeData = $stmt->fetch())
        {
            $employee = new Employee();
            $employee->hydrate($employeeData);
            $employees[] = $employee;
        }

        return $employees;
    }

    private function hydrate(array|object $data)
    {
        $fields = ['employee_id', 'name', 'surname', 'job', 'wage', 'room', 'login', 'password', 'admin', 'hash'];
        if (is_array($data))
        {
            foreach ($fields as $field)
            {
                if (array_key_exists($field, $data))
                    $this->{$field} = $data[$field];
            }
        }
        else
        {
            foreach ($fields as $field)
            {
                if (property_exists($data, $field))
                    $this->{$field} = $data->{$field};
            }
        }
    }

    public function insert() : bool
    {
        $query = "INSERT INTO ".self::DB_TABLE." (`name`, `surname`, `job`, `wage`, `room`, `login`, `hash`, `admin`) VALUES (:name, :surname, :job, :wage, :room, :login, :hash, :admin)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute(['name'=>$this->name, 'surname'=>$this->surname, 'job'=>$this->job, 'wage'=>$this->wage, 'room'=>$this->room, 'login'=>$this->login,'hash'=>$this->hash,'admin'=>$this->admin? 1 : 0]);
        if (!$result)
            return false;

        $this->employee_id = PDOProvider::get()->lastInsertId();
        return true;
    }

    public function update() : bool
    {
        if (!isset($this->employee_id) || !$this->employee_id)
            throw new Exception("Cannot update model without ID");

        $query = "UPDATE ".Employee::DB_TABLE." SET `name` = :name, `surname` = :surname, `job` = :job, `wage` = :wage, `room` = :room, `admin` = :admin WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute([
            'employeeId'=>$this->employee_id,
            'name'=>$this->name,
            'surname'=>$this->surname,
            'job'=>$this->job,
            'wage'=>$this->wage,
            'room'=>$this->room,
            'admin'=>$this->admin]);
    }

    public function updatePass() : bool
    {
        if (!isset($this->employee_id) || !$this->employee_id)
            throw new Exception("Cannot update model without ID");

        $query = "UPDATE ".Employee::DB_TABLE." SET `hash` = :hash WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute([
            'employeeId'=>$this->employee_id,
            'hash'=>$this->hash,]);
    }

    public function delete() : bool
    {
        return self::deleteByID($this->employee_id);
    }

    public static function deleteByID(int $employeeId) : bool
    {
        $stmt = PDOProvider::get()->prepare("SELECT `key_id` FROM `key` WHERE employee = :employeeId");
        $stmt->execute(['employeeId' => $employeeId]);
        while($key = $stmt->fetch()){
            Key::deleteByID($key->key_id);
        }
        $query = "DELETE FROM `".self::DB_TABLE."` WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute(['employeeId'=>$employeeId]);
    }

    public function validate(&$errors = []) : bool
    {
        if (!isset($this->name) || (!$this->name))
            $errors['name'] = 'Jméno nesmí být prázdné';

        if (!isset($this->surname) || (!$this->surname))
            $errors['surname'] = 'Příjmení musí být vyplněno';

        if (!isset($this->job) || (!$this->job))
            $errors['job'] = 'Pozice musí být vyplněna';

        if (!isset($this->wage))
            $errors['wage'] = 'Plat musí být vyplněn';

        if ($this->wage < 0)
            $errors['wage'] = 'Nemůžete mít zápornou mzdu';

        if (!isset($this->room) || (!$this->room))
            $errors['room'] = 'Číslo místnosti musí být vyplněno';

        return count($errors) === 0;
    }

    public static function readPost() : self
    {
        $employee = new Employee();
        $employee->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

        $employee->name = filter_input(INPUT_POST, 'name');
        if ($employee->name)
            $employee->name = trim($employee->name);

        $employee->surname = filter_input(INPUT_POST, 'surname');
        if ($employee->surname)
            $employee->surname = trim($employee->surname);

        $employee->job = filter_input(INPUT_POST, 'job');
        if ($employee->job)
            $employee->job = trim($employee->job);

        $employee->wage = filter_input(INPUT_POST, 'wage');
        if ($employee->wage)
            $employee->wage = trim($employee->wage);

        $employee->room = filter_input(INPUT_POST, 'room');
        if ($employee->room)
            $employee->room = trim($employee->room);

        $employee->login = filter_input(INPUT_POST, 'login');
        if ($employee->login)
            $employee->login = trim($employee->login);

        $employee->admin = filter_input(INPUT_POST, 'admin');
        if ($employee->admin)
            $employee->admin = trim($employee->admin);

        $pass = filter_input(INPUT_POST,'pass');
        if($pass){
            $employee->hash = password_hash($pass, PASSWORD_DEFAULT);
        }

        return $employee;
    }
}

