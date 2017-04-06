<?php
	$data = json_decode(file_get_contents('php://input'), true);
	
	$DOC_ROOT = './';
	include_once($DOC_ROOT.'dbconnect.php');
	if($data['action'] == 'LoginValidate'){
		$return = ValidateUser($data['username'], $data['password']);
		echo json_encode($return);
	}else if($data['action'] == 'addBook2Lib'){
		$return = AddItemToLibrary($data);
	}else if($data['action'] == 'fetchBookList'){
		$return = FetchItemFromLibrary();
	}
	exit;
?>