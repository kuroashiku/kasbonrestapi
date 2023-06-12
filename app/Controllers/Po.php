<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\PoModel;

class Po extends ResourceController
{
    public function read()
    {
        $poModel = new PoModel();
        $retobj = $poModel->read();
        echo json_encode($retobj);
    }

    public function poitem()
    {
        $poModel = new PoModel();
        $retobj = $poModel->poItem();
        echo json_encode($retobj);
    }

    public function search()
    {
        $poModel = new PoModel();
        $retobj = $poModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $poModel = new PoModel();
        $retobj = $poModel->savePO();
        echo json_encode($retobj);
    }

    public function approve()
    {
        $poModel = new PoModel();
        $retobj = $poModel->approve();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $poModel = new PoModel();
        $retobj = $poModel->deletePO();
        echo json_encode($retobj);
    }

    public function receipt()
    {
        $poModel = new PoModel();
        $retobj = $poModel->receipt();
        echo json_encode($retobj);
    }

    public function backup()
    {
        $poModel = new PoModel();
        $retobj = $poModel->backup();
        echo json_encode($retobj);
    }

    public function backuplist()
    {
        $poModel = new PoModel();
        $retobj = $poModel->backupList();
        echo json_encode($retobj);
    }

    public function restore()
    {
        $poModel = new PoModel();
        $retobj = $poModel->restore();
        echo json_encode($retobj);
    }

    public function deletebackup()
    {
        $poModel = new PoModel();
        $retobj = $poModel->deleteBackup();
        echo json_encode($retobj);
    }
}
