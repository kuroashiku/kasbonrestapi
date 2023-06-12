<?php 
namespace App\Models;
use CodeIgniter\Model;

class UsermanModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT com_paket FROM rms_company
            LEFT JOIN rms_lokasi ON lok_com_id=com_id
            WHERE lok_id=".$_POST['lok_id']);
        $paket = $query->getRow()->com_paket;
        $query = $db->query("SELECT * FROM pos_fufi
            WHERE INSTR(fun_tipe,'".$paket."')");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['fufi'] = $query->getResult();
            $query = $db->query("SELECT COUNT(*) total FROM pos_role
                WHERE rol_lok_id=".$_POST['lok_id']);
            if ($query->getRow()->total == 0) {
                $query = $db->query("SELECT * FROM pos_role WHERE rol_lok_id=0");
                $rows = $query->getResult();
                foreach($rows as $row) {
                    $query = $db->query("SELECT MAX(rol_id) as maxid
                        FROM pos_role");
                    $db->query("INSERT INTO pos_role(rol_id,rol_lok_id,rol_nama,
                        rol_fun_kode) VALUES(".(string)($query->getRow()->maxid+1).
                        ",".$_POST['lok_id'].",'".$row->rol_nama."','".
                        $row->rol_fun_kode."')");
                }
            }
            $result['role'] = $this->readRole();
            $result['user'] = $this->readUser();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Modul';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function readRole()
    {
        $db = db_connect();
        $query = $db->query("SELECT * FROM pos_role
            WHERE rol_lok_id=".$_POST['lok_id']);
        $rows = $query->getResult();
        foreach($rows as &$row) {
            $rol_fun_kode = explode(',', $row->rol_fun_kode);
            $row->rol_fun_kode = $rol_fun_kode;
        }
        return $rows;
    }

    function readUser()
    {
        $db = db_connect();
        $query = $db->query("SELECT kas_id, kas_nama, kas_nick, kas_role, kas_mintrans
            FROM pos_kasir WHERE kas_lok_id=".$_POST['lok_id']);
        $rows = $query->getResult();
        return $rows;
    }

    function saveRoleFunCode()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $rol_fun_kode = '';
        foreach($_POST['rol_fun_kode'] as $kode) {
            if ($rol_fun_kode != '') $rol_fun_kode .= ',';
            $rol_fun_kode .= $kode;
        }
        $db->query("UPDATE pos_role SET rol_fun_kode='".$rol_fun_kode."'
            WHERE rol_id=".$_POST['rol_id']);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['role'] = $this->readRole();
        }
        else {
            $result['error']['title'] = 'Update Role';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }

    function saveRoleName()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $nama = strtoupper($_POST['rol_nama']);

        // cek apakah nama role sudah ada kembarannya?
        $query = $db->query("SELECT COUNT(*) total FROM pos_role
            WHERE rol_lok_id=".$_POST['lok_id']." AND
            rol_nama='".$nama."'");
        if ($query->getRow()->total > 0) {
            $result['error']['title'] = 'Tambah Role';
            $result['error']['message'] = 'Nama role "'.$nama.'" sudah ada';
            $result['status'] = 'failed';
        }
        else {
            $db->query("UPDATE pos_role SET rol_nama='".
                strtoupper($_POST['rol_nama'])."' WHERE rol_id=".$_POST['rol_id']);
            $error = $db->error();
            if ($error['code'] == 0) {
                $result['role'] = $this->readRole();
            }
            else {
                $result['error']['title'] = 'Update Role';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function addRole()
    {
        $db = db_connect();
        $result['status'] = 'success';

        // cek apakah jumlah role untuk lokasi ybs sudah mencapai kuota
        $query = $db->query("SELECT COUNT(*) total FROM pos_role
            WHERE rol_lok_id=".$_POST['lok_id']);
        $nrole = $query->getRow()->total;
        $query = $db->query("SELECT com_nama, com_maxrole FROM rms_company
            LEFT JOIN rms_lokasi ON lok_com_id=com_id
            WHERE lok_id=".$_POST['lok_id']);
        $com_nama = $query->getRow()->com_nama; 
        $maxrole = $query->getRow()->com_maxrole;
        if ($nrole == $maxrole) {
            $result['error']['title'] = 'Tambah Role';
            $result['error']['message'] = 'Jumlah role untuk "'.$com_nama.'" '.
                'maximal '.$maxrole;
            $result['status'] = 'failed';
        }

        // cek apakah nama role sudah ada kembarannya?
        if ($result['status'] == 'success') {
            $query = $db->query("SELECT COUNT(*) total FROM pos_role
                WHERE rol_lok_id=".$_POST['lok_id']." AND
                rol_nama='NEW ROLE'");
            if ($query->getRow()->total > 0) {
                $result['error']['title'] = 'Tambah Role';
                $result['error']['message'] = 'Nama role "NEW ROLE" yang sudah '.
                    'ada harus diedit dulu';
                $result['status'] = 'failed';
            }
        }

        if ($result['status'] == 'success') {
            $query = $db->query("SELECT MAX(rol_id) as maxid
                FROM pos_role");
            $db->query("INSERT INTO pos_role(rol_id,rol_lok_id,rol_nama,
                rol_fun_kode) VALUES(".(string)($query->getRow()->maxid+1).
                ",".$_POST['lok_id'].",'NEW ROLE','')");
            $error = $db->error();
            if ($error['code'] == 0) {
                $result['role'] = $this->readRole();
            }
            else {
                $result['error']['title'] = 'Tambah Role';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteRole()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $query = $db->query("SELECT rol_nama FROM pos_role WHERE rol_id=".
            $_POST['rol_id']);
        $rol_nama = $query->getRow()->rol_nama;
        $query = $db->query("SELECT COUNT(*) total FROM pos_kasir
            WHERE kas_lok_id=".$_POST['lok_id']." AND
            kas_role='".$rol_nama."'");
        $nrole = $query->getRow()->total;
        if ($nrole == 0) {
            $db->query("DELETE FROM pos_role WHERE rol_id=".$_POST['rol_id']);
            $result['role'] = $this->readRole();
        }
        else {
            $result['error']['title'] = 'Hapus Role';
            $result['error']['message'] = 'Role "'.$rol_nama.'" '.
                'sudah dipakai, tidak bisa dihapus';
            $result['status'] = 'failed';
        }
        return $result;
    }

    function saveUser()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $nick = strtolower($_POST['kas_nick']);

        // cek apakah nick user sudah ada kembarannya?
        $query = $db->query("SELECT COUNT(*) total FROM pos_kasir
            WHERE kas_lok_id=".$_POST['lok_id']." AND
            kas_nick='".$nick."' AND kas_id<>".$_POST['kas_id']);
        if ($query->getRow()->total > 0) {
            $result['error']['title'] = 'Tambah User';
            $result['error']['message'] = 'Username "'.$nick.'" sudah ada';
            $result['status'] = 'failed';
        }
        else {
            if(isset($_POST['kas_mintrans']))
                $kas_mintrans = $_POST['kas_mintrans'];
            else
                $kas_mintrans = 10000000;
            $db->query("UPDATE pos_kasir SET
                kas_nama='".$_POST['kas_nama']."',
                kas_nick='".$_POST['kas_nick']."',
                kas_role='".$_POST['kas_role']."',
                kas_mintrans=".$kas_mintrans."
                WHERE kas_id=".$_POST['kas_id']);
            $error = $db->error();
            if ($error['code'] == 0) {
                $result['user'] = $this->readUser();
            }
            else {
                $result['error']['title'] = 'Update User';
                $result['error']['message'] = $error['message'].'. Sql: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function addUser()
    {
        $db = db_connect();
        $result['status'] = 'success';

        // cek apakah jumlah user untuk company ybs sudah mencapai kuota
        $query = $db->query("SELECT COUNT(*) total FROM pos_kasir
            WHERE kas_lok_id=".$_POST['lok_id']);
        $nuser = $query->getRow()->total;
        $query = $db->query("SELECT com_id, com_nama, com_maxuser
            FROM rms_company
            LEFT JOIN rms_lokasi ON lok_com_id=com_id
            WHERE lok_id=".$_POST['lok_id']);
        $com_id = $query->getRow()->com_id;
        $com_nama = $query->getRow()->com_nama; 
        $maxuser = $query->getRow()->com_maxuser;
        if ($nuser == $maxuser) {
            $result['error']['title'] = 'Tambah User';
            $result['error']['message'] = 'Jumlah user untuk "'.$com_nama.'" '.
                'maximal '.$maxuser;
            $result['status'] = 'failed';
        }

        // cek apakah nick user sudah ada kembarannya?
        if ($result['status'] == 'success') {
            $query = $db->query("SELECT COUNT(*) total FROM pos_kasir
                WHERE kas_lok_id=".$_POST['lok_id']." AND
                kas_nick='newuser'");
            if ($query->getRow()->total > 0) {
                $result['error']['title'] = 'Tambah User';
                $result['error']['message'] = 'Username "newuser" yang sudah '.
                    'ada harus diedit dulu';
                $result['status'] = 'failed';
            }
        }

        if ($result['status'] == 'success') {
            $query = $db->query("SELECT rol_nama FROM pos_role
                WHERE rol_lok_id=".$_POST['lok_id']." AND rol_nama<>'ADMIN'
                LIMIT 1");
            $row = $query->getRow();
            if($row) $default_role = $query->getRow()->rol_nama;
            else $default_role = 'ADMIN';
            $query = $db->query("SELECT MAX(kas_id) as maxid
                FROM pos_kasir");
            $db->query("INSERT INTO pos_kasir(kas_id,kas_nama,kas_nick,
                kas_gender,kas_password,kas_lok_id,kas_com_id,kas_role,
                kas_mintrans)
                VALUES(".(string)($query->getRow()->maxid+1).
                ",'New User','newuser','P',MD5('user'),".
                $_POST['lok_id'].",".$com_id.",'".$default_role."',240,0)");
            $error = $db->error();
            if ($error['code'] == 0) {
                $result['user'] = $this->readUser();
            }
            else {
                $result['error']['title'] = 'Tambah User';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function deleteUser()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $query = $db->query("SELECT kas_nama FROM pos_kasir WHERE kas_id=".
            $_POST['kas_id']);
        $kas_nama = $query->getRow()->kas_nama;
        $query = $db->query("SELECT COUNT(*) total FROM pos_nota
            WHERE not_kas_id=".$_POST['kas_id']);
        $n = $query->getRow()->total;
        $query = $db->query("SELECT COUNT(*) total FROM inv_po
            WHERE po_kas_id=".$_POST['kas_id']);
        $n += $query->getRow()->total;
        $query = $db->query("SELECT COUNT(*) total FROM inv_receive
            WHERE rcv_kas_id=".$_POST['kas_id']);
        $n += $query->getRow()->total;
        if ($n == 0) {
            $db->query("DELETE FROM pos_kasir WHERE kas_id=".$_POST['kas_id']);
            $result['user'] = $this->readUser();
        }
        else {
            $result['error']['title'] = 'Hapus User';
            $result['error']['message'] = 'User "'.$kas_nama.'" '.
                'sudah dipakai, tidak bisa dihapus';
            $result['status'] = 'failed';
        }
        return $result;
    }

    function resetPassword()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $db->query("UPDATE pos_kasir SET kas_password=MD5('user')
            WHERE kas_id=".$_POST['kas_id']);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['user'] = $this->readUser();
        }
        else {
            $result['error']['title'] = 'Reset Password';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }
}