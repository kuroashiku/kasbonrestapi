<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UsermanModel;

class Userman extends ResourceController
{
    public function read()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->read();
        echo json_encode($retobj);
    }

    public function saverolefuncode()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->saveRoleFunCode();
        echo json_encode($retobj);
    }

    public function saverolename()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->saveRoleName();
        echo json_encode($retobj);
    }

    public function saveuser()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->saveUser();
        echo json_encode($retobj);
    }

    public function addrole()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->addRole();
        echo json_encode($retobj);
    }

    public function deleterole()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->deleteRole();
        echo json_encode($retobj);
    }

    public function adduser()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->addUser();
        echo json_encode($retobj);
    }

    public function deleteuser()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->deleteUser();
        echo json_encode($retobj);
    }

    public function resetpassword()
    {
        $supplierModel = new UsermanModel();
        $retobj = $supplierModel->resetPassword();
        echo json_encode($retobj);
    }
}
