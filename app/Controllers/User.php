<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class User extends ResourceController
{
    public function check()
    {
        $userModel = new UserModel();
        $retobj = $userModel->check();
        echo json_encode($retobj);
    }
}
