<?php 
namespace App\Models;
use CodeIgniter\Model;

class GeneralSettingModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        
        if (isset($_POST['lok_id']))
            $clause = " WHERE gen_lok_id=".$_POST['lok_id'];

        if (isset($_POST['key_val']))
            $clause .= " AND (gen_nama LIKE '%".$_POST['key_val']."%')";
        $query = $db->query("SELECT * FROM pos_general_setting ".$clause." ORDER BY gen_id DESC");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getRow();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data General Setting';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function saveGeneralSetting()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        if ($data['gen_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(gen_id)+1
            // Dan pencarian ini tidak tergantung lok_id

            $query = $db->query("SELECT gen_id FROM pos_general_setting ORDER BY gen_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->gen_id+1;
                    elseif ($available_id == $row->gen_id) $available_id++;
                    else break;
                }
                $data['gen_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO pos_general_setting(gen_id) VALUES(".$data['gen_id'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan ID General Setting Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID General Setting';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder = $db->table('pos_general_setting');
            $builder->set('gen_lok_id', $data['gen_lok_id']);
            $builder->set('gen_set_max_draft', $data['gen_set_max_draft']);
            $builder->set('gen_set_max_piutang', $data['gen_set_max_piutang']);
            $builder->set('gen_set_scan_mode', $data['gen_set_scan_mode']);
            $builder->set('gen_set_dp_0', $data['gen_set_dp_0']);
            $builder->set('gen_set_auto_logout', $data['gen_set_auto_logout']);
            $builder->set('gen_set_lok_type', $data['gen_set_lok_type']);
            $builder->set('gen_set_resto_type', $data['gen_set_resto_type']);
            $builder->where('gen_id', $data['gen_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT * FROM pos_general_setting
                    WHERE gen_id=".$data['gen_id']);
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data General Setting';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }
}