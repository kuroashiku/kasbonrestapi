<html>
<body>
<?
require_once("cQRCode.php");
$qr1 = new cQRCode("Class cQRCode",ECL_M);
$qr1->getQRImg("PNG","cqrcode");
$qr2 = new cQRCode("http://www.phpclasses.org",ECL_L);
$qr2->getQRImg("JPG","phpclasses");
$qr3 = new cQRCode("HTTP://WWW.PHP.NET",ECL_H);
$qr3->getQRImg("GIF","phpnet");
$qr4 = new cQRCode("01234567899876543210",ECL_Q);
$qr4->getQRImg("PNG","numbers");

?>
<h1 align='center'>Examples</h1>
<table cellpadding="20" border='1' width='100%'>
<tr><td >Encoded Text</td><td align='center'>Image</td><td >Encoded Text</td><td align='center'>Image</td></tr>
<tr><td>Class cQRCode<br>Error Correction Level M</td><td align='center'><img src='./tmp/cqrcode.png'></td>
<td>http://www.phpclasses.org<br>Error Correction Level L</td><td align='center'><img src='./tmp/phpclasses.jpg'></td></tr>
<tr><td>HTTP://WWW.PHP.NET<br>Error Correction Level H</td><td align='center'><img src='./tmp/phpnet.gif'></td>
<td>01234567899876543210<br>Error Correction Level Q</td><td align='center'><img src='./tmp/numbers.png'></td></tr>
</table>
</body>
</html>