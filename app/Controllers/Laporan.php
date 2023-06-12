<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\SalesrepModel;

class Laporan extends ResourceController
{
    public function salesrep()
    {
        $salesrepModel = new SalesrepModel();
        $retobj = $salesrepModel->create();
        echo json_encode($retobj);
    }

    public function msalesrep()
    {
        $salesrepModel = new SalesrepModel();
        $retobj = $salesrepModel->mcreate();
        echo json_encode($retobj);
    }

    public function test()
    {
        $salesrepModel = new SalesrepModel();
        $retobj = $salesrepModel->test();
        echo json_encode($retobj);
    }
}
