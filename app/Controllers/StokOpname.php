<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\StokOpnameModel;

class Stokopname extends ResourceController
{
    public function read()
    {
        $stokOpnameModel = new StokOpnameModel();
        $retobj = $stokOpnameModel->read();
        echo json_encode($retobj);
    }
    
    public function save()
    {
        $stokOpnameModel = new StokOpnameModel();
        $retobj = $stokOpnameModel->saveStokOpname();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $stokOpnameModel = new StokOpnameModel();
        $retobj = $stokOpnameModel->deleteStokOpname();
        echo json_encode($retobj);
    }
}
