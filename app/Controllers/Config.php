<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ConfigModel;

class Config extends ResourceController
{
    public function read()
    {
        $configModel = new ConfigModel();
        $retobj = $configModel->read();
        echo json_encode($retobj);
    }

    public function update($id=null)
    {
        $configModel = new ConfigModel();
        $retobj = $configModel->updateProfile();
        echo json_encode($retobj);
    }

    public function backupdb()
    {
        $configModel = new ConfigModel();
        $retobj = $configModel->backupDatabase();
        echo json_encode($retobj);
    }
}
