<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\GeneralSettingModel;

class Generalsetting extends ResourceController
{
    public function read()
    {
        $generalSettingModel = new GeneralSettingModel();
        $retobj = $generalSettingModel->read();
        echo json_encode($retobj);
    }

    public function save()
    {
        $generalSettingModel = new GeneralSettingModel();
        $retobj = $generalSettingModel->saveGeneralSetting();
        echo json_encode($retobj);
    }
}
