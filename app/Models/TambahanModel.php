<?php 
namespace App\Models;
use CodeIgniter\Model;
use App\Models\ConfigModel;
use App\Models\KasirModel;

class TambahanModel extends Model
{
    function grafikBayarNota()
    {
        $db = db_connect();
        if (isset($_POST['dayly']) && $_POST['dayly']=='yes')
        {
            $queryawal=$db->query("SELECT CAST(not_tanggal AS date) AS stat_day, DAY(not_tanggal) AS perhari, 
            MONTH(not_tanggal) AS perbulan, YEAR(not_tanggal) AS pertahun FROM pos_nota 
            WHERE MONTH(not_tanggal)=".$_POST['bln']." AND YEAR(not_tanggal)=".$_POST['thn']." AND not_lok_id=".$_POST['lok_id']." 
            GROUP BY CAST(not_tanggal AS date)");
            $rowawal=$queryawal->getResult();
            foreach($rowawal as $aw) {
                $query = $db->query("SELECT  SUM(not_dibayar) AS totaldibayar,SUM(not_kembalian) AS kembalian 
                FROM pos_nota
                WHERE CAST(not_tanggal AS date) ='".$aw->stat_day."' AND not_kembalian>=0 AND not_lok_id=".$_POST['lok_id']."
                GROUP BY '".$aw->stat_day."'");
                $row=$query->getRow();
                $query3 = $db->query("SELECT SUM(not_dibayar) AS totaldibayar,SUM(not_kembalian) AS kembalian 
                FROM pos_nota
                WHERE CAST(not_tanggal AS date) ='".$aw->stat_day."' AND not_kembalian<0 AND not_lok_id=".$_POST['lok_id']."
                GROUP BY '".$aw->stat_day."'");
                $row3=$query3->getRow();
                $aw->totaldibayarpas=$row->totaldibayar;
                $aw->kembalian=$row->kembalian;
                $aw->totaldibayartakpas=$row3->totaldibayar;
                $query2 = $db->query("SELECT SUM(cil_cicilan) AS totaldicicil  
                FROM pos_nota
                LEFT JOIN pos_cicilan ON cil_not_id=not_id 
                WHERE CAST(not_tanggal AS date) ='".$aw->stat_day."' AND not_kembalian<0 AND not_lok_id=".$_POST['lok_id']."
                GROUP BY '".$aw->stat_day."'");
                $row2=$query2->getRow();
                $aw->totaldicicil=$row2->totaldicicil;
                $aw->total=($aw->totaldibayarpas-$aw->kembalian)+$aw->totaldibayartakpas+$aw->totaldicicil;
            }
        }
        else if (isset($_POST['monthly']) && $_POST['monthly']=='yes')
        {
            $queryawal=$db->query("SELECT CAST(not_tanggal AS date) AS stat_day,  
            MONTH(not_tanggal) AS perbulan, YEAR(not_tanggal) AS pertahun FROM pos_nota 
            WHERE YEAR(not_tanggal)=".$_POST['thn']." AND not_lok_id=".$_POST['lok_id']." 
            GROUP BY MONTH(not_tanggal)");
            $rowawal=$queryawal->getResult();
            foreach($rowawal as $aw) {
                $query = $db->query("SELECT  SUM(not_dibayar) AS totaldibayar,SUM(not_kembalian) AS kembalian 
                FROM pos_nota
                WHERE MONTH(not_tanggal) ='".$aw->perbulan."' AND YEAR(not_tanggal) ='".$aw->pertahun."' AND not_kembalian>=0 AND not_lok_id=".$_POST['lok_id']." 
                GROUP BY '".$aw->perbulan."'");
                $row=$query->getRow();
                $query3 = $db->query("SELECT SUM(not_dibayar) AS totaldibayar,SUM(not_kembalian) AS kembalian 
                FROM pos_nota
                WHERE MONTH(not_tanggal) ='".$aw->perbulan."' AND YEAR(not_tanggal) ='".$aw->pertahun."' AND not_kembalian<0 AND not_lok_id=".$_POST['lok_id']." 
                GROUP BY '".$aw->perbulan."'");
                $row3=$query3->getRow();
                $aw->totaldibayarpas=$row->totaldibayar;
                $aw->kembalian=$row->kembalian;
                $aw->totaldibayartakpas=$row3->totaldibayar;
                $query2 = $db->query("SELECT SUM(cil_cicilan) AS totaldicicil  
                FROM pos_nota
                LEFT JOIN pos_cicilan ON cil_not_id=not_id 
                WHERE MONTH(not_tanggal) ='".$aw->perbulan."' AND YEAR(not_tanggal) ='".$aw->pertahun."' AND not_kembalian<0 AND not_lok_id=".$_POST['lok_id']."
                GROUP BY '".$aw->perbulan."'");
                $row2=$query2->getRow();
                $aw->totaldicicil=$row2->totaldicicil;
                $aw->total=($aw->totaldibayarpas-$aw->kembalian)+$aw->totaldibayartakpas+$aw->totaldicicil;
            }

            // $query = $db->query("SELECT SUM(not_dibayar) AS totaldibayar,SUM(not_kembalian) AS kembalian, 
            // CAST(not_tanggal AS date) AS stat_day,
            // MONTH(not_tanggal) AS perbulan, YEAR(not_tanggal) AS pertahun 
            // FROM pos_nota
            // WHERE YEAR(not_tanggal)=".$_POST['thn']." AND not_lok_id=".$_POST['lok_id']." AND not_kembalian>=0
            // GROUP BY MONTH(not_tanggal)");
            // $row=$query->getResult();
            // $result['sql2'] = (string)($db->getLastQuery());
            // $query2 = $db->query("SELECT SUM(cil_cicilan) AS totaldicicil, 
            // CAST(not_tanggal AS date) AS stat_day, 
            // MONTH(not_tanggal) AS perbulan, YEAR(not_tanggal) AS pertahun 
            // FROM pos_nota
            // LEFT JOIN pos_cicilan ON cil_not_id=not_id 
            // WHERE YEAR(not_tanggal)=".$_POST['thn']." AND not_lok_id=".$_POST['lok_id']." AND not_kembalian<0 
            // GROUP BY MONTH(not_tanggal)");
            // $row2=$query2->getResult();
            // foreach($row as $a) {
            //     foreach($row2 as $b) {
            //         if($a->stat_day==$b->stat_day){
            //             $a->total=($a->totaldibayar-$a->totalkembalian)+$b->totaldicicil;
            //             $a->tes=$b->totaldicicil;
            //         }
            //     }
            // }
        }
        $result['sql'] = (string)($db->getLastQuery());
        $result['data'] = $rowawal;
        $result['data2'] = $rowawal;
        return $result;
    }
    function updatePassword()
    {
        $data = $_POST;
        $db = db_connect();
        $query = $db->query("UPDATE pos_kasir SET kas_password='".md5($data['kas_password'])."' WHERE kas_nick='".$data['kas_nick']."'");
        $error = $db->error();
        $result['error'] = $error;
        $result['data'] = $data;
        return $result;
    }
    function bestSelling()
    {
        $par = explode('*', $_POST['command']);
        $isCSV = false; // jika tidak ada opsi CSV maka defaultnya adalah PDF
        if (isset($par[3]) && $par[3] == 'csv') {
            $isCSV = true;
            $folder = 'public/report/'.md5('lok_id='.$_POST['lok_id']);
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            $file = $folder.'/bestselling.csv';
            $handle = fopen($file, 'w');
        }
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT * FROM rms_lokasi
            WHERE lok_id=".$_POST['lok_id']);
        $row = $query->getRow();
        $company = $row->lok_nama;
        $address = $row->lok_alamat;
        $query = $db->query("SELECT itm_kode, itm_nama, itm_satuan1,
            COUNT(nit_qty*nit_satuan0of1) totqty,
            SUM(nit_total-nit_disnom) totnilai
            FROM pos_notaitem
            LEFT JOIN pos_nota ON not_id=nit_not_id
            LEFT JOIN pos_item ON itm_id=nit_itm_id
            WHERE not_lok_id=".$_POST['lok_id']." AND (not_tanggal BETWEEN
            STR_TO_DATE('".$par[1]."','%d-%m-%Y') AND
            STR_TO_DATE('".$par[2]."','%d-%m-%Y'))
            GROUP BY itm_id ORDER BY ".
            ($par[0]=='bestselling'?'COUNT(nit_qty*nit_satuan0of1)':
            'SUM(nit_total-nit_disnom)')." DESC");
        $rows = $query->getResult();
        $title = $par[0]=='bestselling'?'Laporan Barang Terlaris':
            'Laporan Barang Penjualan Tertinggi';
        $subtitle = 'Tanggal '.$par[1].' s/d '.$par[2];
        if ($isCSV) {
            $csv[] = array('','',$company);
            $csv[] = array('','',$address);
            $csv[] = array('','',$title);
            $csv[] = array('','',$subtitle);
            $csv[] = array('No.','Kode','Nama Barang','Total Qty','Satuan',
                'Total Penjualan');
        }
        else {
            $content = '<tr style="font-weight:bold">
                <td width="0%" align="right">No.</td>
                <td width="0%" nowrap style="padding:0 5px 0 5px">Kode</td>
                <td width="100%" nowrap>Nama Barang</td>
                <td width="0%" nowrap align="right">Total Qty</td>
                <td width="0%" nowrap>Satuan</td>
                <td width="0%" nowrap align="right">Total Penjualan</td>
                </tr>';
        }
        $no = 1;
        $total = 0;
        foreach($rows as $row) {
            if ($isCSV) {
                $csv[] = array($no++,$row->itm_kode,$row->itm_nama,$row->totqty,
                    $row->itm_satuan1,$row->totnilai);
            }
            else {
                $content .= '<tr>
                    <td align="right">'.$no++.'</td>
                    <td style="padding:0 3px 0 3px">'.$row->itm_kode.'</td>
                    <td>'.$row->itm_nama.'</td>
                    <td align="right">'.$row->totqty.'</td>
                    <td style="padding:0 3px 0 3px">'.$row->itm_satuan1.'</td>
                    <td align="right">Rp.'.number_format($row->totnilai, 2).'</td>
                    </tr>';
            }
            $total += $row->totnilai;
        }
        if ($isCSV) {
            $csv[] = array('','','','','','',$total);
            foreach($csv as $c) {
                fputcsv($handle, $c);
            }
            fclose($handle);
            $url = base_url('report/'.md5('lok_id='.$_POST['lok_id']).
                '/bestselling.csv');
            $result['content'] = '<div><a href="'.$url.'">Download CSV</a></div>';
            $result['media'] = 'csv';
        }
        else {
            $content .= '<tr><td></td><td></td><td></td><td></td><td></td>
                <td align="right" style="font-weight:bold">Rp.'.
                number_format($total, 2).'</td></tr>';
            $html = '<table width="100%"><tr><td align="center"
                style="font-size:2em;font-weight:bold">'.$company.'</td></tr>
                <tr><td align="center"
                style="font-size:1.2em">'.$address.'</td></tr>
                <tr><td align="center" style="font-size:1.5em;font-weight:bold">'.
                $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
                $subtitle.'</td></tr><tr><td>
                <table width="100%">'.$content.'</table></td></tr></table>';
            $newheader = '<table width="100%"><tr><td align="center"
                style="font-size:7vw;font-weight:bold">'.$company.'</td></tr>
                <tr><td align="center"
                style="font-size:5vw">'.$address.'</td></tr>
                <tr><td align="center" style="font-size:6vw;font-weight:bold">'.
                $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
                $subtitle.'</td></tr</table>';
            $newtable = '<table width="100%" id="table2" style="font-size:2vw;">'.$content.'</table>';;
            $result['content'] = $html;
            $result['media'] = 'pdf';
            $result['header'] = $newheader;
            $result['table'] = $newtable;
            $result['rows'] = $rows;
            $result['total']=$total;
        }
        return $result;
    }
    function grossProfit()
    {
        $par = explode('*', $_POST['command']);
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT * FROM rms_lokasi
            WHERE lok_id=".$_POST['lok_id']);
        $row = $query->getRow();
        $company = $row->lok_nama;
        $address = $row->lok_alamat;
        $d1 = date_create_from_format('d-m-Y', $par[1]);
        $d2 = date_create_from_format('d-m-Y', $par[2]);
        while($d1 <= $d2) {
            $key = date_format($d1, 'Y-m-d');
            $arr[$key]['income'] = 0;
            $arr[$key]['outcome'] = 0;
            $arr[$key]['grossprofit'] = 0;
            $d1->modify('1 days');
        }
        $query = $db->query("SELECT DATE_FORMAT(not_tanggal, '%Y-%m-%d') tanggal,
            SUM(not_total) total
            FROM pos_nota
            WHERE not_lok_id=".$_POST['lok_id']." AND (not_tanggal BETWEEN
            STR_TO_DATE('".$par[1]."','%d-%m-%Y') AND
            STR_TO_DATE('".$par[2]."','%d-%m-%Y'))
            GROUP BY DATE_FORMAT(not_tanggal, '%Y-%m-%d')
            ORDER BY DATE_FORMAT(not_tanggal, '%Y-%m-%d')");
        $rows = $query->getResult();
        foreach($rows as $row) {
            $arr[$row->tanggal]['income'] = $row->total;
        }
        $query = $db->query("SELECT DATE_FORMAT(rcv_tglunas, '%Y-%m-%d') tanggal,
            SUM(rcv_total) total
            FROM inv_receive
            WHERE rcv_lok_id=".$_POST['lok_id']." AND (rcv_tglunas BETWEEN
            STR_TO_DATE('".$par[1]."','%d-%m-%Y') AND
            STR_TO_DATE('".$par[2]."','%d-%m-%Y')) AND rcv_status='PAID'
            GROUP BY DATE_FORMAT(rcv_tglunas, '%Y-%m-%d')
            ORDER BY DATE_FORMAT(rcv_tglunas, '%Y-%m-%d')");
        $rows = $query->getResult();
        foreach($rows as $row) {
            $arr[$row->tanggal]['outcome'] = $row->total;
        }
        foreach($arr as &$a) {
            $a['grossprofit'] = $a['income']-$a['outcome'];
        }
        $title = 'Laporan Laba Kotor Harian';
        $subtitle = 'Tanggal '.$par[1].' s/d '.$par[2];
        $content = '<tr style="font-weight:bold">
            <td width="0%" align="right">No.</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px">Tanggal</td>
            <td width="0%" nowrap align="right">Penjualan</td>
            <td width="0%" nowrap align="right">Pengeluaran</td>
            <td width="0%" nowrap align="right">Laba Kotor</td>
            </tr>';
        $no = 1;
        $totalIncome = $totalOutcome = $totalLabaKotor = 0;
        foreach($arr as $key=>$a) {
            $d = date_create_from_format('Y-m-d', $key);
            $tanggal = date_format($d, 'd-m-Y');
            $content .= '<tr>
                <td align="right">'.$no++.'</td>
                <td width="100%" style="padding:0 3px 0 3px">'.$tanggal.'</td>
                <td align="right">Rp.'.number_format($a['income'], 2).'</td>
                <td align="right">Rp.'.number_format($a['outcome'], 2).'</td>
                <td align="right">Rp.'.number_format($a['grossprofit'], 2).'</td>
                </tr>';
            $totalIncome += $a['income'];
            $totalOutcome += $a['outcome'];
            $totalLabaKotor += $a['grossprofit'];
        }
        $content .= '<tr><td></td><td></td>
            <td align="right" style="font-weight:bold">Rp.'.
            number_format($totalIncome, 2).'</td>
            <td align="right" style="font-weight:bold">Rp.'.
            number_format($totalOutcome, 2).'</td>
            <td align="right" style="font-weight:bold">Rp.'.
            number_format($totalLabaKotor, 2).'</td></tr>';
        $html = '<table width="100%"><tr><td align="center"
            style="font-size:2em;font-weight:bold">'.$company.'</td></tr>
            <tr><td align="center"
            style="font-size:1.2em">'.$address.'</td></tr>
            <tr><td align="center" style="font-size:1.5em;font-weight:bold">'.
            $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
            $subtitle.'</td></tr><tr><td>
            <table width="100%">'.$content.'</table></td></tr></table>';
        $newheader = '<table width="100%"><tr><td align="center"
            style="font-size:7vw;font-weight:bold">'.$company.'</td></tr>
            <tr><td align="center"
            style="font-size:5vw">'.$address.'</td></tr>
            <tr><td align="center" style="font-size:6vw;font-weight:bold">'.
            $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
            $subtitle.'</td></tr</table>';
        $newtable = '<table width="100%" id="table2" style="font-size:2vw;">'.$content.'</table>';;
        $result['content'] = $html;
        $result['media'] = 'pdf';
        $result['header'] = $newheader;
        $result['table'] = $newtable;
        $result['rows'] = $arr;
        $result['total']=$totalIncome.'/'.$totalOutcome.'/'.$totalLabaKotor;
        return $result;
    }

    function netProfit()
    {
        $par = explode('*', $_POST['command']);
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT * FROM rms_lokasi
            WHERE lok_id=".$_POST['lok_id']);
        $row = $query->getRow();
        $company = $row->lok_nama;
        $address = $row->lok_alamat;
        $d1 = date_create_from_format('d-m-Y', $par[1]);
        $d2 = date_create_from_format('d-m-Y', $par[2]);
        while($d1 <= $d2) {
            $key = date_format($d1, 'Y-m-d');
            $arr[$key]['income'] = 0;
            $arr[$key]['rcvoutcome'] = 0;
            $arr[$key]['oproutcome'] = 0;
            $arr[$key]['netprofit'] = 0;
            $d1->modify('1 days');
        }
        $query = $db->query("SELECT DATE_FORMAT(not_tanggal, '%Y-%m-%d') tanggal,
            SUM(not_total) total
            FROM pos_nota
            WHERE not_lok_id=".$_POST['lok_id']." AND (not_tanggal BETWEEN
            STR_TO_DATE('".$par[1]."','%d-%m-%Y') AND
            STR_TO_DATE('".$par[2]."','%d-%m-%Y'))
            GROUP BY DATE_FORMAT(not_tanggal, '%Y-%m-%d')
            ORDER BY DATE_FORMAT(not_tanggal, '%Y-%m-%d')");
        $rows = $query->getResult();
        foreach($rows as $row) {
            $arr[$row->tanggal]['income'] = $row->total;
        }
        $query = $db->query("SELECT DATE_FORMAT(rcv_tglunas, '%Y-%m-%d') tanggal,
            SUM(rcv_total) total
            FROM inv_receive
            WHERE rcv_lok_id=".$_POST['lok_id']." AND (rcv_tglunas BETWEEN
            STR_TO_DATE('".$par[1]."','%d-%m-%Y') AND
            STR_TO_DATE('".$par[2]."','%d-%m-%Y')) AND rcv_status='PAID'
            GROUP BY DATE_FORMAT(rcv_tglunas, '%Y-%m-%d')
            ORDER BY DATE_FORMAT(rcv_tglunas, '%Y-%m-%d')");
        $rows = $query->getResult();
        foreach($rows as $row) {
            $arr[$row->tanggal]['rcvoutcome'] = $row->total;
        }
        $query = $db->query("SELECT DATE_FORMAT(bel_tanggal, '%Y-%m-%d') tanggal,
            SUM(IF(right(bel_deskripsi,3)='(-)',-1*bel_jumlah, bel_jumlah)) total
            FROM pos_belanja
            WHERE bel_lok_id=".$_POST['lok_id']." AND (bel_tanggal BETWEEN
            STR_TO_DATE('".$par[1]."','%d-%m-%Y') AND
            STR_TO_DATE('".$par[2]."','%d-%m-%Y'))
            GROUP BY DATE_FORMAT(bel_tanggal, '%Y-%m-%d')
            ORDER BY DATE_FORMAT(bel_tanggal, '%Y-%m-%d')");
        $rows = $query->getResult();
        foreach($rows as $row) {
            $arr[$row->tanggal]['oproutcome'] = $row->total;
        }
        foreach($arr as &$a) {
            $a['netprofit'] = $a['income']-($a['rcvoutcome']+$a['oproutcome']);
        }
        $title = 'Laporan Laba Bersih Harian';
        $subtitle = 'Tanggal '.$par[1].' s/d '.$par[2];
        $content = '<tr style="font-weight:bold">
            <td width="0%" align="right">No.</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px">Tanggal</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px" align="right">Penjualan</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px" align="right">Kulakan</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px" align="right">Operasional</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px" align="right">Laba Bersih</td>
            </tr>';
        $no = 1;
        $totalIncome = $totalRcvOutcome = $totalOprOutcome = $totalLabaBersih = 0;
        foreach($arr as $key=>$a) {
            $d = date_create_from_format('Y-m-d', $key);
            $tanggal = date_format($d, 'd-m-Y');
            $content .= '<tr>
                <td align="right">'.$no++.'</td>
                <td width="100%" style="padding:0 5px 0 5px">'.$tanggal.'</td>
                <td style="padding:0 5px 0 5px" align="right">Rp.'.number_format($a['income'], 2).'</td>
                <td style="padding:0 5px 0 5px" align="right">Rp.'.number_format($a['rcvoutcome'], 2).'</td>
                <td style="padding:0 5px 0 5px" align="right">Rp.'.number_format($a['oproutcome'], 2).'</td>
                <td style="padding:0 5px 0 5px" align="right">Rp.'.number_format($a['netprofit'], 2).'</td>
                </tr>';
            $totalIncome += $a['income'];
            $totalRcvOutcome += $a['rcvoutcome'];
            $totalOprOutcome += $a['oproutcome'];
            $totalLabaBersih += $a['netprofit'];
        }
        $content .= '<tr><td></td><td></td>
            <td align="right" style="font-weight:bold;padding:0 5px 0 5px">Rp.'.
            number_format($totalIncome, 2).'</td>
            <td align="right" style="font-weight:bold;padding:0 5px 0 5px">Rp.'.
            number_format($totalRcvOutcome, 2).'</td>
            <td align="right" style="font-weight:bold;padding:0 5px 0 5px">Rp.'.
            number_format($totalOprOutcome, 2).'</td>
            <td align="right" style="font-weight:bold;padding:0 5px 0 5px">Rp.'.
            number_format($totalLabaBersih, 2).'</td></tr>';
        $html = '<table width="100%"><tr><td align="center"
            style="font-size:2em;font-weight:bold">'.$company.'</td></tr>
            <tr><td align="center"
            style="font-size:1.2em">'.$address.'</td></tr>
            <tr><td align="center" style="font-size:1.5em;font-weight:bold">'.
            $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
            $subtitle.'</td></tr><tr><td>
            <table width="100%">'.$content.'</table></td></tr></table>';
        $newheader = '<table width="100%"><tr><td align="center"
            style="font-size:7vw;font-weight:bold">'.$company.'</td></tr>
            <tr><td align="center"
            style="font-size:5vw">'.$address.'</td></tr>
            <tr><td align="center" style="font-size:6vw;font-weight:bold">'.
            $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
            $subtitle.'</td></tr</table>';
        $newtable = '<table width="100%" id="table2" style="font-size:2vw;">'.$content.'</table>';;
        $result['content'] = $html;
        $result['media'] = 'pdf';
        $result['header'] = $newheader;
        $result['table'] = $newtable;
        $result['rows'] = $arr;
        $result['d1'] = $key;
        $result['total']=$totalIncome.'/'.$totalRcvOutcome.'/'.$totalOprOutcome.'/'.$totalLabaBersih;
        return $result;
    }
    function dailyCashierOmzet()
    {
        $par = explode('*', $_POST['command']);
        $isCSV = false; // jika tidak ada opsi CSV maka defaultnya adalah PDF
        if (isset($par[3]) && $par[3] == 'csv') {
            $isCSV = true;
            $folder = 'public/report/'.md5('lok_id='.$_POST['lok_id']);
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            $file = $folder.'/cashieromzet.csv';
            $handle = fopen($file, 'w');
        }
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT * FROM rms_lokasi
            WHERE lok_id=".$_POST['lok_id']);
        $row = $query->getRow();
        $company = $row->lok_nama;
        $address = $row->lok_alamat;
        $query = $db->query("SELECT mod_checkin, mod_checkout, kas_id,
            kas_nama, mod_awal, mod_akhir, mod_status FROM pos_modal
            LEFT JOIN pos_kasir ON kas_id=mod_kas_id
            WHERE kas_lok_id=".$_POST['lok_id']." AND (mod_checkin BETWEEN
            STR_TO_DATE('".$par[1]." 00:00:00','%d-%m-%Y %H:%i:%s') AND
            STR_TO_DATE('".$par[2]." 23:59:59','%d-%m-%Y %H:%i:%s'))
            ORDER BY mod_checkin");
        $rows = $query->getResult();
        $title = 'Laporan Omzet Harian Per Kasir';
        $subtitle = 'Tanggal '.$par[1].' s/d '.$par[2];
        if ($isCSV) {
            $csv[] = array('','',$company);
            $csv[] = array('','',$address);
            $csv[] = array('','',$title);
            $csv[] = array('','',$subtitle);
            $csv[] = array('No.','Check-In','Check-Out','Nama Kasir','Modal Awal',
                'Modal Akhir', 'Omzet');
        }
        else {
            $content = '<tr style="font-weight:bold">
                <td width="0%" align="right">No.</td>
                <td width="0%" nowrap style="padding:0 5px 0 5px">Check-In</td>
                <td width="0%" nowrap style="padding:0 5px 0 5px">Check-Out</td>
                <td width="100%" nowrap>Nama Kasir</td>
                <td width="0%" nowrap align="right">Modal Awal</td>
                <td width="0%" nowrap align="right">Modal Akhir</td>
                <td width="0%" nowrap align="right">Omzet</td>
                </tr>';
        }
        $no = 1;
        $total = 0;
        foreach($rows as $row) {
            if ($row->mod_status == 'CHECKEDOUT') {
                $query = $db->query("SELECT SUM(not_total-not_disnom) omzet
                    FROM pos_nota
                    WHERE not_tanggal >= '".$row->mod_checkin."' AND not_tanggal <= '".
                    $row->mod_checkout."' AND not_kas_id=".$row->kas_id);
            }
            else {
                $query = $db->query("SELECT SUM(not_total-not_disnom) omzet
                    FROM pos_nota
                    WHERE not_tanggal >= '".$row->mod_checkin."'
                    AND not_kas_id=".$row->kas_id);
            }
            $row->omzet = $query->getRow()->omzet;
            $date = date_create($row->mod_checkin);
            $row->mod_checkin = date_format($date, 'd-m-Y H:i');
            $date = date_create($row->mod_checkout);
            $row->mod_checkout = date_format($date, 'd-m-Y H:i');
            if ($isCSV) {
                $csv[] = array($no++,$row->mod_checkedin,$row->mod_checkout,
                    $row->kas_nama,$row->mod_awal,$row->mod_akhir,$row->omzet);
            }
            else {
                if ($row->mod_status == 'CHECKEDIN')
                    $akhir = '';
                else
                    $akhir = 'Rp.'.number_format($row->mod_akhir, 2);
                $content .= '<tr>
                    <td align="right">'.$no++.'</td>
                    <td nowrap style="padding:0 3px 0 3px">'.$row->mod_checkin.'</td>
                    <td nowrap style="padding:0 3px 0 3px">'.$row->mod_checkout.'</td>
                    <td>'.$row->kas_nama.'</td>
                    <td align="right">Rp.'.number_format($row->mod_awal, 2).'</td>
                    <td align="right">'.$akhir.'</td>
                    <td align="right">Rp.'.number_format($row->omzet, 2).'</td>
                    </tr>';
            }
            $total += $row->omzet;
        }
        if ($isCSV) {
            $csv[] = array('','','','','','',$total);
            foreach($csv as $c) {
                fputcsv($handle, $c);
            }
            fclose($handle);
            $url = base_url('report/'.md5('lok_id='.$_POST['lok_id']).
                '/cashieromzet.csv');
            $result['content'] = '<div><a href="'.$url.'">Download CSV</a></div>';
            $result['media'] = 'csv';
        }
        else {
            $content .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td>
                <td align="right" style="font-weight:bold">Rp.'.
                number_format($total, 2).'</td></tr>';
            $html = '<table width="100%"><tr><td align="center"
                style="font-size:2em;font-weight:bold">'.$company.'</td></tr>
                <tr><td align="center"
                style="font-size:1.2em">'.$address.'</td></tr>
                <tr><td align="center" style="font-size:1.5em;font-weight:bold">'.
                $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
                $subtitle.'</td></tr><tr><td>
                <table width="100%">'.$content.'</table></td></tr></table>';
            $newheader = '<table width="100%"><tr><td align="center"
                style="font-size:7vw;font-weight:bold">'.$company.'</td></tr>
                <tr><td align="center"
                style="font-size:5vw">'.$address.'</td></tr>
                <tr><td align="center" style="font-size:6vw;font-weight:bold">'.
                $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
                $subtitle.'</td></tr</table>';
            $newtable = '<table width="100%" id="table2" style="font-size:2vw;">'.$content.'</table>';;
            $result['content'] = $html;
            $result['media'] = 'pdf';
            $result['header'] = $newheader;
            $result['table'] = $newtable;
            $result['total']=$total;
        }
        return $result;
    }
    function dailyOmzet()
    {
        $par = explode('*', $_POST['command']);
        $result['status'] = 'success';
        $db = db_connect();
        $query = $db->query("SELECT * FROM rms_lokasi
            WHERE lok_id=".$_POST['lok_id']);
        $row = $query->getRow();
        $company = $row->lok_nama;
        $address = $row->lok_alamat;
        $d1 = date_create_from_format('d-m-Y', $par[1]);
        $d2 = date_create_from_format('d-m-Y', $par[2]);
        $ed1 = explode('-', $par[1]);
        $newd1 = trim($ed1[2]) . '-' . trim($ed1[1]) . '-' . trim($ed1[0]);
        $ed2 = explode('-', $par[2]);
        $newd2 = trim($ed2[2]) . '-' . trim($ed2[1]) . '-' . trim($ed2[0]);

        $query = $db->query("
            SELECT not_id, not_nomor, DATE_FORMAT(not_tanggal, '%d-%m-%Y') tanggal,
            SUM(not_total) total 
            FROM pos_nota
            WHERE not_lok_id=".$_POST['lok_id']." AND (STR_TO_DATE(not_tanggal,'%Y-%m-%d') BETWEEN
            '".$newd1."' AND '".$newd2."')
            GROUP BY not_id
            ORDER BY not_id;");
        // SELECT not_id, not_nomor, DATE_FORMAT(not_tanggal, '%Y-%m-%d') tanggal,
        // SUM(not_total) total 
        // FROM pos_nota
        // WHERE not_lok_id=".$_POST['lok_id']." AND (not_tanggal BETWEEN
        // rbs_tolocaldate(STR_TO_DATE('".$par[1]."','%d-%m-%Y')) AND date_add(rbs_tolocaldate(STR_TO_DATE('".$par[2]."','%d-%m-%Y')),interval 1 day ))
        // GROUP BY not_id
        // ORDER BY not_id
        $rows = $query->getResult();
        $result['sql'] = (string)($db->getLastQuery());
        $title = 'Laporan Omzet';
        $subtitle = 'Tanggal '.$par[1].' s/d '.$par[2];
        $content = '<tr style="font-weight:bold">
            <td width="0%" align="right">No.</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px">Nomor Nota</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px">Item</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px" align="right">Jumlah</td>
            <td width="0%" nowrap style="padding:0 5px 0 5px" align="right">Harga</td>
            </tr>';
        $no = 1;
        $totalomzet=0;
        foreach($rows as $row) {
            $query2 = $db->query("SELECT * 
                FROM pos_notaitem
                LEFT JOIN pos_item ON itm_id=nit_itm_id 
                WHERE nit_not_id='".$row->not_id."' 
                GROUP BY nit_id
                ORDER BY nit_id");
            $rows2 = $query2->getResult();
            foreach($rows2 as $row2) {
                $content .= '<tr>
                    <td align="right">'.$no++.'</td>
                    <td style="padding:0 5px 0 5px">'.$row->not_nomor.'</td>
                    <td width="100%" style="padding:0 5px 0 5px;">'.$row2->itm_nama.'</td>
                    <td style="padding:0 5px 0 5px" align="right">'.$row2->nit_qty.'</td>
                    <td style="padding:0 5px 0 5px" align="right">Rp.'.number_format($row2->nit_total).'</td>
                </tr>';
            }
            $content .= '<tr>
                <td align="right"></td>
                <td style="padding:0 5px 0 5px"></td>
                <td width="100%" style="padding:0 5px 0 5px"></td>
                <td style="padding:0 5px 0 5px" align="right"></td>
                <td style="padding:0 5px 0 5px;" align="right">Rp.'.number_format($row->total).'</td>
            </tr><tr">
                <td align="right"></td>
                <td style="padding:0 5px 0 5px"></td>
                <td width="100%" style="padding:0 5px 0 5px"></td>
                <td style="padding:0 5px 0 5px" align="right"></td>
                <td style="padding:0 5px 0 5px" align="right">&nbsp;</td>
            </tr>';
            $totalomzet += $row->total;
        }
        $content .= '<tr><td></td><td></td>
            <td align="right" style="font-weight:bold;padding:0 5px 0 5px"></td>
            <td align="right" style="font-weight:bold;padding:0 5px 0 5px"></td>
            <td align="right" style="font-weight:bold;padding:0 5px 0 5px">Rp.'.
            number_format($totalomzet, 2).'</td></tr>';
            $html = '<table width="100%"><tr><td align="center"
            style="font-size:2em;font-weight:bold">'.$company.'</td></tr>
            <tr><td align="center"
            style="font-size:1.2em">'.$address.'</td></tr>
            <tr><td align="center" style="font-size:1.5em;font-weight:bold">'.
            $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
            $subtitle.'</td></tr><tr><td>
            <table width="100%">'.$content.'</table></td></tr></table>';
        $newheader = '<table width="100%"><tr><td align="center"
            style="font-size:7vw;font-weight:bold">'.$company.'</td></tr>
            <tr><td align="center"
            style="font-size:5vw">'.$address.'</td></tr>
            <tr><td align="center" style="font-size:6vw;font-weight:bold">'.
            $title.'</td></tr><tr><td align="center" style="padding-bottom:10">'.
            $subtitle.'</td></tr</table>';
        $newtable = '<table width="100%" id="table2" style="font-size:2vw;">'.$content.'</table>';;
        $result['content'] = $html;
        $result['media'] = 'pdf';
        $result['header'] = $newheader;
        $result['table'] = $newtable;
        $result['total']=$newd1;
        return $result;
    }
    function poread()
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
            LEFT JOIN inv_receive ON po_id=rcv_po_id
            LEFT JOIN pos_kasir ON kas_id=po_kas_id
            LEFT JOIN inv_supplier ON sup_id=po_sup_id
            WHERE rcv_id IS NULL AND po_status='APPROVED' AND po_lok_id=".$_POST['lok_id'];
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
    function rcvread()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
            $strSelect = "SELECT rcv_id, rcv_nomor, rcv_tgterima, rcv_tglunas, rcv_status, po_nomor, po_id,
                rcv_total, kas_id, kas_nama, sup_nama, rcv_catatan, rcv_status, rcv_diskon";
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
                    $strSelect = "SELECT rcvi_id, rcvi_qty qty, rcvi_diskon diskon,
                        rcvi_itm_id itm_id, itm_nama, rcvi_total,
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
    function columnList()
    {
        // itm_photo tidak ikut diselect karena data BLOB terlalu besar
        // menyebabkan lama saat loading
        // itm_photo hanya diload saat mau menampilkan saja

        return 'itm_id,itm_kode,itm_lok_id,itm_nama,itm_satuan,itm_stokaman,
        itm_tgstokopnam,itm_stok,itm_satuan1,itm_satuan1hpp,itm_satuan1hrg,
        itm_satuan2,itm_satuan2hpp,itm_satuan2hrg,itm_satuan2of1,
        itm_satuan3,itm_satuan3hpp,itm_satuan3hrg,itm_satuan3of1,
        itm_gallery,itm_pakaistok,itm_durasi,itm_satuandurasi';
    }

    function readGallery()
    {
        $db = db_connect();
        $filter = "itm_lok_id=".$_POST['lok_id'];
        if (isset($_POST['key_val']))
            $filter .= " AND itm_nama LIKE '%".$_POST['key_val']."%'";
        $query = $db->query("SELECT itm_photo FROM pos_item
            WHERE ".$filter." AND itm_gallery=1
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
        $result['status'] = 'success';
        return $result;
    }
    function satuanCheck()
    {
        $db = db_connect();
        // if (isset($_POST['satuan0hrg']))
        //     $filter .= " AND itm_nama LIKE '%".$_POST['key_val']."%'";
        $querycheck2 = $db->query("SELECT COUNT(*) total FROM pos_item WHERE itm_id=".$_POST['itm_id']." AND itm_satuan2='".$_POST['satuan2']."'");
        $rowscheck2 = $querycheck2->getResult();
        $querycheck3 = $db->query("SELECT COUNT(*) total FROM pos_item WHERE itm_id=".$_POST['itm_id']." AND itm_satuan3='".$_POST['satuan3']."'");
        $rowscheck3 = $querycheck3->getResult();
        $result['check2'] = $rowscheck2;
        $result['check3'] = $rowscheck3;
        return $result;
    }
    function updateHarga()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $filter='';
        if (isset($_POST['satuan2hpp'])){
            $filter .= " itm_satuan2hpp=".$_POST['satuan2hpp']." , itm_satuan2hrg=".$_POST['satuan2hrg']." ";
        }
        if (isset($_POST['satuan3hpp'])){
            $filter .= " itm_satuan3hpp=".$_POST['satuan3hpp']." , itm_satuan3hrg=".$_POST['satuan3hrg']." ";
        }
        $query = $db->query("UPDATE pos_item SET itm_satuan1hpp=".$_POST['satuan1hpp'].", 
        itm_satuan1hrg=".$_POST['satuan1hrg'].", ".$filter." 
        WHERE itm_id=".$_POST['itm_id']);
        $error = $db->error();
        if ($error['code'] == 0)
            $result['status'] = 'success';
        else {
            $result['status'] = 'failed';
            $result['error']['title'] = 'Gagal update';
            $result['error']['message'] = $error['message'];
        }
        return $result;
    }

    function readNota()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        if (isset($_POST['orifields']) && $_POST['orifields']=='yes')
            $strSelect = "SELECT not_id, not_nomor, not_tanggal,
                not_total, not_dibayar, not_kembalian, kas_id, kas_nama, cus_nama,
                not_sft_id, not_diskon, not_disnom, not_catatan,
                not_dicicil, not_jatuhtempo, not_carabayar, byr_nama";
        else
            $strSelect = "SELECT not_id id, not_nomor nomor, not_tanggal tanggal,
                not_total total, not_dibayar dibayar, not_kembalian kembalian,
                kas_id, kas_nama, cus_nama, not_sft_id sft_id,
                not_diskon diskon, not_disnom disnom, not_catatan catatan,
                not_id, not_dicicil, not_dicicil dicicil, not_jatuhtempo jatuhtempo,
                not_carabayar, byr_nama carabayar";
        $strQuery = $strSelect." FROM pos_nota
            LEFT JOIN pos_kasir ON kas_id=not_kas_id
            LEFT JOIN pos_customer ON cus_id=not_cus_id
            LEFT JOIN pos_carabayar ON byr_kode=not_carabayar
            WHERE not_lok_id=".$_POST['lok_id'];
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
        if (isset($_POST['id'])) {
            $strQuery .= " AND not_id=".$_POST['id'];
        }
        $strQuery .= " ORDER BY not_id DESC";
        $query = $db->query($strQuery);
        $result['aaaa']=$query;
        $result['bbb']=(string)($db->getLastQuery());
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

    function monitoringstok()
    {
        $db = db_connect();
        $clause='';
        $newclause='';
        if($_POST['bulan']!=''){
            $clause .= " AND MONTH(not_tanggal) = '". $_POST['bulan'] . "' AND YEAR(not_tanggal) = '". $_POST['tahun'] . "' ";
            $newclause .= " AND MONTH(rcv_tgterima) = '". $_POST['bulan'] . "' AND YEAR(rcv_tgterima) = '". $_POST['tahun'] . "' ";
        }
        $query = $db->query("SELECT itm_id, itm_stok, itm_nama, itm_kode, not_tanggal FROM pos_item  
            LEFT JOIN pos_notaitem ON itm_id=nit_itm_id 
            LEFT JOIN pos_nota ON not_id=nit_not_id ". $clause. "
            WHERE itm_lok_id='". $_POST['lok_id'] . "' GROUP BY itm_id");
        $data = $query->getResult();
        $tanggal_ganjil = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
        $tanggal_genap = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30);
        $detail=[];
        $total_masuk=0;
        $total_keluar=0;
        $total_masuk_bulanan=0;
        $total_keluar_bulanan=0;
        
        foreach ($data as $row) {
            $query8 = $db->query("SELECT SUM(ABS(nit_qty)) total FROM pos_item m LEFT JOIN pos_notaitem ON itm_id=nit_itm_id LEFT JOIN pos_nota ON not_id=nit_not_id WHERE m.itm_id = ".$row->itm_id." AND nit_qty>0 AND itm_lok_id='". $_POST['lok_id'] . "' AND not_tanggal BETWEEN '".$_POST['tahun']."/".$_POST['bulan']."/01' AND NOW()");
            $data8 = $query8->getRow();
            if($data8->total)
            $stok_mau_dikurang=(int)$data8->total;
            $query9 = $db->query("SELECT SUM(ABS(rcvi_qty)) total FROM pos_item m LEFT JOIN inv_rcvitem ON itm_id=rcvi_itm_id LEFT JOIN inv_receive ON rcv_id=rcvi_rcv_id WHERE m.itm_id = ".$row->itm_id." AND rcvi_qty>0 AND itm_lok_id='". $_POST['lok_id'] . "' AND rcv_tgterima BETWEEN '".$_POST['tahun']."/".$_POST['bulan']."/01' AND NOW()");
            $data9 = $query9->getRow();
            if($data9->total)
            $stok_mau_ditambah=(int)$data9->total;
            $row->stok_awal_bulan=(((int)$row->itm_stok)-$stok_mau_dikurang)+$stok_mau_ditambah;
            foreach ($tanggal_ganjil as $row2) {
                $query2 = $db->query("SELECT SUM(ABS(rcvi_qty)) total FROM pos_item m LEFT JOIN inv_rcvitem ON itm_id=rcvi_itm_id LEFT JOIN inv_receive ON rcv_id=rcvi_rcv_id WHERE m.itm_id = ".$row->itm_id." AND rcvi_qty>0 AND DAY(rcv_tgterima) = ".$row2." AND itm_lok_id='". $_POST['lok_id'] . "'".$newclause);
                $data2 = $query2->getRow();
                if($data2->total)
                $total_masuk=(int)$data2->total;
                $query3 = $db->query("SELECT SUM(ABS(nit_qty)) total FROM pos_item m LEFT JOIN pos_notaitem ON itm_id=nit_itm_id LEFT JOIN pos_nota ON not_id=nit_not_id WHERE m.itm_id = ".$row->itm_id." AND nit_qty>0 AND DAY(not_tanggal) = ".$row2." AND itm_lok_id='". $_POST['lok_id'] . "'".$clause);
                $data3 = $query3->getRow();
                if($data3->total)
                $total_keluar=(int)$data3->total;
                
                $query6 = $db->query("SELECT SUM(ABS(rcvi_qty)) total FROM pos_item m LEFT JOIN inv_rcvitem ON itm_id=rcvi_itm_id LEFT JOIN inv_receive ON rcv_id=rcvi_rcv_id WHERE m.itm_id = ".$row->itm_id." AND rcvi_qty>0 AND itm_lok_id='". $_POST['lok_id'] . "'".$newclause);
                $data6 = $query6->getRow();
                if($data6->total)
                $total_masuk_bulanan=(int)$data6->total;
                $query7 = $db->query("SELECT SUM(ABS(nit_qty)) total FROM pos_item m LEFT JOIN pos_notaitem ON itm_id=nit_itm_id LEFT JOIN pos_nota ON not_id=nit_not_id WHERE m.itm_id = ".$row->itm_id." AND nit_qty>0 AND itm_lok_id='". $_POST['lok_id'] . "'".$clause);
                $data7 = $query7->getRow();
                if($data7->total)
                $total_keluar_bulanan=(int)$data7->total;

                

                $stok_akhir_bulan=(int)$row->stok_awal_bulan+$total_masuk_bulanan-$total_keluar_bulanan;
                $jsonnew='{"tanggal":'.$row2.',"total_masuk":"'.$total_masuk.'","total_keluar":"'.$total_keluar.'","total_masuk_bulanan":"'.$total_masuk_bulanan.'","total_keluar_bulanan":"'.$total_keluar_bulanan.'","stok_akhir_bulan":"'.$stok_akhir_bulan.'"}';
                $dekodejson=json_decode($jsonnew, true);
                $total_masuk=0;
                $total_keluar=0;
                $total_masuk_bulanan=0;
                $total_keluar_bulanan=0;
                
                array_push($detail,$dekodejson);
            }
            $row->stok_detail=$detail;
            $detail=[];
        }
        $result['data'] = $data;
        $result['status'] = 'success';
        return $result;
    }
    function delete_transaksi(){
        $db = db_connect();
        $nota =$db->query("SELECT * FROM pos_nota WHERE not_id = '". $_POST['not_id'] . "'");
        $datanota = $nota->getRow();
        $result['sql'] = (string)($db->getLastQuery());
        $notaitem =$db->query("SELECT * FROM pos_notaitem WHERE nit_not_id = '". $_POST['not_id'] . "'");
        $datanotaitem = $notaitem->getResult();
        $result['sql2'] = (string)($db->getLastQuery());
        $db->query("ALTER TABLE pos_dumpnota AUTO_INCREMENT = 1");
        $db->query("INSERT INTO pos_dumpnota 
        SELECT null, p.* FROM pos_nota p WHERE not_id = '". $_POST['not_id'] . "'");
        $db->query("ALTER TABLE pos_dumpnotaitem AUTO_INCREMENT = 1");
        $db->query("INSERT INTO pos_dumpnotaitem 
        SELECT null, q.* FROM pos_notaitem q WHERE nit_not_id = '". $_POST['not_id'] . "'");
        
        if($datanota->not_dicicil=='1'){
            $db->query("ALTER TABLE pos_dumpcicilan AUTO_INCREMENT = 1");
            $db->query("INSERT INTO pos_dumpcicilan 
            SELECT null, q.* FROM pos_cicilan q WHERE cil_not_id = '". $_POST['not_id'] . "'");
        }
        $this->retur($datanotaitem,$datanota->not_nomor,'Hapus transaksi',$_POST['not_id']);
        // $db->query("DELETE FROM pos_nota WHERE not_id = '". $_POST['not_id'] . "'");
        $result['data'] = $datanota;
        
        return $result;
    }

    function retur($data,$nomor,$alasan,$id)
    {
        $db = db_connect();
        $result['status'] = 'success';
        $new_id = '';
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $builder = $db->table('inv_retur');
        $builder->set('ret_id', null);
        $builder->set('ret_nomor', $nomor);
        $builder->set('ret_tanggal', date_format($date, 'Y-m-d H:i:s'));
        $builder->set('ret_ket', $alasan);
        if ($builder->insert()) {
            $nota = $db->query("SELECT * FROM inv_retur WHERE ret_nomor='".
                $nomor."' ");
            $rownota = $nota->getRow();
            $kode=substr($rownota->ret_nomor, 0, 2);
            
            foreach($data as $r) {
                $item =$db->query("SELECT * FROM pos_item WHERE itm_id = '". $r->nit_itm_id . "'");
                $dataitem = $item->getRow();
                $builder = $db->table('inv_returitem');
                $builder->set('reti_id', null);
                $builder->set('reti_ret_id', $rownota->ret_id);
                $builder->set('reti_itm_id', $r->nit_itm_id);
                $builder->set('reti_qty', (int)$r->nit_qty*(int)$r->nit_satuan0of1);
                $builder->set('reti_ket', 'Hapus Item Transaksi');
                $result['sql'] = (string)($db->getLastQuery());
                if ($builder->insert()) {
                    if($kode[0].$kode[1]!='RE'){
                        $totalstok=(int)$dataitem->itm_stok+((int)$r->nit_qty*(int)$r->nit_satuan0of1);
                        $db->query("DELETE FROM pos_nota WHERE not_id = '". $id . "'");
                        $db->query("DELETE FROM pos_notaitem WHERE nit_not_id = '". $id . "'");
                        $db->query("UPDATE pos_item SET itm_stok= '".$totalstok."' WHERE itm_id = '". $r->nit_itm_id . "'");
                    }
                }
            }
        }
        return $result;
    }
    public function cek_login()
    {
        $db = db_connect();
        $result['data'] = [];
        $query = $db->query("SELECT count(kas_id) total  
            FROM pos_kasir
            WHERE kas_nick='".$_POST['username']."' ");
        $row = $query->getRow();
        $result['sql'] = (string)($db->getLastQuery());
        if($row->total>=2)
        return false;
        else
        return true;
    }
    public function cek_kas()
    {
        $db = db_connect();
        $result['data'] = [];
        $strQuery='';
        if (isset($_POST['kode']) && $_POST['kode']!='')
        {
            $strQuery = $strQuery." AND com_kode='".$_POST['kode']."' ";
        }
        $query = $db->query("SELECT kas_id  
            FROM pos_kasir 
            LEFT JOIN rms_lokasi ON kas_lok_id=lok_id 
            LEFT JOIN rms_company ON lok_com_id=com_id 
            WHERE kas_nick='".$_POST['username']."' ".$strQuery);
        $row = $query->getRow();
        return $row;
    }
    public function kode_otp()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $new_id = '';
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $db->query("DELETE FROM pos_kodeotp WHERE otp_kas_id =  '".$_POST['kas_id']."'");
        $db->query("INSERT INTO pos_kodeotp VALUES (null,'".$_POST['kas_id']."','".$_POST['otp_kode']."') ");
    }

    public function update_setting()
    {
        $db = db_connect();
        $result['status'] = 'success';
        $new_id = '';
        $date = date_create(null, timezone_open("Asia/Jakarta"));
        $db->query("UPDATE rms_lokasi SET lok_app_setting = '".$_POST['app_setting']."' WHERE lok_id = '".$_POST['lok_id']."'");
        $result['sql'] = (string)($db->getLastQuery());
        return $result;
    }

    public function cek_otp()
    {
        $db = db_connect();
        $result['data'] = [];
        $query = $db->query("SELECT count(*) total  
            FROM pos_kodeotp
            WHERE otp_kode='".$_POST['otp_kode']."' AND otp_kas_id='".$_POST['kas_id']."'");
        $row = $query->getRow();
        $result['sql'] = (string)($db->getLastQuery());
        if($row->total>=1)
        return true;
        else
        return false;
    }
    public function reset_login()
    {
        $db = db_connect();
        $result['data'] = [];
        if (isset($_POST['password']) && $_POST['password']!='')
        {
            $str = " MD5('".$_POST['password']."') ";
        }
        else
        {
            $str="MD5('admin123')";
        }
        $db->query(" UPDATE pos_kasir 
        SET kas_password = ".$str." 
        WHERE kas_id='".$_POST['kas_id']."' ");
        $result['sql'] = (string)($db->getLastQuery());
        return $result;
    }
    public function read_login()
    {
        $db = db_connect();
        $result['status'] = 'failed';
        $result['data'] = [];
        $strQuery='';
        if (isset($_POST['kode']) && $_POST['kode']!='')
        {
            $strQuery = $strQuery." AND com_kode='".$_POST['kode']."' ";
        }
        $query = $db->query("SELECT kas_id, kas_nama, kas_gender, kas_wa, kas_lok_id,
            kas_com_id, kas_role, com_kode, lok_id, lok_app_setting  
            FROM pos_kasir
            LEFT JOIN rms_lokasi ON kas_lok_id=lok_id 
            LEFT JOIN rms_company ON lok_com_id=com_id 
            WHERE kas_nick='".$_POST['username']."'
            AND (kas_password=MD5('".$_POST['password']."')) ".$strQuery);
        $error = $db->error();
        if ($error['code'] == 0) {
            $row = $query->getRow();
            if($row) {
                $configModel = new ConfigModel();
                $kasirModel = new KasirModel();
                $result['data'] = $query->getRow();
                $result['funkode'] = $kasirModel->getFunCode($db, $row);
                $result['status'] = 'success';
            }
            else {
                $result['error']['title'] = 'Cek Data Login';
                $result['error']['message'] = 'Username atau password tidak valid';
                $result['sql'] = (string)($db->getLastQuery());
            }
        }
        else {
            $result['error']['title'] = 'Cek Data Login';
            $result['error']['message'] = $error['message'];
            $result['sql'] = (string)($db->getLastQuery());
        }
        return $result;
    }
}