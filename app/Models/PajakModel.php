<?php 
namespace App\Models;
use CodeIgniter\Model;

class PajakModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        
        if (isset($_POST['lok_id']))
            $clause = " WHERE paj_lok_id=".$_POST['lok_id'];
        if (isset($_POST['key_val']))
            $clause .= " AND (paj_nama LIKE '%".$_POST['key_val']."%')";
        $query = $db->query("SELECT * FROM pos_pajak".$clause." ORDER BY paj_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Pajak';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
    
    function read_count()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        if (isset($_POST['lok_id']))
            $clause = " WHERE paj_lok_id=".$_POST['lok_id'];
        $query = $db->query("SELECT count(*) total FROM pos_pajak".$clause." ORDER BY paj_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getRow();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Pajak';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function search()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $qClause = "";
        if (isset($_POST['q']))
            $qClause = " AND paj_nama LIKE '%".$_POST['q']."%'";
        $queryStr = "SELECT * FROM pos_pajak
            WHERE paj_lok_id=".$_POST['lok_id'].$qClause." ORDER BY paj_id DESC";
        $query = $db->query($queryStr);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Cari Data Supplier';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function savePajak()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        if ($data['paj_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(paj_id)+1
            // Dan pencarian ini tidak tergantung lok_id

            $query = $db->query("SELECT paj_id FROM pos_pajak ORDER BY paj_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->paj_id+1;
                    elseif ($available_id == $row->paj_id) $available_id++;
                    else break;
                }
                $data['paj_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO pos_pajak(paj_id) VALUES(".$data['paj_id'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan ID Supplier Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Supplier';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder = $db->table('pos_pajak');
            $builder->set('paj_lok_id', $data['paj_lok_id']);
            $builder->set('paj_nama', $data['paj_nama']);
            $builder->set('paj_value', $data['paj_value']);
            $builder->where('paj_id', $data['paj_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT * FROM pos_pajak
                    WHERE paj_id=".$data['paj_id']);
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data Pajak';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deletePajak()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM pos_pajak WHERE paj_id=".$_POST['paj_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data Pajak';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}