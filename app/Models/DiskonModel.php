<?php 
namespace App\Models;
use CodeIgniter\Model;

class DiskonModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        
        if (isset($_POST['lok_id']))
            $clause = " WHERE dis_lok_id=".$_POST['lok_id'];
        if (isset($_POST['dis_global'])&&$_POST['dis_global']=='true')
            $clause .= " AND dis_global='".$_POST['dis_global']."'";
        if (!isset($_POST['dis_global']))
            $clause .= " AND dis_global IS NULL";
        if (isset($_POST['key_val']))
            $clause .= " AND (dis_nama LIKE '%".$_POST['key_val']."%')";
        $query = $db->query("SELECT * FROM pos_diskon".$clause." ORDER BY dis_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
             $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Diskon';
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
            $clause = " WHERE dis_lok_id=".$_POST['lok_id'];
        $query = $db->query("SELECT count(*) total FROM pos_diskon".$clause." ORDER BY dis_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getRow();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data diskon';
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
            $qClause = " AND dis_nama LIKE '%".$_POST['q']."%'";
        $queryStr = "SELECT * FROM pos_diskon
            WHERE dis_lok_id=".$_POST['lok_id'].$qClause." ORDER BY dis_id DESC";
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

    function saveDiskon()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        if ($data['dis_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(dis_id)+1
            // Dan pencarian ini tidak tergantung lok_id

            $query = $db->query("SELECT dis_id FROM pos_diskon ORDER BY dis_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->dis_id+1;
                    elseif ($available_id == $row->dis_id) $available_id++;
                    else break;
                }
                $data['dis_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO pos_diskon(dis_id) VALUES(".$data['dis_id'].")");
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
            $builder = $db->table('pos_diskon');
            $builder->set('dis_lok_id', $data['dis_lok_id']);
            $builder->set('dis_nama', $data['dis_nama']);
            $builder->set('dis_value', $data['dis_value']);
            $builder->set('dis_nominal', $data['dis_nominal']);
            $builder->set('dis_global', $data['dis_global']);
            $builder->where('dis_id', $data['dis_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT * FROM pos_diskon
                    WHERE dis_id=".$data['dis_id']);
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data diskon';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteDiskon()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM pos_diskon WHERE dis_id=".$_POST['dis_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data diskon';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}