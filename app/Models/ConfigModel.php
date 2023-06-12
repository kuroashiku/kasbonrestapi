<?php 
namespace App\Models;
use CodeIgniter\Model;

class ConfigModel extends Model
{
    public $lokId;

    public function __construct()
    {
        $this->lokId = 12; // default
    }

    public function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';

        if (isset($_POST['kas_id']))
            $query = $db->query("SELECT lok_nama nama, lok_alamat alamat,
                lok_telpon telpon, lok_telpon telp,
                lok_footer1 footer1, lok_footer2 footer2,
                kas_nama fullname, kas_nick username, kas_password password
                FROM rms_lokasi
                LEFT JOIN pos_kasir ON kas_lok_id=lok_id
                WHERE kas_id=".$_POST['kas_id']);
        else
            $query = $db->query("SELECT lok_nama nama, lok_alamat alamat,
                lok_telpon telpon, lok_telpon telp,
                lok_footer1 footer1, lok_footer2 footer2,
                kas_nama fullname, kas_nick username, kas_password password
                FROM rms_lokasi
                LEFT JOIN pos_kasir ON kas_lok_id=lok_id
                WHERE lok_id=".$_POST['lok_id']." AND kas_nama='Admin'");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getRow();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Konfigurasi';
            $result['error']['message'] = $error['message'];
            $result['sql'] = (string)($db->getLastQuery());
        }
        return $result;
    }

