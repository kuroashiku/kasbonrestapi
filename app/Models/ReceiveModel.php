<?php 
namespace App\Models;
use CodeIgniter\Model;
use App\Models\PoModel;

class ReceiveModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
            $strSelect = "SELECT rcv_id, rcv_nomor, rcv_tgterima, rcv_tglunas,
                rcv_total, kas_id, kas_nama, sup_nama, rcv_catatan, rcv_status";
        else
            $strSelect = "SELECT rcv_id id, rcv_nomor nomor, rcv_po_id po_id, po_nomor,
                po_tgorder, po_total, sup_id, sup_nama,
                rcv_tgterima tgterima, rcv_tglunas tglunas,
                rcv_total total, kas_id, kas_nama,
                rcv_catatan catatan, rcv_status status, rcv_id, rcv_diskon diskon";
        $strQuery = $strSelect." FROM inv_receive
            LEFT JOIN inv_po ON po_id=rcv_po_id
            LEFT JOIN pos_kasir ON kas_id=rcv_kas_id
            LEFT JOIN inv_supplier ON sup_id=po_sup_id
            WHERE rcv_lok_id=".$_POST['lok_id'];
        if (isset($_POST['q'])) {
            $strQuery .= " AND (rcv_catatan LIKE '%".$_POST['q']."%'
                OR rcv_nomor LIKE '%".$_POST['q']."%'
                OR EXISTS(SELECT 1 FROM inv_rcvitem
                LEFT JOIN pos_item ON itm_id=rcvi_itm_id
                WHERE rcvi_rcv_id=rcv_id AND itm_nama LIKE '%".$_POST['q']."%'))";
        }
        elseif (isset($_POST['thn']) && isset($_POST['bln'])) {
            $strQuery .= " AND YEAR(rcv_tgterima)=".$_POST['thn']."
                AND MONTH(rcv_tgterima)=".$_POST['bln'];
            if (isset($_POST['har'])) {
                $strQuery .= " AND DAY(rcv_tgterima)=".$_POST['har'];
            }
        }
        elseif (isset($_POST['id'])) {
            $strQuery .= " AND rcv_id=".$_POST['id'];
        }
        $strQuery .= " ORDER BY rcv_id DESC";
        $query = $db->query($strQuery);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['status'] = 'success';
            $result['data'] = $query->getResult();
            if(isset($_POST['loaditems']) && $_POST['loaditems'] == 'yes') {
                if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
                    $strSelect = "SELECT rcvi_id, rcvi_qty qty,
                        rcvi_itm_id, itm_nama, rcvi_total,
                        rcvi_satuan1, rcvi_satuan1hpp, rcvi_satuan1hrg,
                        rcvi_satuan0, rcvi_satuan0hpp, rcvi_satuan0hrg,
                        rcvi_satuan0of1";
                else
                    $strSelect = "SELECT rcvi_id id, rcv_nomor nomor,
                        rcvi_itm_id itm_id, itm_nama, rcvi_qty qty,
                        rcvi_satuan0 satuan, rcvi_diskon diskon, rcvi_total total,
                        rcvi_satuan1 satuan1, rcvi_satuan1hpp satuan1hpp,
                        rcvi_satuan1hrg satuan1hrg,
                        itm_satuan2 satuan2, itm_satuan2hpp satuan2hpp,
                        itm_satuan2hrg satuan2hrg, itm_satuan2of1 satuan2of1,
                        itm_satuan3 satuan3, itm_satuan3hpp satuan3hpp,
                        itm_satuan3hrg satuan3hrg, itm_satuan3of1 satuan3of1,
                        rcvi_satuan0 satuan0";
                foreach($result['data'] as &$rcv) {
                    $query = $db->query($strSelect." FROM inv_rcvitem
                        LEFT JOIN inv_receive ON rcv_id=rcvi_rcv_id
                        LEFT JOIN pos_item ON itm_id=rcvi_itm_id
                        WHERE rcvi_rcv_id=".$rcv->rcv_id);
                    $error = $db->error();
                    if ($error['code'] == 0) {
                        $rcv->rcvitems = $query->getResult();
                    }
                    else {
                        $result['error']['title'] = 'Baca Data Item Penerimaan';
                        $result['error']['message'] = $error['message'];
                        $result['status'] = 'failed';
                        break;
                    }
                }
            }
        }
        else {
            $result['error']['title'] = 'Baca Data Penerimaan';
            $result['error']['message'] = $error['message'].". Query:".$strQuery;
        }
        return $result;
    }

    function poItem()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $query = $db->query("SELECT rcvi_id id, not_nomor nomor,
            rcvi_itm_id itm_id, itm_nama, rcvi_qty qty, rcvi_satuan0 satuan, rcvi_total total,
            rcvi_satuan1 satuan1, rcvi_satuan1hpp satuan1hpp, rcvi_satuan1hrg satuan1hrg,
            rcvi_satuan0 satuan0, rcvi_satuan0hpp satuan0hpp, rcvi_satuan0hrg satuan0hrg,
            rcvi_diskon diskon
            FROM inv_poitem
            LEFT JOIN inv_po ON rcv_id=rcvi_rcv_id
            LEFT JOIN pos_item ON itm_id=rcvi_itm_id
            WHERE rcvi_rcv_id=".$_POST['rcv_id']);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data PO Item';
            $result['error']['message'] = $error['message'].'.Query: '.
                (string)($db->getLastQuery());
        }
        return $result;
    }

    function saveReceive()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $date = date_create(null, timezone_open("Asia/Jakarta"));

        $builder = $db->table('inv_receive');
        if ($_POST['rcv_id'] == 0) { // new record
            $new_id = '';

            // generate id dengan format YYYYnnnnnn
            // dalam setahun dialokasikan sebanyak 999,999 Receive
            // inv_receive ini adalah kumpulan semua Receive
            // tidak unique berdasar com_id atau lok_id

            $query = $db->query("SELECT MAX(rcv_id) max_id, ".
                date_format($date, 'Y')." yearof_id
                FROM inv_receive
                WHERE LEFT(rcv_id,4)=".date_format($date, 'Y'));
            $error = $db->error();
            if ($error['code'] == 0) {
                $row = $query->getRow();
                if (!$row->max_id) $new_id = $row->yearof_id."000001";
                else $new_id = $row->yearof_id.
                    sprintf("%06d",((int)substr($row->max_id,4,6))+1);
                $builder->set('rcv_id', $new_id);
                // generate nomor Receive dengan format RCYYMMDDnnnnnRE

                $query = $db->query("SELECT MAX(SUBSTR(rcv_nomor,9,5)) max_nomor, ".
                    "'RC".date_format($date, 'ymd')."' dateof_nomor
                    FROM inv_receive
                    WHERE LEFT(rcv_nomor,8)='RC".date_format($date, 'ymd')."'
                    AND rcv_lok_id=".$_POST['rcv_lok_id']);
                $error = $db->error();
                if ($error['code'] == 0) {
                    $row = $query->getRow();
                    if (!$row->max_nomor) $new_nomor = $row->dateof_nomor."00001RE";
                    else $new_nomor = $row->dateof_nomor.
                        sprintf("%05dRE", ((int)$row->max_nomor)+1);
                    $builder->set('rcv_nomor', $new_nomor);
                    $builder->set('rcv_tgterima', date_format($date, 'Y-m-d H:i:s'));
                    if ($builder->insert()) {
                        $_POST['rcv_id'] = $new_id;
                    }
                    else {
                        $result['error']['title'] = 'Pembuatan Penerimaan';
                        $result['error']['message'] = $error['message'];
                        $result['status'] = 'failed';
                    }
                }
                else {
                    $result['error']['title'] = 'Pembuatan Nomor Penerimaan';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Pembuatan ID Penerimaan';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder->set('rcv_po_id', $_POST['rcv_po_id']);
            $builder->set('rcv_total', $_POST['rcv_total']);
            $builder->set('rcv_diskon', $_POST['rcv_diskon']);
            $builder->set('rcv_kas_id', $_POST['rcv_kas_id']);
            $builder->set('rcv_lok_id', $_POST['rcv_lok_id']);
            $builder->set('rcv_catatan', $_POST['rcv_catatan']);
            $builder->set('rcv_status', $_POST['rcv_status']);
            $builder->where('rcv_id', $_POST['rcv_id']);
            if ($builder->update()) {
                $builder = $db->table('inv_rcvitem');
                if ($builder->delete(['rcvi_rcv_id' => $_POST['rcv_id']])) {
                    $rows = $_POST['rows'];
                    $poModel = new PoModel();
                    foreach($rows as $r) {
                        $sisa = $poModel->calcQtyLeft($_POST['rcv_po_id'], $r['itm_id']);
                        if ($r['qty'] > $sisa) {
                            $result['error']['title'] = 'Simpan Item Penerimaan';
                            $result['error']['message'] = 'Qty item "'.$r['itm_nama'].'" '.
                                '('.$r['qty'].') melebihi qty atau sisa qty '.
                                'yang ada di PO ('.$sisa.')';
                            $result['status'] = 'failed';
                            break;
                        }
                        // generate id dengan format YYYYnnnnnnnnnnn
                        $query = $db->query("SELECT MAX(rcvi_id) max_id, ".
                            date_format($date, 'Y')." yearof_id
                            FROM inv_rcvitem
                            WHERE LEFT(rcvi_id,4)=".date_format($date, 'Y'));
                        $row = $query->getRow();
                        if (!$row->max_id) $new_ntmid = $row->yearof_id."00000000001";
                        else $new_ntmid = $row->yearof_id.
                            sprintf("%011d",((int)substr($row->max_id,4,11))+1);
                        $builder->set('rcvi_id', $new_ntmid);
                        $builder->set('rcvi_rcv_id', $_POST['rcv_id']);
                        $builder->set('rcvi_itm_id', $r['itm_id']);
                        $builder->set('rcvi_qty', $r['qty']);
                        $builder->set('rcvi_total', $r['total']);
                        $builder->set('rcvi_diskon', $r['diskon']);
                        $builder->set('rcvi_satuan1', $r['satuan1']);
                        $builder->set('rcvi_satuan1hpp', $r['satuan1hpp']);
                        $builder->set('rcvi_satuan1hrg', $r['satuan1hrg']);
                        $builder->set('rcvi_satuan0', $r['satuan0']);
                        $builder->set('rcvi_satuan0hpp', $r['satuan0hpp']);
                        $builder->set('rcvi_satuan0hrg', $r['satuan0hrg']);
                        $builder->set('rcvi_satuan0of1', $r['satuan0of1']);
                        if (!$builder->insert()) {
                            $result['error']['title'] = 'Simpan Item Penerimaan';
                            $result['error']['message'] = 'Proses gagal. Query: '.
                                (string)($db->getLastQuery());
                            $result['status'] = 'failed';
                            break;
                        }
                    }
                }
                else {
                    $result['error']['title'] = 'Replace Data Item Penerimaan';
                    $result['error']['message'] = 'Proses gagal. Query: '.
                        (string)($db->getLastQuery());
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Update Data Penerimaan';
                $result['error']['message'] = 'Proses gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $query = $db->query("SELECT * FROM inv_receive WHERE rcv_id=".$_POST['rcv_id']);
            $row = $query->getRow();
            $result['rcv_id'] = $row->rcv_id;
            $result['rcv_nomor'] = $row->rcv_nomor;
            $result['rcv_tgterima'] = $row->rcv_tgterima;
        }
        return $result;
    }

    function updateStock($db, $rcv_id)
    {
        $success = true;
        $query = $db->query("SELECT * FROM inv_rcvitem
            WHERE rcvi_rcv_id='".$rcv_id."'");
        $rows = $query->getResult();
        foreach($rows as $row) {
            // angka-angka sebelum update stok dicatat dulu untuk keperluan
            // menghitung HPP moving average
            $query = $db->query("SELECT * FROM pos_item
                WHERE itm_id=".$row->rcvi_itm_id);
            $item = $query->getRow();
            // convertedQty adalah qty berbasis satuan terkecil (satuan1)
            // jadi misal saat PO menggunakan satuan kodi misal, maka convertedQty
            // adalah qty yang dientri saat PO (misal 5 kodi) dikalikan dengan
            // faktor pengali kodi terhadap satuan terkecil PCS yaitu 20
            // 1 kode adalah 20 PCS
            // di PO kolom yang menyimpan angka 20 ini adalah rcvi_satuan0of1
            $convertedQty = $row->rcvi_satuan0of1*$row->rcvi_qty;
            $db->query("UPDATE pos_item SET itm_stok=itm_stok+".$convertedQty."
                WHERE itm_id=".$row->rcvi_itm_id);
            $error = $db->error();
            if ($error['code'] == 0) {
                // update HPP
                $nilaiStokAwal = $item->itm_stok*$item->itm_satuan1hpp;
                $nilaiRcv = $row->rcvi_qty*$row->rcvi_satuan0hpp;
                $nilaiStokBaru = $nilaiStokAwal+$nilaiRcv;
                $satuan1hppAvg = floor($nilaiStokBaru/($item->itm_stok+$convertedQty));
                $satuan2hppAvg = floor($item->itm_satuan2hpp*$satuan1hppAvg/
                    $item->itm_satuan1hpp);
                $satuan3hppAvg = floor($item->itm_satuan3hpp*$satuan1hppAvg/
                    $item->itm_satuan1hpp);
                $db->query("UPDATE pos_item SET itm_satuan1prevhpp=itm_satuan1hpp,
                    itm_satuan2prevhpp=itm_satuan2hpp, itm_satuan3prevhpp=itm_satuan3hpp
                    WHERE itm_id=".$row->rcvi_itm_id);
                $db->query("UPDATE pos_item SET itm_satuan1hpp=".$satuan1hppAvg.",
                    itm_satuan2hpp=".$satuan2hppAvg.", itm_satuan3hpp=".$satuan3hppAvg."
                    WHERE itm_id=".$row->rcvi_itm_id);
            }
            else {
                $success = false;
                break;
            }
        }
        return $success;
    }

    function pay()
    {
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("UPDATE inv_receive SET rcv_status='PAID', rcv_tglunas='".
            date_format($date, 'Y-m-d H:i:s')."'
            WHERE rcv_id='".$_POST['rcv_id']."'");
        $error = $db->error();
        if ($error['code'] == 0) {
            if ($this->updateStock($db, $_POST['rcv_id'])) {
                // Setelah sebuah receive menjadi PAID, maka harus selalu cek
                // apakah PO yang terkait sudah COMPLETED
                // ----------------------------------------------------------
                // Pertama: Mengambil ID PO terkait
                $query = $db->query("SELECT rcv_po_id FROM inv_receive
                    WHERE rcv_id='".$_POST['rcv_id']."'");
                $po_id = $query->getRow()->rcv_po_id;
                // Kedua: Mengambil daftar item dalam PO terkait
                $query = $db->query("SELECT poi_itm_id, poi_qty FROM inv_poitem
                    WHERE poi_po_id='".$po_id."'");
                $poi = $query->getResult();
                $completed = true;
                foreach($poi as $i) {
                    // Ketiga: Memeriksa tiap item dalam PO apakah sudah RECEIVE 100%
                    $query = $db->query("SELECT SUM(rcvi_qty) total
                        FROM inv_rcvitem
                        LEFT JOIN inv_receive ON rcv_id=rcvi_rcv_id
                        WHERE rcv_po_id='".$po_id."' AND rcvi_itm_id=".$i->poi_itm_id);
                    if ($query->getRow()->total < $i->poi_qty) {
                        $completed = false;
                        break;
                    }
                }
                if ($completed) {
                    $db->query("UPDATE inv_po, inv_receive
                        SET po_status='COMPLETED', po_tgcomplete='".
                        date_format($date, 'Y-m-d H:i:s')."'
                        WHERE po_id=rcv_po_id AND rcv_id='".$_POST['rcv_id']."'");
                }
                $query = $db->query("SELECT * FROM inv_receive
                    WHERE rcv_id='".$_POST['rcv_id']."'");
                $row = $query->getRow();
                $result['rcv_status'] = $row->rcv_status;
                $result['rcv_tglunas'] = $row->rcv_tglunas;
                $result['po_status'] = $completed?'COMPLETED':'APPROVED';
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Pelunasan Penerimaan';
                $result['error']['message'] = 'Proses update stok gagal';
            }
        }
        else {
            $result['error']['title'] = 'Pelunasan Penerimaan';
            $result['error']['message'] = 'Proses gagal. Query: '.
                (string)($db->getLastQuery());
        }
        return $result;
    }

    function deleteItem()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("DELETE FROM inv_item WHERE itm_id=".$_POST['itm_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else
            $result['errmsg'] = 'Gagal menghapus data. Kemungkinan item barang '.
                'tersebut sudah dipakai dalam salah satu transaksi';
        $result['sql'] = (string)($db->getLastQuery());
        $result['error'] = $error;
        return $result;
    }

    function receipt()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT lok_nama, lok_alamat, lok_kodepos,
            not_nomor, not_total, not_dibayar, not_kembalian, not_kasir,
            DATE_FORMAT(CONVERT_TZ(not_tanggal, @@global.time_zone, '+07:00'),
            '%d %M %Y, %H:%i') not_tanggal
            FROM pos_nota
            LEFT JOIN rms_lokasi ON lok_id=not_lok_id
            WHERE not_id='".$_POST['not_id']."'");
        $row = $query->getRow();
        if ($row) {
            $result['nota'] = $row;
            $query = $db->query("SELECT itm_nama, rcvi_qty, rcvi_satuan0hrg, rcvi_total
                FROM pos_notaitem
                LEFT JOIN pos_item ON itm_id=rcvi_itm_id
                WHERE rcvi_not_id=".$_POST['not_id']);
            $rows = $query->getResult();
            $result['rows'] = $rows;
            $result['status'] = 'success';
        }
        return $result;
    }

    function backup()
    {
        $result['status'] = 'failed';
        $retval = $this->read();
        if ($retval['status'] == 'success') {
            try {
                $folder = 'public/backup/'.md5('lok_id='.$_POST['lok_id']);
                if (!file_exists($folder)) {
                    mkdir($folder, 0755, true);
                }
                $file = $folder.'/sales-'.sprintf('%04d',intval($_POST['thn'])).
                    sprintf('%02d', intval($_POST['bln'])).'.csv';
                $result['file'] = $file;
                $handle = fopen($file, 'w');
                $json = $retval['data'];
                $first = true;
                foreach($json as $notaobj) {
                    $notaarr = json_decode(json_encode($notaobj), true);
                    $n = sizeof($notaarr);
                    array_splice($notaarr, $n-1, 1);
                    foreach($notaobj->notaitems as $itemobj) {
                        $itemarr = json_decode(json_encode($itemobj), true);
                        if ($first) {
                            $notaheader = array_keys($notaarr);
                            $itemheader = array_keys($itemarr);
                            $header = array_merge($notaheader, $itemheader);
                            fputcsv($handle, $header);
                            $first = false;
                        }
                        $row = array_merge($notaarr, $itemarr);
                        fputcsv($handle, $row);
                    }
                }
                fclose($handle);
                $result['status'] = 'success';
            }
            catch (Exception $e) {
                $result['error']['title'] = 'Backup data penjualan bulanan';
                $result['error']['message'] = $e->getMessage();
            }
        }
        return $result;
    }

    function restore()
    {
        $_POST = $_GET;
        $result['status'] = 'failed';
        $result['data'] = [];
        try {
            $folder = 'public/backup/'.md5('lok_id='.$_POST['lok_id']);
            $file = $folder.'/'.$_POST['file'];
            $handle = fopen($file, 'r');
            $data = [];
            $nota = null;

            // parsing dari CSV ke object json array
            while(!feof($handle))
            {
                $item = fgetcsv($handle);
                if ($item[0] == 'id') continue;
                if (!$nota || $item[0] != $nota['id']) {
                    if ($nota)
                        array_push($data, $nota);
                    $nota = [
                        'not_id' => $item[0],
                        'not_nomor' => $item[1],
                        'not_tanggal' => $item[2],
                        'not_total' => $item[3],
                        'not_dibayar' => $item[4],
                        'not_kembalian' => $item[5],
                        'not_kasir' => $item[6],
                        'not_sft_id' => $item[7],
                        'not_diskon' => $item[8],
                        'not_catatan' => $item[9],
                        'notaitems' => []
                    ];
                }
                $item = [
                    'rcvi_id' => $item[10],
                    'not_nomor' => $item[1],
                    'rcvi_qty' => $item[11],
                    'rcvi_itm_id' => $item[12],
                    'itm_nama' => $item[13],
                    'rcvi_satuan' => $item[19],
                    'rcvi_total' => $item[14],
                    'rcvi_diskon' => $item[15],
                    'rcvi_satuan1' => $item[16],
                    'rcvi_satuan1hpp' => $item[17],
                    'rcvi_satuan1hrg' => $item[18],
                    'rcvi_satuan0' => $item[19],
                    'rcvi_satuan0hpp' => $item[20],
                    'rcvi_satuan0hrg' => $item[21],
                    'rcvi_satuan0of1' => $item[22]
                ];
                array_push($nota['notaitems'], $item);
            }
            fclose($handle);

            // merge dari object json array ke database
            $result['status'] = 'success';
            $result['data'] = $data;
        }
        catch (Exception $e) {
            $result['error']['title'] = 'Restore data penjualan bulanan';
            $result['error']['message'] = $e->getMessage();
        }
        return $result;
    }

    function deleteBackup()
    {
        $result['status'] = 'success';
        try {
            $folder = 'public/backup/'.md5('lok_id='.$_POST['lok_id']);
            $file = $folder.'/'.$_POST['file'];
            unlink($file);
        }
        catch (Exception $e) {
            $result['error']['title'] = 'Hapus backup data penjualan bulanan';
            $result['error']['message'] = $e->getMessage();
            $result['status'] = 'failed';
        }
        return $result;
    }

    function backupList()
    {
        $result['status'] = 'failed';
        try {
            $folder = 'public/backup/'.md5('lok_id='.$_POST['lok_id']);
            if (file_exists($folder)) {
                $files = scandir($folder);
                array_splice($files, 0, 2);
                $result['folder'] = $folder;
                $result['files'] = $files;
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Daftar data backup penjualan';
                $result['error']['message'] = 'Tidak ditemukan';
            }
        }
        catch (Exception $e) {
            $result['error']['title'] = 'Daftar data backup penjualan';
            $result['error']['message'] = $e->getMessage();
        }
        return $result;
    }
}