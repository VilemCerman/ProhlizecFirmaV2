<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class passChangePage extends CRUDPage
{
    protected ?string $oldPassword = null;
    protected ?string $newPassword = null;
    protected ?string $secondNewPassword = null;
    protected ?Employee $employee = null;
    private array $errors = [];
    private int $state;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Změna hesla";


        $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        //jdi dál
        $this->employee = Employee::findByID($employeeId);
        if (!$this->employee)
            throw new NotFoundException();

        //když poslal data
        elseif($this->state === self::STATE_DATA_SENT) {

            //načti je
            $this->oldPassword = filter_input(INPUT_POST,'oldPassword');
            $this->newPassword = filter_input(INPUT_POST,'newPassword');
            $this->secondNewPassword = filter_input(INPUT_POST,'secondNewPassword');

            //zkontroluj je, jinak formulář
            $this->errors = [];
            $isOk = true;
            if($this->oldPassword == null)
            {
                $this->errors['oldPass'] = 'Prosím zadejte své staré heslo';
                $isOk = false;
            }
            else if($this->newPassword == null )
            {
                $this->errors['newPass'] = 'Prosím zadejte své nové heslo';
                $isOk = false;
            }
            else if($this->secondNewPassword == null)
            {
                $this->errors['secondNewPass'] = 'Prosím potvrďte své nové heslo';
                $isOk = false;
            }
            else if($this->oldPassword !== $this->employee->password )
            {
                $this->errors['oldPass'] = 'Špatné heslo';
                $isOk = false;
            }
            else if ($this->newPassword !== $this->secondNewPassword)
            {
                $this->errors['secondNewPass'] = 'Hesla se neshodují';
                $isOk = false;
            }

            echo $isOk;
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {
                //ulož je
                $this->employee->password = $this->newPassword;
                $success = $this->employee->updatePass();

                //přesměruj
                $this->redirect(self::ACTION_UPDATE, $success);
            }
        }
    }
    protected function redirect(string $action, bool $success) : void
    {
        $data = [
            'action' => $action,
            'success' => $success ? 1 : 0
        ];
        header('Location: logout.php?' . http_build_query($data) );
        exit;
    }

    private function findState() : void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $this->state = self::STATE_DATA_SENT;
        else
            $this->state = self::STATE_FORM_REQUESTED;
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render('changePass', ['errors' => $this->errors]);
    }
    protected function pageHeader(): string
    {
        return "<h1>Změna hesla</h1>";
    }

}




//class passChangePage extends BasePage
//{
//    protected ?string $oldPassword = null;
//    protected ?string $newPassword = null;
//    protected ?string $secondNewPassword = null;
//    private array $errors = [];
//
//    protected function pageHeader(): string
//    {
//        return "<h1>Změna hesla</h1>";
//    }
//
//
//    protected function prepare() : void
//    {
//        $this->oldPassword = filter_input(INPUT_POST,'oldPassword');
//        $this->newPassword = filter_input(INPUT_POST,'newPassword');
//        $this->secondNewPassword = filter_input(INPUT_POST,'secondNewPassword');
//
//        dump($_SESSION);
//
//        if($this->oldPassword == null)
//        {
//            $this->errors['oldPass'] = 'Prosím zadejte své staré heslo';
//        }
//        else if($this->newPassword == null )
//        {
//            $this->errors['newPass'] = 'Prosím zadejte své nové heslo';
//        }
//        else if($this->secondNewPassword == null)
//        {
//            $this->errors['secondNewPass'] = 'Prosím potvrďte své nové heslo';
//        }
//        //else(isset($_SESSION['id']))
//        else
//        {
//            $this->user = Employee::findByID($_SESSION['id']);
//            dump($this->user);
//
//            if($this->oldPassword !== $this->user->password )
//            {
//                $this->errors['oldPass'] = 'Špatné heslo';
//            }
//            else if ($this->newPassword !== $this->secondNewPassword)
//            {
//                $this->errors['secondNewPass'] = 'Hesla se neshodují';
//            }
//            else
//            {
//                $this->user->password = $this->newPassword;
//                $this->user->update();
//
//                dump($_SESSION);
//                dump($this->user);
//                $this->user = Employee::findByID($_SESSION['id']);
//                dump($this->user);
//                unset($_SESSION);
//            }
//        }
//        $this->title = "Změna hesla";
//
//    }
//
//    protected function pageBody() :string
//    {
//        return MustacheProvider::get()->render('changePass', ['errors' => $this->errors]);
//    }
//    public function render(): void
//    {
//        $this->prepare();
//        header("Location: /index.php");
//        $data = [
//            'lang' => AppConfig::get('app.lang'),
//            'title' => $this->title,
//            'pageHeader' => $this->pageHeader(),
//            'pageBody' => $this->pageBody(),
//            'pageFooter' => $this->pageFooter()
//        ];
//
//        //header("Location: logout.php");
//
//        $this->sendHttpHeaders();
//        $m = MustacheProvider::get();
//        echo $m->render("page", $data);
//    }
//}

$page = new passChangePage();
$page->render();

?>
