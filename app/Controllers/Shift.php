<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ShiftModel;

class Shift extends ResourceController
{
    public function read()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->read();
        echo json_encode($data);
    }

    public function checkin()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->checkIn();
        echo json_encode($data);
    }

    public function checkOut()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->checkOut();
        echo json_encode($data);
    }

    public function ischeckedin()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->isCheckedIn();
        echo json_encode($data);
    }

    // fungsi2 lama saat masih ReePOS

    public function check()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->checkLastShiftStatus();
        echo json_encode($data);
    }

    public function log()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->logShift();
        echo json_encode($data);
    }

    public function logclose()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->logCloseShift();
        echo json_encode($data);
    }

    public function updatemodal()
    {
        $shiftModel = new ShiftModel();
        $data = $shiftModel->updateModal();
        echo json_encode($data);
    }

    public function open()
    {
        return view('opensft_view');
    }

    public function close()
    {
        return view('closesft_view');
    }

    public function view()
    {
        return view('shift_view');
    }
}
