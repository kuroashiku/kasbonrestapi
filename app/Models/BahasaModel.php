<?php 
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

class BahasaModel extends Model
{
    function read()
    {
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT * FROM pos_bahasa");
        $error = $db->error();
        if ($error['code'] == 0) {
            $rows = $query->getResult();
            $result['data'] = $rows;
        }
        else {
            $result['error']['title'] = 'Baca Data Bahasa';
            $result['error']['message'] = $error['message'];
            $result['status'] = 'failed';
        }
        return $result;
    }
}
