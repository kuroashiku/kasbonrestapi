<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Test ReenDoo</title>
    <script type="text/javascript" src="<?= base_url('jquery.min.js') ?>"></script>
</head>
<body>
</body>
</html>
<script type="text/javascript">
    var data = [{"kode": 9184758607980, "nama": "KARTU DOMINO", "satuan1": "PAK", "satuan1hpp": 4000, "satuan1hrg": 5000}, {"kode": 8594837485968, "nama": "PIGORA", "satuan1": "PCS", "satuan1hpp": 29600, "satuan1hrg": 37000}, {"kode": 7463869708896, "nama": "VAS BUNGA", "satuan1": "PCS", "satuan1hpp": 14000, "satuan1hrg": 17500}, {"kode": 7772637485968, "nama": "PENITI", "satuan1": "PAK", "satuan1hpp": 1600, "satuan1hrg": 2000, "satuan2": "BOX", "satuan2hpp": 31000, "satuan2hrg": 38000, "satuan2of1": 20, "satuan3": "KAR", "satuan3hpp": 155000, "satuan3hrg": 180000, "satuan3of1": 100}, {"kode": 6389789437543, "nama": "BOLA VOLLEY", "satuan1": "PCS", "satuan1hpp": 48000, "satuan1hrg": 60000}, {"kode": 7868765437654, "nama": "SEPEDA MTB", "satuan1": "UNT", "satuan1hpp": 1000000, "satuan1hrg": 1250000}, {"kode": 9078067687565, "nama": "KURSI KAYU", "satuan1": "UNT", "satuan1hpp": 80000, "satuan1hrg": 100000, "satuan2": "SET", "satuan2hpp": 300000, "satuan2hrg": 380000, "satuan2of1": 4}, {"kode": 6745634786548, "nama": "SEPATU RODA", "satuan1": "PCS", "satuan1hpp": 240000, "satuan1hrg": 300000}, {"kode": 9785698790587, "nama": "PISAU DAPUR", "satuan1": "PCS", "satuan1hpp": 24000, "satuan1hrg": 30000}, {"kode": 3647623874784, "nama": "TERPAL", "satuan1": "PCS", "satuan1hpp": 60000, "satuan1hrg": 75000}, {"kode": 5763423747457, "nama": "CHARGER HP", "satuan1": "PCS", "satuan1hpp": 20000, "satuan1hrg": 25000}, {"kode": 9856509690673, "nama": "RADIO BLUETOOTH", "satuan1": "UNT", "satuan1hpp": 80000, "satuan1hrg": 100000}, {"kode": 9938377864358, "nama": "CERMIN RIAS", "satuan1": "UNT", "satuan1hpp": 160000, "satuan1hrg": 200000}, {"kode": 4516227377834, "nama": "GERGAJI", "satuan1": "PCS", "satuan1hpp": 52000, "satuan1hrg": 65000}, {"kode": 7773647567575, "nama": "HELM GOWES", "satuan1": "PCS", "satuan1hpp": 200000, "satuan1hrg": 250000}, {"kode": 8950202020293, "nama": "KACAMATA BACA", "satuan1": "PCS", "satuan1hpp": 40000, "satuan1hrg": 50000}, {"kode": 8397777564433, "nama": "LAMPU TAMAN", "satuan1": "UNT", "satuan1hpp": 100000, "satuan1hrg": 125000}, {"kode": 6637485555342, "nama": "SOLAR", "satuan1": "LTR", "satuan1hpp": 6400, "satuan1hrg": 8000, "satuan2": "DRM", "satuan2hpp": 1200000, "satuan2hrg": 1500000, "satuan2of1": 200}, {"kode": 3384958777686, "nama": "BENDERA", "satuan1": "LBR", "satuan1hpp": 9600, "satuan1hrg": 12000}, {"kode": 1777774658484, "nama": "WEBCAM BLUETOOTH", "satuan1": "PCS", "satuan1hpp": 320000, "satuan1hrg": 400000}, {"kode": 8575857474433, "nama": "AQUA 800ML", "satuan1": "BTL", "satuan1hpp": 2400, "satuan1hrg": 3000}, {"kode": 9585857446744, "nama": "GUNTING RUMPUT", "satuan1": "PCS", "satuan1hpp": 36000, "satuan1hrg": 45000}, {"kode": 5534758675565, "nama": "STOP KONTAK", "satuan1": "PCS", "satuan1hpp": 16000, "satuan1hrg": 20000}, {"kode": 4439888576653, "nama": "KABEL LISTRIK", "satuan1": "MTR", "satuan1hpp": 6400, "satuan1hrg": 8000}, {"kode": 2875876095865, "nama": "SPEAKER AKTIF", "satuan1": "PCS", "satuan1hpp": 80000, "satuan1hrg": 100000}, {"kode": 8938495069776, "nama": "LAPTOP", "satuan1": "UNT", "satuan1hpp": 4000000, "satuan1hrg": 5000000}, {"kode": 7849393849490, "nama": "TAS RANSEL", "satuan1": "UNT", "satuan1hpp": 520000, "satuan1hrg": 650000}, {"kode": 8474847437383, "nama": "SANDAL HIKING", "satuan1": "PCS", "satuan1hpp": 72000, "satuan1hrg": 90000}, {"kode": 8374846252633, "nama": "SABUN LUX", "satuan1": "BKS", "satuan1hpp": 4000, "satuan1hrg": 5000, "satuan2": "BAG", "satuan2hpp": 38000, "satuan2hrg": 48000, "satuan2of1": 10}, {"kode": 7466664848484, "nama": "SOFFELL", "satuan1": "BTL", "satuan1hpp": 9600, "satuan1hrg": 12000}];
    $.ajax({
        type:'POST',
        data:{
            data:JSON.stringify(data),
            lok_id:1
        },
        // url:'http://localhost/kasbonrestapi/item/import',
        url:'https://jobs.reendoo.com/kasbonrestapi/item/import',
        success:function(retval) {
        }
    });
</script>