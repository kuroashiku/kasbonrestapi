<?php 
namespace App\Models;
use CodeIgniter\Model;

class NotificationModel extends Model
{
    public function getFlag()
    {
        $result['status'] = 'failed';
        $file = 'public/notification/'.md5('kas_id:'.$_POST['kas_id']);
        if (file_exists($file)) {
            $handle = fopen($file, 'r');
            $result['value'] = fgets($handle);
            $result['status'] = 'success';
            fclose($handle);
        }
        return $result;
    }

    public function setFlag()
    {
        if(!isset($_POST['kas_id'])) $_POST = $_GET;
        $result['status'] = 'success';
        $file = 'public/notification/'.md5('kas_id:'.$_POST['kas_id']);
        $handle = fopen($file, 'w');
        mt_srand(mktime());
        fwrite($handle, mt_rand());
        fclose($handle);
        return $result;
    }

    public function push($db, $nota, $user)
    {
        $result['status'] = 'failed';
        $query = $db->query("SELECT kas_id FROM pos_kasir
            WHERE kas_lok_id=".$user->kas_lok_id." AND kas_nama='Admin'");
        $adm = $query->getRow();
        $db->query("INSERT INTO pos_notifikasi(nti_kas_id,nti_tanggal,
            nti_title,nti_message) VALUES(".$adm->kas_id.",'".$nota->not_tanggal."',
            'Transaksi penjualan','".$user->kas_nama.": Rp.".
            number_format($nota->not_total,2)."')");
        $_POST['kas_id'] = $adm->kas_id;
        $this->setFlag();
    }

    public function pull()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT * FROM pos_notifikasi
            WHERE nti_kas_id=".$_POST['kas_id']." ORDER BY nti_tanggal");
        $rows = $query->getResult();
        $notif = [];
        foreach($rows as $row) {
            $message = [
                'title' => $row->nti_title." ".$row->nti_tanggal,
                'message' => $row->nti_message
            ];
            array_push($notif, $message);
        }
        $result['notif'] = $notif;
        $db->query("DELETE FROM pos_notifikasi WHERE nti_kas_id=".$_POST['kas_id']);
        return $result;
    }
}