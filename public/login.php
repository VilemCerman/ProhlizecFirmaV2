<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LoginPage extends BasePage
{
    protected ?string $login = null;
    protected ?string $pass = null;
    public function __construct()
    {
        $this->login = filter_input(INPUT_POST,'login');
        $this->pass = filter_input(INPUT_POST,'password');

        if($this->login !== null && $this->pass !== null){
            $stmt = PDOProvider::get()->prepare("SELECT name, surname, admin, login, password AS pass FROM employee WHERE login = :userLogin");
            $stmt->execute(['userLogin' => $this->login]);
            $user = $stmt->fetch();
            //if($user['login'] === $this->login && password_verify($this->pass,$user['hash'])){

            if($this->pass === $user->pass){
                session_abort();
                session_start();
                $_SESSION['user'] = $user->login;
                $_SESSION['admin'] = $user->admin;
                header("Location: index.php");
            }
        }

        $this->title = "Přihlášení";
    }

    public function render(): void
    {
        $this->prepare();
        $this->sendHttpHeaders();

        $m = MustacheProvider::get();
        $data = [
            'lang' => AppConfig::get('app.lang'),
            'title' => $this->title,
            'pageHeader' => $this->pageHeader(),
            'pageBody' => $this->pageBody(),
            'pageFooter' => $this->pageFooter()
        ];

        echo $m->render("page", $data);
    }

    protected function pageBody() :string
    {
        return "<form method='post'>
                    Jméno: <input type='text' name='login' value='$this->login'/><br />
                    Heslo: <input type='password' name='password'/><br />
                    <input type='submit' value='Submit' />
                </form>";
    }

    protected function pageHeader(): string
    {
        return "<a href='index.php'>Zpět na hlavní stránku</a>";
    }

}

$page = new LoginPage();
$page->render();

?>