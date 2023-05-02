<?php

abstract class CRUDPage extends BasePage
{
    public const STATE_FORM_REQUESTED = 0;
    public const STATE_DATA_SENT = 1;

    public const ACTION_INSERT = "insert";
    public const ACTION_UPDATE = "update";
    public const ACTION_DELETE = "delete";
    public const ACTION_UPDATE_PASS = "updatePass";

    protected $alert = [];

    protected function redirect(string $action, bool $success, string $entity) : void
    {
        $data = [
            'action' => $action,
            'entity' => $entity,
            'success' => $success ? 1 : 0
        ];
        //header('Location: '.$entity === 'pass'?'index':'list'.'.php?' . http_build_query($data) );

        if($entity === 'pass')
            header('Location: index.php?' . http_build_query($data) );
        else
            header('Location: list.php?' . http_build_query($data) );

        exit;
    }

    protected function prepare(): void
    {
        parent::prepare();
        //pokud přišel výsledek, zachytím ho
        $crudResult = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
        $crudAction = filter_input(INPUT_GET, 'action');
        $crudEntity = filter_input(INPUT_GET, 'entity');

        if (is_int($crudResult)) {
            $this->alert = [
                'alertClass' => $crudResult === 0 ? 'danger' : 'success'
            ];
            $message = '';

            if ($crudResult === 0)
            {
                $message = 'Operace nebyla úspěšná';

                if($crudAction === self::ACTION_DELETE && $crudEntity === 'room')
                {
                    $message = 'Místnost nejde smazat, může to být protože ji mají někteří zaměstnanci jako domovskou';
                }
            }
            else if($crudEntity === 'staff'){
                if ($crudAction === self::ACTION_DELETE)
                {
                    $message = 'Smazání zaměstnace proběhlo úspěšně';
                }
                else if ($crudAction === self::ACTION_INSERT)
                {
                    $message = 'Zaměstnanec zapsán úspěšně';
                }
                else if ($crudAction === self::ACTION_UPDATE)
                {
                    $message = 'Úprava zaměstnance byla úspěšná';
                }
            }else if($crudEntity === 'room'){
                if ($crudAction === self::ACTION_DELETE)
                {
                    $message = 'Smazání místnosti proběhlo úspěšně';
                }
                else if ($crudAction === self::ACTION_INSERT)
                {
                    $message = 'Místnost založena úspěšně';
                }
                else if ($crudAction === self::ACTION_UPDATE)
                {
                    $message = 'Úprava místnosti byla úspěšná';
                }
            }else if($crudEntity === 'pass' && $crudAction === self::ACTION_UPDATE_PASS){
                $message = "Změna hesla proběhla úspěšně";
            }
            $this->alert['message'] = $message;
        }

    }
}