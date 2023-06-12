<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ReceiveModel;

class Receive extends ResourceController
{
    public function read()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->read();
        echo json_encode($retobj);
    }

    public function poitem()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->receiveItem();
        echo json_encode($retobj);
    }

    public function search()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->saveReceive();
        echo json_encode($retobj);
    }

    public function pay()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->pay();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->deleteReceive();
        echo json_encode($retobj);
    }

    public function receipt()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->receipt();
        echo json_encode($retobj);
    }

    public function backup()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->backup();
        echo json_encode($retobj);
    }

    public function backuplist()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->backupList();
        echo json_encode($retobj);
    }

    public function restore()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->restore();
        echo json_encode($retobj);
    }

    public function deletebackup()
    {
        $receiveModel = new ReceiveModel();
        $retobj = $receiveModel->deleteBackup();
        echo json_encode($retobj);
    }
}
