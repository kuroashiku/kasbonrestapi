<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\PiutangModel;

class Piutang extends ResourceController
{
    public function read()
    {
        $piutangModel = new PiutangModel();
        $retobj = $piutangModel->read();
        echo json_encode($retobj);
    }

    public function getnewpayment()
    {
        $piutangModel = new PiutangModel();
        $retobj = $piutangModel->getNewPayment();
        echo json_encode($retobj);
    }

    public function save()
    {
        $piutangModel = new PiutangModel();
        $retobj = $piutangModel->savePiutang();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $piutangModel = new PiutangModel();
        $retobj = $piutangModel->deletePiutang();
        echo json_encode($retobj);
    }
}
