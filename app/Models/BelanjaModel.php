<?php 
namespace App\Models;
use CodeIgniter\Model;

class BelanjaModel extends Model
{
    function read()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT *,
            DATE_FORMAT(bel_tanggal,'%d-%m-%Y') formattedtgl
            FROM pos_belanja
            WHERE YEAR(bel_tanggal)=".$_POST['thn']." AND
            MONTH(bel_tanggal)=".$_POST['bln']." AND
            bel_lok_id=".$_POST['lok_id']." ORDER BY bel_tanggal DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $rows = $query->getResult();
            $result['data'] = $rows;
        }
        else {
            $result['error']['title'] = 'Baca Data Belanja';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }

    function saveBelanja()
    {
        $db = db_connect();
        $result['status'] = 'success';
        if ($_POST['bel_id'] == 0) { // new record
            // generate id dengan format YYYYnnnnnnnnnnn
            // dalam setahun dialokasikan sebanyak 99,999,999,999 transaksi
            // pos_belanja ini adalah kumpulan semua belanja
            // tidak unique berdasar com_id atau lok_id

            $query = $db->query("SELECT MAX(bel_id) max_id,
                DATE_FORMAT(rbs_currentdate(), '%Y') yearof_id
                FROM pos_belanja
                WHERE LEFT(bel_id,4)=DATE_FORMAT(rbs_currentdate(), '%Y')");
            $error = $db->error();
            if ($error['code'] == 0) {
                $row = $query->getRow();
                if (!$row->max_id) $new_id = $row->yearof_id."00000000001";
                else $new_id = $row->yearof_id.
                    sprintf("%011d",((int)substr($row->max_id,4,11))+1);
                $_POST['bel_id'] = $new_id;
                $db->query("INSERT INTO pos_belanja(bel_id, bel_lok_id)
                    VALUES('".$_POST['bel_id']."', ".$_POST['bel_lok_id'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan Pengeluaran Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Pengeluaran Baru';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $db->query("UPDATE pos_belanja SET
                bel_tanggal='".$_POST['bel_tanggal']."',
                bel_deskripsi='".$_POST['bel_deskripsi']."',
                bel_jumlah=".$_POST['bel_jumlah']."
                WHERE bel_id='".$_POST['bel_id']."'");
            $error = $db->error();
            if ($error['code'] != 0) {
                $result['error']['title'] = 'Update Data Pengeluaran';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteBelanja()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM pos_belanja WHERE bel_id=".$_POST['bel_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data Pengeluaran';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}