<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class IndexPage extends BasePage
{
    public function __construct()
    {
        session_start();
        $this->title = "Prohlížeč databáze firmy";
    }

    protected function pageBody()
    {
        if(isset($_SESSION['user'])) {
            echo 'Uživatel ' . $_SESSION['user'] . ' je přihlášen.';
    
            if(isset($_SESSION['admin']) && $_SESSION['admin'] === 1) {
                echo ' Je admin.';
            }else{
                echo ' Není admin.';
            }
    
        } else {
            echo 'Uživatel není přihlášen, pro přístup do databáze se přihlašte.';
        }
    }
}

$page = new IndexPage();
$page->render();

?>