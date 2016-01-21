<?php

// version 2.0 / 2015.12.14

class AuthException extends Exception{
	// usually used in ajax endpoints when user is not logged in
	// JS then redirects to login page
}

class PhnxUserException extends Exception{
	// an error occurred within the class
	// usually used for reporting back or logging the error
	// $login should be set to 0
}

class phnx_user{

	private $login;
	private $cookie;
	private $db_auth;
	
	public $db_main;

	public $username;
	public $id;
	public $loginID;
	public $info;
	public $error = array();

	function __construct(){
		global $db_auth;
		global $db_main;
		global $db2use;
		if ( $db2use['db_auth'] !== TRUE ) { throw new Exception( 'Tried to initiate user class with out auth database connection.' ); }
		if ( $db2use['db_main'] !== TRUE ) { throw new Exception( 'Tried to initiate user class with out main database connection.' ); }
		$this->db_auth = $db_auth;
		$this->db_main = $db_main;
	}

	function build_tables(){
		$this->db_auth->query("
			CREATE TABLE `activeLogins` (
				`loginID` varchar(255) NOT NULL,
				`userid` int(20) NOT NULL,
				`logintime` int(20) NOT NULL,
				`IP` text NOT NULL,
				`userAgent` text NOT NULL,
				PRIMARY KEY (`loginID`),
				KEY `userid` (`userid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
		$this->db_auth->query("
			CREATE TABLE `users` (
				`userid` int(20) NOT NULL AUTO_INCREMENT,
				`saltedHash` text NOT NULL,
				`lastLogin` int(20) NOT NULL,
				`lastUsedVer` decimal(6,3) NOT NULL,
				`facebook` varchar(100) DEFAULT NULL,
				PRIMARY KEY (`userid`),
				UNIQUE KEY `facebook` (`facebook`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
		$this->db_main->query("
			CREATE TABLE `users` (
				`userid` int(20) NOT NULL,
				`username` varchar(32) NOT NULL,
				`firstname` text NOT NULL,
				`lastname` text NOT NULL,
				`email` text NOT NULL,
				`token` varchar(25) DEFAULT NULL,
				`userLevel` int(2) NOT NULL,
				PRIMARY KEY (`userid`),
				UNIQUE KEY `username` (`username`),
				UNIQUE KEY `token` (`token`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
	}



	/* RETURN CURRENT LOGIN STATE */
	function login(){
		return $this->login;
	}


	/* IMPORT LOGIN COOKIE TO CLASS */
	function get_cookie(){
		if(isset($_COOKIE[LOGIN_COOKIE_NAME])){
			$this->cookie = $_COOKIE[LOGIN_COOKIE_NAME];
		}
	}

	/* COOKIE MONSTER */
	function cookieMonster($action, $data, $exp = 864000, $name = NULL, $domain = NULL){
		if($name === NULL){
			$name = LOGIN_COOKIE_NAME;
		}
		if($domain === NULL){
			$domain = LOGIN_COOKIE_DOMAIN;
		}
		if(isset($name) && isset($domain) && isset($data) && isset($action)){
			switch($action){
				case 'set':
					$this->error['cookie'] = (setcookie($name, $data, time() + $exp, '/', $domain, 0, 1)) ? FALSE : 'could_not_set';
					break;
				case 'delete':
					if(setcookie($name, $data, time() - 3600, '/', $domain, 0, 1)){
						$this->error['cookie'] = FALSE;
						$this->cookie = NULL;
					}else{
						$this->error['cookie'] = 'could_not_delete';
					}
					break;
				default:
					throw new PhnxUserException('Cookie Monster invalid action');
					break;
			}
		}else{
			throw new PhnxUserException('Cookie Monster missing argument.');
		}
	}

	/* KILL SESSION */
	function kill_session(){
		$s = (session_status() === PHP_SESSION_ACTIVE) ? TRUE : FALSE;
		if (!$s) { session_start(); }
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		session_destroy();
	}






	/* MAKE A NEW LOGIN */
	function newlogin(){
		if(isset($this->id)){
			$useragent = $_SERVER['HTTP_USER_AGENT'];
			$ipAddy = $_SERVER['REMOTE_ADDR'];
			$logintime = time();
			$sprinkles = '|' . $logintime . '-' . md5(uniqid(rand(),true)) . sha1(uniqid(rand(),true));
			$cookieString = $this->id . $sprinkles;
			$this->db_auth->query("INSERT INTO activeLogins (userid, loginID, loginTime, IP, useragent) VALUES ('".$this->id."', '$sprinkles', '$logintime', '$ipAddy', '$useragent')");
			$this->cookieMonster('set',$cookieString);
			$this->db_auth->query("UPDATE users SET lastLogin='$logintime' WHERE userid='".$this->id."' LIMIT 1");
			$this->loginID = $sprinkles;
		}else{
			throw new PhnxUserException('Tried to create a new login, and userID is not set.');
		}
	}

	/* REGENERATE THE ACTIVE LOGIN */
	function regen(){
		$sprinkles = substr($this->cookie, 0 - strlen($this->cookie) + strpos($this->cookie,'|'));
		$this->del_active_login($sprinkles);
		$this->cookieMonster('delete','logout');
		$this->newlogin();
	}

	/* DELETE A SPECIFIC ACTIVE LOGIN */
	function del_active_login($loginID){
		if($this->db_auth->query("DELETE FROM activeLogins WHERE loginID='$loginID'")){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	/* RETURN ARRAY OF ACTIVE LOGINS */
	function get_active_logins(){
		if($this->login === 2){
			$activeLogins = array();
			$R_logins = $this->db_auth->query("SELECT * FROM activeLogins WHERE userid = '".$this->id."' ORDER BY loginTime DESC");
			$R_logins->data_seek(0);
			while($login = $R_logins->fetch_assoc()){
				$browser = @get_browser($login['userAgent'],true);
				$activeLogins[] = array(
					'loginID'	=> $login['loginID'],
					'logintime'	=> $login['logintime'],
					'IP'		=> $login['IP'],
					'useragent'	=> $login['userAgent'],
					'browser'	=> $browser
				);
			}
			$R_logins->free();
			unset($R_logins);
		}else{
			$activeLogins[] = array(
				'error'	=> 1,
				'msg'	=> 'Level 2 access is required to obtain the list of active logins.'
			);
		}
		return($activeLogins);
	}





	/* CHECK TO SEE IF A USERNAME (or email) EXISTS */
	function exists($lookup = NULL, $mode = null, $populate_id = TRUE){
		if($lookup == '' || $lookup == NULL || $mode == '' || $mode == NULL){
			throw new PhnxUserException("Error checking for existence of user, invalid parameter.");
		}
		$params = array('s', $lookup);
		switch($mode){
			case 'username':
				$id = db1($this->db_main, "SELECT userid FROM users WHERE username=? LIMIT 1", FALSE, $params);
				break;
			case 'email':
				$id = db1($this->db_main, "SELECT userid FROM users WHERE email=? LIMIT 1", FALSE, $params);
				break;
			case 'facebook':
				$id = db1($this->db_main, "SELECT userid FROM users WHERE facebook=? LIMIT 1", FALSE, $params);
				break;
			default:
				throw new PhnxUserException("Error checking for existence of user, invalid mode.");
				break;
		}
		if($id !== FALSE){
			if ( $populate_id ) { $this->id = $id; }
			return TRUE;
		}else{
			return FALSE;
		}
	}




	/* CHECK PASSWORD */
	function comparepass($pass = NULL){
		if(isset($this->id)){
			$saltedHash = $this->getSaltedHash();
			if($saltedHash === FALSE){
				$this->error[] = 'Could not get salted hash. Error CMP.01';

				return FALSE;
			}else{
				if($pass !== NULL){
					$salt = substr($saltedHash,0,22);
					$saltedHash = '$2y$11$'.$saltedHash;
					$hash2test = crypt($pass, '$2y$11$'.$salt.'$');
					if($hash2test === $saltedHash){
						return TRUE;
					}else{
						$this->error[] = 'Password does not match. Error CMP.03';
						return FALSE;
					}
				}else{
					$this->error[] = 'Submitted password is not set. Error CMP.02';
					return FALSE;
				}
			}
		}else{
			throw new PhnxUserException("UserMgmt tried to verify password, and userID is not set.");
		}
	}

	/* GET SALTED HASH */
	private function getSaltedHash(){
		$saltedHash = db1($this->db_auth, "SELECT saltedHash FROM users WHERE userid = ".$this->id." LIMIT 1");
		if($saltedHash !== FALSE){
			// Strip the pepper
			$saltedHash = substr($saltedHash,0,53);
		}else{
			$saltedHash = FALSE;
			$this->error[] = 'getSaltedHash 01';
		}
		return $saltedHash;
	}

	/* PASSWORD HASH GENERATOR */
	function new_hash($pword = NULL){
		if($pword === NULL){
			throw new PhnxUserException("UserMgmt tried to create a new hash, and the password is not set.");
		}
		$salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
		$salt = base64_encode($salt);
		$salt = str_replace('+', '.', $salt);
		$pepper = md5(uniqid(rand(),true));
		$saltedHash = crypt($pword, '$2y$11$'.$salt.'$');
		$pepperedHash = substr($saltedHash,7) . $pepper;
		return $pepperedHash;
	}




	/* POPULATE OR REFRESH USER DATA */
	public function updateInfo(){
		$R_info = $this->db_main->query("SELECT * FROM users WHERE userid = '".$this->id."' LIMIT 1");
		if($R_info != FALSE){
			$info = $R_info->fetch_assoc();
			$this->username = $info['username'];
			$this->info = $info;
			$R_info->free();
			unset($R_info);
		}
	}





	/* LOGOUT */
	function logout($all = NULL){

		$s = (session_status() === PHP_SESSION_ACTIVE) ? TRUE : FALSE;

		if(!$s){
			session_start();
		}

		if($this->cookie == NULL){
			$this->get_cookie();
		}

		$sprinkles = substr($this->cookie, 0 - strlen($this->cookie) + strpos($this->cookie,'|'));
		$this->del_active_login($sprinkles);
		$this->cookieMonster('delete','logout');
		$this->kill_session();

		if($all === 'all'){
			$this->db_auth->query("DELETE FROM activeLogins WHERE userid='".$this->id."'");
		}

		$this->login = 0;
		$this->id = NULL;
		$this->username = NULL;
		$this->info = NULL;
	}

	/* RUN THE LOGIN CHECK UP TO LEVEL 1 */
	private function checklogin1(){
		if($this->cookie == NULL){
			$this->get_cookie();
		}
		if(isset($this->cookie)){
			$this->level1();
		}else{
			$this->login = 0;
		}
	}

	/* RUN THE LOGIN CHECK UP TO LEVEL 2 */
	private function checklogin2(){
		if($this->login === 1){
			$this->level2();
		}else{
			$this->checklogin1();
			if($this->login === 1){
				$this->level2();
			}else{
				$this->login = 0;
			}
		}
	}

	/* THE LEVEL 1 CHECK */
	private function level1(){
		$userid = substr($this->cookie, 0, strpos($this->cookie, '|'));
		$sprinkles = substr($this->cookie, 0 - strlen($this->cookie) + strpos($this->cookie,'|'));
		$R_activeLogins = $this->db_auth->query("SELECT * FROM activeLogins WHERE userid = '$userid'");
		if($R_activeLogins != FALSE){
			$R_activeLogins->data_seek(0);
			while ($activeLogins = $R_activeLogins->fetch_assoc()) {
				// This loop checks all Active Logins for a match, if it finds one it sets login to 1 and breaks out of the loop
				if( $activeLogins['loginID'] == $sprinkles && $sprinkles != ''){
					$this->login = 1;
					$this->id = $userid;
					$this->updateInfo();
					break;
				}else{
					$this->login = 0;
				}
			}
		}else{
			$this->login = 0;
		}
		$R_activeLogins->free();
		unset($R_activeLogins);
	}

	/* THE LEVEL 2 CHECK */
	private function level2(){
		session_start();
		if (!isset($_SESSION['level2session'])){
			session_regenerate_id();
		}
		if($_SESSION['level2session'] === TRUE && $_SESSION['sessionUserID'] === $this->id){
			$diff = time()-$_SESSION['last_activity'];
			if($diff < 1800 && $diff >= 0){
				$this->login = 2;
				$_SESSION['last_activity'] = time();
			}else{
				$this->kill_session();
				if($this->login !== 1){
					$this->login = 0;
				}
			}
		}else{
			if(isset($_POST['pass'])){
				if($this->comparepass($_POST['pass'])){
					// CREATE SESSION
					$_SESSION['level2session'] = TRUE;
					$_SESSION['sessionUserID'] = $this->id;
					$_SESSION['last_activity'] = time();
					session_write_close();
					$this->login = 2;
				}else{
					$this->kill_session();
					if($this->login !== 1){
						$this->login = 0;
					}
				}
			}else{
				$this->kill_session();
				if($this->login !== 1){
					$this->login = 0;
				}
			}
		}
	}

	/* RUN THE LOGIN CHECK */
	function checklogin($l){
		if($l === 1){
			$this->checklogin1();
		}elseif($l === 2){
			$this->checklogin2();
		}else{
			throw new PhnxUserException('Invalid parameter specified for logincheck');
		}
	}


}

?>
