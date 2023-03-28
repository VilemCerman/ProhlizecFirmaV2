<?php //TODO password change page
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class passChange extends BasePage
{
    protected function pageBody() :string
    {
        return "<a href='index.php'>Zpět na hlavní stránku</a>
                <form method='post'>
                    Jméno: <input type='text' name='login' value='$this->login'/><br />
                    Heslo: <input type='password' name='password'/><br />
                    <input type='submit' value='Submit' />
                </form>";
    }
}