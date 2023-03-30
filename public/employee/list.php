<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeesPage extends CRUDPage
{
    private $alert = [];

    public function __construct()
    {
        $this->title = "Výpis zamestnancu";
    }

    protected function prepare(): void
    {
        parent::prepare();
        //pokud přišel výsledek, zachytím ho
        $crudResult = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
        $crudAction = filter_input(INPUT_GET, 'action');

        if (is_int($crudResult)) {
            $this->alert = [
                'alertClass' => $crudResult === 0 ? 'danger' : 'success'
            ];

            $message = '';
            if ($crudResult === 0)
            {
                $message = 'Operace nebyla úspěšná';
            }
            else if ($crudAction === self::ACTION_DELETE)
            {
                $message = 'Smazání proběhlo úspěšně';
            }
            else if ($crudAction === self::ACTION_INSERT)
            {
                $message = 'Clovek založen úspěšně';
            }
            else if ($crudAction === self::ACTION_UPDATE)
            {
                $message = 'Úprava cloveka byla úspěšná';
            }

            $this->alert['message'] = $message;
        }
    }
    protected function pageBody()
    {
        $html = "";
        //zobrazit alert
        if ($this->alert) {
            $html .= MustacheProvider::get()->render('crudResult', $this->alert);
        }

        //získat data
        $employees = Employee::getAll(['name' => 'ASC']);

        //prezentovat data
        $html .= MustacheProvider::get()->render('employeeList', [
            'employees' => $employees,
            'isAdmin' => isset($_SESSION['admin']) && $_SESSION['admin'] === true
        ]);

        return $html;
    }

    protected function checkPermissions(): void
    {
        parent::checkPermissions();

        // not logged in users can't access this page
        if (!isset($_SESSION['user'])) {
            $this->redirect('/active/public/login.php');
        }
    }

    protected function checkAdminPermissions(): void
    {
        parent::checkAdminPermissions();

        // logged in users can view the employees list
        if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
            // hide the edit and delete buttons from non-admin users
            $employees = Employee::getAll(['name' => 'ASC']);
            foreach ($employees as &$employee) {
                unset($employee['edit_url']);
                unset($employee['delete_url']);
            }
        }
    }
}


?>