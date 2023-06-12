<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\TambahanModel;

class Tambahan extends ResourceController
{
    public function grafikbayarnota()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->grafikBayarNota();
        echo json_encode($retobj);
    }
    public function updatelogin()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->updatePassword();
        echo json_encode($retobj);
    }
    public function bestselling()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->bestSelling();
        echo json_encode($retobj);
    }
    public function grossprofit()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->grossProfit();
        echo json_encode($retobj);
    }
    public function netprofit()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->netProfit();
        echo json_encode($retobj);
    }
    public function poread()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->poread();
        echo json_encode($retobj);
    }

    public function rcvread()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->rcvread();
        echo json_encode($retobj);
    }

    public function readgallery()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->readGallery();
        echo json_encode($retobj);
    }

    public function updateharga()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->updateHarga();
        echo json_encode($retobj);
    }

    public function readnota()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->readNota();
        echo json_encode($retobj);
    }

    public function satuancheck()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->satuanCheck();
        echo json_encode($retobj);
    }

    public function monitoringstok()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->monitoringstok();
        echo json_encode($retobj);
    }
}