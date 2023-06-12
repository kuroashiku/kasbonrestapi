<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\BelanjaModel;

class Belanja extends ResourceController
{
    public function read()
    {
        $model = new BelanjaModel();
        $data = $model->read();
        echo json_encode($data);
    }

    public function save()
    {
        $model = new BelanjaModel();
        $data = $model->saveBelanja();
        echo json_encode($data);
    }

    public function delete($id=null)
    {
        $model = new BelanjaModel();
        $data = $model->deleteBelanja();
        echo json_encode($data);
    }
}
