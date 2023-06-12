Steps to generate a QR code:
1. Include cQRCode.php library in your script
require_once("cQRCode.php");
2. create a cQRCode object
$qr1 = new cQRCode("Class cQRCode",ECL_M);
1st parameter is the string you want to encode
2nd parameter is the error level, yo can choose (ECL_L, L, "L", "l"),(ECL_M, M, "M", "m"),(ECL_Q, Q, "Q", "q"), (ECL_H, H, "H", "h")
3th parameter is experimental, true means you want rounder corners in your qr code ($qr1 = new cQRCode("Class cQRCode",ECL_M, true);)
3. Generate image
$qr1->getQRImg("PNG","cqrcode");
1st parameter is the format type (PNG,"PNG"),(JPG,"JPG"),(GIF,"GIF")
2nd parameter is the name of the file (without extension) if you omit this parameter, the image is output to screen
The image is generate in tmp directory, see FILE_PATH constant in cQRCode.php library
Yo can change the module size in pixels, see PXS constant in cQRCode.php library

You can see, step by step, the process to generate QR Codes if you active DEBUG mode, see DEBUG and DEBUG_LEVEL constants in cQRCode.php library