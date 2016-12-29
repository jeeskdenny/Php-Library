<?php
 require_once 'Database.class.php';
 require_once 'Crud.class.php';

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
    	$fullArray=array_merge($username,$password);
    	$result = $this->cru->select( $table,[
          'where'=> $fullArray,
          'return_type'=>'all'
       	]);
 		return $result;
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
	           	$user = $this->cru->insert('members',array_merge($data,$password));
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


 }