<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemModel;

class Item extends ResourceController
{
    public function readget()
    {
        $_POST = $_GET;
        $itemModel = new ItemModel();
        $retobj = $itemModel->read();
        echo json_encode($retobj);
    }
    public function read()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->read();
        echo json_encode($retobj);
    }

    // membaca item untuk ditampilkan dalam pilihan PO
    // yaitu item-item dengan tipe tidak "pakaistok" misal jasa
    // fungsi ini juga dipakai oleh BOM, karena item BOM
    // adalah item yang non jasa

    public function readforpo()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->readForPO();
        echo json_encode($retobj);
    }

    // fungsi ini dipakai untuk filter di view item
    // yaitu untuk menampilkan item-item yang terlibat dalam BOM
    
    public function readforbom()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->readForBOM();
        echo json_encode($retobj);
    }

    public function readgalleryget()
    {
        $_POST = $_GET;
        $itemModel = new ItemModel();
        $retobj = $itemModel->readGallery();
        echo json_encode($retobj);
    }

    public function readgallery()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->readGallery();
        echo json_encode($retobj);
    }

    public function readthumbnailget()
    {
        $_POST = $_GET;
        $itemModel = new ItemModel();
        $retobj = $itemModel->readThumbnail();
        echo json_encode($retobj);
    }

    public function readthumbnail()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->readThumbnail();
        echo json_encode($retobj);
    }

    public function search()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->search();
        echo json_encode($retobj);
    }

    public function getdetail()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->getDetail();
        echo json_encode($retobj);
    }

    public function save()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->saveItem();
        echo json_encode($retobj);
    }

    public function delete($id=null)
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->deleteItem();
        echo json_encode($retobj);
    }

    public function import()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->import();
        echo json_encode($retobj);
    }

    public function proses()
    {
        $itemModel = new ItemModel();
        $retobj = $itemModel->proses();
        //echo json_encode($retobj);
    }
}
