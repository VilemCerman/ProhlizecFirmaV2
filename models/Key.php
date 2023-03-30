<?php

class Key
{
    public const DB_TABLE = "key";

    public ?int $key_id;
    public ?int $employee;
    public ?int $room;
    public ?int $room_name;

    public function __construct(?int $room = null, ?int $employee = null)
    {
        $this->room = $room;
        $this->employee = $employee;
    }

    public static function findByID(int $id) : ?self
    {
        $stmt = PDOProvider::get()->prepare("SELECT * FROM `" . self::DB_TABLE . "` WHERE `key_id`= :keyId");
        $stmt->execute(['keyId' => $id]);

        if ($stmt->rowCount() < 1)
            return null;

        $key = new self();
        $key->hydrate($stmt->fetch());
        $stmt = PDOProvider::get()->prepare("SELECT `name` FROM " . Room::DB_TABLE . " WHERE `room_id` = :roomId");
        $stmt->execute(['roomId'=> $key->room]);
        $key->room_name = $stmt->fetch();
        return $key;
    }

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

        $keys = [];
        while ($keyData = $stmt->fetch())
        {
            $key = new Key();
            $key->hydrate($keyData);
            $keys[] = $key;
        }

        return $keys;
    }

    private function hydrate(array|object $data)
    {
        $fields = ['key_id', 'room_id', 'employee_id'];
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

    public static function deleteByID(int $Id) : bool
    {
        $query = "DELETE FROM `".self::DB_TABLE."` WHERE `key_id` = :keyId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute(['keyId'=>$Id]);
    }
    public static function deleteByEmployeeAndRoomId(int $employee, int $room) : bool
    {
        $query = "DELETE FROM `".self::DB_TABLE."` WHERE `employee` = :employee AND `room` = :room";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute(['employee'=>$employee, 'room' => $room]);
    }

    public function validate(&$errors = []) : bool
    {
        if (!isset($this->room_id) || (!$this->room_id))
            $errors['room_id'] = 'Neplatná místnost';

        return count($errors) === 0;
    }

    public static function readPost() : self
    {
        $key = new self();
        $key->key_id = filter_input(INPUT_POST, 'key_id', FILTER_VALIDATE_INT);
        if ($key->key_id)
            $key->key_id = trim($key->key_id);

        $key->room = filter_input(INPUT_POST, 'room', FILTER_VALIDATE_INT);
        if($key->room)
            $key->room = trim($key->room);

        $key->employee = filter_input(INPUT_POST, 'employee', FILTER_VALIDATE_INT);
        if($key->employee)
            $key->employee = trim($key->employee);
        return $key;
    }

    public static function readKeysPost() : self
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."` WHERE ");
        $stmt->execute([]);
        $key = new self();
        $key->key_id = filter_input(INPUT_POST, 'key_id', FILTER_VALIDATE_INT);
        if ($key->key_id)
            $key->key_id = trim($key->key_id);

        $key->room = filter_input(INPUT_POST, 'room', FILTER_VALIDATE_INT);
        if($key->room)
            $key->room = trim($key->room);

        $key->employee = filter_input(INPUT_POST, 'employee', FILTER_VALIDATE_INT);
        if($key->employee)
            $key->employee = trim($key->employee);
        return $key;
    }

    public function insert() : bool
    {
        $stmt = PDOProvider::get()->prepare("SELECT * FROM `".self::DB_TABLE."` WHERE `employee` = :employee AND `room` = :room");
        $result = $stmt->fetch();
        if($result){
            return false;
        }
        $query = "INSERT INTO `".self::DB_TABLE."` (`employee`, `room`) VALUES (:employee, :room)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute(['employee'=>$this->employee, 'room'=>$this->room]);
        if (!$result)
            return false;

        $this->room = PDOProvider::get()->lastInsertId();
        return true;
    }
}