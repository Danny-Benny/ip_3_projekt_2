<?php

class Employee
{
    public const DB_TABLE = "employee";

    public ?int $employee_id;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?string $room;
    public ?string $room_name;
    public ?string $login;
    public ?string $password;
    public ?bool $admin;

    public function __construct(
        ?int $employee_id = null,
        ?string $name = null,
        ?string $surname = null,
        ?string $job = null,
        ?int $wage = null,
        ?string $room = null,
        ?string $login = null,
        ?string $password = null,
        ?bool $admin = null
    ) {
        $this->employee_id = $employee_id;
        $this->name = $name;
        $this->surname = $surname;
        $this->job = $job;
        $this->wage = $wage;
        $this->room = $room;
        $this->login = $login;
        $this->password = $password;
        $this->admin = $admin;
    }

    public static function findByID(int $id): ?self
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `" . self::DB_TABLE . "` WHERE `employee_id`= :employeeId");
        $stmt->execute(['employeeId' => $id]);

        if ($stmt->rowCount() < 1) {
            return null;
        }

        $employee = new self();
        $employee->hydrate($stmt->fetch());

        return $employee;
    }

    /**
     * @return Employee[]
     */
    public static function getAll($sorting = []): array
    {
        $sortSQL = "";
        if (count($sorting)) {
            $SQLchunks = [];
            foreach ($sorting as $field => $direction) {
                $SQLchunks[] = "e.`{$field}` {$direction}";
            }
            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
        }

        /*
        select e.*, r.name as room_name 
        from employee as e 
        inner join room as r on e.room = r.room_id;
        */
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("select e.*, r.name as room_name from `" . self::DB_TABLE . "` as e inner join room as r on e.room = r.room_id " . $sortSQL);
        $stmt->execute([]);

        $employees = [];
        while ($employeeData = $stmt->fetch()) {
            $employee = new Employee();
            $employee->hydrate($employeeData);
            $employees[] = $employee;
        }

        return $employees;
    }

    private function hydrate(array|object $data)
    {
        $fields = [
            'employee_id',
            'name',
            'surname',
            'job',
            'wage',
            'room',
            'room_name',
            'login',
            'password',
            'admin'
        ];

        if (is_array($data)) {
            foreach ($fields as $field) {
                if (array_key_exists($field, $data)) {
                    $this->{$field} = $data[$field];
                }
            }
        } else {
            foreach ($fields as $field) {
                if (property_exists($data, $field)) {
                    $this->{$field} = $data->{$field};
                }
            }
        }
    }

    public static function checkAdminPermissions(): bool {
        if (isset($_SESSION['admin']) && $_SESSION['admin']) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insert() : bool {
    $query = "INSERT INTO ".self::DB_TABLE." (`name`, `surname`, `job`, `wage`, `room`, `login`, `password`, `admin`) VALUES (:name, :surname, :job, :wage, :room, :login, :password, :admin)";
    $stmt = PDOProvider::get()->prepare($query);
    if ($this->admin == 'on') {
        $admin = 1;
    } else {
        $admin = 0;
    }
    $result = $stmt->execute([
        'name' => $this->name,
        'surname' => $this->surname,
        'job' => $this->job,
        'wage' => $this->wage,
        'room' => $this->room,
        'login' => $this->login,
        'password' => $this->password,
        'admin' => $admin
    ]);

    if (!$result) {
        return false;
    }

    $this->employee_id = PDOProvider::get()->lastInsertId();
    return true;
    }
    public function update() : bool{
    $query = "UPDATE ".self::DB_TABLE." SET name=:name, surname=:surname, job=:job, wage=:wage, room=:room, login=:login, password=:password, admin=:admin WHERE employee_id=:id";
    $stmt = PDOProvider::get()->prepare($query);
    $result = $stmt->execute(['name'=>$this->name, 'surname'=>$this->surname, 'job'=>$this->job, 'wage'=>$this->wage, 'room'=>$this->room, 'login'=>$this->login, 'password'=>$this->password, 'admin'=>$this->admin, 'id'=>$this->employee_id]);
    return $result;
    }

    public function validate(): array {
        $errors = [];
    
        if (empty($this->name)) {
            $errors['name'] = 'Name is required.';
        }
    
        if (empty($this->surname)) {
            $errors['surname'] = 'Surname is required.';
        }
    
        if (empty($this->job)) {
            $errors['job'] = 'Job is required.';
        }
    
        if (empty($this->wage)) {
            $errors['wage'] = 'Wage is required.';
        } elseif (!is_numeric($this->wage) || $this->wage <= 0) {
            $errors['wage'] = 'Wage must be a positive number.';
        }
    
        if (empty($this->room)) {
            $errors['room'] = 'Room is required.';
        }
    
        if (empty($this->login)) {
            $errors['login'] = 'Login is required.';
        }
    
        if (empty($this->password)) {
            $errors['password'] = 'Password is required.';
        }
    
        if (!isset($this->admin)) {
            $errors['admin'] = 'Admin field is required.';
        }
    
        return $errors;
    }
    
    public function delete() : bool
    {   
    $query = "DELETE FROM ".self::DB_TABLE." WHERE employee_id = :employee_id";
    $stmt = PDOProvider::get()->prepare($query);
    $result = $stmt->execute(['employee_id'=>$this->employee_id]);
    return $result;
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

    $employee->wage = filter_input(INPUT_POST, 'wage', FILTER_VALIDATE_INT);
    
    $employee->room = filter_input(INPUT_POST, 'room');
    if ($employee->room)
        $employee->room = trim($employee->room);

    $employee->login = filter_input(INPUT_POST, 'login');
    if ($employee->login)
        $employee->login = trim($employee->login);

    $employee->password = filter_input(INPUT_POST, 'password');
    if ($employee->password)
        $employee->password = trim($employee->password);

    $employee->admin = filter_input(INPUT_POST, 'admin', FILTER_VALIDATE_BOOLEAN);

    return $employee;
    }
}
