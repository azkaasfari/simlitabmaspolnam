<?php

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**return sql rows */
function getRoles($app, $login){
	
	$sql="select userlogin.userlogin_id, userlogin.login,user_role_types.name ".
	" from userlogin, userroles,user_role_types ".
	" where  userlogin.userlogin_id=userroles.userlogin_id ".
	" and user_role_types.USER_ROLE_TYPE_ID=userroles.user_role_type_id ".
	" AND userlogin.login='".$login."'";

	return $app['db']->fetchAll($sql, array());
}

function startsWith($haystack, $needle)
{
	 $length = strlen($needle);
	 return (substr($haystack, 0, $length) === $needle);
	 
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function deny($login,$cfgIndexpage, $uri,$strLogout){
	
	//@todo can't throw exception ...
	//throw new AccessDeniedException($login. "! Anda tidak berhak ke  #".$uri ."#\n" ); 
	//
	
	echo "<center><H1><font color=\"red\"> \n";
	echo $login. "! Anda tidak berhak ke  #".$uri ."#\n" ; 
	echo "<br><a href=\"javascript:history.back()\">Kembali</a> \n";
	echo "<br><a href=\"".$cfgIndexpage."\">\n".
	"Halaman Awal"."</a>";
	echo "<br><a href=\"".$cfgIndexpage."/vendor_01/logout.php\">\n".
	$strLogout."</a>\n";
	echo "</font></H1></center>\n";
		

	die;
}

function is_authorized($app, $login,$cfgIndexpage,$strLogout,$uri) { 
	
	//echo "pass 0" ; die;
	/**admin all allow */
	//var_dump($_POST) ;die;
	if($login=="admin") {
	
		$credentials=array();
		$roles=array();
		array_push($roles,"Administrator");
		array_push($roles,"Reviewer");
		array_push($roles,"Pengusul");
		$credentials['login']=$login;
		$credentials['userlogin_id']=1;//admin id always 1
		
		/**@TODO, cek for requesting roles here .....*/
		//error_log("@TODO, assign current role without, checking requesting roles ...");
		$credentials['current_role']=$roles[0];
		if(isset($_SESSION['request_role']))
		if(
		    $_SESSION['request_role']==$roles[0]
			||$_SESSION['request_role']==$roles[1]
		    ||$_SESSION['request_role']==$roles[2]
		){
				$credentials['current_role']=$_SESSION['request_role'];
				$request_role_match=true;
				//var_dump ($credentials['current_role']); die;
		} 	
		$credentials['roles']=$roles;
		$app['credentials']=$credentials;
		return true;
	
	}
	
	//var_dump($_SESSION) ; die;

	
	/**not admin allow **/

	
	
	//if ($uri==$GLOBALS['$www_root_uri'] ) $uri ="Halaman Awal";
	//echo "pass 1" ; die;
	$roles_row=getRoles($app,$login);
	$roles=array();

	$request_role_match=false;
	//$request_role_idx=-1;
	
	if($roles_row) {
		$credentials=array();
		//error_log($roles_row);
		//error_log(var_dump($roles_row));
		foreach ($roles_row as $row){
			//error_log($row['userlogin_id']." ".$row['name']); 
			//var_dump($_SESSION); die;
			//error_log("row=>".$row['name']);
			//error_log("session=>".$_SESSION['request_role']);
			if(isset($_SESSION['request_role']))	
			if($_SESSION['request_role']==$row['name']){
				$credentials['current_role']=$row['name'];
				$request_role_match=true;
				//var_dump ($credentials['current_role']); die;
			}
			array_push($roles,$row['name']);
			$credentials['userlogin_id']=$row['userlogin_id'];
		}
		
		$credentials['roles']=$roles;
		$credentials['login']=$login;
		if(!$request_role_match){
			$credentials['current_role']=$roles[0];
		}
		$app['credentials']=$credentials;
		
		
		//cek table get here ....
		if($credentials['current_role']==="Administrator"){
			return ;
		}
		if(startsWith($uri,'/simlitabmas/web/resources')) return ;
		//echo "pass 3" ; die;
		
		//	echo "pass 1" ; die;
		if($credentials['current_role']!="Administrator"){
			
			//echo strpos(strtolower($uri),"/simlitabmas/web/usulandibuka") ;die;
		if(strpos(strtolower($uri),"/simlitabmas/web/navchangerole")!==false) return ;
	
		if(strpos(strtolower($uri),"/simlitabmas/web/usulandibuka/create")!==false) 
				deny($login,$cfgIndexpage,$uri,$strLogout); 
			if(strpos(strtolower($uri),"/simlitabmas/web/usulandibuka/delete")!==false) 
				deny($login,$cfgIndexpage,$uri,$strLogout); 
			if(strpos(strtolower($uri),"/simlitabmas/web/usulandibuka/edit")!==false) 
				deny($login,$cfgIndexpage,$uri,$strLogout); 
			if(strpos(strtolower($uri),"/simlitabmas/web/usulandibuka")!==false) return true; 
			if(strpos(strtolower($uri),"/simlitabmas/web/uploaded/simlitabmas/")!==false) return true; 
			//echo "enter" ; die;
		}
		
		if($credentials['current_role']=="Pengusul"){
			
			if(startsWith($uri,'/simlitabmas/web/usulandibuka')) 
				deny($login,$cfgIndexpage,$uri,$strLogout); ;
			if(startsWith($uri,'/simlitabmas/web/usulan')) return ;
			if($uri=="/simlitabmas/web/") return ;
			/*if($uri=="/simlitabmas/web/usulan") return ;
			if($uri=="/simlitabmas/web/usulan/list") return ;
			if($uri=="/simlitabmas/web/usulan/create") return ;
			if(startsWith($uri,'/simlitabmas/web/usulan/list')){
				deny($login,$cfgIndexpage,$uri,$strLogout);
			}
			if(startsWith($login,$uri,'/simlitabmas/web/usulan'))
			{
				return ;
			}else deny($login,$cfgIndexpage,$uri,$strLogout);
			*/
		
		}
		
		if($credentials['current_role']=="Reviewer"){
			
			if(startsWith($uri,'/simlitabmas/web/usulandibuka/create')) 
				deny($login,$cfgIndexpage,$uri,$strLogout); ;
			if(startsWith($uri,'/simlitabmas/web/usulan/create')) 
				deny($login,$cfgIndexpage,$uri,$strLogout); ;
			if(startsWith($uri,'/simlitabmas/web/usulan')) return ;
			if($uri=="/simlitabmas/web/") return ;
			/*if($uri=="/simlitabmas/web/usulan") return ;
			if($uri=="/simlitabmas/web/usulan/list") return ;
			if($uri=="/simlitabmas/web/usulan/create") return ;
			if(startsWith($uri,'/simlitabmas/web/usulan/list')){
				deny($login,$cfgIndexpage,$uri,$strLogout);
			}
			if(startsWith($login,$uri,'/simlitabmas/web/usulan'))
			{
				return ;
			}else deny($login,$cfgIndexpage,$uri,$strLogout);
			*/
		
		}
		
		
		//return ;
	}
	/** block **/
	
	deny($login,$cfgIndexpage,$uri,$strLogout);
}


?>
