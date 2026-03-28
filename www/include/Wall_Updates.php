<?php
//Srinivas Tamada http://9lessons.info
//Wall_Updates

class Wall_Updates {

public $perpage = 10; // Uploads perpage
    

	
     // Updates   	
	  public function Updates($uid,$lastid) 
	{
	  // More Button
       $morequery="";
		if($lastid)
		$morequery=" and M.msg_id<'".$lastid."' ";
	   // More Button End
	   
	    $query = mysql_query("SELECT M.msg_id, M.uid_fk, M.message, M.created, M.uploads FROM wmessages M WHERE M.uid_fk='$uid' $morequery order by M.msg_id desc limit " .$this->perpage) or sqlerr(__FILE__,__LINE__);
		
         while($row=mysql_fetch_array($query))
		$data[]=$row;
	    return $data;
		
    }
	     // Updates   	
	  public function Total_Updates($uid) 
	{
	 	   
	    $query = mysql_query("SELECT M.msg_id, M.uid_fk, M.message, M.created, M.uploads FROM wmessages M WHERE M.uid_fk='$uid' $morequery order by M.msg_id ")  or sqlerr(__FILE__,__LINE__);
		$data=mysql_num_rows($query);
        return $data;
		
    }
	
	//Comments
	   public function Comments($msg_id,$second_count) 
	{
	$query='';
	  if($second_count)
	  $query="limit $second_count,2";
	    $query = mysql_query("SELECT C.com_id, C.uid_fk, C.comment, C.created, U.username FROM wcomments C, users U WHERE C.uid_fk=U.id and C.msg_id_fk='$msg_id' order by C.com_id asc $query")  or sqlerr(__FILE__,__LINE__);
	   while($row=mysql_fetch_array($query))
	    $data[]=$row;
        if(!empty($data))
		{
       return $data;
         }
	}
	
	
	//Avatar Image
	//From database
     public function Profile_Pic($uid) 
	{
	    $query = mysql_query("SELECT avatar FROM `users` WHERE id='$uid'")  or sqlerr(__FILE__,__LINE__);
	   $row=mysql_fetch_array($query);
	   if(!empty($row['avatar']))
	   {
	    
	    $data= $row['avatar'];
        return $data;
         }
		 else
		 {
		 $data="icons/default.jpg";
		return $data;
		 }
	}
	//  Gravatar Image
	public function Gravatar($uid) 
	{
	    $query = mysql_query("SELECT email FROM `users` WHERE id='$uid'")  or sqlerr(__FILE__,__LINE__);
	   $row=mysql_fetch_array($query);
	   if(!empty($row))
	   {
	    $email=$row['email'];
        $lowercase = strtolower($email);
        $imagecode = md5( $lowercase );
		$data="http://www.gravatar.com/avatar.php?gravatar_id=$imagecode";
		return $data;
         }
		 else
		 {
		 $data="default.jpg";
		return $data;
		 }
	}
	
	//Insert Update
	public function Insert_Update($uid, $update,$uploads) 
	{
	$update=mysql_real_escape_string($update);
      $time=time();
	   $ip=$_SERVER['REMOTE_ADDR'];
        $query = mysql_query("SELECT msg_id,message FROM `wmessages` WHERE uid_fk='$uid' order by msg_id desc limit 1") or sqlerr(__FILE__,__LINE__);
        $result = mysql_fetch_array($query);
		
        if ($update!=$result['message']) {
		  $uploads_array=explode(',',$uploads);
		  $uploads=implode(',',array_unique($uploads_array));
            $query = mysql_query("INSERT INTO `wmessages` (message, uid_fk, ip,created,uploads) VALUES (N'$update', '$uid', '$ip','$time','$uploads')")  or sqlerr(__FILE__,__LINE__);
            $newquery = mysql_query("SELECT M.msg_id, M.uid_fk, M.message, M.created, U.username FROM wmessages M, users U where M.uid_fk=U.id and M.uid_fk='$uid' order by M.msg_id desc limit 1 ");
            $result = mysql_fetch_array($newquery);
		
			 return $result;
        } 
		else
		{
				 return false;
		}
		
       
    }
	
	//Delete update
		public function Delete_Update($uid, $msg_id) 
	{
	    $query = mysql_query("DELETE FROM `wcomments` WHERE msg_id_fk = '$msg_id' and uid_fk='$uid' ") or sqlerr(__FILE__,__LINE__);
        $query = mysql_query("DELETE FROM `wmessages` WHERE msg_id = '$msg_id' and uid_fk='$uid'") or sqlerr(__FILE__,__LINE__);
        return true;
      	       
    }
	
		//Image Upload
		public function Image_Upload($uid, $image) 
	{
	//Base64 encoding
	$path= $_SERVER['DOCUMENT_ROOT']."/uploads/";
	 	  $img_src = $path.$image;
     $imgbinary = fread(fopen($img_src, "r"), filesize($img_src));
     $img_base = base64_encode($imgbinary);
	 $ids = 0;
        $query = mysql_query("insert into user_uploads (image_path,uid_fk)values('$image' ,'$uid')") or sqlerr(__FILE__,__LINE__);
		$ids = mysql_insert_id();
        return $ids;
    }
	
			//get Image Upload
		public function Get_Upload_Image($uid,$image) 
	{	
	    if($image)
		{
		  $query = mysql_query("select id,image_path from user_uploads where image_path='$image'") or sqlerr(__FILE__,__LINE__);
		}
		else
		{
		   $query = mysql_query("select id,image_path from user_uploads where uid_fk='$uid' order by id desc ") or sqlerr(__FILE__,__LINE__);
		}
      
         $result = mysql_fetch_array($query);
		
		return $result;
    }
	
		//Id Image Upload
		public function Get_Upload_Image_Id($id) 
	{	
        $query = mysql_query("select image_path from user_uploads where id='$id'") or sqlerr(__FILE__,__LINE__);
         $result = mysql_fetch_array($query);
		
		return $result;
    }
	
	//Insert Comments
	public function Insert_Comment($uid,$msg_id,$comment) 
	{
	$comment=mysql_real_escape_string($comment);
	
	   	    $time=time();
	   $ip=$_SERVER['REMOTE_ADDR'];
        $query = mysql_query("SELECT com_id,comment FROM `wcomments` WHERE uid_fk='$uid' and msg_id_fk='$msg_id' order by com_id desc limit 1 ") or sqlerr(__FILE__,__LINE__);
        $result = mysql_fetch_array($query);
    
		if ($comment!=$result['comment']) {
            $query = mysql_query("INSERT INTO `wcomments` (comment, uid_fk,msg_id_fk,ip,created) VALUES (N'$comment', '$uid','$msg_id', '$ip','$time')") or sqlerr(__FILE__,__LINE__);
            $newquery = mysql_query("SELECT C.com_id, C.uid_fk, C.comment, C.msg_id_fk, C.created, U.username FROM wcomments C, users U where C.uid_fk=U.id and C.uid_fk='$uid' and C.msg_id_fk='$msg_id' order by C.com_id desc limit 1 ");
            $result = mysql_fetch_array($newquery);
         
		   return $result;
        } 
		else
		{
		return false;
		}
       
    }
	
	//Delete Comments
		public function Delete_Comment($uid, $com_id) 
	{
	    $query = mysql_query("DELETE FROM `wcomments` WHERE uid_fk='$uid' and com_id='$com_id'") or sqlerr(__FILE__,__LINE__);
        return true;
      	       
    }

    

}

?>
