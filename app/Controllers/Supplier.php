<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\SupplierModel;

class Supplier extends ResourceController
{
    public function read()
    {
        $supplierModel = new SupplierModel();
        $retobj = $supplierModel->read();
        echo json_encode($retobj);
    }

    public function search()
    {
        $supplierModel = new SupplierModel();
        $retobj = $supplierModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $supplierModel = new SupplierModel();
        $retobj = $supplierModel->saveSupplier();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $supplierModel = new SupplierModel();
        $retobj = $supplierModel->deleteSupplier();
        echo json_encode($retobj);
    }
}
