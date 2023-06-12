<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\DraftnotaModel;

class Draftnota extends ResourceController
{
    public function read()
    {
        $model = new DraftnotaModel();
        $retobj = $model->read();
        echo json_encode($retobj);
    }

    public function save()
    {
        $model = new DraftnotaModel();
        $retobj = $model->saveDraftnota();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $model = new DraftnotaModel();
        $retobj = $model->deleteDraftnota();
        echo json_encode($retobj);
    }
}
