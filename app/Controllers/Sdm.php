<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\SdmModel;

class Sdm extends ResourceController
{
    public function read()
    {
        $sdmModel = new SdmModel();
        $retobj = $sdmModel->read();
        echo json_encode($retobj);
    }

    public function dokter()
    {
        $sdmModel = new SdmModel();
        $retobj = $sdmModel->dokter();
        echo json_encode($retobj);
    }

    public function search()
    {
        $sdmModel = new SdmModel();
        $retobj = $sdmModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $sdmModel = new SdmModel();
        $retobj = $sdmModel->saveSdm();
        echo json_encode($retobj);
    }

    public function changestatus()
    {
        $sdmModel = new SdmModel();
        $retobj = $sdmModel->changeStatus();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $sdmModel = new SdmModel();
        $retobj = $sdmModel->deleteSdm();
        echo json_encode($retobj);
    }
}
