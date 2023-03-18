<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LoginPage extends BasePage
{
    public function __construct()
    {
        $this->title = "Přihlášení";
    }

    protected function pageBody()
    {
        return "Hello World!!!";
    }

}

//$name = filter_input(INPUT_POST,'name');
//$pass = filter_input(INPUT_POST,'password');
//
//if($name !== null && $pass !== null){
//    require_once "inc/users.inc.php";
//    foreach ($users as $userID => $user){
//        if($user['name'] === $name && $user['pass'] === $pass){
//            session_start();
//            $_SESSION['user'] = $userID;
//            header("Location: home.php");
//            exit;
//        }
//    }
//}




$page = new LoginPage();
$page->render();

?>