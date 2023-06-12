<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\CarabayarModel;

class Carabayar extends ResourceController
{
    public function read()
    {
        $model = new CarabayarModel();
        $retobj = $model->read();
        echo json_encode($retobj);
    }
}
