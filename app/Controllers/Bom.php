<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\BomModel;

class Bom extends ResourceController
{
    public function read()
    {
        $bomModel = new BomModel();
        $retobj = $bomModel->read();
        echo json_encode($retobj);
    }

    public function add()
    {
        $bomModel = new BomModel();
        $retobj = $bomModel->add();
        echo json_encode($retobj);
    }

    public function save()
    {
        $bomModel = new BomModel();
        $retobj = $bomModel->saveBom();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $bomModel = new BomModel();
        $retobj = $bomModel->deleteBom();
        echo json_encode($retobj);
    }
}
