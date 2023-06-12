<?php 
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

class UserModel extends Model
{
    function users()
    {
        $group_data = [

        //////////////////////////////////////////////////////////////////////////////////////////
        // GROUP POS
        //////////////////////////////////////////////////////////////////////////////////////////

        [
            'role'    => 'admin_pos',
            'master'  => 'edit',
            'history' => 'enable',
            'receive' => 'enable',
            'admin'   => 1
        ],[
            'role'    => 'user_pos',
            'master'  => 'view',
            'history' => 'enable',
            'receive' => 'disable',
            'admin'   => 0
        ]];

        $user_data = [

        //////////////////////////////////////////////////////////////////////////////////////////
        // USER POS DEMO
        //////////////////////////////////////////////////////////////////////////////////////////

        [
            'username' => '3admin.pos',
            'password' => '3demo',
            'role'     => 'admin_pos',
            'nama'     => 'Juwita',
            'lok_id'   => 1
        ],[
            'username' => '3user.pos',
            'password' => '3demo',
            'role'     => 'user_pos',
            'nama'     => 'Wanda',
            'lok_id'   => 1
        ],[
            'username' => '3kasir1.pos',
            'password' => '3demo',
            'role'     => 'user_pos',
            'nama'     => 'Farid',
            'lok_id'   => 1
        ],[
            'username' => '3kasir2.pos',
            'password' => '3demo',
            'role'     => 'user_pos',
            'nama'     => 'Minarsih',
            'lok_id'   => 1
        ],

        //////////////////////////////////////////////////////////////////////////////////////////
        // USER POS ATK POINT
        //////////////////////////////////////////////////////////////////////////////////////////

        [
            'username' => '1admatk.pos',
            'password' => '1atk',
            'role'     => 'admin_pos',
            'nama'     => 'Betty',
            'lok_id'   => 2
        ]];

        foreach($user_data as &$u){
            foreach($group_data as $g) {
                if ($g['role'] == $u['role']) {
                    $u = array_merge($u, $g);
                    break;
                }
            }
        }
        return $user_data;
    }

    public function check()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $result['status'] = 'failed';
        $data = $this->users();
        foreach($data as $d) {
            if ($d['username'] == $username && $d['password'] == $password) {
                $result['status'] = 'success';
                $result['login_data'] = $d;
                break;
            }
        }
        return $result;
    }
}
