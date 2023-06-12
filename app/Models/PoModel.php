<?php 
namespace App\Models;
use CodeIgniter\Model;

class PoModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
            $strSelect = "SELECT po_id, po_nomor, po_tgorder, po_tgapprove,
                po_total, kas_id, kas_nama, sup_nama, po_catatan, po_status";
        else
            $strSelect = "SELECT po_id id, po_nomor nomor,
                po_tgorder tgorder, po_tgapprove tgapprove,
                po_total total, kas_id, kas_nama, sup_id, sup_nama,
                po_catatan catatan, po_status status, po_id";
        $strQuery = $strSelect." FROM inv_po
            LEFT JOIN pos_kasir ON kas_id=po_kas_id
            LEFT JOIN inv_supplier ON sup_id=po_sup_id
            WHERE po_lok_id=".$_POST['lok_id'];
        if (isset($_POST['q'])) {
            $strQuery .= " AND (po_catatan LIKE '%".$_POST['q']."%'
                OR po_nomor LIKE '%".$_POST['q']."%'
                OR EXISTS(SELECT 1 FROM inv_poitem
                LEFT JOIN pos_item ON itm_id=poi_itm_id
                WHERE poi_po_id=po_id AND itm_nama LIKE '%".$_POST['q']."%'))";
        }
        if (isset($_POST['sup_nama'])) {
            $strQuery .= " AND sup_nama LIKE '%".$_POST['sup_nama']."%'";
        }
        if (isset($_POST['thn']) && isset($_POST['bln'])) {
            $strQuery .= " AND YEAR(po_tgorder)=".$_POST['thn']."
                AND MONTH(po_tgorder)=".$_POST['bln'];
            if (isset($_POST['har'])) {
                $strQuery .= " AND DAY(po_tgorder)=".$_POST['har'];
            }
        }
        if (isset($_POST['id'])) {
            $strQuery .= " AND po_id=".$_POST['id'];
        }
        if (isset($_POST['status'])) {
            $strQuery .= " AND po_status='".$_POST['status']."'";
        }
        $strQuery .= " ORDER BY po_id DESC";
        $query = $db->query($strQuery);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['status'] = 'success';
            $result['data'] = $query->getResult();
            if(isset($_POST['loaditems']) && $_POST['loaditems'] == 'yes') {
                if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
                    $strSelect = "SELECT poi_id, poi_qty qty,
                        poi_itm_id, itm_nama, poi_total,
                        poi_satuan1, poi_satuan1hpp, poi_satuan1hrg,
                        poi_satuan0, poi_satuan0hpp, poi_satuan0hrg,
                        poi_satuan0of1";
                else
                    $strSelect = "SELECT poi_id id, po_nomor nomor, po_id,
                        poi_itm_id itm_id, itm_nama, poi_qty qty,
                        poi_satuan0 satuan, poi_total total,
                        poi_satuan1 satuan1, poi_satuan1hpp satuan1hpp,
                        poi_satuan1hrg satuan1hrg,
                        itm_satuan2 satuan2, itm_satuan2hpp satuan2hpp,
                        itm_satuan2hrg satuan2hrg, itm_satuan2of1 satuan2of1,
                        itm_satuan3 satuan3, itm_satuan3hpp satuan3hpp,
                        itm_satuan3hrg satuan3hrg, itm_satuan3of1 satuan3of1,
                        poi_satuan0 satuan0";
                foreach($result['data'] as &$po) {
                    $strQuery = $strSelect." FROM inv_poitem
                        LEFT JOIN inv_po ON po_id=poi_po_id
                        LEFT JOIN pos_item ON itm_id=poi_itm_id
                        WHERE poi_po_id=".$po->po_id;
                    $query = $db->query($strQuery);
                    $error = $db->error();
                    if ($error['code'] == 0) {
                        $po->poitems = $query->getResult();
                        if (isset($_POST['receiving']) && $_POST['receiving']=='yes') {
                            $temp = [];
                            foreach($po->poitems as &$lpoi) {
                                $lpoi->qty = $this->calcQtyLeft($lpoi->po_id,
                                    $lpoi->itm_id);
                                if ($lpoi->satuan0 == $lpoi->satuan1)
                                    $lpoi->total = $lpoi->satuan1hpp*$lpoi->qty;
                                elseif ($lpoi->satuan0 == $lpoi->satuan2)
                                    $lpoi->total = $lpoi->satuan2hpp*$lpoi->qty;
                                elseif ($lpoi->satuan0 == $lpoi->satuan3)
                                    $lpoi->total = $lpoi->satuan3hpp*$lpoi->qty;
                                if ($lpoi->qty > 0)
                                    array_push($temp, $lpoi);
                            }
                            $po->poitems = $temp;
                        }
                    }
                    else {
                        $result['error']['title'] = 'Baca Data PO Item';
                        $result['error']['message'] = $error['message'];
                        $result['status'] = 'failed';
                        break;
                    }
                }
            }
        }
        else {
            $result['error']['title'] = 'Baca Data PO';
            $result['error']['message'] = $error['message'].". Query:".$strQuery;
        }
        return $result;
    }

    function calcQtyLeft($po_id, $itm_id) {
        $db = db_connect();
        $query = $db->query("SELECT poi_qty FROM inv_poitem
            WHERE poi_po_id='".$po_id."' AND poi_itm_id=".$itm_id);
        $row = $query->getRow();
        $poi_qty = $row->poi_qty;
        $query = $db->query("SELECT SUM(rcvi_qty) AS totalrcvi_qty
            FROM inv_rcvitem
            LEFT JOIN inv_receive ON rcv_id=rcvi_rcv_id
            WHERE rcv_po_id='".$po_id."' AND rcvi_itm_id=".$itm_id." AND
            rcv_status='PAID'");
        $row = $query->getRow();
        return $poi_qty-$row->totalrcvi_qty;
    }

    function poItem()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $query = $db->query("SELECT poi_id id, not_nomor nomor,
            poi_itm_id itm_id, itm_nama, poi_qty qty, poi_satuan0 satuan, poi_total total,
            poi_satuan1 satuan1, poi_satuan1hpp satuan1hpp, poi_satuan1hrg satuan1hrg,
            poi_satuan0 satuan0, poi_satuan0hpp satuan0hpp, poi_satuan0hrg satuan0hrg,
            poi_diskon diskon
            FROM inv_poitem
            LEFT JOIN inv_po ON po_id=poi_po_id
            LEFT JOIN pos_item ON itm_id=poi_itm_id
            WHERE poi_po_id=".$_POST['po_id']);
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

    function savePO()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $date = date_create(null, timezone_open("Asia/Jakarta"));

        $builder = $db->table('inv_po');
        if ($_POST['po_id'] == 0) { // new record
            $new_id = '';

            // generate id dengan format YYYYnnnnnn
            // dalam setahun dialokasikan sebanyak 999,999 PO
            // inv_po ini adalah kumpulan semua PO
            // tidak unique berdasar com_id atau lok_id

            $query = $db->query("SELECT MAX(po_id) max_id, ".
                date_format($date, 'Y')." yearof_id
                FROM inv_po
                WHERE LEFT(po_id,4)=".date_format($date, 'Y'));
            $error = $db->error();
            if ($error['code'] == 0) {
                $row = $query->getRow();
                if (!$row->max_id) $new_id = $row->yearof_id."000001";
                else $new_id = $row->yearof_id.
                    sprintf("%06d",((int)substr($row->max_id,4,6))+1);
                $builder->set('po_id', $new_id);
                // generate nomor PO dengan format POYYMMDDnnnnnRE

                $query = $db->query("SELECT MAX(SUBSTR(po_nomor,9,5)) max_nomor, ".
                    "'PO".date_format($date, 'ymd')."' dateof_nomor
                    FROM inv_po
                    WHERE LEFT(po_nomor,8)='PO".date_format($date, 'ymd')."'
                    AND po_lok_id=".$_POST['po_lok_id']);
                $error = $db->error();
                if ($error['code'] == 0) {
                    $row = $query->getRow();
                    if (!$row->max_nomor) $new_nomor = $row->dateof_nomor."00001RE";
                    else $new_nomor = $row->dateof_nomor.
                        sprintf("%05dRE", ((int)$row->max_nomor)+1);
                    $builder->set('po_nomor', $new_nomor);
                    $builder->set('po_tgorder', date_format($date, 'Y-m-d H:i:s'));
                    if ($builder->insert()) {
                        $_POST['po_id'] = $new_id;
                    }
                    else {
                        $result['error']['title'] = 'Pembuatan PO';
                        $result['error']['message'] = $error['message'];
                        $result['status'] = 'failed';
                    }
                }
                else {
                    $result['error']['title'] = 'Pembuatan Nomor PO';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Pembuatan ID PO';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder->set('po_sup_id', $_POST['po_sup_id']);
            $builder->set('po_total', $_POST['po_total']);
            $builder->set('po_kas_id', $_POST['po_kas_id']);
            $builder->set('po_lok_id', $_POST['po_lok_id']);
            $builder->set('po_catatan', $_POST['po_catatan']);
            $builder->set('po_status', $_POST['po_status']);
            $builder->where('po_id', $_POST['po_id']);
            if ($builder->update()) {
                $builder = $db->table('inv_poitem');
                if ($builder->delete(['poi_po_id' => $_POST['po_id']])) {
                    $rows = $_POST['rows'];
                    foreach($rows as $r) {
                        // generate id dengan format YYYYnnnnnnnnnnn
                        $query = $db->query("SELECT MAX(poi_id) max_id, ".
                            date_format($date, 'Y')." yearof_id
                            FROM inv_poitem
                            WHERE LEFT(poi_id,4)=".date_format($date, 'Y'));
                        $row = $query->getRow();
                        if (!$row->max_id) $new_ntmid = $row->yearof_id."00000000001";
                        else $new_ntmid = $row->yearof_id.
                            sprintf("%011d",((int)substr($row->max_id,4,11))+1);
                        $builder->set('poi_id', $new_ntmid);
                        $builder->set('poi_po_id', $_POST['po_id']);
                        $builder->set('poi_itm_id', $r['itm_id']);
                        $builder->set('poi_qty', $r['qty']);
                        $builder->set('poi_total', $r['total']);
                        $builder->set('poi_satuan1', $r['satuan1']);
                        $builder->set('poi_satuan1hpp', $r['satuan1hpp']);
                        $builder->set('poi_satuan1hrg', $r['satuan1hrg']);
                        $builder->set('poi_satuan0', $r['satuan0']);
                        $builder->set('poi_satuan0hpp', $r['satuan0hpp']);
                        $builder->set('poi_satuan0hrg', $r['satuan0hrg']);
                        $builder->set('poi_satuan0of1', $r['satuan0of1']);
                        if (!$builder->insert()) {
                            $result['error']['title'] = 'Simpan PO Item';
                            $result['error']['message'] = 'Proses gagal. Query: '.
                                (string)($db->getLastQuery());
                            $result['status'] = 'failed';
                            break;
                        }
                    }
                }
                else {
                    $result['error']['title'] = 'Replace Data PO Item';
                    $result['error']['message'] = 'Proses gagal. Query: '.
                        (string)($db->getLastQuery());
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Update Data PO';
                $result['error']['message'] = 'Proses gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $query = $db->query("SELECT * FROM inv_po WHERE po_id=".$_POST['po_id']);
            $row = $query->getRow();
            $result['po_id'] = $row->po_id;
            $result['po_nomor'] = $row->po_nomor;
            $result['po_tgorder'] = $row->po_tgorder;
        }
        return $result;
    }

    function approve()
    {
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("UPDATE inv_po SET po_status='APPROVED', po_tgapprove='".
            date_format($date, 'Y-m-d H:i:s')."'
            WHERE po_id=".$_POST['po_id']);
        $error = $db->error();
        if ($error['code'] == 0) {
            $query = $db->query("SELECT * FROM inv_po WHERE po_id=".$_POST['po_id']);
            $row = $query->getRow();
            $result['po_status'] = $row->po_status;
            $result['po_tgapprove'] = $row->po_tgapprove;
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Approval PO';
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
            $query = $db->query("SELECT itm_nama, poi_qty, poi_satuan0hrg, poi_total
                FROM pos_notaitem
                LEFT JOIN pos_item ON itm_id=poi_itm_id
                WHERE poi_not_id=".$_POST['not_id']);
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
                    'poi_id' => $item[10],
                    'not_nomor' => $item[1],
                    'poi_qty' => $item[11],
                    'poi_itm_id' => $item[12],
                    'itm_nama' => $item[13],
                    'poi_satuan' => $item[19],
                    'poi_total' => $item[14],
                    'poi_diskon' => $item[15],
                    'poi_satuan1' => $item[16],
                    'poi_satuan1hpp' => $item[17],
                    'poi_satuan1hrg' => $item[18],
                    'poi_satuan0' => $item[19],
                    'poi_satuan0hpp' => $item[20],
                    'poi_satuan0hrg' => $item[21],
                    'poi_satuan0of1' => $item[22]
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