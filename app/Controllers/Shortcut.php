<?php 
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ShortcutModel;
use App\Models\SalesrepModel;

class Shortcut extends ResourceController
{
    public function read()
    {
        $url = "localhost/kbscutrestapi/shortcut/read";
        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
    }

    public function execute()
    {
        $url = "localhost/kbscutrestapi/shortcut/execute";
        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => "lok_id=".$_POST['lok_id'].
                "&command=".$_POST['command'],
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
    }
}
