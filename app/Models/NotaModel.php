<?php 
namespace App\Models;
use CodeIgniter\Model;
use App\Models\NotificationModel;

class NotaModel extends Model
{
    function read()
    {
        $db = db_connect();
        $page = isset($_POST['page'])?$_POST['page']:null;
        $rows = isset($_POST['rows'])?$_POST['rows']:null;
        $result['status'] = 'failed';
        $result['data'] = [];
        if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
            $strSelect = "SELECT not_id, not_nomor, not_tanggal,
                not_total, not_dibayar, not_kembalian, kas_id, kas_nama, cus_nama, 
                not_sft_id, not_diskon, not_disnom, not_catatan,
                not_dicicil, not_jatuhtempo, not_carabayar, byr_nama, not_status";
        else
            $strSelect = "SELECT not_id id, not_nomor nomor, not_tanggal tanggal, not_id, 
                not_total total, not_dibayar dibayar, not_kembalian kembalian,
                kas_id, kas_nama, cus_nama, not_sft_id sft_id,
                not_diskon diskon, not_disnom disnom, not_catatan catatan,
                not_dicicil dicicil, not_jatuhtempo jatuhtempo,
                byr_nama carabayar, not_status";
        $strQuery = $strSelect." FROM pos_nota
            LEFT JOIN pos_kasir ON kas_id=not_kas_id
            LEFT JOIN pos_customer ON cus_id=not_cus_id
            LEFT JOIN pos_carabayar ON byr_kode=not_carabayar
            WHERE not_lok_id=".$_POST['lok_id']." AND not_deleteddate IS NULL ";
        if (isset($_POST['q'])) {
            $strQuery .= " AND (not_catatan LIKE '%".$_POST['q']."%'
                OR not_nomor LIKE '%".$_POST['q']."%'
                OR EXISTS(SELECT 1 FROM pos_notaitem
                LEFT JOIN pos_item ON itm_id=nit_itm_id
                WHERE nit_not_id=not_id AND itm_nama LIKE '%".$_POST['q']."%'))";
        }
        if (isset($_POST['datepast']) && isset($_POST['datenow'])) {
            $strQuery .= " AND not_tanggal BETWEEN '".$_POST['datepast']."'
                AND '".$_POST['datenow']."'";
        }
        if (isset($_POST['thn']) && isset($_POST['bln'])) {
            $strQuery .= " AND YEAR(not_tanggal)=".$_POST['thn']."
                AND MONTH(not_tanggal)=".$_POST['bln'];
            if (isset($_POST['har'])) {
                $strQuery .= " AND DAY(not_tanggal)=".$_POST['har'];
            }
        }
        if (isset($_POST['id'])) {
            $strQuery .= " AND not_id=".$_POST['id'];
        }
        $strQuery .= " ORDER BY not_id DESC";
        if ($page)
            $strQuery .= " LIMIT " . ($page - 1) * $rows . "," . $rows;
        $query = $db->query($strQuery);
        $result['sql'] = (string)($db->getLastQuery());
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['status'] = 'success';
            if(isset($_POST['id']))
                $result['data'] = $query->getRow();
            else {
                $result['data'] = $query->getResult();
                if(isset($_POST['loaditems']) && $_POST['loaditems'] == 'yes') {
                    if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
                        $strSelect = "SELECT nit_id, nit_qty qty, nit_itm_id,
                            itm_nama, nit_total, nit_diskon, nit_disnom,
                            nit_satuan1, nit_satuan1hpp, nit_satuan1hrg,
                            nit_satuan0, nit_satuan0hpp, nit_satuan0hrg,
                            nit_satuan0of1";
                    else
                        $strSelect = "SELECT nit_id id, not_nomor nomor,
                            nit_itm_id itm_id, itm_nama, nit_qty qty, nit_satuan0 satuan,
                            nit_total total, nit_diskon diskon, nit_disnom disnom,
                            nit_satuan1 satuan1, nit_satuan1hpp satuan1hpp,
                            nit_satuan1hrg satuan1hrg, nit_satuan0 satuan0,
                            nit_satuan0hpp satuan0hpp, nit_satuan0hrg satuan0hrg,
                            nit_satuan0of1 satuan0of1";
                    foreach($result['data'] as &$not) {
                        $query = $db->query($strSelect." FROM pos_notaitem
                            LEFT JOIN pos_nota ON not_id=nit_not_id
                            LEFT JOIN pos_item ON itm_id=nit_itm_id
                            WHERE nit_not_id=".$not->not_id);
                        $error = $db->error();
                        if ($error['code'] == 0) {
                            $not->notaitems = $query->getResult();
                            if ($not->not_dicicil == 1) {
                                $query = $db->query("SELECT cil_sisa FROM pos_cicilan
                                    WHERE cil_not_id='".$not->not_id."'
                                    ORDER BY cil_id DESC LIMIT 1");
                                $error = $db->error();
                                if ($error['code'] == 0) {
                                    $row = $query->getRow();
                                    if ($row && $row->cil_sisa == 0)
                                        $not->piutlunas = 1;
                                    else
                                        $not->piutlunas = 0;
                                }
                                else {
                                    $result['error']['title'] = 'Baca Data Piutang';
                                    $result['error']['message'] = $error['message'];
                                    $result['status'] = 'failed';
                                    break;
                                }
                            }
                        }
                        else {
                            $result['error']['title'] = 'Baca Data Nota Item';
                            $result['error']['message'] = $error['message'];
                            $result['status'] = 'failed';
                            break;
                        }
                    }
                }
            }
        }
        else {
            $result['error']['title'] = 'Baca Data Nota';
            $result['error']['message'] = $error['message'].
                '. Query: '.(string)($db->getLastQuery());
        }
        return $result;
    }

