<?php 
namespace App\Models;
use CodeIgniter\Model;

class SdmModel extends Model
{
    function read()
    {
        $db = db_connect();
        $page = isset($_POST['page'])?$_POST['page']:null;
        $rows = isset($_POST['rows'])?$_POST['rows']:null;
        $filter = "sdm_com_id=".$_POST['com_id'];
        if (isset($_POST['key_val']))
            $filter .= " AND sdm_nama LIKE '%".$_POST['key_val']."%'";
        if (isset($_POST['kelamin']) && $_POST['kelamin'])
            $filter .= " AND sdm_kelamin='".$_POST['kelamin']."'";
        if ($page) {
            $query = $db->query("SELECT COUNT(*) total FROM emp_sdm WHERE ".$filter);
            $retval['total'] = $query->getRow('total');
        }
        $queryStr = "SELECT sdm.*, are_nama, jab_nama
            FROM emp_sdm sdm
            LEFT JOIN rms_area ON are_id=sdm_are_id
            LEFT JOIN emp_jabatan ON jab_id=sdm_jab_id
            WHERE ".$filter." ORDER BY sdm_id DESC";
        if ($page)
            $queryStr .= " LIMIT " . ($page - 1) * $rows . "," . $rows;
        $query = $db->query($queryStr);
        $data = $query->getResult();
        if ($page)
            $retval['rows'] = $data;
        else
            $retval = $data;
        return $retval;
    }

    function dokter()
    {
        $db = db_connect();
        $filter = "sdm_com_id=".$_POST['com_id']." AND jab_tipe='D' AND sdm_status='A'";
        if (isset($_POST['kelamin']) && $_POST['kelamin'])
            $filter .= " AND sdm_kelamin='".$_POST['kelamin']."'";
        $queryStr = "SELECT sdm.*, are_nama, jab_nama
            FROM emp_sdm sdm
            LEFT JOIN rms_area ON are_id=sdm_are_id
            LEFT JOIN emp_jabatan ON jab_id=sdm_jab_id
            WHERE ".$filter." ORDER BY sdm_id DESC";
        $query = $db->query($queryStr);
        $data = $query->getResult();
        return $data;
    }

    function search()
    {
        $db = db_connect();
        $qClause = "";
        if (isset($_POST['jab_tipe']))
            $qClause = " AND jab_tipe='".$_POST['jab_tipe']."'";
        if (isset($_POST['q']))
            $qClause = " AND sdm_nama LIKE '%".$_POST['q']."%'";
        $queryStr = "SELECT sdm.* FROM emp_sdm sdm
            LEFT JOIN emp_jabatan ON jab_id=sdm_jab_id
            WHERE sdm_status='A' AND sdm_com_id=".$_POST['com_id'].$qClause;
        $query = $db->query($queryStr);
        $data = $query->getResult();
        return $data;
    }

    function beaRead()
    {
        $db = db_connect();
        $qClause = "";
        if (isset($_POST['q']))
            $qClause = " AND sdm_nama LIKE '%".$_POST['q']."%'";
        $query = $db->query("SELECT sdm_id bea_id, sdm_nama bea_nama,
            IF(sdm_harga, sdm_harga, jab_harga) bea_harga
            FROM emp_sdm
            LEFT JOIN emp_jabatan ON jab_id=sdm_jab_id
            WHERE sdm_status='A' AND jab_tipe='".$_POST['jns_id']."'
                AND sdm_com_id=".$_POST['com_id'].$qClause);
        $data = $query->getResult();
        return $data;
    }

    function saveSdm()
    {
        $db = db_connect();
        $builder = $db->table('emp_sdm');
        $result['status'] = 'success';
        $data = $_POST;
        if ($data['sdm_id'] == '0') { // new record
            $result['msg'] = 'Proses menambah data gagal';

            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(sdm_id)+1
            // Dan pencarian ini tidak tergantung com_id

            $query = $db->query("SELECT sdm_id FROM emp_sdm ORDER BY sdm_id");
            $rows = $query->getResult();
            $available_id = null;
            foreach($rows as $row) {
                if (!$available_id) $available_id = $row->sdm_id+1;
                elseif ($available_id == $row->sdm_id) $available_id++;
                else break;
            }
            $data['sdm_id'] = $available_id;
            $db->query("INSERT INTO emp_sdm(sdm_id) VALUES(".$data['sdm_id'].")");
            $error = $db->error();
            if ($error['code'] == 0)
                $result['msg'] = 'Proses menambah data berhasil';
            else
                $result['status'] = 'failed';
            $result['sql_insert'] = (string)($db->getLastQuery());
        }
        if ($result['status'] == 'success') {
            $builder->set('sdm_com_id', $data['sdm_com_id']);
            $builder->set('sdm_nip', $data['sdm_nip']?$data['sdm_nip']:'null', false);
            $builder->set('sdm_nama', $data['sdm_nama']);
            $builder->set('sdm_kelamin', $data['sdm_kelamin']);
            $builder->set('sdm_status', $data['sdm_status']);
            $builder->set('sdm_alamat', $data['sdm_alamat']);
            $builder->set('sdm_telpon', $data['sdm_telpon']);
            $builder->set('sdm_harga', $data['sdm_harga']);
            $builder->set('sdm_are_id', $data['sdm_are_id']?$data['sdm_are_id']:'null', false);
            $builder->set('sdm_jab_id', $data['sdm_jab_id']?$data['sdm_jab_id']:'null', false);
            $builder->set('sdm_subjabatan', $data['sdm_subjabatan']);
            $builder->where('sdm_id', $data['sdm_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT are_nama, jab_nama
                    FROM emp_sdm sdm
                    LEFT JOIN rms_area ON are_id=sdm_are_id
                    LEFT JOIN emp_jabatan ON jab_id=sdm_jab_id
                    WHERE sdm_id=".$data['sdm_id']);
                $row = $query->getRow();
                $data['are_nama'] = $row->are_nama;
                $data['jab_nama'] = $row->jab_nama;
                $result['msg'] = 'Proses menyimpan data berhasil';
            }
            else {
                $result['msg'] = 'Proses menyimpan data gagal';
                $result['status'] = 'failed';
            }
            $result['sql'] = (string)($db->getLastQuery());
        }
        $result['data'] = $data;
        return $result;
    }

    function changeStatus()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT sdm_status FROM emp_sdm WHERE sdm_id=".$_POST['sdm_id']);
        $row = $query->getRow();
        if ($row) {
            if ($row->sdm_status == 'A')
                $new_status = 'N';
            else
                $new_status = 'A';
            $db->query("UPDATE emp_sdm SET sdm_status='".$new_status."'
                WHERE sdm_id=".$_POST['sdm_id']);
            $error = $db->error();
            if ($error['code'] != 0) {
                $result['msg'] = 'Gagal saat mengubah status';
                $result['error'] = $error;
            }
            else {
                $result['status'] = 'success';
                $result['new_status'] = $new_status;
            }
        }
        return $result;
    }

    function deleteSdm()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM emp_sdm WHERE sdm_id=".$_POST['sdm_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else
            $result['errmsg'] = 'Gagal menghapus data. Kemungkinan pegawai '.
                'tersebut sudah ditugaskan dalam salah satu transaksi';
        $result['sql'] = (string)($db->getLastQuery());
        $result['error'] = $error;
        return $result;
    }
}