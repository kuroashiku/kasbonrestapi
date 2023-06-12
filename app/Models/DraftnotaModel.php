<?php 
namespace App\Models;
use CodeIgniter\Model;

class DraftnotaModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $strSelect = "SELECT dot_id, dot_total total,
            kas_id, kas_nama, cus_id, cus_nama,
            dot_diskon diskon, dot_disnom disnom, dot_catatan catatan";
        $strQuery = $strSelect." FROM pos_draftnota
            LEFT JOIN pos_kasir ON kas_id=dot_kas_id
            LEFT JOIN pos_customer ON cus_id=dot_cus_id
            WHERE dot_kas_id=".$_POST['kas_id'];
        if (isset($_POST['q'])) {
            $strQuery .= " AND (dot_catatan LIKE '%".$_POST['q']."%'
                OR EXISTS(SELECT 1 FROM pos_draftnotaitem
                LEFT JOIN pos_item ON itm_id=dit_itm_id
                WHERE dit_dot_id=dot_id AND itm_nama LIKE '%".$_POST['q']."%'))";
        }
        $strQuery .= " ORDER BY dot_id DESC";
        $query = $db->query($strQuery);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['status'] = 'success';
            $result['data'] = $query->getResult();
            $strSelect = "SELECT dit_itm_id itm_id,
                itm_nama, dit_qty qty,
                dit_total total, dit_diskon diskon, dit_disnom disnom,
                dit_satuan1 satuan1, dit_satuan1hpp satuan1hpp, dit_satuan1hrg satuan1hrg,
                dit_satuan2 satuan2, dit_satuan2hpp satuan2hpp, dit_satuan2hrg satuan2hrg,
                dit_satuan2of1 satuan2of1,
                dit_satuan3 satuan3, dit_satuan3hpp satuan3hpp, dit_satuan3hrg satuan3hrg,
                dit_satuan3of1 satuan3of1, dit_konvidx konvidx";
            foreach($result['data'] as &$dot) {
                $query = $db->query($strSelect." FROM pos_draftnotaitem
                    LEFT JOIN pos_draftnota ON dot_id=dit_dot_id
                    LEFT JOIN pos_item ON itm_id=dit_itm_id
                    WHERE dit_dot_id=".$dot->dot_id);
                $error = $db->error();
                if ($error['code'] == 0) {
                    $dot->notaitems = $query->getResult();
                }
                else {
                    $result['error']['title'] = 'Baca Data Draft Nota Item';
                    $result['error']['message'] = $error['message'].'.Sql: '.
                        (string)($db->getLastQuery());
                    $result['status'] = 'failed';
                    break;
                }
            }
        }
        else {
            $result['error']['title'] = 'Baca Data Draft Nota';
            $result['error']['message'] = $error['message'].
                '. Query: '.(string)($db->getLastQuery());
        }
        return $result;
    }

    function saveDraftnota()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $date = date_create(null, timezone_open("Asia/Jakarta"));

        // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
        // yang dihapus di tengah, jadi tidak harus MAX(dot_id)+1
        // Dan pencarian ini tidak tergantung lok_id

        $query = $db->query("SELECT dot_id FROM pos_draftnota ORDER BY dot_id");
        $error = $db->error();
        if ($error['code'] == 0) {
            $rows = $query->getResult();
            $available_id = null;
            foreach($rows as $row) {
                if (!$available_id) $available_id = $row->dot_id+1;
                elseif ($available_id == $row->dot_id) $available_id++;
                else break;
            }
            $new_dotid = $available_id?$available_id:1;
            $db->query("INSERT INTO pos_draftnota(dot_id,dot_tanggal,dot_total,
                dot_kas_id,dot_kasir,dot_cus_id,dot_lok_id,dot_catatan,
                dot_diskon,dot_disnom) VALUES(".$new_dotid.",'".
                date_format($date, 'Y-m-d H:i:s')."',".$_POST['total'].",".
                $_POST['kas_id'].",'".$_POST['kas_nama']."',".$_POST['cus_id'].",".
                $_POST['lok_id'].",'".$_POST['catatan']."',".
                $_POST['diskon'].",".$_POST['disnom'].")");
            $error = $db->error();
            if ($error['code'] != 0) {
                $result['error']['title'] = 'Simpan Header Draft Nota';
                $result['error']['message'] = $error['message'].'.Sql: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        else {
            $result['error']['title'] = 'Hitung ID Draft Nota';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        if ($result['status'] == 'success') {
            $item = $_POST['rows'];
            foreach($item as $i) {
                // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
                // yang dihapus di tengah, jadi tidak harus MAX(dit_id)+1
                // Dan pencarian ini tidak tergantung lok_id

                $query = $db->query("SELECT dit_id FROM pos_draftnotaitem
                    ORDER BY dit_id");
                $error = $db->error();
                if ($error['code'] == 0) {
                    $rows = $query->getResult();
                    $available_id = null;
                    foreach($rows as $row) {
                        if (!$available_id) $available_id = $row->dit_id+1;
                        elseif ($available_id == $row->dit_id) $available_id++;
                        else break;
                    }
                    $new_ditid = $available_id?$available_id:1;
                    $db->query("INSERT INTO pos_draftnotaitem(dit_id,dit_dot_id,
                        dit_itm_id,dit_qty,dit_diskon,dit_disnom,dit_total,
                        dit_satuan1,dit_satuan1hpp,dit_satuan1hrg,
                        dit_satuan2,dit_satuan2hpp,dit_satuan2hrg,dit_satuan2of1,
                        dit_satuan3,dit_satuan3hpp,dit_satuan3hrg,dit_satuan3of1,
                        dit_konvidx)
                        VALUE(".$new_ditid.",".$new_dotid.",".$i['itm_id'].",".
                        $i['qty'].",".$i['diskon'].",".$i['disnom'].",".$i['total'].",'".
                        $i['satuan1']."',".$i['satuan1hpp'].",".$i['satuan1hrg'].",".
                        ($i['satuan2']?"'".$i['satuan2']."',":"null,").
                        ($i['satuan2hpp']?$i['satuan2hpp']:"null").",".
                        ($i['satuan2hrg']?$i['satuan2hrg']:"null").",".
                        ($i['satuan2of1']?$i['satuan2of1']:"null").",".
                        ($i['satuan3']?"'".$i['satuan3']."',":"null,").
                        ($i['satuan3hpp']?$i['satuan3hpp']:"null").",".
                        ($i['satuan3hrg']?$i['satuan3hrg']:"null").",".
                        ($i['satuan3of1']?$i['satuan3of1']:"null").",".
                        $i['konvidx'].")");
                    $error = $db->error();
                    if ($error['code'] != 0) {
                        $result['error']['title'] = 'Simpan Draft Nota Item';
                        $result['error']['message'] = 'Proses gagal. Query: '.
                            (string)($db->getLastQuery());
                        $result['status'] = 'failed';
                        break;
                    }
                }
                else {
                    $result['error']['title'] = 'Pembuatan ID Draft Nota Item';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                    break;
                }
            }
        }
        return $result;
    }

    function deleteDraftnota()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $db->query("DELETE FROM pos_draftnotaitem WHERE dit_dot_id=".$_POST['dot_id']);
        $db->query("DELETE FROM pos_draftnota WHERE dot_id=".$_POST['dot_id']);
        return $result;
    }
}