    public function updateProfile()
    {
        $db = db_connect();
        $result['status'] = 'success';

        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $query = $db->query("UPDATE rms_lokasi SET
            lok_nama='".$_POST['lok_nama']."',
            lok_alamat='".$_POST['lok_alamat']."',
            lok_telpon='".$_POST['lok_telpon']."',
            lok_footer1='".$_POST['lok_footer1']."',
            lok_footer2='".$_POST['lok_footer2']."',
            lok_tgedit='".date_format($date, 'Y-m-d H:i:s')."'
            WHERE lok_id=".$_POST['lok_id']);
        $query = $db->query("UPDATE pos_kasir SET
            kas_password='".$_POST['kas_password']."',
            kas_tgedit='".date_format($date, 'Y-m-d H:i:s')."'
            WHERE kas_id=".$_POST['kas_id']);
        $error = $db->error();
        if ($error['code'] != 0) {
            $result['error']['title'] = 'Update Data Profil';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;

    }

    public function setLokId($lokId)
    {
        $this->lokId = $lokId;
    }

    public function getLokId()
    {
        return $this->lokId;
    }

    public function read_old()
    {
        $db = db_connect();
        if ($_GET['lok_id']!=0)
            $this->setLokId($_GET['lok_id']);
        $query = $db->query("SELECT lok.*, com_nama, com_rcvdcoa_id, com_rcvkcoa_id
            FROM rms_lokasi lok
            LEFT JOIN rms_company ON com_id=lok_com_id
            WHERE lok_id=".$this->lokId);
        $row = $query->getRow();
        $config['kun_status'] = [[
            'sta_id'    => 'ANTRI',
            'sta_nama'  => 'ANTRI',
            'sta_color' => '#ff0000',
            'sta_bold'  => 1
        ],[
            'sta_id'    => 'DILAYANI',
            'sta_nama'  => 'DILAYANI',
            'sta_color' => '#0060a4',
            'sta_bold'  => 0
        ],[
            'sta_id'    => 'RAWAT INAP',
            'sta_nama'  => 'RAWAT INAP',
            'sta_color' => '#00b81e',
            'sta_bold'  => 0
        ],[
            'sta_id'    => 'BATAL',
            'sta_nama'  => 'BATAL',
            'sta_color' => '#969696',
            'sta_bold'  => 0
        ],[
            'sta_id'    => 'SELESAI',
            'sta_nama'  => 'SELESAI',
            'sta_color' => 'black',
            'sta_bold'  => 0
        ]];
        $config['lok_id'] = $row->lok_id;
        $config['lok_nama'] = $row->lok_nama;
        $config['lok_alamat'] = $row->lok_alamat;
        $config['lok_kodepos'] = $row->lok_kodepos;
        $config['com_id'] = $row->lok_com_id;
        $config['com_nama'] = $row->com_nama;
        $config['com_rcvdcoa_id'] = $row->com_rcvdcoa_id;
        $config['com_rcvkcoa_id'] = $row->com_rcvkcoa_id;

        $data = [];
        $strKlinikList = $row->lok_yan_ids;
        $arrKlinikYan = explode(";", $strKlinikList);
        foreach($arrKlinikYan as $strKlinikYan) {
            $arrKlinik = explode(":", $strKlinikYan);
            if(sizeof($arrKlinik) == 2) {
                $strKlinik = $arrKlinik[0];
                $strYanList = $arrKlinik[1];
                $arrYan = explode(",", $strYanList);
                foreach($arrYan as $strYan) {
                    $query = $db->query("SELECT yan_nama FROM rms_layanan WHERE yan_id=".$strYan);
                    $r = $query->getRow();
                    array_push($data, [
                        'yan_id' => $strYan,
                        'yan_nama' => $r->yan_nama,
                        'yan_klinik' => $strKlinik,
                        'yan_label' => $r->yan_nama." (".$strKlinik.")"
                    ]);
                }
            }
        }
        $jenis = null;
        foreach($data as &$row) {
            $row['jenis'] = floor($row['yan_id']/100);
            if (!$jenis) $jenis = $row['jenis'];
            elseif ($jenis != 3 && $row['jenis'] != $jenis) $jenis = 3;
            if ($row['jenis'] == 1) $row['iconCls'] = 'icon-man';
            else $row['iconCls'] = 'icon-animal';
        }
        $config['lok_jenis'] = $jenis;
        $config['app_nama'] = 'ReePOS';
        $config['layanan'] = $data;
        $config['login_data'] = null;
        return $config;
    }
    
    public function sendFileToTEG($filename, $chat_id) {
        $url = "https://api.telegram.org/".
            "bot1811939945:AAHfUE7saD7i_CgUXuHL--5UcSCulhjRjGw/".
            "sendDocument?chat_id=".$chat_id;
        $url = $url."&document=".$filename;
        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);
    }
    
    public function sendFileToGoogleDrive($filename) {
        //$client = new Google\Client();
    }

    public function backupDatabase()
    {
        $result['status'] = "success";
        $folder = 'public/backup/reenduxs_kasbon/';
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $date->modify('-1 days');
        $filename = $folder.'kasbon-db-'.date_format($date, 'Ymd');
        if (file_exists($filename.'.zip')) {
            $result['status'] = "cancelled";
            return $result;
        }
        $sql = "";
        $db = db_connect();
        $query = $db->query("SHOW TABLES");
        $rows = $query->getResult();
        foreach($rows as $r) {
            $table = $r->Tables_in_reenduxs_kasbon;
            $sql .= "DROP TABLE IF EXISTS ".$table.";\n";

            // ambil script untuk create table
            $query = $db->query("SHOW CREATE TABLE ".$table);
            $row = $query->getRowArray();
            $sql .= $row['Create Table'].";\n";

            // ambil daftar field
            $fieldType = [];
            $fields = "";
            $query = $db->query("DESC ".$table);
            foreach($query->getResult() as $f) {
                if ($fields != "") $fields .= ", ";
                $fields .= $f->Field;
                $fieldType[$f->Field] = $f->Type;
            }
            
            // ambil data record
            $query = $db->query("SELECT * FROM ".$table);
            $values = "";
            $tablerows = $query->getResult();
            foreach($tablerows as $trow) {
                if ($values != "") $values .= ",";
                $values .= "\n   (";
                $valofrec = "";
                foreach($trow as $key=>$r) {
                    if ($valofrec != "") $valofrec .= ", ";
                    $val = "null";
                    if (isset($r)) {
                        if (strstr($fieldType[$key], 'int')||
                            strstr($fieldType[$key], 'decimal'))
                            $val = $r;
                        elseif (strstr($fieldType[$key], 'varchar')||
                            strstr($fieldType[$key], 'datetime')||
                            strstr($fieldType[$key], 'blob'))
                            $val = "'".$r."'";
                        else
                            $val = "'".$r."'";
                    }
                    $valofrec .= $val;
                }
                $values .= $valofrec;
                $values .= ")";
            }
            if ($values != "")
                $sql .= "INSERT INTO ".$table."(".$fields.") VALUES".$values.";\n";
            $sql .= "\n";
        }
        $handle = fopen($filename.'.sql','w');
        fwrite($handle, $sql);
        fclose($handle);
        exec('/usr/bin/zip -j '.$filename.'.zip '.$filename.'.sql');
        
        // pengiriman ke WAG
        // eRDe Backup DB Kasbon, Group ID : -646107799
        
        $this->sendFileToTEG(base_url($filename.'.zip'), "-1001685263849");
        // chat id lama tidak bisa dipakai karena group chat
        // sudah diupgrade ke super group chat. Chat id lama: -646107799);
        
        unlink($filename.'.sql');
        
        return $result;
    }
}