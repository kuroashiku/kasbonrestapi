<?php 
namespace App\Models;
use CodeIgniter\Model;

class ShortcutModel extends Model
{
    public function read()
    {
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $b = date_format($date, 'm');
        $t = date_format($date, 'Y');
        $lastday = cal_days_in_month(CAL_GREGORIAN, $b, $t);
        $result['data'] = [[
            'nama' => 'Laporan barang paling laris',
            'command' => 'bestselling*tgl-bln-thn*tgl-bln-thn',
            'default' => 'bestselling*1-'.$b.'-'.$t.'*'.$lastday.'-'.$b.'-'.$t
        ],[
            'nama' => 'Laporan barang penjualan tertinggi',
            'command' => 'bestvalue*tgl-bln-thn*tgl-bln-thn',
            'default' => 'bestvalue*1-'.$b.'-'.$t.'*'.$lastday.'-'.$b.'-'.$t
        ],[
            'nama' => 'Laporan laba kotor',
            'command' => 'grossprofit*tgl-bln-thn*tgl-bln-thn',
            'default' => 'grossprofit*1-'.$b.'-'.$t.'*'.$lastday.'-'.$b.'-'.$t

//////////////// contoh untuk shortcut yang proses bukan report
/*        ],[
            'nama' => 'Reset data penjualan',
            'command' => 'reset*bln-thn',
            'default' => 'reset*'.$b.'-'.$t,
            'base' => 'reset',
            'warning' => 'Proses ini akan menghapus semua data penjualan bulan: '.
                $b.'-'.$t.'. Apakah dilanjutkan?'*/
        ],[
            'nama' => 'Batal'
        ]];
        return $result;
    }
}