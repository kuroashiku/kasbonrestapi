<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\KasirModel;

class Kasir extends ResourceController
{
    public function read()
    {
        $kasirModel = new KasirModel();
        $retobj = $kasirModel->read();
        echo json_encode($retobj);
    }
}
