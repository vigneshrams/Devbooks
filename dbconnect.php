<?php
function GetDBConnection(){
	include_once($DOC_ROOT.'adodb5/adodb.inc.php');
	include_once($DOC_ROOT.'adodb5/adodb-errorhandler.inc.php');

	define('ADODB_ERROR_LOG_TYPE', 3);
	define('ADODB_ERROR_LOG_DEST', 'adodb_errors.log');

	$os = PHP_OS;
	$dbinfo = array('localhost', 'bookApp', 'postgres', 'suriya227', 'BookApp');
	//array('HOST_NAME', 'ODBC_NAME', 'USERNAME', 'PASSWORD', 'DB_NAME');
	
	$odbcStrValue = (($os=='Linux' || $os=='Darwin' || $os=='FreeBSD')?$USE_DATABASE:'odbc');
	$dsName = (($os=='Linux' || $os=='Darwin' || $os=='FreeBSD')? $dbinfo[0]:$dbinfo[1]);
	$conn = &ADONewConnection($odbcStrValue); //create a connection
	
	if(!$conn){
		$returnData[0] = 1;
		$returnData[1] = $eventLabels['Error_DB_Connection'];
	}else{
		$conn->autoRollback = true; //default is false
		$returnValue = $conn->PConnect($dsName, $dbinfo[2], $dbinfo[3], $dbinfo[4]);
		if($conn->IsConnected()){
			$returnData[0] = 0;
			$returnData[1] = $conn;
		}else{
			$returnData[0] = 1;
			$returnData[1] = $eventLabels['DB_Connection_Failed'];
		}
	}
	return $returnData;
}

function ValidateUser($usrName, $password){
	$connResult = GetDBConnection();
	if($connResult[0] == 0){
		$selectQuery = "";
		$selectQuery = "Select * from user_details where user_name='".$usrName."' and password='".$password."'";
		$handle = $connResult[1];//Connection string
		$resultSet = $handle->Execute($selectQuery);
		if(!$resultSet->EOF){
			if($resultSet->fields[0] != '' && $resultSet->fields[0] >0){
				$selUsrTokenQry = "select * from user_token where user_id=?;";
				$tokenInsertQry = "insert into user_token (user_id, user_token, time) values (?,?,?);";
				$usrID = $resultSet->fields[0];
				$currTime = gmdate('Ymwdhis');					
				$tokenString = $resultSet->fields[1].$resultSet->fields[2].$currTime;
				$usrToken = md5($tokenString);
				$selTokenRslt = $handle->Execute($selUsrTokenQry, array($usrID));
				if($selTokenRslt->EOF){		
					$insrtRslt = $handle->Execute($tokenInsertQry, array($usrID, $usrToken, $currTime));
					if($insrtRslt){
						$retdata = array('status'=>0,'userID'=>$usrID, 'userToken'=>$usrToken, 'userLoggedTime'=>$currTime);
					}else{
						$retdata = array('status'=>1,'userID'=>$usrID, 'userToken'=>$usrToken, 'userLoggedTime'=>$currTime, 'Message'=>'Error while inserting data');
					}
				}else{
					$retdata = array('status'=>1,'userID'=>$usrID, 'userToken'=>$usrToken, 'userLoggedTime'=>$currTime,'Message'=>'Another session for user exist.', 'code'=>'Session Exist');
				}
			}
			$resultSet->Close();
		}else{
			$retdata = array('status'=>1,'Message'=>'Invalid Username or Password.', 'code'=>'Invalid Credentials');
		}
		$handle->Close();
	}
	return $retdata;
}

function AddItemToLibrary($addData){	
	$connResult = GetDBConnection();
	if($connResult[0] == 0){
		$selectQuery = "";
		$selectQuery = "Select * from user_details where user_id = ?";
		$handle = $connResult[1];//Connection string
		$selUsrInfoRslt = $handle->Execute($selectQuery, array($addData['activeusrID']));
		if(!$selUsrInfoRslt->EOF){			
			$currUsername = $selUsrInfoRslt->fields[1];
			$currPassword = $selUsrInfoRslt->fields[2];
			$tokenString = $currUsername.$currPassword.$addData['activeusrTime'];
			if(md5($tokenString) == $addData['activeusrToken']){
				$bookInsertQry = "insert into bapp_list (book_name,book_desc,book_author,book_link,book_price,book_categ,book_rating,book_img,book_show,book_delete) values (?,?,?,?,?,?,?,?,?,?);";
				$bookInsertRslt = $handle->Execute($bookInsertQry, array($addData['bname'],$addData['bdesc'],$addData['baname'],$addData['bhyplink'],$addData['bprice'],$addData['bcategory'],$addData['brating'],$addData['bImg'],0,0));
				$exeStat = 1;
				if($bookInsertRslt){
					$exeStat = 0;
				}
			}else{
				$exeStat = 1;
			}
		}
	}
	$handle->Close();
	echo json_encode(array('stat'=>$exeStat));
}

function FetchItemFromLibrary(){
	$connResult = GetDBConnection();
	if($connResult[0] == 0){
		$selectQuery = "";
		$selectQuery = "select * from bapp_list where book_show=0 and book_delete=0";
		$handle = $connResult[1];//Connection string
		$fetchBookRslt = $handle->Execute($selectQuery);
		if($fetchBookRslt){
			$i = 0;
			while(!$fetchBookRslt->EOF){
				$resultArr[$i]['id'] = $fetchBookRslt->fields[0];
				$resultArr[$i]['name'] = $fetchBookRslt->fields[1];
				$resultArr[$i]['desc'] = $fetchBookRslt->fields[2];
				$resultArr[$i]['authName'] = $fetchBookRslt->fields[3];
				$resultArr[$i]['link'] = $fetchBookRslt->fields[4];
				$resultArr[$i]['price'] = $fetchBookRslt->fields[5];
				$resultArr[$i]['category'] = $fetchBookRslt->fields[6];
				$resultArr[$i]['rating'] = $fetchBookRslt->fields[7];
				$resultArr[$i]['image'] = $fetchBookRslt->fields[8];
				$fetchBookRslt->MoveNext();
				$i++;
			}
		}
	}
	$handle->Close();
	echo json_encode($resultArr);
}

?>