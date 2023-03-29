<?php

class Key
{
    public const DB_TABLE = "key";

    public ?int $key_id;
    public ?int $room_id;
    public ?int $employee_id;
    public ?string $room_name;

    public function __construct(?int $key_id = null, ?int $room_id = null, ?int $employee_id = null)
    {
        $this->room_id = $room_id;
        $this->key_id = $key_id;
        $this->employee_id = $key_id;
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
        $stmt->execute(['roomId'=> $key->room_id]);
        $key->room_name = $stmt->fetch();
        return $key;
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

        $key->room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        if($key->room_id)
            $key->room_id = trim($key->room_id);

        $key->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        if($key->employee_id)
            $key->employee_id = trim($key->employee_id);
        return $key;
    }

    public function insert() : bool
    {
        $query = "INSERT INTO `".self::DB_TABLE."` (`key_id`, `employee`, `room`) VALUES (:keyId, :employee, :room)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute(['keyId'=>$this->key_id, 'employee'=>$this->employee_id, 'room'=>$this->room_id]);
        if (!$result)
            return false;

        $this->room_id = PDOProvider::get()->lastInsertId();
        return true;
    }
}