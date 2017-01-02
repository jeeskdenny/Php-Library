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
    public function update($table,$set,$where){
        if(!empty($set) && is_array($set)){
            $setvalue = '';
            $wherevalue = '';
            $sql= "UPDATE ".$table." SET ";
        
            if(!array_key_exists('modified',$set)){
                $set['modified'] = [date("Y-m-d H:i:s"),'s'];
            }
            $i = 0;
            foreach($set as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $setvalue .= $pre.$key."= ? ";
                $i++;
            }
            $sql.=$setvalue;

            if(!empty($where)&& is_array($where)){
                $wherevalue .= ' WHERE ';
                $i = 0;
                foreach($where as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $wherevalue .= $pre.$key." = ? ";
                    $i++;
                }
                $sql.=$wherevalue;
            }

            if($query = $this->db->prepare($sql))
            {
                $paramBind='';
                $paramBindCond='';

                foreach($set as $key=>$val)
                {
                    $valueBind[]= $val[0];
                    $paramBind .= $val[1]?$val[1]:'s';
                
                }
         
                foreach($where as $key=>$val)
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

                return $update?$this->db->affected_rows():false;
                
            }else{
                return false;
            }
        }else{
            return false;
        }
    }    

    /**
    * Delete Method! 
    *   call this method 
    *
    *       $whereData = array(
    *           'id'=>['1','i'],
    *           'first_name'=> ['Jees','s']
    *           );
    *
    *       $delte=$cru->delete('members',$whereData); 
    *       $delte=$cru->delete('members'); --all data will deleted in the table
    */

    public function delete($table,$where=false){
        $wherevalue='';
        $sql = "DELETE FROM ". $table;
        if($where==true)
        {
            if(!empty($where)&& is_array($where))
            {
                $wherevalue .=' WHERE ';
                $i = 0;
                foreach($where as $key => $value)
                {
                    $pre = ($i > 0)?' AND ':'';
                    $wherevalue .= $pre.$key."= ? ";
                    $i++;
                }
                $sql.= $wherevalue;
            }  
        }
        if($query = $this->db->prepare($sql))
        {   
            if($where==true)
            {
                $paramBind='';
                foreach($where as $key=>$val)
                {
                    $valueBind[]= $val[0];
                    $paramBind .= $val[1]?$val[1]:'s';
                }
                $bindType[]= $paramBind ;
                $fullArray = array_merge($bindType,$valueBind);
                foreach ($fullArray as $key => $value) {
                    $tmp[]= &$fullArray[$key];
                }
                $ref = new ReflectionClass('mysqli_stmt'); 
                $method = $ref->getMethod("bind_param"); 
                $method->invokeArgs($query,$tmp);
            }

            $delete = $query->execute(); 
            return $delete?$delete:false;
        }else{
            return false;
        }
    } 

    /**
    * FindPrimaryColumn Name method
    *   call this method 
    *
    *       $name = findPrimaryCol($table_name);
    *
    */

    public function findPrimaryCol($table)
    {   
        if($table)
        {
            $sql = "SHOW KEYS FROM ". $table ." WHERE Key_name = 'PRIMARY'";

            if($query = $this->db->prepare($sql))
            {
                $query->execute();
                $result =  $query->get_result();
                $myrow = $result->fetch_assoc();
                if($myrow['Column_name'])
                {
                    return $myrow['Column_name'];
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