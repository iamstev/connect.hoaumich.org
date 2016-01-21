<?php

    require_once('classes/phnx-user.class.php');

    class PermissionException extends Exception{}

	class hoa_user extends phnx_user{

        function permission($permission){
            $access = db1($this->db_main, "SELECT value FROM permissions WHERE permission='$permission' AND username='".$this->username."' LIMIT 1");
            if($access === '1'){
                return TRUE;
            }else{
                return FALSE;
            }
        }

    }

?>