    function notaItem()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $query = $db->query("SELECT nit_id id, not_nomor nomor,
            nit_itm_id itm_id, itm_nama, nit_qty qty, nit_satuan0 satuan, nit_total total,
            nit_satuan1 satuan1, nit_satuan1hpp satuan1hpp, nit_satuan1hrg satuan1hrg,
            nit_satuan0 satuan0, nit_satuan0hpp satuan0hpp, nit_satuan0hrg satuan0hrg,
            nit_diskon diskon, nit_disnom disnom
            FROM pos_notaitem
            LEFT JOIN pos_nota ON not_id=nit_not_id
            LEFT JOIN pos_item ON itm_id=nit_itm_id
            WHERE nit_not_id=".$_POST['not_id']);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Nota Item';
            $result['error']['message'] = $error['message'].'.Query: '.
                (string)($db->getLastQuery());
        }
        return $result;
    }

    function readitem()
    {
        $db = db_connect();
        $page = isset($_POST['page'])?$_POST['page']:null;
        $rows = isset($_POST['rows'])?$_POST['rows']:null;
        $filter = "itm_lok_id=".$_POST['lok_id'];
        if (isset($_POST['key_val']))
            $filter .= " AND (not_nomor LIKE '%".$_POST['key_val']."%' OR itm_nama LIKE '%".
                $_POST['key_val']."%' OR not_catatan LIKE '%".$_POST['key_val']."%')";
        if ($page) {
            $query = $db->query("SELECT COUNT(*) total
                FROM pos_notaitem
                LEFT JOIN pos_item ON itm_id=nit_itm_id
                WHERE ".$filter);
            $retval['total'] = $query->getRow('total');
        }
        $queryStr = "SELECT nit_id, nit_not_id, not_nomor, itm_nama, nit_qty,
            nit_satuan0, nit_satuan0hrg, nit_diskon, not_disnom, nit_total,
            DATE_FORMAT(CONVERT_TZ(not_tanggal, @@global.time_zone, '+07:00'),
            '%d-%m-%Y %H:%i') not_tanggal,
            not_total, not_dibayar, not_kembalian, not_kasir, not_catatan
            FROM pos_notaitem
            LEFT JOIN pos_item ON itm_id=nit_itm_id
            LEFT JOIN pos_nota ON not_id=nit_not_id
            WHERE ".$filter." ORDER BY nit_id DESC";
        if ($page)
            $queryStr .= " LIMIT " . ($page - 1) * $rows . "," . $rows;
        $query = $db->query($queryStr);
        $data = $query->getResult();
        $nomorNota = '';
        $color = '#f3f3f3';
        foreach($data as $row) {
            if ($row->not_tanggal) {
                $date = date_create($row->not_tanggal);
                $row->not_tanggal = date_format($date,"m/d/Y H:i");
            }
            if ($row->not_nomor != $nomorNota) {
                $row->first = true;
                $nomorNota = $row->not_nomor;
                if ($color == '#f3f3f3') $color = 'white';
                else $color = '#f3f3f3';
                $not_total += $row->not_total;
            }
            else $row->first = false;
            $row->color = $color;
        }
        $query = $db->query("SELECT SUM(not_total) total, SUM(not_dibayar) dibayar,
            SUM(not_kembalian) kembalian
            FROM pos_nota
            WHERE EXISTS(SELECT 1 FROM pos_notaitem
                LEFT JOIN pos_item ON itm_id=nit_itm_id
                WHERE nit_not_id=not_id AND ".$filter.")");
        $retval['sql'] = (string)($db->getLastQuery());
        $row = $query->getRow();
        $sumrow = (object) array(
            'not_nomor'     => '{total}',
            'not_total'     => $row->total,
            'not_dibayar'   => $row->dibayar,
            'not_kembalian' => $row->kembalian
        );
        $query = $db->query("SELECT SUM(nit_qty) qty, SUM(nit_total) total
            FROM pos_notaitem
            LEFT JOIN pos_item ON itm_id=nit_itm_id
            LEFT JOIN pos_nota ON not_id=nit_not_id
            WHERE ".$filter);
        $row = $query->getRow();
        $sumrow->nit_qty = $row->qty;
        $sumrow->nit_total = $row->total;
        array_push($data, $sumrow);
        if ($page)
            $retval['rows'] = $data;
        else
            $retval = $data;
        return $retval;
    }

    function getNewID()
    {
        $db = db_connect();
        $query = $db->query("SELECT MAX(SUBSTR(not_nomor,7,5)) max_nomor,
            DATE_FORMAT(rbs_currentdate(), '%y%m%d') dateof_nomor
            FROM pos_nota
            WHERE LEFT(not_nomor,6)=DATE_FORMAT(rbs_currentdate(), '%y%m%d')");
        $row = $query->getRow();
        if (!$row->max_nomor) $new_nomor = $row->dateof_nomor."00001RE";
        else $new_nomor = $row->dateof_nomor.sprintf("%05dRE", ((int)$row->max_nomor)+1);
        $result['new_nomornota'] = $new_nomor;
        return $result;
    }

    function updateBOMStock($db, $itm_id, $qty)
    {
        $success = true;
        $query = $db->query("SELECT b.*, itm_pakaistok FROM pos_bom
            LEFT JOIN pos_item ON itm_id=bom_itm_id
            WHERE bom_itm_id=".$itm_id);
        $rows = $query->getResult();
        foreach($rows as $row) {
            $convertedQty = $row->bom_itm_satuanof1*$row->bom_qty*$qty;
            if ($row->itm_pakaistok == 1) {
                $db->query("UPDATE pos_item SET itm_stok=itm_stok-".$convertedQty."
                    WHERE itm_id=".$row->bom_itm_id_bahan);
                $error = $db->error();
                if ($error['code'] != 0) {
                    $success = false;
                    break;
                }
            }
            $query = $db->query("SELECT COUNT(*) total FROM pos_bom
                WHERE bom_itm_id=".$row->bom_itm_id_bahan);
            if ($query->getRow()->total > 0) {
                $success = $this->updateBOMStock($db, $row->bom_itm_id_bahan,
                    $convertedQty);
                if (!$success)
                    break;
            }
        }
        return $success;
    }

    function updateStock($db, $not_id)
    {
        $success = true;
        $query = $db->query("SELECT i.*, itm_pakaistok FROM pos_notaitem i
            LEFT JOIN pos_item ON itm_id=nit_itm_id
            WHERE nit_not_id='".$not_id."'");
        $rows = $query->getResult();
        foreach($rows as $row) {
            $convertedQty = $row->nit_satuan0of1*$row->nit_qty;
            if ($row->itm_pakaistok == 1) {
                $db->query("UPDATE pos_item SET itm_stok=itm_stok-".$convertedQty."
                    WHERE itm_id=".$row->nit_itm_id);
                $error = $db->error();
                if ($error['code'] != 0) {
                    $success = false;
                    break;
                }
            }
            $query = $db->query("SELECT COUNT(*) total FROM pos_bom
                WHERE bom_itm_id=".$row->nit_itm_id);
            if ($query->getRow()->total > 0) {
                $success = $this->updateBOMStock($db, $row->nit_itm_id,
                    $convertedQty);
                if (!$success)
                    break;
            }
        }
        return $success;
    }

    function isOverStock(&$msg)
    {
        $db = db_connect();
        $retval = false;
        $rows = $_POST['rows'];
        foreach($rows as $r) {
            if ($r['itm_pakaistok'] == 0) continue;
            $query = $db->query("SELECT itm_nama, itm_stok, itm_satuan
                FROM pos_item
                WHERE itm_id=".$r['itm_id']);
            $error = $db->error();
            if ($error['code'] == 0) {
                $itmrow = $query->getRow();
                $qty = $r['qty']*$r['satuan0of1'];
                $stok = $itmrow->itm_stok;
                if ($qty > $stok) {
                    if ($msg != '') $msg .= ', ';
                    if ($stok * 100 % 100 == 0) $stok = intval($stok);
                    $msg .= $itmrow->itm_nama.' (Stok '.$stok.' '.
                    $itmrow->itm_satuan.')';
                }
            }
            else {
                $msg = 'Error saat membaca data stok';
                $retval = true;
                break;
            }
        }
        if ($msg != '' && !$retval) {
            $msg = 'Maaf nota tidak bisa disimpan karena item berikut kekurangan stok: '.$msg;
            $retval = true;
        }
        return $retval;
    }

    function notify($db, $nota)
    {
        $query = $db->query("SELECT * FROM pos_kasir WHERE kas_id=".$nota->not_kas_id);
        $user = $query->getRow();
        $mintrans = $user->kas_mintrans?$user->kas_mintrans:0;
        if ($nota->not_total >= $mintrans && $user->kas_nama != 'Admin') {
            $model = new NotificationModel();
            $model->push($db, $nota, $user);
        }
    }

    function saveNota()
    {
        $overStockMsg = '';
        if ($this->isOverStock($overStockMsg)) {
            $result['error']['title'] = 'Penyimpanan Nota';
            $result['error']['message'] = $overStockMsg;
            $result['status'] = 'failed';
            return $result;
        }

        $db = db_connect();
        $result['status'] = 'success';
        $new_id = '';
        $date = date_create(null, timezone_open("Asia/Jakarta"));

        // generate id dengan format YYYYnnnnnnnnnn
        // dalam setahun dialokasikan sebanyak 9,999,999,999 transaksi
        // pos_nota ini adalah kumpulan semua nota dari
        // tidak unique berdasar com_id atau lok_id

        $query = $db->query("SELECT MAX(not_id) max_id, ".
            date_format($date, 'Y')." yearof_id
            FROM pos_nota
            WHERE LEFT(not_id,4)=".date_format($date, 'Y'));
        $error = $db->error();
        if ($error['code'] == 0) {
            $row = $query->getRow();
            if (!$row->max_id) $new_id = $row->yearof_id."0000000001";
            else $new_id = $row->yearof_id.
                sprintf("%010d",((int)substr($row->max_id,4,10))+1);
            $builder = $db->table('pos_nota');
            $builder->set('not_id', $new_id);

            // generate nomor nota dengan format YYMMDDnnnnnRE

            $query = $db->query("SELECT MAX(SUBSTR(not_nomor,7,5)) max_nomor, ".
                "'".date_format($date, 'ymd')."' dateof_nomor
                FROM pos_nota
                WHERE LEFT(not_nomor,6)='".date_format($date, 'ymd')."'
                AND not_lok_id=".$_POST['lok_id']);
            $error = $db->error();
            if ($error['code'] == 0) {
                $row = $query->getRow();
                if (!$row->max_nomor) $new_nomor = $row->dateof_nomor."00001RE";
                else $new_nomor = $row->dateof_nomor.
                    sprintf("%05dRE", ((int)$row->max_nomor)+1);
                $builder->set('not_nomor', $new_nomor);
                $builder->set('not_tanggal', date_format($date, 'Y-m-d H:i:s'));
                $builder->set('not_total', $_POST['total']);
                $builder->set('not_dibayar', $_POST['dibayar']);
                $builder->set('not_kembalian', $_POST['kembalian']);
                $builder->set('not_kas_id', $_POST['kas_id']);
                $builder->set('not_kasir', $_POST['kas_nama']);
                $builder->set('not_cus_id', $_POST['cus_id']);
                $builder->set('not_lok_id', $_POST['lok_id']);
                $builder->set('not_diskon', $_POST['diskon']);
                $builder->set('not_disnom', $_POST['disnom']);
                $builder->set('not_catatan', $_POST['catatan']);
                $builder->set('not_sft_id', $_POST['sft_id']);
                $builder->set('not_pajak', $_POST['pajak']);
                $builder->set('not_dicicil', $_POST['dicicil']);
                $builder->set('not_jatuhtempo', $_POST['jatuhtempo']);
                $builder->set('not_carabayar', isset($_POST['carabayar'])?
                    $_POST['carabayar']:'KAS');
                if ($builder->insert()) {
                    $rows = $_POST['rows'];
                    foreach($rows as $r) {
                        // generate id dengan format YYYYnnnnnnnn
                        $query = $db->query("SELECT MAX(nit_id) max_id, ".
                            date_format($date, 'Y')." yearof_id
                            FROM pos_notaitem
                            WHERE LEFT(nit_id,4)=".date_format($date, 'Y'));
                        $row = $query->getRow();
                        if (!$row->max_id) $new_ntmid = $row->yearof_id."00000000001";
                        else $new_ntmid = $row->yearof_id.
                            sprintf("%011d",((int)substr($row->max_id,4,11))+1);
                        $builder = $db->table('pos_notaitem');
                        $builder->set('nit_id', $new_ntmid);
                        $builder->set('nit_not_id', $new_id);
                        $builder->set('nit_itm_id', $r['itm_id']);
                        $builder->set('nit_qty', $r['qty']);
                        $builder->set('nit_diskon', $r['diskon']);
                        $builder->set('nit_disnom', $r['disnom']);
                        $builder->set('nit_total', $r['total']);
                        $builder->set('nit_satuan1', $r['satuan1']);
                        $builder->set('nit_satuan1hpp', $r['satuan1hpp']);
                        $builder->set('nit_satuan1hrg', $r['satuan1hrg']);
                        $builder->set('nit_satuan0', $r['satuan0']);
                        $builder->set('nit_satuan0hpp', $r['satuan0hpp']);
                        $builder->set('nit_satuan0hrg', $r['satuan0hrg']);
                        $builder->set('nit_satuan0of1', $r['satuan0of1']);
                        if (!$builder->insert()) {
                            $result['error']['title'] = 'Simpan Nota Item';
                            $result['error']['message'] = 'Proses gagal. Query: '.
                                (string)($db->getLastQuery());
                            $result['status'] = 'failed';
                            break;
                        }
                    }
                }
                else {
                    $result['error']['title'] = 'Simpan Nota';
                    $result['error']['message'] = 'Proses gagal. Query: '.
                        (string)($db->getLastQuery());
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Pembuatan Nomor Nota';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        else {
            $result['error']['title'] = 'Pembuatan Nota';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        if ($result['status'] == 'success') {
            $sql = [];
            if ($this->updateStock($db, $new_id)) {
                $query = $db->query("SELECT * FROM pos_nota
                    WHERE not_id=".$new_id);
                $row = $query->getRow();
                $result['not_id'] = $row->not_id;
                $result['not_nomor'] = $row->not_nomor;
                $result['not_tanggal'] = $row->not_tanggal;
                $this->notify($db, $row);
            }
            else {
                $result['error']['title'] = 'Pembuatan Nota';
                $result['error']['message'] = 'Proses update stok gagal';
                $result['status'] = 'failed';
            }
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
    function deleteNotaNew(){
        $db = db_connect();
        $result['status'] = 'failed';
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            $ip = $_SERVER['REMOTE_ADDR'];
        $filter='null';
        if (isset($_POST['log_reason']))
            $filter = "'".$_POST['log_reason']."'";
        $db->query("UPDATE pos_nota SET not_deleteddate='".date("Y-m-d H:i:s")."' WHERE not_id='".$_POST['not_id']."'");
        $result['sql2'] = (string)($db->getLastQuery());
        $db->query("INSERT INTO pos_log(log_kas_id,log_tabel,log_aksi,log_date,log_table_id,log_reason,log_ip) VALUES('".$_POST['kas_id']."','pos_nota','DELETE','".date("Y-m-d H:i:s")."','".$_POST['not_id']."',".$filter.",'".$ip."')");
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else
            $result['errmsg'] = 'Gagal menghapus data. Kemungkinan item barang tersebut sudah dipakai dalam salah satu transaksi';
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
            $query = $db->query("SELECT itm_nama, nit_qty, nit_satuan0hrg, nit_total
                FROM pos_notaitem
                LEFT JOIN pos_item ON itm_id=nit_itm_id
                WHERE nit_not_id=".$_POST['not_id']);
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

    function localRestore()
    {
        $result['status'] = 'failed';
        try {
            $folder = 'public/backup/local/'.md5($_POST['username']);
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            $file = $folder.'/'.$_POST['file'];
            $handle = fopen($file, 'r');
            $result['data'] = fread($handle, filesize($file));
            fclose($handle);
            $result['status'] = 'success';
        }
        catch (Exception $e) {
            $result['error']['title'] = 'Restore';
            $result['error']['message'] = $e->getMessage();
        }
        return $result;

    }

    function localBackup()
    {
        $result['status'] = 'failed';
        try {
            $folder = 'public/backup/local/'.md5($_POST['username']);
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            if (isset($_POST['thn']))
                $file = $folder.'/sales-'.sprintf('%04d',intval($_POST['thn'])).
                    sprintf('%02d', intval($_POST['bln'])).'.json';
            else
                $file = $folder.'/master.json';
            $result['file'] = $file;
            $handle = fopen($file, 'w');
            fwrite($handle, $_POST['data']);
            fclose($handle);
            $result['status'] = 'success';
        }
        catch (Exception $e) {
            $result['error']['title'] = 'Backup';
            $result['error']['message'] = $e->getMessage();
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
                    'nit_id' => $item[10],
                    'not_nomor' => $item[1],
                    'nit_qty' => $item[11],
                    'nit_itm_id' => $item[12],
                    'itm_nama' => $item[13],
                    'nit_satuan' => $item[19],
                    'nit_total' => $item[14],
                    'nit_diskon' => $item[15],
                    'nit_satuan1' => $item[16],
                    'nit_satuan1hpp' => $item[17],
                    'nit_satuan1hrg' => $item[18],
                    'nit_satuan0' => $item[19],
                    'nit_satuan0hpp' => $item[20],
                    'nit_satuan0hrg' => $item[21],
                    'nit_satuan0of1' => $item[22]
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
            if (isset($_POST['lok_id']) && $_POST['lok_id'])
                $folder = 'public/backup/'.md5('lok_id='.$_POST['lok_id']);
            else
                $folder = 'public/backup/local/'.md5($_POST['username']);
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
            if (isset($_POST['lok_id']) && $_POST['lok_id'])
                $folder = 'public/backup/'.md5('lok_id='.$_POST['lok_id']);
            else
                $folder = 'public/backup/local/'.md5($_POST['username']);
            if (file_exists($folder)) {
                $files = scandir($folder);
                array_splice($files, 0, 2);
                $result['folder'] = $folder;
                $result['files'] = $files;
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Daftar data backup penjualan';
                $result['error']['message'] = 'Tidak ditemukan'.
                    ' Username: '.$_POST['username'];
            }
        }
        catch (Exception $e) {
            $result['error']['title'] = 'Daftar data backup penjualan';
            $result['error']['message'] = $e->getMessage();
        }
        return $result;
    }

    function prosesNota()
    {
        $db = db_connect();
        $string = file_get_contents("public/backup/transkasbon_202205_228.json");
        $json = json_decode($string);
        $nota = $json->not;
        $success = true;
        foreach($nota as $i) {
            $query = $db->query("SELECT MAX(not_id) max_id,
                DATE_FORMAT(rbs_currentdate(), '%Y') yearof_id
                FROM pos_nota
                WHERE LEFT(not_id,4)=DATE_FORMAT(rbs_currentdate(), '%Y')");
            $error = $db->error();
            if ($error['code'] == 0) {
                $row = $query->getRow();
                if (!$row->max_id) $new_id = $row->yearof_id."0000000001";
                else $new_id = $row->yearof_id.
                    sprintf("%010d",((int)substr($row->max_id,4,10))+1);
                $db->query("INSERT INTO pos_nota(not_id, not_nomor, not_tanggal, not_total,
                    not_dibayar, not_kembalian, not_kas_id, not_kasir, not_cus_id,
                    not_lok_id, not_catatan, not_diskon) VALUES(".$new_id.",
                    '".$i->nomor."', '".$i->tanggal."', ".$i->total.",
                    ".$i->dibayar.", ".$i->kembalian.", 8, 'Admin', null, 2,
                    '".$i->catatan."', ".$i->diskon.")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    echo $error['message'].". Query: ".(string)($db->getLastQuery());
                    $success = false;
                    break;
                }
            }
            else {
                echo $error['message'].". Query: ".(string)($db->getLastQuery());
                $success = false;
                break;
            }
        }
        if ($success)
            echo "Sukses";
        echo $n;
    }

    function prosesNotaItem()
    {
        $db = db_connect();
        $string = file_get_contents("public/backup/transkasbon_202205_228.json");
        $json = json_decode($string);
        $notaitem = $json->nit;
        $success = true;
        foreach($notaitem as $i) {
            $query = $db->query("SELECT MAX(nit_id) max_id,
                DATE_FORMAT(rbs_currentdate(), '%Y') yearof_id
                FROM pos_notaitem
                WHERE LEFT(nit_id,4)=DATE_FORMAT(rbs_currentdate(), '%Y')");
            $error = $db->error();
            if ($error['code'] == 0) {
                $row = $query->getRow();
                if (!$row->max_id) $new_ntmid = $row->yearof_id."00000000001";
                else $new_ntmid = $row->yearof_id.
                    sprintf("%011d",((int)substr($row->max_id,4,11))+1);
                $query = $db->query("SELECT not_id FROM pos_nota
                    WHERE not_lok_id=2 AND not_nomor='".$i->nomor."'");
                $row = $query->getRow();
                if ($row) $not_id = $row->not_id;
                else $not_id = null;
                $query = $db->query("SELECT itm_id FROM pos_item
                    WHERE itm_oldid=".$i->itm_id);
                $row = $query->getRow();
                if ($row) $itm_id = $row->itm_id;
                else $itm_id = null;
                if ($not_id && $itm_id) {
                    $db->query("INSERT INTO pos_notaitem(nit_id, nit_not_id, nit_itm_id,
                        nit_qty, nit_diskon, nit_total, nit_satuan1, nit_satuan1hpp,
                        nit_satuan1hrg, nit_satuan0, nit_satuan0hpp, nit_satuan0hrg,
                        nit_satuan0of1) VALUES(".$new_ntmid.", '".$not_id."',
                        ".$itm_id.", ".$i->qty.", ".$i->diskon.", ".$i->total.",
                        '".$i->satuan1."', ".$i->satuan1hpp.", ".$i->satuan1hrg.",
                        '".$i->satuan0."', ".$i->satuan0hpp.", ".$i->satuan0hrg.",
                        ".$i->satuan0of1.")");
                    $error = $db->error();
                    if ($error['code'] != 0) {
                        echo $error['message'].". Query: ".(string)($db->getLastQuery());
                        $success = false;
                        break;
                    }
                }
                else {
                    echo "not_id atau itm_id tidak ditemukan";
                }
            }
            else {
                echo $error['message'].". Query: ".(string)($db->getLastQuery());
                $success = false;
                break;
            }
        }
        if ($success)
            echo "Sukses";
        echo $n;
    }
}