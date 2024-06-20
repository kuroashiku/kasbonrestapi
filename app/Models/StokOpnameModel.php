<?php 
namespace App\Models;
use CodeIgniter\Model;

class StokOpnameModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        
        if (isset($_POST['lok_id']))
            $clause = " WHERE sop_lok_id=".$_POST['lok_id'];

        if (isset($_POST['key_val']))
            $clause .= " AND (sop_nama LIKE '%".$_POST['key_val']."%')";
        $query = $db->query("SELECT * FROM inv_stokopname LEFT JOIN pos_item ON sop_itm_id=itm_id ".$clause." ORDER BY sop_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Stok Opname';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function saveStokOpname()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        if ($data['sop_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(sop_id)+1
            // Dan pencarian ini tidak tergantung lok_id

            $query = $db->query("SELECT sop_id FROM inv_stokopname ORDER BY sop_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->sop_id+1;
                    elseif ($available_id == $row->sop_id) $available_id++;
                    else break;
                }
                $data['sop_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO inv_stokopname(sop_id) VALUES(".$data['sop_id'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan ID Stok Opname Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Stok Opname';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder = $db->table('inv_stokopname');
            $builder->set('sop_lok_id', $data['sop_lok_id']);
            $builder->set('sop_date', $data['sop_date']);
            $builder->set('sop_itm_id', $data['sop_itm_id']);
            $builder->set('sop_qty_satuan_1', $data['sop_qty_satuan_1']);
            $builder->set('sop_qty_satuan_2', $data['sop_qty_satuan_2']);
            $builder->set('sop_qty_satuan_3', $data['sop_qty_satuan_3']);
            $builder->set('sop_ket_satuan_1', $data['sop_ket_satuan_1']);
            $builder->set('sop_ket_satuan_2', $data['sop_ket_satuan_2']);
            $builder->set('sop_ket_satuan_3', $data['sop_ket_satuan_3']);
            $builder->set('sop_status_satuan_1', $data['sop_status_satuan_1']);
            $builder->set('sop_status_satuan_2', $data['sop_status_satuan_2']);
            $builder->set('sop_status_satuan_3', $data['sop_status_satuan_3']);
            $builder->set('sop_status', $data['sop_status']);
            $builder->where('sop_id', $data['sop_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT * FROM inv_stokopname LEFT JOIN pos_item ON sop_itm_id=itm_id 
                    WHERE sop_id=".$data['sop_id']);
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data Stok Opname';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteStokOpname()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM inv_stokopname LEFT JOIN pos_item ON sop_itm_id=itm_id WHERE sop_id=".$_POST['sop_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['error']['title'] = 'Hapus Data Stok Opname';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}