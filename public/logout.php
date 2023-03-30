<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LogoutPage extends BasePage
{
    private ?string $header = "Odhlášení proběhlo úspěšně";

    protected function prepare(): void
    {
        parent::prepare();
        //pokud přišel výsledek, zachytím ho
        $crudResult = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
        $crudAction = filter_input(INPUT_GET, 'action');

        if (is_int($crudResult)) {
            if($crudResult == 1 && $crudAction == "updatePass"){
                $this->header = "Změna hesla proběhla úspěšně";
            }
        }
    }

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
        unset($_SESSION['user']);
        unset($_SESSION['admin']);
        unset($_SESSION['id']);
        return "<h1>".$this->header."</h1>
                <a href='index.php'>Zpět na hlavní stránku</a>";
    }
}

$page = new LogoutPage();
$page->render();

?>