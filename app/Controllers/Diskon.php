<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\DiskonModel;

class Diskon extends ResourceController
{
    public function read()
    {
        $diskonModel = new DiskonModel();
        $retobj = $diskonModel->read();
        echo json_encode($retobj);
    }

    public function read_count()
    {
        $diskonModel = new DiskonModel();
        $retobj = $diskonModel->read_count();
        echo json_encode($retobj);
    }

    public function search()
    {
        $diskonModel = new DiskonModel();
        $retobj = $diskonModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $diskonModel = new DiskonModel();
        $retobj = $diskonModel->saveDiskon();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $diskonModel = new DiskonModel();
        $retobj = $diskonModel->deleteDiskon();
        echo json_encode($retobj);
    }
}
