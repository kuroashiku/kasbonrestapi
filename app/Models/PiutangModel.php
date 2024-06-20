<?php 
namespace App\Models;
use CodeIgniter\Model;

class PiutangModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $order = 'DESC';
        if (isset($_POST['ascending']) && $_POST['ascending']=='yes')
            $order = 'ASC';
        $query = $db->query("SELECT c.*, byr_nama FROM pos_cicilan c
            LEFT JOIN pos_carabayar ON byr_kode=cil_carabayar
            WHERE cil_not_id='".$_POST['not_id']."' ORDER BY cil_tanggal ".$order);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $query = $db->query("SELECT not_id id, not_nomor nomor, not_tanggal tanggal,
                kas_nama, cus_nama, cus_wa, not_total total, not_dibayar dp,
                not_kembalian kurang, not_jatuhtempo jatuhtempo
                FROM pos_nota
                LEFT JOIN pos_kasir ON kas_id=not_kas_id
                LEFT JOIN pos_customer ON cus_id=not_cus_id
                WHERE not_id='".$_POST['not_id']."'");
            $error = $db->error();
            if ($error['code'] == 0) {
                $result['nota'] = $query->getRow();
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Baca Data Nota';
                $result['error']['message'] = $error['message'];
            }
        }
        else {
            $result['error']['title'] = 'Baca Data Piutang';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function getNewPayment()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT cil_sisa, cil_bunga FROM pos_cicilan
            WHERE cil_not_id='".$_POST['not_id']."'
            ORDER BY cil_id DESC LIMIT 1");
        $error = $db->error();
        if ($error['code'] == 0) {
            $row = $query->getRow();
            if ($row) {
                $result['kekurangan'] = $row->cil_sisa;
                $result['bunga'] = $row->cil_bunga;
                $result['status'] = 'success';
            }
            else {
                $query = $db->query("SELECT ABS(not_kembalian) kekurangan FROM pos_nota
                    WHERE not_id='".$_POST['not_id']."'");
                $error = $db->error();
                if ($error['code'] == 0) {
                    $row = $query->getRow();
                    $result['kekurangan'] = $row->kekurangan;
                    $result['bunga'] = 0;
                    $result['status'] = 'success';
                }
                else {
                    $result['error']['title'] = 'Baca Data Nota';
                    $result['error']['message'] = $error['message'];
                }
            }
        }
        else {
            $result['error']['title'] = 'Baca Data Piutang';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function savePiutang()
    {
        $db = db_connect();
        $builder = $db->table('pos_cicilan');
        $result['status'] = 'success';
        $result['data'] = [];
        if ($_POST['cil_id'] == -1) { // new record
            // generate id dengan format YYYYnnnnnnnnnn
            // dalam setahun dialokasikan sebanyak 9,999,999,999 transaksi
            // pos_cicilan ini adalah kumpulan semua cicilan
            // tidak unique berdasar com_id atau lok_id

            $query = $db->query("SELECT MAX(cil_id) max_id,
                DATE_FORMAT(rbs_currentdate(), '%Y') yearof_id
                FROM pos_cicilan
                WHERE LEFT(cil_id,4)=DATE_FORMAT(rbs_currentdate(), '%Y')");
            $error = $db->error();
            if ($error['code'] == 0) {
                $row = $query->getRow();
                if (!$row->max_id) $new_id = $row->yearof_id."0000000001";
                else $new_id = $row->yearof_id.
                    sprintf("%010d",((int)substr($row->max_id,4,10))+1);
                $_POST['cil_id'] = $new_id;
                $db->query("INSERT INTO pos_cicilan(cil_id, cil_not_id, cil_tanggal,
                    cil_kekurangan) VALUES('".$_POST['cil_id']."', '".
                    $_POST['cil_not_id']."', rbs_currentdate(), ".$_POST['cil_kekurangan'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan Piutang Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Piutang Baru';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $db->query("UPDATE pos_cicilan SET cil_bunga=".$_POST['cil_bunga'].",
                cil_tagihan=".$_POST['cil_tagihan'].",
                cil_cicilan=".$_POST['cil_cicilan'].",
                cil_carabayar='".$_POST['cil_carabayar']."',
                cil_sisa=".$_POST['cil_sisa']."
                WHERE cil_id='".$_POST['cil_id']."'");
            $error = $db->error();
            $result['sql'] = (string)($db->getLastQuery());
            if ($error['code'] == 0) {
                $_POST['not_id'] = $_POST['cil_not_id'];
                $temp = $this->read();
                $result['data'] = $temp['data'];
            }
            else {
                $result['error']['title'] = 'Update Data Piutang';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deletePiutang()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM pos_cicilan WHERE cil_id=".$_POST['cil_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data Piutang';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}