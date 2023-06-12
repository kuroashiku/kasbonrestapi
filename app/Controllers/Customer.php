<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\CustomerModel;

class Customer extends ResourceController
{
    public function read()
    {
        $customerModel = new CustomerModel();
        $retobj = $customerModel->read();
        echo json_encode($retobj);
    }

    public function search()
    {
        $customerModel = new CustomerModel();
        $retobj = $customerModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $customerModel = new CustomerModel();
        $retobj = $customerModel->saveCustomer();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $customerModel = new CustomerModel();
        $retobj = $customerModel->deleteCustomer();
        echo json_encode($retobj);
    }
}
