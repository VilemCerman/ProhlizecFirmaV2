<?php

abstract class BasePage
{
    protected ?string $title = null;
    protected ?string $status = null;
    protected Employee $user;

    protected function prepare() : void
    {}

    protected function sendHttpHeaders() : void
    {}

    protected function extraHTMLHeaders() : string
    {
        return "";
    }

    protected function pageHeader() : string
    {
        $m = MustacheProvider::get();
        return $m->render('header',['logged' => $this->status !== "unauthorized", 'fullName' => $this->user->name.' '.$this->user->surname, 'employee_id' => $this->user->employee_id]);
    }

    abstract protected function pageBody();

    protected function pageFooter() : string
    {
        $m = MustacheProvider::get();
        return $m->render('footer',[]);
    }

    public function render() : void
    {
        try
        {
            if(!isset($_SESSION))
                session_start();
            $this->prepare();
            //mam uzivatele?
            if(!isset($_SESSION['user'])){
                //neprihlaseno
                $this->status = "unauthorized";
                http_response_code(401);

                header("Location: /login.php");

                $data = [
                    'lang' => AppConfig::get('app.lang'),
                    'title' => 'Přihlaste se prosím',
                    'pageHeader' => 'Pro přístup k databázi je potřeba se přihlásit',
                    'pageBody' => '',
                    'pageFooter' => ''
                ];

            }else {
                $userLogin = $_SESSION['user'];
                //$this->user = ['name' => $_SESSION->name, 'surname' => $_SESSION->surname, 'admin' => $_SESSION->admin];
                $this->user = Employee::findByID($_SESSION['id']);
                $this->status = "OK";

                $data = [
                    'lang' => AppConfig::get('app.lang'),
                    'title' => $this->title,
                    'pageHeader' => $this->pageHeader(),
                    'pageBody' => $this->pageBody(),
                    'pageFooter' => $this->pageFooter()
                ];
            }
            $this->sendHttpHeaders();
            $m = MustacheProvider::get();
            echo $m->render("page", $data);
        }
        catch (BaseException $e)
        {
            $exceptionPage = new ExceptionPage($e);
            $exceptionPage->render();
            exit;
        }
        catch (Exception $e)
        {
            if (AppConfig::get('debug'))
                throw $e;

            $e = new BaseException("Server error", 500);
            $exceptionPage = new ExceptionPage($e);
            $exceptionPage->render();
            exit;
        }
    }
}