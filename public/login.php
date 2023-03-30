<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LoginPage extends BasePage
{
    protected ?string $login = null;
    protected ?string $password = null;
    protected ?string $hash = null;
    private array $errors = [];

    protected function prepare() :void
    {
            $this->login = filter_input(INPUT_POST, 'login');
            $this->password = filter_input(INPUT_POST, 'password');

            if ($this->login == null) {
                $this->errors['login'] = 'Prosím vyplňte své přihlašovací jméno';
            } else if ($this->password == null) {
                $this->errors['pass'] = 'Prosím zadejte své heslo';

            } else {
                $stmt = PDOProvider::get()->prepare("SELECT employee_id, name, surname, admin, login, hash FROM employee WHERE login = :userLogin");
                $stmt->execute(['userLogin' => $this->login]);
                $user = $stmt->fetch();

                //if($user['login'] === $this->login && password_verify($this->pass,$user['hash'])){
                //if (!$user || $this->password !== $user->password) {
                if(!password_verify($this->password,$user->hash)){
                    $this->errors['invalid'] = 'Jméno nebo heslo není správné';
                } else {
                    $_SESSION['user'] = $user->login;
                    $_SESSION['admin'] = $user->admin;
                    $_SESSION['id'] = $user->employee_id;
                    $this->status = 'OK';
                }
            }
        $this->title = "Přihlášení";
    }

    public function render(): void
    {
        if(!isset($_SESSION))
            session_start();
        $this->prepare();
        if(isset($_SESSION['user'])){
            header("Location: /index.php");
        }
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
        return MustacheProvider::get()->render('login', ['login' => $this->login, 'errors' => $this->errors]);
    }

    protected function pageHeader(): string
    {
        return "<h1>Pro přístup k databázi je potřeba se přihlásit</h1>";
    }
}

$page = new LoginPage();
$page->render();

?>