<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\MejaModel;

class Meja extends ResourceController
{
    public function read()
    {
        $mejaModel = new MejaModel();
        $retobj = $mejaModel->read();
        echo json_encode($retobj);
    }

    public function save()
    {
        $mejaModel = new MejaModel();
        $retobj = $mejaModel->saveMeja();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $mejaModel = new MejaModel();
        $retobj = $mejaModel->deleteMeja();
        echo json_encode($retobj);
    }
}
