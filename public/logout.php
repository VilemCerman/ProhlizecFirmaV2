<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LogoutPage extends BasePage
{
    protected ?string $login = null;
    protected ?string $pass = null;
    public function __construct()
    {

        $this->title = "Odhlášení";
    }

    protected function pageBody() :string
    {
        return '';
    }

    protected function pageHeader(): string
    {
        //session_start();
        unset($_SESSION['user']);
        unset($_SESSION['admin']);
        return "<h1>Odhlášení proběhlo úspěšně</h1>
                <a href='index.php'>Zpět na hlavní stránku</a>";
    }

}

$page = new LogoutPage();
$page->render();

?>