<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LoginPage extends BasePage
{
    protected string $title = "Přihlášení";

    public function __construct()
    {
        session_start();
        parent::__construct();
        
        if (!empty($_POST)) {
            $this->login();
        }
    }

    protected function login() : void
    {
        echo "lakjdsf";
    $login = filter_input(INPUT_POST, "login");
    $password = filter_input(INPUT_POST, "password");

    if (empty($login) || empty($password)) {
        $this->addErrorMessage("Je nutné zadat uživatelské jméno a heslo.");
        echo "lakjdsf";
        return;
    }

    $stmt = PDOProvider::get()->prepare("SELECT login, password, admin FROM employee WHERE login = :login");
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch();


    if($password === $user->password){
        $_SESSION['user'] = $user->login;

        if($user->admin == 1)
            $_SESSION['admin'] = 1;
        
        var_dump($user->login);
        header("Location: index.php");
        exit;
    } 
    else {
        $this->addErrorMessage("Nesprávné uživatelské jméno nebo heslo.");
        return;
    }
    }
    

    protected function pageBody() : string 
    {
        return "
        <div class='container'>
            <div class='row'>
                <div class='col-md-4 offset-md-4'>
                    <form method='post'>
                        <div class='form-group'>
                            <label for='login'>Uživatelské jméno:</label>
                            <input type='text' class='form-control' id='login' name='login' required>
                        </div>
                        <div class='form-group'>
                            <label for='password'>Heslo:</label>
                            <input type='password' class='form-control' id='password' name='password' required>
                        </div>
                        <button type='submit' class='btn btn-primary'>Přihlásit se</button>
                        <a href='index.php' class='btn btn-secondary'>Zpět na hlavní stránku</a>
                    </form>
                </div>
            </div>
        </div>
        ";
    }
}

$page = new LoginPage();
$page->render();