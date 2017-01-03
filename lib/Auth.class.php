<?php
/**
 * 
 * User Authentication Handles Here! 
 *
 * @author  Jees K Denny
 * @version 1.0, 28/12/16
 * @since   28/12/16
 */
 require_once 'Database.class.php';
 require_once 'Crud.class.php';
 require_once 'Session.class.php';

 class Auth 
 {
 	private $db;
 	private $cru;

	function __construct()
	{
		$this->db = new Database();
		$this->cru = new Crud();
	}

	/*
	* Random String Generation
	* Call this Method
	* 	random();
	*	random(50);
	*	random(20, '0123456789AB'); 
	*/	
	private function random($l=50, $c = 'abcdefghijklmnopqrstuvwxyz1234567890') {
	    for ($s = '', $cl = strlen($c)-1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i);
	    return $s;
	}

	/*
	*	Password Hashing 
	*	call this method 
	* 		passHash('password');
	*/
	protected function passHash($data)
    {	
    	return password_hash("$data", PASSWORD_BCRYPT);
    }

    /*
    *Login Handling!
    * Call Login method
	*	$auth = new Auth();
	*	$username =['email'=>'jees@gmail.com'];
	*	$password =['pass'=>'123'];
	*	$result=$auth->login('members',$username,$password);
	*
    */
    public function login($table,$username,$password)
    {	
    	$username_key=implode('', array_keys($username));
    	$username_value=implode('', $username);
    	$result=$this->cru->select($table,[
          	'where'=>[$username_key=>$username_value],
          	'return_type'=>'all'
       	]);
       	if($result){
       		if($result->num_rows==1)
	       	{
		    	$myrow = $result->fetch_assoc();
		    	$password_key=implode('', array_keys($password));
		    	$password_value=implode('', $password);
		    	if(password_verify($password_value,$myrow[$password_key])) {
				    return true;
				} else {
				    return false;
				}
	       	}else{
	       		return false;
	       	}
       	}else{
       		return false;
       	}
    }
    
    /*
    *Login Handling!
    *	Call Login method
    *	$auth = new Auth();
    *	$data= ['first_name'=> ['ajeesh','s'],
	*		'last_name'=>['M Anand','s'],
	*		'email'=>['ajeesh@gmail.com','s'],
	*		'uname'=>['ajee','s']
	*		];
	*	$password =['pass' =>['123','s']];
	*	$result=$auth->newUser('members',$data,$password); 
	*
    */

    public function newUser($table,$data,$password)
    {
    	if(!empty($data) && is_array($data))
    	{
    		if(!empty($password) && is_array($password))
	    	{
	    		foreach($password as $key=>&$val)
	            {
	            	$val[0]= $this->passHash($val[0]);
	           	}
	           	$user = $this->cru->insert($table,array_merge($data,$password));
	           	if($user){
	           		return $user;
	           	}else{
	           		return false;
	           	}
	    	}else{
	    		return false;
	    	}
    	}else{
    		return false;
    	}	
    }

    /*
    *UpdateToken Method. Usually Use Inside logout. 
    *Cookie Token Updated with random number encrypted with sha256!
    *	Call Login method
    *	$auth = new Auth();
    *	$data= ['id'=> ['1','i']];
	*	$auth->updateToken('members','token',$data);  
	*
    */
    

    public function updateToken($table,$update_field_name,$data)
    {
    	$upd_key=implode('', array_keys($data));

    	$random_number = $this->random(50);
		$hashdata = hash('sha256', $random_number);
		
		$updateData = array(
			$update_field_name=>[ $hashdata,'s']
			);

		$name = $this->cru->update($table, $updateData , $data);

    }

    /*
    *LoginviaToken Method. 
    *If There is No Session data found, 
    *You can check cookies and token for authorization.
    *	Call loginviaToken method
    *		$auth = new Auth();
    *		$username=['email'=>$_COOKIE['user_name']};
	*		$password=['token'=>$_COOKIE['Cookie_token']];
	*		$myrow=$auth->loginviaToken('members',$username,$password);
	*
    */

    public function loginviaToken($table,$username,$token)
    {	
    	$token_key=implode('', array_keys($token));
    	$token_value=implode('', $token);

    	$username_key=implode('', array_keys($username));
    	$username_value=implode('', $username);

    	$hashdata = hash('sha256', $token_value);

    	$result=$this->cru->select($table,[
           'where'=>[$username_key=>$username_value,$token_key=>$hashdata],
           'return_type'=>'all'
      	]);
    	if($result)
    	{
    		if($result->num_rows==1)
	       	{
		    	$myrow = $result->fetch_assoc();
		    	$pricolum = $this->cru->findPrimaryCol($table);
		    	if($pricolum){
				    return $myrow;
				}   	
		    }	
    	}
    }	

 }