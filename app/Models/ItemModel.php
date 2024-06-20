<?php 
namespace App\Models;
use CodeIgniter\Model;

class ItemModel extends Model
{
    function columnList()
    {
        // itm_photo tidak ikut diselect karena data BLOB terlalu besar
        // menyebabkan lama saat loading
        // itm_photo hanya diload saat mau menampilkan saja

        return 'itm_id,itm_kode,itm_lok_id,itm_nama,itm_satuan,itm_stokaman,
        itm_tgstokopnam,itm_stok,itm_satuan1,itm_satuan1hpp,itm_satuan1hrg,
        itm_satuan2,itm_satuan2hpp,itm_satuan2hrg,itm_satuan2of1,
        itm_satuan3,itm_satuan3hpp,itm_satuan3hrg,itm_satuan3of1,
        itm_gallery,itm_pakaistok,itm_durasi,itm_satuandurasi,itm_sellable,itm_buyable,itm_urlimage1, itm_urlimage2, itm_urlimage3, itm_kategori';
    }

    function read()
    {
        $db = db_connect();
        $page = isset($_POST['page'])?$_POST['page']:null;
        $rows = isset($_POST['rows'])?$_POST['rows']:null;
        $limit = isset($_POST['limit'])?$_POST['limit']:null;
        $filter = "itm_lok_id=".$_POST['lok_id']." AND itm_deleteddate IS NULL ";
        if (isset($_POST['key_val']))
            $filter .= " AND (itm_nama LIKE '%".$_POST['key_val']."%' OR itm_kode LIKE '%".$_POST['key_val']."%')";
        if (isset($_POST['category']))
            $filter .= " AND (".$_POST['category'].") ";
        if (isset($_POST['sellable']))
            $filter .= " AND itm_sellable='true'";
        if (isset($_POST['buyable']))
            $filter .= " AND itm_buyable='true'";
        if ($page) {
            $query = $db->query("SELECT COUNT(*) total
                FROM pos_item WHERE ".$filter);
            $result['total'] = $query->getRow('total');
        }
        $queryStr = "SELECT ".$this->columnList()." FROM pos_item
            WHERE ".$filter." ORDER BY itm_id DESC";
        if ($page)
            $queryStr .= " LIMIT " . ($page - 1) * $rows . "," . $rows;
        if ($limit)
            $queryStr .= " LIMIT " . $limit ;
        $query = $db->query($queryStr);
        $data = $query->getResult();
        $result['error']='.Sql: '.
                        (string)($db->getLastQuery());
        $result['data'] = $data;
        
        $result['status'] = 'success';
        return $result;
    }

    function readGallery()
    {
        $db = db_connect();
        $filter = "";
        if (isset($_POST['key_val']))
            $filter .= " AND (itm_nama LIKE '%".$_POST['key_val']."%' OR itm_kode LIKE '%".$_POST['key_val']."%')";
        $query = $db->query("SELECT ".$this->columnList().",itm_photo FROM pos_item
            WHERE itm_lok_id=".$_POST['lok_id']." AND itm_gallery=1 ".$filter."
            ORDER BY itm_nama");
        $rows = $query->getResult();
        foreach($rows as &$row) {
            if ($row->itm_photo) {
                $temp = base64_decode($row->itm_photo);
                $imageObj = imagecreatefromstring($temp);
                $smallImageObj = imagescale($imageObj, 400);
                ob_start();
                imagejpeg($smallImageObj);
                $imageData = ob_get_contents();
                ob_end_clean();
                $row->itm_photo = base64_encode($imageData);
            }
        }
        $result['data'] = $rows;
        $result['error']='.Sql: '.
                        (string)($db->getLastQuery());
        $result['status'] = 'success';
        return $result;
    }
    
    function readForCategories()
    {
        $db = db_connect();
        $query = $db->query("SELECT itm_kategori, count(*) itm_total FROM pos_item
            WHERE itm_lok_id=".$_POST['lok_id']." AND itm_kategori IS NOT NULL AND itm_kategori != '' GROUP BY itm_kategori 
             UNION
            SELECT 'TANPA KATEGORI' as itm_kategori, count(*) itm_total FROM pos_item
            WHERE itm_lok_id=".$_POST['lok_id']." AND (itm_kategori IS NULL OR itm_kategori = '') 
            ORDER BY itm_kategori ASC");
        $data = $query->getResult();
        $data2=[];
        $detailganjil=[];
        $detailgenap=[];
        $tempcount=0;
        $tempnama='';
        $tempcount2=0;
        $tempnama2='';
        foreach($data as $key =>$row) {
            if ($key%2==0) {
                $tempcount=$row->itm_total;
                $tempnama=$row->itm_kategori;
                if($key==count($data)-1){
                    array_push($data2,array('count_ganjil' => $tempcount,'nama_ganjil' => $tempnama,'count_genap' => 0,'nama_genap' => ''));
                }
            }
            else{
                array_push($data2,array('count_ganjil' => $tempcount,'nama_ganjil' => $tempnama,'count_genap' => $row->itm_total?$row->itm_total:0,'nama_genap' => $row->itm_kategori?$row->itm_kategori:''));
            }
        }
        $result['error']='.Sql: '.
                        (string)($db->getLastQuery());
        if (isset($_POST['all']))
        $result['data'] = $data;
        else
        $result['data'] = $data2;
        $result['status'] = 'success';
        return $result;
    }

    function readForPO()
    {
        $db = db_connect();
        $query = $db->query("SELECT ".$this->columnList()." FROM pos_item
            WHERE itm_lok_id=".$_POST['lok_id']." AND itm_pakaistok=1 AND
            NOT EXISTS(SELECT 1 FROM pos_bom WHERE bom_itm_id=itm_id)
            ORDER BY itm_id DESC");
        $data = $query->getResult();
        $result['data'] = $data;
        $result['status'] = 'success';
        return $result;
    }

    function readForBOM()
    {
        $db = db_connect();
        $query = $db->query("SELECT ".$this->columnList()." FROM pos_item
            WHERE itm_lok_id=".$_POST['lok_id']." AND itm_pakaistok=1 AND
            EXISTS(SELECT 1 FROM pos_bom WHERE bom_itm_id=itm_id OR
            bom_itm_id_bahan=itm_id)
            ORDER BY itm_id DESC");
        $data = $query->getResult();
        $result['data'] = $data;
        $result['status'] = 'success';
        return $result;
    }

    function search()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $qClause = "";
        if (isset($_POST['q']))
            $qClause = " AND (itm_kode='".$_POST['q']."' OR itm_nama LIKE '%".$_POST['q']."%')";
        $queryStr = "SELECT ".$this->columnList()." FROM pos_item
            WHERE itm_lok_id=".$_POST['lok_id'].$qClause."ORDER BY itm_id DESC";
        $query = $db->query($queryStr);
        $error = $db->error();
        if ($error['code'] == 0) {
            $rows = $query->getResult();
            if (sizeof($rows) > 0) {
                $result['data'] = $rows;
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Cari Data Barang';
                $result['error']['message'] = 'Barang dengan kode '.$_POST['q'].
                    ' tidak ditemukan';
            }
        }
        else {
            $result['error']['title'] = 'Cari Data Barang';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function readThumbnail()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT itm_photo FROM pos_item
            WHERE itm_id=".$_POST['itm_id']);
        $row = $query->getRow();
        if ($row) {
            $result['status'] = 'success';
            $result['imageData'] = $row->itm_photo;
        }
        return $result;
    }

    function getDetail()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT ".$this->columnList()." FROM pos_item
            WHERE itm_id='".$_POST['itm_id']."'");
        $row = $query->getRow();
        if ($row) {
            $result['status'] = 'success';
            $result['row'] = $row;
        }
        return $result;
    }

    function saveItem()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;
        $query = $db->query("SELECT COUNT(*) total FROM pos_item
            WHERE itm_kode='".$data['itm_kode']."' AND itm_lok_id=".$data['itm_lok_id']."
                AND itm_id<>".$data['itm_id']);
        if ($query->getRow()->total > 0) {
            $result['error']['title'] = 'Simpan Data Barang';
            $result['error']['message'] = 'Kode Item "'.$data['itm_kode'].'" sudah ada';
            $result['status'] = 'failed';
            return $result;
        }
        if ($data['itm_id'] == -1) { // new record
            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(itm_id)+1
            // Dan pencarian ini tidak tergantung lok_id

            $query = $db->query("SELECT itm_id FROM pos_item ORDER BY itm_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->itm_id+1;
                    elseif ($available_id == $row->itm_id) $available_id++;
                    else break;
                }
                $data['itm_id'] = $available_id?$available_id:1;
                $db->query("INSERT INTO pos_item(itm_id) VALUES(".$data['itm_id'].")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan ID Barang Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Barang';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        if ($result['status'] == 'success') {
            $builder = $db->table('pos_item');
            $builder->set('itm_kode', $data['itm_kode']);
            $builder->set('itm_lok_id', $data['itm_lok_id']);
            $builder->set('itm_nama', $data['itm_nama']);
            $builder->set('itm_kategori', $data['itm_kategori']);
            $builder->set('itm_satuan1', $data['itm_satuan1']);
            $builder->set('itm_satuan1hpp', $data['itm_satuan1hpp']);
            $builder->set('itm_satuan1hrg', $data['itm_satuan1hrg']);
            $builder->set('itm_sellable', $data['itm_sellable']);
            $builder->set('itm_buyable', $data['itm_buyable']);
            $builder->set('itm_stokaman', $data['itm_stokaman']);
            $builder->set('itm_tgstokopnam', $data['itm_tgstokopnam']);
            $builder->set('itm_stok', $data['itm_stok']);
            $builder->set('itm_urlimage1', $data['itm_urlimage1']);
            $builder->set('itm_urlimage2', $data['itm_urlimage2']);
            $builder->set('itm_urlimage3', $data['itm_urlimage3']);
            if($data['itm_satuan2']!='') {
                if($data['itm_satuan2']=='***') {
                    $builder->set('itm_satuan2', '');
                    $builder->set('itm_satuan2hpp', 0);
                    $builder->set('itm_satuan2hrg', 0);
                    $builder->set('itm_satuan2of1', 0);
                }
                else {
                    $builder->set('itm_satuan2', $data['itm_satuan2']);
                    $builder->set('itm_satuan2hpp', $data['itm_satuan2hpp']);
                    $builder->set('itm_satuan2hrg', $data['itm_satuan2hrg']);
                    $builder->set('itm_satuan2of1', $data['itm_satuan2of1']);
                }
            }
            if($data['itm_satuan3']!='') {
                if($data['itm_satuan3']=='***') {
                    $builder->set('itm_satuan3', '');
                    $builder->set('itm_satuan3hpp', 0);
                    $builder->set('itm_satuan3hrg', 0);
                    $builder->set('itm_satuan3of1', 0);
                }
                else {
                    $builder->set('itm_satuan3', $data['itm_satuan3']);
                    $builder->set('itm_satuan3hpp', $data['itm_satuan3hpp']);
                    $builder->set('itm_satuan3hrg', $data['itm_satuan3hrg']);
                    $builder->set('itm_satuan3of1', $data['itm_satuan3of1']);
                }
            }
            if($data['itm_satuan4']!='') {
                if($data['itm_satuan4']=='***') {
                    $builder->set('itm_satuan4', '');
                    $builder->set('itm_satuan4hpp', 0);
                    $builder->set('itm_satuan4hrg', 0);
                    $builder->set('itm_satuan4of1', 0);
                }
                else {
                    $builder->set('itm_satuan4', $data['itm_satuan4']);
                    $builder->set('itm_satuan4hpp', $data['itm_satuan4hpp']);
                    $builder->set('itm_satuan4hrg', $data['itm_satuan4hrg']);
                    $builder->set('itm_satuan4of1', $data['itm_satuan4of1']);
                }
            }
            if(isset($data['itm_photo'])) {
                $builder->set('itm_photo', $data['itm_photo']);
                $builder->set('itm_gallery', $data['itm_gallery']);
            }
            if(isset($data['itm_pakaistok'])) {
                $builder->set('itm_pakaistok', $data['itm_pakaistok']);
                $builder->set('itm_durasi', $data['itm_durasi']);
                $builder->set('itm_satuandurasi', $data['itm_satuandurasi']);
            }
            $builder->where('itm_id', $data['itm_id'], false);
            if ($builder->update()) {
                $query = $db->query("SELECT ".$this->columnList()." FROM pos_item
                    WHERE itm_id='".$data['itm_id']."'");
                $row = $query->getRow();
                $result['data'] = $row;
            }
            else {
                $result['error']['title'] = 'Update Data Barang';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function isUsed($db, $itm_id)
    {
        $query = $db->query("SELECT COUNT(*) total FROM pos_notaitem
            WHERE nit_itm_id=".$itm_id);
        if ($query->getRow()->total > 0)
            return true;
        $query = $db->query("SELECT COUNT(*) total FROM inv_poitem
            WHERE po_itm_id=".$itm_id);
        if ($query->getRow()->total > 0)
            return true;
        $query = $db->query("SELECT COUNT(*) total FROM pos_rcvitem
            WHERE rcv_itm_id=".$itm_id);
        if ($query->getRow()->total > 0)
            return true;
        $query = $db->query("SELECT COUNT(*) total FROM pos_bom
            WHERE bom_itm_id=".$itm_id." OR
            bom_itm_id_bahan=".$itm_id);
        if ($query->getRow()->total > 0)
            return true;
        return false;
    }

    function deleteItem()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        if ($this->isUsed($db, $_POST['itm_id'])) {
            $result['error']['title'] = 'Hapus Data Barang';
            $result['error']['message'] = 'Item ini sudah dipakai jadi tidak '.
                'bisa dihapus';
        }
        else {
            $db->query("DELETE FROM pos_item WHERE itm_id=".$_POST['itm_id']);
            $error = $db->error();
            if ($error['code'] == 0)
                $result['status'] = 'success';
            else {
                $result['error']['title'] = 'Hapus Data Barang';
                $result['error']['message'] = $error['message'];
            }
        }
        return $result;
    }
    
    function updateDeleteItem()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $db->query("UPDATE pos_item SET itm_deleteddate='".date("Y-m-d H:i:s")."' WHERE itm_id=".$_POST['itm_id']);
        $result['error']='.Sql: '.
                        (string)($db->getLastQuery());
        
        return $result;
    }

    function import()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $json = json_decode($_POST['data']);
        foreach($json as $data) {
            $query = $db->query("SELECT COUNT(*) total FROM pos_item
                WHERE itm_kode='".$data->kode."' AND itm_lok_id=".$_POST['lok_id']);
            if ($query->getRow()->total > 0)
                continue;

            // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
            // yang dihapus di tengah, jadi tidak harus MAX(itm_id)+1
            // Dan pencarian ini tidak tergantung lok_id

            $query = $db->query("SELECT itm_id FROM pos_item ORDER BY itm_id");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                $available_id = null;
                foreach($rows as $row) {
                    if (!$available_id) $available_id = $row->itm_id+1;
                    elseif ($available_id == $row->itm_id) $available_id++;
                    else break;
                }
                $newid = $available_id?$available_id:1;
                $db->query("INSERT INTO pos_item(itm_id) VALUES(".$newid.")");
                $error = $db->error();
                if ($error['code'] != 0) {
                    $result['error']['title'] = 'Simpan ID Barang Baru';
                    $result['error']['message'] = $error['message'];
                    $result['status'] = 'failed';
                }
            }
            else {
                $result['error']['title'] = 'Hitung ID Barang';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
            if ($result['status'] == 'success') {
                $builder = $db->table('pos_item');
                $builder->set('itm_kode', $data->kode);
                $builder->set('itm_lok_id', $_POST['lok_id']);
                $builder->set('itm_nama', $data->nama);
                $builder->set('itm_satuan', $data->satuan1);
                $builder->set('itm_satuan1', $data->satuan1);
                $builder->set('itm_satuan1hpp', $data->satuan1hpp);
                $builder->set('itm_satuan1hrg', $data->satuan1hrg);
                $builder->set('itm_satuan2', $data->satuan2);
                $builder->set('itm_satuan2hpp', $data->satuan2hpp);
                $builder->set('itm_satuan2hrg', $data->satuan2hrg);
                $builder->set('itm_satuan2of1', $data->satuan2of1);
                $builder->set('itm_satuan3', $data->satuan3);
                $builder->set('itm_satuan3hpp', $data->satuan3hpp);
                $builder->set('itm_satuan3hrg', $data->satuan3hrg);
                $builder->set('itm_satuan3of1', $data->satuan3of1);
                $builder->set('itm_satuan4', $data->satuan4);
                $builder->set('itm_satuan4hpp', $data->satuan4hpp);
                $builder->set('itm_satuan4hrg', $data->satuan4hrg);
                $builder->set('itm_satuan4of1', $data->satuan4of1);
                $builder->where('itm_id', $newid, false);
                if (!$builder->update()) {
                    $result['error']['title'] = 'Update Data Barang';
                    $result['error']['message'] = 'Proses update data gagal. Query: '.
                        (string)($db->getLastQuery());
                    $result['status'] = 'failed';
                }
            }
            if ($result['status'] == 'failed') {
                break;
            }
        }
        if ($result['status'] == 'success') {
            $result = $this->read();
        }
        return $result;
    }

    function proses()
    {
        $db = db_connect();
        $string = file_get_contents("public/backup/master_kasbon.json");
        $json = json_decode($string);
        $item = $json->item;
        $success = true;
        foreach($item as $i) {
            $query = $db->query("SELECT MAX(itm_id)+1 new_id FROM pos_item");
            $itm_id = $query->getRow()->new_id;
            $db->query("INSERT INTO pos_item(itm_id, itm_kode, itm_lok_id, itm_nama, itm_satuan,
                itm_stokaman, itm_tgstokopnam, itm_stok, itm_satuan1, 
                itm_satuan1hpp, itm_satuan1hrg, itm_satuan2, itm_satuan2hpp,
                itm_satuan2hrg, itm_satuan2of1, itm_satuan3, itm_satuan3hpp,
                itm_satuan3hrg, itm_satuan3of1, itm_oldid) VALUES(".$itm_id.",
                '".$i->itm_kode."', 2, '".$i->itm_nama."',
                '".$i->itm_satuan1."', ".$i->itm_stokaman.", '".$i->itm_tgstokopnam."',
                ".$i->itm_stok.", '".$i->itm_satuan1."', ".$i->itm_satuan1hpp.",
                ".$i->itm_satuan1hrg.", '".$i->itm_satuan2."', ".$i->itm_satuan2hpp.",
                ".$i->itm_satuan2hrg.", ".$i->itm_satuan2of1.",
                '".$i->itm_satuan3."', ".$i->itm_satuan3hpp.", ".$i->itm_satuan3hrg.",
                ".$i->itm_satuan3of1.",".$i->itm_id.")");
            $error = $db->error();
            if ($error['code'] != 0) {
                echo $error['message'].". Query: ".(string)($db->getLastQuery());
                $success = false;
                break;
            }
        }
        if ($success)
            echo "Sukses";
    }
}