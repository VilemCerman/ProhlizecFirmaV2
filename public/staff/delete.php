<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDeletePage extends CRUDPage
{

    protected function prepare(): void
    {
        parent::prepare();

        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        //když se snaži odstranit sám sebe
        if($employeeId == $_SESSION['id']){
            $success = false;
        }else{
            //když poslal data
            $success = Employee::deleteByID($employeeId);
        }
        //přesměruj
        $this->redirect(self::ACTION_DELETE, $success, 'staff');
    }

    protected function pageBody()
    {
        return "";
    }
}

$page = new EmployeeDeletePage();
$page->render();
?>
