<?php 
namespace App\Controllers;
require 'vendor/autoload.php';
use CodeIgniter\RESTful\ResourceController;
use ImageKit\ImageKit;
class Images extends ResourceController
{
    public function auth()
    {
        $imageKit =new ImageKit("public_+YvqfWS3KeSGVDDoKm2n4pfcodc=","private_zZ5rIN/QYFvztLIfv1ECSsQSldA=","https://ik.imagekit.io/3ec6wafmg");
        $authenticationParameters = $imageKit->getAuthenticationParameters();
        echo json_encode($authenticationParameters);

    }
}
