<?php 
namespace App\Models;
use CodeIgniter\Model;

class BomModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $clause = '';
        $query = $db->query("SELECT b.*, i.itm_nama, i.itm_satuan1,
            i.itm_satuan1hpp, i.itm_satuan2, i.itm_satuan2hpp, i.itm_satuan2of1,
            i.itm_satuan3, i.itm_satuan3hpp, i.itm_satuan3of1
            FROM pos_bom b
            LEFT JOIN pos_item i ON itm_id=bom_itm_id_bahan
            WHERE bom_itm_id=".$_POST['itm_id']."
            ORDER BY bom_id");
        $error = $db->error();
        if ($error['code'] == 0) {
            $rows = $query->getResult();
            foreach($rows as &$row) {
                $row->bom_qtydec = $row->bom_qty*100%100;
                $row->bom_qty = intval($row->bom_qty);
            }
            $result['data'] = $rows;
            $result['status'] = 'success';
        }
        else {
            $result['error']['title'] = 'Baca Data Bill of Material';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function isCircular($db, $bom_itm_id, $bom_itm_id_bahan)
    {
        $circular = false;
        if ($bom_itm_id == $bom_itm_id_bahan)
            $circular = true;
        else {
            // Mencari kemungkinan circular dengan parentnya
            $query = $db->query("SELECT * FROM pos_bom
                WHERE bom_itm_id_bahan=".$bom_itm_id);
            $rows = $query->getResult();
            foreach($rows as $row) {
                if ($row->bom_itm_id == $bom_itm_id_bahan) {
                    $circular = true;
                    break;
                }
                else {
                    $circular = $this->isCircular($db, $row->bom_itm_id,
                        $bom_itm_id_bahan);
                }
            }
            // Mencari kemungkinan circular dengan childnya
            if (!$circular) {
                $query = $db->query("SELECT * FROM pos_bom
                    WHERE bom_itm_id=".$bom_itm_id_bahan);
                $rows = $query->getResult();
                foreach($rows as $row) {
                    if ($row->bom_itm_id_bahan == $bom_itm_id) {
                        $circular = true;
                        break;
                    }
                    else {
                        $circular = $this->isCircular($db, $bom_itm_id,
                            $row->bom_itm_id_bahan);
                    }
                }
            }
        }
        return $circular;
    }

    function isExists($db, $row)
    {
        $exists = false;
        $query = $db->query("SELECT COUNT(*) total FROM pos_bom
            WHERE bom_itm_id=".$row['bom_itm_id']." AND
            bom_itm_id_bahan=".$row['bom_itm_id_bahan']);
        if ($query->getRow()->total > 0)
            $exists = true;
        return $exists;
    }

    function add()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $result['data'] = [];
        $data = $_POST;

        if ($this->isExists($db, $data)) {
            $result['error']['title'] = 'Tambah Data Bill of Material';
            $result['error']['message'] = 'Material yang dipilih '.
            'sudah ada di daftar, jadi tidak bisa ditambahkan lagi';
            $result['status'] = 'failed';
            return $result;
        }

        if ($this->isCircular($db, $data['bom_itm_id'],
            $data['bom_itm_id_bahan'])) {
            $result['error']['title'] = 'Tambah Data Bill of Material';
            $result['error']['message'] = 'Terjadi circular, artinya material '.
                'yang dipilih sudah dipakai di hulu atau hilir. Atau material '.
                'di hilir sudah dipakai di hulu dan sebaliknya';
            $result['status'] = 'failed';
            return $result;
        }

        // Loop ID mulai dari 1 dicari yang belum terpakai, karena mungkin ada ID
        // yang dihapus di tengah, jadi tidak harus MAX(bom_id)+1
        // Dan pencarian ini tidak tergantung com_id

        $query = $db->query("SELECT bom_id FROM pos_bom ORDER BY bom_id");
        $error = $db->error();
        if ($error['code'] == 0) {
            $rows = $query->getResult();
            $available_id = null;
            foreach($rows as $row) {
                if (!$available_id) $available_id = $row->bom_id+1;
                elseif ($available_id == $row->bom_id) $available_id++;
                else break;
            }
            $data['bom_id'] = $available_id?$available_id:1;
            $db->query("INSERT INTO pos_bom(bom_id) VALUES(".$data['bom_id'].")");
            $error = $db->error();
            if ($error['code'] != 0) {
                $result['error']['title'] = 'Simpan ID Bill of Material Baru';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
            }
        }
        else {
            $result['error']['title'] = 'Hitung ID Bill of Material';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        if ($result['status'] == 'success') {
            $db->query("UPDATE pos_bom SET
                bom_itm_id=".$data['bom_itm_id'].",
                bom_itm_id_bahan=".$data['bom_itm_id_bahan'].",
                bom_qty=0, bom_itm_satuan='".$data['bom_itm_satuan']."',
                bom_itm_satuanhpp=".$data['bom_itm_satuanhpp'].",
                bom_itm_satuanof1=".$data['bom_itm_satuanof1'].",
                bom_hpp=0
                WHERE bom_id=".$data['bom_id']);
            $error = $db->error();
            if ($error['code'] == 0) {
                $query = $db->query("SELECT b.*, i.itm_nama, i.itm_satuan1,
                    i.itm_satuan1hpp, i.itm_satuan2, i.itm_satuan2hpp,
                    i.itm_satuan2of1, i.itm_satuan3, i.itm_satuan3hpp,
                    i.itm_satuan3of1
                    FROM pos_bom b
                    LEFT JOIN pos_item i ON itm_id=bom_itm_id_bahan
                    WHERE bom_itm_id=".$_POST['bom_itm_id']."
                    ORDER BY bom_id");
                $rows = $query->getResult();
                foreach($rows as &$row) {
                    $row->bom_qtydec = $row->bom_qty*100%100;
                    $row->bom_qty = intval($row->bom_qty);
                }
                $result['data'] = $rows;
            }
            else {
                $result['error']['title'] = 'Update Data Bill of Material';
                $result['error']['message'] = 'Proses update data gagal. Query: '.
                    (string)($db->getLastQuery());
                $result['status'] = 'failed';
            }
        }
        return $result;
    }

    function saveBom()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $rows = $_POST['rows'];
        foreach($rows as $row) {
            $row['bom_qty'] = ($row['bom_qty']*100+$row['bom_qtydec'])/100;
            $db->query("UPDATE pos_bom SET
                bom_qty=".$row['bom_qty'].",
                bom_itm_satuan='".$row['bom_itm_satuan']."',
                bom_itm_satuanhpp=".$row['bom_itm_satuanhpp'].",
                bom_itm_satuanof1=".$row['bom_itm_satuanof1'].",
                bom_hpp=".$row['bom_hpp']."
                WHERE bom_id=".$row['bom_id']);
            $error = $db->error();
            if ($error['code'] != 0) {
                $result['error']['title'] = 'Update Data Bill of Material';
                $result['error']['message'] = $error['message'];
                $result['status'] = 'failed';
                break;
            }
        }
        if ($result['status'] == 'success') {
            $_POST['itm_id'] = $rows[0]['bom_itm_id'];
            $result = $this->read();
        }
        return $result;
    }

    function deleteBom()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $query = $db->query("SELECT bom_itm_id itm_id FROM pos_bom
            WHERE bom_id=".$_POST['bom_id']);
        $_POST['itm_id'] = $query->getRow()->itm_id;
        $db->query("DELETE FROM pos_bom WHERE bom_id=".$_POST['bom_id']);
        $error = $db->error();
        if ($error['code'] == 0) {
            $result = $this->read();
        }
        else {
            $result['error']['title'] = 'Hapus Data Bill of Material';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }
}