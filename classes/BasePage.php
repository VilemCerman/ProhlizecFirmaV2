<?php

abstract class BasePage
{
    protected string $title = "";
    protected string $status = "";

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
        return $m->render('header',['logged' => $this->status !== "unauthorized"]);
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
            session_start();
            //mam uzivatele?
            if(!isset($_SESSION['user'])){
                //neprihlaseno
                $status = "unauthorized";
                http_response_code(401);
            }else{
                $userLogin = $_SESSION['user'];
//                require_once "inc/users.inc.php";
//                $user = $users[$userLogin];
                var_dump($_SESSION['user']);
                $status = "OK";
            }
            var_dump($status);

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