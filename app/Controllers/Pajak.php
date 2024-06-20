<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\PajakModel;

class Pajak extends ResourceController
{
    public function read()
    {
        $pajakModel = new PajakModel();
        $retobj = $pajakModel->read();
        echo json_encode($retobj);
    }

    public function read_count()
    {
        $pajakModel = new PajakModel();
        $retobj = $pajakModel->read_count();
        echo json_encode($retobj);
    }

    public function search()
    {
        $pajakModel = new PajakModel();
        $retobj = $pajakModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $pajakModel = new PajakModel();
        $retobj = $pajakModel->savePajak();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $pajakModel = new PajakModel();
        $retobj = $pajakModel->deletePajak();
        echo json_encode($retobj);
    }
}
