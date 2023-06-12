<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\LocaluserModel;

class Localuser extends ResourceController
{
    public function signup()
    {
        $localuserModel = new LocaluserModel();
        $retobj = $localuserModel->signup();
        echo json_encode($retobj);
    }

    public function login()
    {
        $localuserModel = new LocaluserModel();
        $retobj = $localuserModel->login();
        echo json_encode($retobj);
    }

    public function update($id=null)
    {
        $localuserModel = new LocaluserModel();
        $retobj = $localuserModel->updateProfile();
        echo json_encode($retobj);
    }
}
