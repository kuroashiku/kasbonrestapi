<?php 
namespace App\Models;
use CodeIgniter\Model;

class ShiftModel extends Model
{
    function read()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $result['tanggal'] = date_format($date, 'd-m-Y');
        $query = $db->query("SELECT mod_id, mod_kas_id, mod_status, mod_awal, mod_akhir,
            mod_checkin,
            DATE_FORMAT(mod_checkin,'%d-%m-%Y %H:%i:%s') formattedcheckin,
            DATE_FORMAT(mod_checkout,'%d-%m-%Y %H:%i:%s') mod_checkout FROM pos_modal
            WHERE mod_kas_id=".$_POST['kas_id']." AND mod_checkin BETWEEN
            STR_TO_DATE('".date('Y-m-d',strtotime('-5 days'))." 00:00:00',
            '%Y-%m-%d %H:%i:%s') AND
            STR_TO_DATE('".date_format($date,'Y-m-d')." 23:59:59','%Y-%m-%d %H:%i:%s')
            ORDER BY mod_checkin DESC");
        $result['sql'] = (string)($db->getLastQuery());
        $error = $db->error();
        if ($error['code'] == 0) {
            $rows = $query->getResult();
            foreach($rows as &$row)
                $row->mod_checkin = $row->formattedcheckin;
            $result['data'] = $rows;
        }
        else {
            $result['error']['title'] = 'Baca Data Shift';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }

    function checkIn()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $date = date_create(null, timezone_open("Asia/Jakarta"));

        // generate id dengan format YYYYnnnnnnnnnn
        // dalam setahun dialokasikan sebanyak 9,999,999,999 transaksi
        // pos_modal ini adalah kumpulan semua shift
        // tidak unique berdasar com_id atau lok_id

        $query = $db->query("SELECT MAX(mod_id) max_id, ".
            date_format($date, 'Y')." yearof_id
            FROM pos_modal
            WHERE LEFT(mod_id,4)=".date_format($date, 'Y'));
        $error = $db->error();
        if ($error['code'] == 0) {
            $row = $query->getRow();
            if (!$row->max_id) $new_id = $row->yearof_id."0000000001";
            else $new_id = $row->yearof_id.
                sprintf("%010d",((int)substr($row->max_id,4,10))+1);
            $db->query("INSERT INTO pos_modal(mod_id,mod_kas_id,mod_checkin,mod_awal,
            mod_checkout,mod_akhir,mod_status) VALUES('".$new_id."',".$_POST['kas_id'].",
            '".date_format($date, 'Y-m-d H:i:s')."',".$_POST['mod_awal'].
            ",null,null,'CHECKEDIN')");
            $error = $db->error();
            if ($error['code'] == 0) {
                $result = $this->read();
            }
            else {
                $result['error']['title'] = 'Simpan Shift';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        else {
            $result['error']['title'] = 'Menghitung ID Shift';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }

    function checkOut()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $date = date_create(null, timezone_open("Asia/Jakarta"));

        $db->query("UPDATE pos_modal SET mod_checkout='".
            date_format($date, 'Y-m-d H:i:s')."', mod_akhir=".$_POST['mod_akhir'].",
            mod_status='CHECKEDOUT' WHERE mod_kas_id='".
            $_POST['kas_id']."' AND mod_status='CHECKEDIN'");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result = $this->read();
        }
        else {
            $result['error']['title'] = 'Simpan Shift';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }
    
    function isCheckedIn()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT COUNT(*) total FROM pos_modal
            WHERE mod_kas_id=".$_POST['kas_id']." AND mod_status='CHECKEDIN'");
        if ($query->getRow()->total == 0) {
            $result['error']['title'] = 'Point Of Sale';
            $result['error']['message'] = 'Harus Check-In dulu untuk membuka menu ini. '.
                'Untuk Check-In ada di menu Shift Kasir';
            $result['status'] = 'failed';
        }
        return $result;
    }

    // fungsi2 lama saat masih ReePOS

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
            DATE_FORMAT(rbs_currentdate(), '%Y') yearof_id
            FROM pos_shift
            WHERE LEFT(sft_id,4)=DATE_FORMAT(rbs_currentdate(), '%Y')");
        $row = $query->getRow();
        if (!$row->max_id) $new_id = $row->yearof_id."00000001";
        else $new_id = $row->yearof_id.sprintf("%08d",((int)substr($row->max_id,4,8))+1);
        $db->query("INSERT INTO pos_shift (sft_id, sft_username, sft_kasir, sft_admin,
            sft_lok_id, sft_checkin, sft_modalawal, sft_status)
            VALUES ('".$new_id."','".$_POST['username']."','".$_POST['kasir']."',".
            $_POST['admin'].",".$_POST['lok_id'].",rbs_currentdate(),".$_POST['modalawal'].",'OPEN')");
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

        $db->query("UPDATE pos_shift SET sft_checkout=rbs_currentdate(),".$strModalAkhir."
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