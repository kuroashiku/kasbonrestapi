<?php 
namespace App\Models;
use CodeIgniter\Model;

class CarabayarModel extends Model
{
    function read()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $query = $db->query("SELECT b.*, COUNT(n.not_id) total FROM pos_carabayar b
            LEFT JOIN pos_nota n ON not_carabayar=byr_kode
            AND not_lok_id=".$_POST['lok_id']."
            GROUP BY byr_kode
            ORDER BY COUNT(n.not_id) DESC");
        $rows = $query->getResult();
        foreach($rows as &$row) {
            $path = 'public/images/'.$row->byr_png.'.png';
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $row->logouri = 'data:image/'.$type.';base64,'.base64_encode($data);
        }
        $result['data'] = $rows;
        return $result;
    }
}