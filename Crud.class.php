<?php
/**
 * 
 * Select , Insert , Update , Delete Handles Here! 
 *
 * @author  Jees K Denny
 * @version 1.0, 28/12/16
 * @since   28/12/16
 */
require_once 'Database.class.php';

class Crud extends Database
{
	private $db;
	
	function __construct()
	{
		$this->db = new Database();
	}

	function __destruct()
	{
		$this->db->disconnect();
	}

	/**
    * Select Method ! 
    *   call this method 
    *   $cru -> select('table_name',[
    *       'select'=> 'first_name last_name',
    *       'where'=>['lname'=>'first','first'=>'last','fee'=>'best'],
    *       'order_by' => 'age',
    *       'start'=> '10',
    *       'limit'=>'15',
    *       'return_type'=>'all | count | single'
    *   ]);
    * 
    *   return_type = all 
    *   while ($myrow = $result->fetch_assoc()) {
    *           echo '<hr />';
    *           var_dump($myrow);
    *   }               
    */

	public function select($table, $conditions = []){

        $sql = 'SELECT ';
        $sql .= array_key_exists("select",$conditions)?$conditions['select']:'*';
        $sql .= ' FROM '.$table;
        
        if(array_key_exists("where",$conditions))
        {
            $sql .= ' WHERE ';
            $i = 0;
            foreach($conditions['where'] as $key => $value)
            {
                $pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }

        if(array_key_exists("order_by",$conditions))
        {
            $sql .= ' ORDER BY '.$conditions['order_by']; 
        }

        if(array_key_exists("start",$conditions) && array_key_exists("limit",$conditions))
        {
            $sql .= ' LIMIT '.$conditions['start'].','.$conditions['limit']; 
        }elseif(!array_key_exists("start",$conditions) && array_key_exists("limit",$conditions))
        {
            $sql .= ' LIMIT '.$conditions['limit']; 
        }

        if($query = $this->db->prepare($sql))
        {
        	$query->execute();
		    $result = $query->get_result();

	        if(array_key_exists("return_type",$conditions) && $conditions['return_type'] != 'all')
            {
	            switch($conditions['return_type'])
                {
	                case 'count':
	                    $data = $result->num_rows;
	                    break;
	                case 'single':
	                    $data = $result->fetch_assoc();
	                    break; 
	                default:
	                    $data = '';
	            }
	        }else{
	            if($result->num_rows > 0)
                {
	                $data = $result;
	                return $data;
	            }
	        }
        }	
       	return !empty($data)?$data:false;
	}	

	/**
	* Insert Method! 
	*	call this method 
    *   $userData = array(
    *        'uname' => ['Jeesk', 's'],
    *        'email' => ['jees@gmail.com', 's'],
    *        'pass' => ['9633450433', 's'],
    *        'first_name'=>['Jees','s'],
    *        'last_name'=>['K Denny','s']
    *    );
    *    $hello = $cru ->insert('members',$userData);
	*/
	public function insert($table,$data){
		if(!empty($data) && is_array($data)){

			if(!array_key_exists('created',$data)){
                $data['created'] = [date("Y-m-d H:i:s"),'s'];
            }
            if(!array_key_exists('modified',$data)){
                $data['modified'] = [date("Y-m-d H:i:s"),'s'];
            }
            $columnString = "`".implode('`, `', array_keys($data))."`";
            $countofData = count($data);
            $sql = "INSERT INTO `".$table."` (".$columnString.") VALUES (";
            for($i=0;$i<$countofData;$i++)
            {	
            	if($i==($countofData-1))
            	{
            		$sql.= ' ? )';
            	}else{
            		$sql.= ' ?, ';
            	}
            }
        	if($query = $this->db->prepare($sql))
        	{
        		$paramBind='';
        		$valueBind=Array(); 

        		foreach($data as $key=>$val)
                {
	                $valueBind[]= $val[0];
	                $paramBind .= $val[1]?$val[1]:'s';
            	}
            	$paramBindArray[]=$paramBind;

            	$result_params= array_merge($paramBindArray,$valueBind);

            	foreach ($result_params as $key => $value) 
                {
            		$tmp[]= &$result_params[$key];
            	}
           
				$ref    = new ReflectionClass('mysqli_stmt'); 
				$method = $ref->getMethod("bind_param"); 
				$method->invokeArgs($query,$tmp); 

            	$insert = $query->execute();
            	return $insert?$this->db->insert_id():false;
        	}
		}else{
            return false;
        }
	}

    /**
    * Update Method! 
    *   call this method 
    *    $updateData = array(
    *        'first_name'=>['bla','s']
    *       );
    *
    *    $whereData = array(
    *        'uname'=>['jees','s'],
    *        'pass'=>['sorry','s']
    *        );
    *    $name = $cru->update('members', $updateData , $whereData);
    */	
    public function update($table,$data,$conditions){
        $colvalSet = '';
        $whereSql = '';
        $i = 0;
        $sql= "UPDATE ".$table." SET ";
    
        if(!array_key_exists('modified',$data)){
            $data['modified'] = [date("Y-m-d H:i:s"),'s'];
        }
        foreach($data as $key=>$val){
            $pre = ($i > 0)?', ':'';
            $colvalSet .= $pre.$key."= ? ";
            $i++;
        }
        $sql.=$colvalSet;

        if(!empty($conditions)&& is_array($conditions)){
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $whereSql .= $pre.$key." = ? ";
                $i++;
            }
            $sql.=$whereSql;
        }

        if($query = $this->db->prepare($sql))
        {
            $paramBind='';
            $paramBindCond='';

            foreach($data as $key=>$val)
            {
                $valueBind[]= $val[0];
                $paramBind .= $val[1]?$val[1]:'s';
            
            }
     
            foreach($conditions as $key=>$val)
            {
                $valueBindCond[]= $val[0];
                $paramBindCond .= $val[1]?$val[1]:'s';
            }

            $valueType= array_merge($valueBind,$valueBindCond);

            $bindType[]= $paramBind . $paramBindCond;

            $fullArray = array_merge($bindType,$valueType);

            foreach ($fullArray as $key => $value) {
                $tmp[]= &$fullArray[$key];
            }
            $ref = new ReflectionClass('mysqli_stmt'); 
            $method = $ref->getMethod("bind_param"); 
            $method->invokeArgs($query,$tmp);
            $update = $query->execute(); 

            var_dump($update);

            return $update?$this->db->affected_rows():false;
            
        }

    }    

}