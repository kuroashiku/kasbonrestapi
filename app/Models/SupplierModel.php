<?php 
namespace App\Models;
use CodeIgniter\Model;

class SupplierModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        if (isset($_POST['com_id']))
            $clause = " WHERE sup_com_id=".$_POST['com_id'];
        $query = $db->query("SELECT * FROM inv_supplier".$clause." ORDER BY sup_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Supplier';
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
            $qClause = " AND sup_nama LIKE '%".$_POST['q']."%'";
        $queryStr = "SELECT * FROM inv_supplier
            WHERE sup_com_id=".$_POST['com_id'].$qClause." ORDER BY sup_id DESC";
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

    function saveSupplier()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        if ($data['sup_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(sup_id)+1
            // Dan pencarian ini tidak tergantung com_id

            $query = $db->query("SELECT sup_id FROM inv_supplier ORDER BY sup_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->sup_id+1;
                    elseif ($available_id == $row->sup_id) $available_id++;
                    else break;
                }
                $data['sup_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO inv_supplier(sup_id) VALUES(".$data['sup_id'].")");
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
            $builder = $db->table('inv_supplier');
            $builder->set('sup_com_id', $data['sup_com_id']);
            $builder->set('sup_nama', $data['sup_nama']);
            $builder->set('sup_alamat', $data['sup_alamat']);
            $builder->set('sup_wa', $data['sup_wa']);
            $builder->where('sup_id', $data['sup_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT * FROM inv_supplier
                    WHERE sup_id=".$data['sup_id']);
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data Supplier';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteSupplier()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM inv_supplier WHERE sup_id=".$_POST['sup_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data Supplier';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}