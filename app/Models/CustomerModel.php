<?php 
namespace App\Models;
use CodeIgniter\Model;

class CustomerModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        if (isset($_POST['com_id']))
            $clause = " WHERE cus_com_id=".$_POST['com_id'];
        $query = $db->query("SELECT * FROM pos_customer".$clause." ORDER BY cus_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Pelanggan';
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
            $qClause = " AND cus_nama LIKE '%".$_POST['q']."%'";
        $queryStr = "SELECT * FROM pos_customer
            WHERE cus_com_id=".$_POST['com_id'].$qClause." ORDER BY cus_id DESC";
        $query = $db->query($queryStr);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Cari Data Pelanggan';
            $result['error']['message'] = $queryStr; // $error['message'];
        }
        return $result;
    }

    function saveCustomer()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        if ($data['cus_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(cus_id)+1
            // Dan pencarian ini tidak tergantung com_id

            $query = $db->query("SELECT cus_id FROM pos_customer ORDER BY cus_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->cus_id+1;
                    elseif ($available_id == $row->cus_id) $available_id++;
                    else break;
                }
                $data['cus_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO pos_customer(cus_id) VALUES(".$data['cus_id'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan ID Pelanggan Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Pelanggan';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder = $db->table('pos_customer');
            $builder->set('cus_com_id', $data['cus_com_id']);
            $builder->set('cus_nama', $data['cus_nama']);
            $builder->set('cus_wa', $data['cus_wa']);
            $builder->where('cus_id', $data['cus_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT * FROM pos_customer
                    WHERE cus_id=".$data['cus_id']);
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data Pelanggan';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteCustomer()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM pos_customer WHERE cus_id=".$_POST['cus_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data Pelanggan';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}