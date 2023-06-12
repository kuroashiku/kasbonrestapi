<?php 
namespace App\Models;
use CodeIgniter\Model;

class SalesrepModel extends Model
{
    function create()
    {
        $par = $_POST;
        $db = db_connect();
        $rkpRows = [];
        $rkpData = [];
        $rkpCols = [];
        if ($par['bulan'] == 0) $query = $db->query("SELECT DISTINCT sft_kasir FROM pos_shift
            WHERE DATE_FORMAT(sft_checkin, '%Y')=".$par['tahun']."
            AND sft_lok_id=".$par['lok_id']);
        else $query = $db->query("SELECT DISTINCT sft_kasir FROM pos_shift
            WHERE DATE_FORMAT(sft_checkin, '%Y')=".$par['tahun']."
            AND DATE_FORMAT(sft_checkin, '%c')=".$par['bulan']."
            AND sft_lok_id=".$par['lok_id']);
        $rows = $query->getResult();
        foreach($rows as $r)
            array_push($rkpRows, $r->sft_kasir);
        
        if ($par['bulan'] == 0) {
            $rkpCols = [1,2,3,4,5,6,7,8,9,10,11,12];
            $query = $db->query("SELECT not_kasir,
                DATE_FORMAT(not_tanggal, '%c') col, SUM(not_total) total
                FROM pos_nota
                WHERE DATE_FORMAT(not_tanggal, '%Y')=".$par['tahun']."
                AND not_lok_id=".$par['lok_id']."
                GROUP BY not_kasir, DATE_FORMAT(not_tanggal, '%c')");
            $rows = $query->getResult();
        }
        else {
            $days = cal_days_in_month(CAL_GREGORIAN, $par['bulan'], $par['tahun']);
            for($i=1;$i<=$days;$i++)
                array_push($rkpCols, $i);
            $query = $db->query("SELECT not_kasir,
                DATE_FORMAT(not_tanggal, '%e') col, SUM(not_total) total
                FROM pos_nota
                WHERE DATE_FORMAT(not_tanggal, '%Y')=".$par['tahun']."
                AND DATE_FORMAT(not_tanggal, '%c')=".$par['bulan']."
                AND not_lok_id=".$par['lok_id']."
                GROUP BY not_kasir, DATE_FORMAT(not_tanggal, '%e')");
            $rows = $query->getResult();
        }
        foreach($rows as $r)
            $rkpData[$r->not_kasir][$r->col] = $r->total;
        $result['rkpnama'] = "REKAP PENJUALAN PER SHIFT";
        $result['rkprows'] = $rkpRows;
        $result['rkpcols'] = $rkpCols;
        $result['rkpdata'] = $rkpData;
        return $result;
    }

    function mcreate()
    {
        $par = $_POST;
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];

        $query = $db->query("SELECT DAY(not_tanggal) tgl,
            SUM(not_total*(100-not_diskon)/100) omzet
            FROM pos_nota
            WHERE YEAR(not_tanggal)=".$par['tahun']." AND
            MONTH(not_tanggal)=".$par['bulan']." AND not_lok_id=".$par['lok_id']."
            GROUP BY DAY(not_tanggal)
            ORDER BY DAY(not_tanggal)");
        $error = $db->error();
        if ($error['code'] == 0) {
            $result['data'] = $query->getResult();
            $rows = $query->getResult();
            foreach($rows as $r) {
                $map[$r->tgl] = $r->omzet;
            }
            $listTgl = [];
            $listDataHarian = [];
            $days = cal_days_in_month(CAL_GREGORIAN, $par['bulan'], $par['tahun']);
            for($i=1;$i<=$days;$i++) {
                array_push($listTgl, $i);
                if(isset($map[$i]))
                    array_push($listDataHarian, $map[$i]/1000);
                else
                    array_push($listDataHarian, 0);
            }
            $query = $db->query("SELECT MONTH(not_tanggal) bulan,
                SUM(not_total*(100-not_diskon)/100) omzet
                FROM pos_nota
                WHERE YEAR(not_tanggal)=".$par['tahun']." AND
                not_lok_id=".$par['lok_id']."
                GROUP BY MONTH(not_tanggal)
                ORDER BY MONTH(not_tanggal)");
            $error = $db->error();
            if ($error['code'] == 0) {
                $rows = $query->getResult();
                foreach($rows as $r) {
                    $map[$r->bulan] = $r->omzet;
                }
                $listDataBulanan = [];
                for($i=1;$i<=12;$i++) {
                    if($map[$i])
                        array_push($listDataBulanan, $map[$i]/1000);
                    else
                        array_push($listDataBulanan, 0);
                }
                $result['status'] = 'success';
                $result['data']['listTgl'] = $listTgl;
                $result['data']['listDataHarian'] = $listDataHarian;
                $result['data']['listDataBulanan'] = $listDataBulanan;
            }
            else {
                $result['error']['title'] = 'Baca Data Penjualan Bulanan';
                $result['error']['message'] = $error['message'];
            }
        }
        else {
            $result['error']['title'] = 'Baca Data Penjualan Harian';
            $result['error']['message'] = $error['message'];
            $result['sql'] = (string)($db->getLastQuery());
        }
        return $result;
    }
}