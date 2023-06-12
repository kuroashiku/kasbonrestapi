<?php 
namespace App\Models;
use CodeIgniter\Model;

class ShiftModel extends Model
{
    function read()
    {
        $db = db_connect();
        $page = isset($_POST['page'])?$_POST['page']:null;
        $rows = isset($_POST['rows'])?$_POST['rows']:null;
        $filter = "sft_lok_id=".$_POST['lok_id'];
        if (isset($_POST['key_val']))
            $filter .= " AND (DATE_FORMAT(sft_checkin, '%Y%m%d')='".
                $_POST['key_val']."' OR sft_kasir LIKE '%".
                $_POST['key_val']."%')";
        if ($page) {
            $query = $db->query("SELECT COUNT(*) total FROM pos_shift WHERE ".$filter);
            $result['total'] = $query->getRow('total');
        }
        $queryStr = "SELECT *,
            DATE_FORMAT(CONVERT_TZ(sft_checkin, @@global.time_zone, '+07:00'),
            '%d-%m-%Y %H:%i') sft_checkinwib,
            DATE_FORMAT(CONVERT_TZ(sft_checkout, @@global.time_zone, '+07:00'),
            '%d-%m-%Y %H:%i') sft_checkoutwib
            FROM pos_shift
            WHERE ".$filter." ORDER BY sft_id DESC";
        if ($page)
            $queryStr .= " LIMIT " . ($page - 1) * $rows . "," . $rows;
        $query = $db->query($queryStr);
        $data = $query->getResult();
        if ($page)
            $result['rows'] = $data;
        else
            $result = $data;
        return $result;
    }

    function checkLastShiftStatus()
    {
        $db = db_connect();
        $result['status'] = 'empty';
        $query = $db->query("SELECT * FROM pos_shift
            WHERE sft_lok_id=".$_POST['lok_id']."
            ORDER BY sft_id DESC");
        $rows = $query->getResult();
        foreach($rows as $r) {
            $result['sft_id'] = $r->sft_id;
            $result['kasir']  = $r->sft_kasir;
            $result['status'] = $r->sft_status;
            break;
        }
        return $result;
    }

    function logShift()
    {
        $db = db_connect();
        $result['status'] = 'failed';

        // generate id dengan format YYYYnnnnnnnnn
        $query = $db->query("SELECT MAX(sft_id) max_id,
            DATE_FORMAT(NOW(), '%Y') yearof_id
            FROM pos_shift
            WHERE LEFT(sft_id,4)=DATE_FORMAT(NOW(), '%Y')");
        $row = $query->getRow();
        if (!$row->max_id) $new_id = $row->yearof_id."00000001";
        else $new_id = $row->yearof_id.sprintf("%08d",((int)substr($row->max_id,4,8))+1);
        $db->query("INSERT INTO pos_shift (sft_id, sft_username, sft_kasir, sft_admin,
            sft_lok_id, sft_checkin, sft_modalawal, sft_status)
            VALUES ('".$new_id."','".$_POST['username']."','".$_POST['kasir']."',".
            $_POST['admin'].",".$_POST['lok_id'].",NOW(),".$_POST['modalawal'].",'OPEN')");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['sft_id'] = $new_id;
            $result['status'] = 'success';
        }
        return $result;
    }

    function logCloseShift()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $strModalAkhir = '';
        if (isset($_POST['sft_modalakhir']))
            $strModalAkhir = "sft_modalakhir=".$_POST['sft_modalakhir'].",";

        $db->query("UPDATE pos_shift SET sft_checkout=NOW(),".$strModalAkhir."
            sft_status='CLOSE' WHERE sft_id='".$_POST['sft_id']."'");
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else
            $result['error'] = $error;
        return $result;
    }

    function updateModal()
    {
        $db = db_connect();
        $result['status'] = 'failed';

        $db->query("UPDATE pos_shift SET sft_modalawal=".$_POST['sft_modalawal'].",
            sft_modalakhir=".$_POST['sft_modalakhir']."
            WHERE sft_id='".$_POST['sft_id']."'");
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else
            $result['error'] = $error;
        return $result;
    }
}