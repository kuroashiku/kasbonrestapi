<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\BahasaModel;

class Bahasa extends ResourceController
{
    public function read()
    {
        $model = new BahasaModel();
        $data = $model->read();
        echo json_encode($data);
    }
}
