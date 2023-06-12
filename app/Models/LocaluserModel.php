<?php 
namespace App\Models;
use CodeIgniter\Model;

class LocaluserModel extends Model
{
    function signup()
    {
        $db = db_connect();
        $result['status'] = 'success';

        $query = $db->query("SELECT COUNT(*) total FROM pos_localuser
            WHERE usr_username='".$_POST['usr_username']."'");
        $error = $db->error();
        if ($error['code'] == 0) {
            if ($query->getRow()->total == 0) {
                $query = $db->query("SELECT MAX(usr_id)+1 next_id FROM pos_localuser");
                $error = $db->error();
                if ($error['code'] == 0) {
                    $row = $query->getRow();
                    $usr_id = $row->next_id?$row->next_id:1;
                    $date = date_create(null, timezone_open("Asia/Jakarta"));
                    $db->query("INSERT INTO pos_localuser(usr_id, usr_nama, usr_alamat,
                        usr_telp, usr_username, usr_password, usr_footer1, usr_footer2,
                        usr_tgregistrasi)
                        VALUES(".$usr_id.",
                        '".$_POST['usr_nama']."', '".$_POST['usr_alamat']."',
                        '".$_POST['usr_telp']."', '".$_POST['usr_username']."',
                        '".$_POST['usr_footer1']."', '".$_POST['usr_footer2']."',
                        '".$_POST['usr_password']."',
                        '".date_format($date, 'Y-m-d H:m:s')."')");
                    $error = $db->error();
                    if ($error['code'] != 0) {
                        $result['error']['title'] = 'Simpan Data Registrasi';
                        $result['error']['message'] = $error['message'];
                        $result['status'] = 'failed';
                    }
                }
                else {
                    $result['error']['title'] = 'Pembuatan ID Registrasi';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Pemeriksaan Username Registrasi';
                $result['error']['message'] = 'Username "'.
                    $_POST['usr_username'].'" sudah dipakai';
                $result['status'] = 'failed';
            }
        }
        else {
            $result['error']['title'] = 'Pemeriksaan Username Registrasi';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }

    function login()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT * FROM pos_localuser
            WHERE usr_username='".$_POST['username']."'
            AND usr_password=MD5('".$_POST['password']."')");
        $error = $db->error();
        if ($error['code'] == 0) {
            $row = $query->getRow();
            if ($row) {
                $result['data'] = $row;
                $date = date_create(null, timezone_open("Asia/Jakarta"));
                $db->query("UPDATE pos_localuser
                    SET usr_tglogin='".date_format($date, 'Y-m-d H:m:s')."'
                    WHERE usr_username='".$_POST['username']."'");
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Login';
                $result['error']['message'] = 'Username atau password salah. '+
                    'Jika anda lupa password satu-satunya solusi saat ini adalah '+
                    'membuat akun baru lagi. Mohon maaf atas ketidaknyamanannya.';
            }
        }
        else {
            $result['error']['title'] = 'Login';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function updateProfile()
    {
        $db = db_connect();
        $result['status'] = 'success';

        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $query = $db->query("UPDATE pos_localuser SET usr_nama='".$_POST['usr_nama']."',
            usr_alamat='".$_POST['usr_alamat']."', usr_telp='".$_POST['usr_telp']."',
            usr_footer1='".$_POST['usr_footer1']."',
            usr_footer2='".$_POST['usr_footer2']."',
            usr_password='".$_POST['usr_password']."',
            usr_tgedit='".date_format($date, 'Y-m-d H:i:s')."'
            WHERE usr_username='".$_POST['usr_username']."'");
        $error = $db->error();
        if ($error['code'] == 0) {
            $query = $db->query("SELECT * FROM pos_localuser
                WHERE usr_username='".$_POST['usr_username']."'");
            $row = $query->getRow();
            $result['data'] = $row;
        }
        else {
            $result['error']['title'] = 'Update Data Profil';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }
}