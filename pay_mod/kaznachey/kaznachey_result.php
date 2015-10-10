<?php
require_once ($_SERVER['DOCUMENT_ROOT']."/pay_mod/kaznachey/payment.php");

$kaznachey = new kaznachey();

if($status = $_REQUEST['status'])
{
	switch ($status) {
	    case "ps":
			$kaznachey->getPaySystems();
	        break;		    
		case "ss":
			$kaznachey->setSession(array("cc_types"=>$_REQUEST['cc_types']));
	        break;		   
	    default:
	       echo "";
	}

}
?>
