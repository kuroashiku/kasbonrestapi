<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\KunjunganModel;
use App\Models\UserModel;

class Main extends ResourceController
{
    public function loginsubmit()
    {
        $userModel = new UserModel();
        $data = $userModel->check();
        echo json_encode($data);
    }
}
