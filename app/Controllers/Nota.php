<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\NotaModel;

class Nota extends ResourceController
{
    public function read()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->read();
        echo json_encode($retobj);
    }

    public function notaitem()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->notaItem();
        echo json_encode($retobj);
    }

    public function readitem()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->readItem();
        echo json_encode($retobj);
    }

    public function getnewid()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->getNewID();
        echo json_encode($retobj);
    }

    public function search()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->search();
        echo json_encode($retobj);
    }

    public function save()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->saveNota();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->deleteNota();
        echo json_encode($retobj);
    }

    public function delete_new()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->deleteNotaNew();
        echo json_encode($retobj);
    }
    
    public function receipt()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->receipt();
        echo json_encode($retobj);
    }

    public function backup()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->backup();
        echo json_encode($retobj);
    }

    public function localrestore()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->localRestore();
        echo json_encode($retobj);
    }

    public function localbackup()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->localBackup();
        echo json_encode($retobj);
    }

    public function backuplist()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->backupList();
        echo json_encode($retobj);
    }

    public function restore()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->restore();
        echo json_encode($retobj);
    }

    public function deletebackup()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->deleteBackup();
        echo json_encode($retobj);
    }

    public function prosesnota()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->prosesNota();
    }

    public function prosesnotaitem()
    {
        $notaModel = new NotaModel();
        $retobj = $notaModel->prosesNotaItem();
    }
}
