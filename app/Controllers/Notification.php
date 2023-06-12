<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\NotificationModel;

class Notification extends ResourceController
{
    public function getflag()
    {
        $model = new NotificationModel();
        $retobj = $model->getFlag();
        echo json_encode($retobj);
    }

    public function setflag()
    {
        $model = new NotificationModel();
        $retobj = $model->setFlag();
        echo json_encode($retobj);
    }

    public function pull()
    {
        $model = new NotificationModel();
        $retobj = $model->pull();
        echo json_encode($retobj);
    }
}
