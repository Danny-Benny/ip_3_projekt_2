<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeCreatePage extends CRUDPage
{
    private ?Employee $employee;
    private ?array $errors = [];
    private int $state;

    public function __construct()
    {
        parent::__construct();

    }

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Založit nového zaměstnance";

        // when the form is requested
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            // proceed
            $this->employee = new Employee();
        }

        // when the data is sent
        elseif($this->state === self::STATE_DATA_SENT) {
            // read the data
            $this->employee = Employee::readPost();

            // validate the data; if it's invalid, show the form again
            $this->errors = [];
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {
                // save the data
                $success = $this->employee->insert();

                // redirect
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
                'errors' => $this->errors
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

