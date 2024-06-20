<?php 
namespace App\Models;
use CodeIgniter\Model;

class MejaModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        
        if (isset($_POST['lok_id']))
            $clause = " WHERE mej_lok_id=".$_POST['lok_id'];
        if (isset($_POST['mej_status']))
            $clause = " AND mej_status IS NULL ";
        if (isset($_POST['key_val']))
            $clause .= " AND (mej_nama LIKE '%".$_POST['key_val']."%')";
        $query = $db->query("SELECT * FROM pos_meja".$clause." ORDER BY mej_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Meja';
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
            $clause = " WHERE mej_lok_id=".$_POST['lok_id'];
        $query = $db->query("SELECT count(*) total FROM pos_meja".$clause." ORDER BY mej_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getRow();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Meja';
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
            $qClause = " AND mej_nama LIKE '%".$_POST['q']."%'";
        $queryStr = "SELECT * FROM pos_meja
            WHERE mej_lok_id=".$_POST['lok_id'].$qClause." ORDER BY mej_id DESC";
        $query = $db->query($queryStr);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Cari Data Meja';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function saveMeja()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        if ($data['mej_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(mej_id)+1
            // Dan pencarian ini tidak tergantung lok_id

            $query = $db->query("SELECT mej_id FROM pos_meja ORDER BY mej_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->mej_id+1;
                    elseif ($available_id == $row->mej_id) $available_id++;
                    else break;
                }
                $data['mej_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO pos_meja(mej_id) VALUES(".$data['mej_id'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan ID Meja Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Meja';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder = $db->table('pos_meja');
            $builder->set('mej_lok_id', $data['mej_lok_id']);
            $builder->set('mej_nama', $data['mej_nama']);
            $builder->set('mej_kapasitas', $data['mej_kapasitas']);
            $builder->set('mej_status', $data['mej_status']);
            $builder->where('mej_id', $data['mej_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT * FROM pos_meja
                    WHERE mej_id=".$data['mej_id']);
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data Meja';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteMeja()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM pos_meja WHERE mej_id=".$_POST['mej_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data Meja';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}