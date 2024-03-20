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

    public function delete_transaksi()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->delete_transaksi();
        echo json_encode($retobj);
    }

    public function read_login()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->read_login();
        echo json_encode($retobj);
    }

    public function kode_otp()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->kode_otp();
        echo json_encode($retobj);
    }
    public function cek_otp()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->cek_otp();
        echo json_encode($retobj);
    }
    
    public function reset_login()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->reset_login();
        echo json_encode($retobj);
    }

    public function cek_login()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->cek_login();
        echo json_encode($retobj);
    }

    public function cek_kas()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->cek_kas();
        echo json_encode($retobj);
    }
    public function cek_email()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->cek_email();
        echo json_encode($retobj);
    }
    public function update_setting()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->update_setting();
        echo json_encode($retobj);
    }
    public function read_email()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->read_email();
        echo json_encode($retobj);
    }
    
    public function omzet()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->dailyOmzet();
        echo json_encode($retobj);
    }
    public function omzetharian()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->dailyCashierOmzet();
        echo json_encode($retobj);
    }
    public function kategori()
    {
        $tambahanModel = new TambahanModel();
        $retobj = $tambahanModel->readKategoriTop();
        echo json_encode($retobj);
    }
}