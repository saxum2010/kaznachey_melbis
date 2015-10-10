<?php

class Kaznachey {

	public	$urlGetMerchantInfo = 'http://payment.kaznachey.net/api/PaymentInterface/CreatePayment';
	public	$urlGetClientMerchantInfo = 'http://payment.kaznachey.net/api/PaymentInterface/GetMerchatInformation';
	public	$merchantGuid = '4D312541-F967-4AD4-8D82-16D543ADB02E';
	public	$merchnatSecretKey = 'C319F760-AA04-4C3A-A655-562543BF9EDC';
	
   function __construct() {
   }
   
   function createOrder()
   {
		global $gData, $gOptions;
			
	// Connect to Data 
	$gData = data_connect();
	
	$goods = $_SESSION['goods_info'];
	$client = &$_SESSION['client_info'];

	$gData = data_connect();
	$command = "SELECT * FROM ".DB_NICK."_archive_order WHERE id='". $_SESSION['order_info']['id']."'";
	$res =  mysql_query($command);
	$order=mysql_fetch_assoc($res);

	$success_url = 'http://'.$_SERVER['SERVER_NAME'].'/pay_get.php?type=kaznachey&show_result=1';
	$result_url = 'http://'.$_SERVER['SERVER_NAME'].'/pay_get.php?type=kaznachey';
	$currency = 'UAH';
	
	$i = 0;
	$amount = 0;
	$product_count =  0;

	$order_id = $_SESSION['order_info']['id'];
	$command_b = "SELECT * FROM ".DB_NICK."_archive_basket WHERE order_id='". $order_id ."'";
	$resbasket =  mysql_query($command_b);
	
  	while ($pr_item=mysql_fetch_assoc($resbasket)){

		$img_path_true = $_SERVER["DOCUMENT_ROOT"]."/files/store$pr_item[store_id].jpg";
		$img_path = $_SERVER["SERVER_NAME"]."/files/store$pr_item[store_id].jpg";
		$products[$i]['ImageUrl'] = (file_exists($img_path_true)) ? $img_path : '';
		
		$products[$i]['ProductItemsNum'] = number_format($pr_item['how'], 2, '.', '');
		$products[$i]['ProductName'] = $pr_item['name'];
		
		$products[$i]['ProductPrice'] = number_format($pr_item['price'], 2, '.', '');
		$amount += $pr_item['price']*$pr_item['how'];
		
		$products[$i]['ProductId'] = $pr_item['store_id'];
		$product_count += $products[$i]['ProductItemsNum'];
		$i++; 
	} 

    $paymentDetails = Array(
       "MerchantInternalPaymentId"=>$order_id,
       "MerchantInternalUserId"=>$order['client_id'],
       "EMail"=>$client['email'],
       "PhoneNumber"=>$client['CLIENT19'],
       "CustomMerchantInfo"=>$client['CLIENT17'],
       "StatusUrl"=>"$result_url",
       "ReturnUrl"=>"$success_url",
       "BuyerCountry"=>'',
       "BuyerFirstname"=>$order['notice'],
       "BuyerPatronymic"=>'',
       "BuyerLastname"=>'',
       "BuyerStreet"=>$order['notice'],
       "BuyerZone"=>'',
       "BuyerZip"=>'',
       "BuyerCity"=>'',
       "DeliveryFirstname"=>'',
       "DeliveryLastname"=>"",
       "DeliveryZip"=>"", 
       "DeliveryCountry"=>'',
       "DeliveryPatronymic"=>"",
       "DeliveryStreet"=>'',
       "DeliveryCity"=>'',
       "DeliveryZone"=>"",
    );

	$product_count = number_format($product_count, 2, '.', '');	
	$amount = number_format($amount, 2, '.', '');	

	$selectedPaySystemId = $this->GetMerchnatInfo(false, 1);
	
	$signature = md5(
		$this->merchantGuid.
		"$amount".
		"$product_count".
		$paymentDetails["MerchantInternalUserId"].
		$paymentDetails["MerchantInternalPaymentId"].
		$selectedPaySystemId.
		$this->merchnatSecretKey
	);

	$request = Array(
        "SelectedPaySystemId"=>$selectedPaySystemId,
        "Products"=>$products,
        "PaymentDetails"=>$paymentDetails,
        "Signature"=>$signature,
        "MerchantGuid"=>$this->merchantGuid,
		"Currency"=> $currency
    );

	$res = $this->sendRequestKaznachey($this->urlGetMerchantInfo, json_encode($request));
	$result = json_decode($res,true);

	if($result['ErrorCode'] != 0){
		return false;
	}
	
		return base64_decode($result["ExternalForm"]);
		
	}
	
	function wtf($text)
	{
		$file = 'wtf.txt';
		$current = file_get_contents($file);
		$current .= '\n'.$text;
		file_put_contents($file, $current);
	}
   		
		function sendRequestKaznachey($url,$data)
		{
			$curl =curl_init();
			if (!$curl)
				return false;

			curl_setopt($curl, CURLOPT_URL,$url );
			curl_setopt($curl, CURLOPT_POST,true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, 
					array("Expect: ","Content-Type: application/json; charset=UTF-8",'Content-Length: ' 
						. strlen($data)));
			curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,True);
			$res =  curl_exec($curl);
			curl_close($curl);

			return $res;
		}

		function GetMerchnatInfo($id = false, $def = false)
		{
			$requestMerchantInfo = Array(
				"MerchantGuid"=>$this->merchantGuid,
				"Signature"=>md5($this->merchantGuid.$this->merchnatSecretKey)
			);

			$resMerchantInfo = json_decode($this->sendRequestKaznachey($this->urlGetClientMerchantInfo , json_encode($requestMerchantInfo)),true); 
			if($id)
			{
				foreach ($resMerchantInfo["PaySystems"] as $key=>$paysystem)
				{
					if($paysystem['Id'] == $id)
					{
						return $paysystem;
					}
				}
			}elseif($def){
				foreach ($resMerchantInfo["PaySystems"] as $key=>$paysystem)
				{
					return $paysystem['Id'];
				}
			}else{
				return $resMerchantInfo;
			}
		}

		function GetTermToUse()
		{
			$requestMerchantInfo = Array(
				"MerchantGuid"=>$this->merchantGuid,
				"Signature"=>md5($this->merchantGuid.$this->merchnatSecretKey)
			);

			$resMerchantInfo = json_decode($this->sendRequestKaznachey($this->urlGetClientMerchantInfo , json_encode($requestMerchantInfo)),true); 

			return $resMerchantInfo["TermToUse"];

		}
		
		function getPaySystems()
		{
			$cc_types = $this->GetMerchnatInfo();
			if(isset($cc_types["PaySystems"]))
			{
				$box = '
				<div id="kznd"><label for="cc_types">Выберите способ оплаты</label><select name="cc_types" id="cc_types" >';
				$term_url = $this->GetTermToUse();
				foreach ($cc_types["PaySystems"] as $paysystem)
				{
					$box .= "<option value='$paysystem[Id]'>$paysystem[PaySystemName]</option>";
				}
				$box .= '</select><br><input type="checkbox" checked="checked" value="1" name="cc_agreed" id="cc_agreed"><label for="cc_agreed"><a href="'.$term_url.'" >Согласен с условиями использования</a></label>
				</div>';
				$box .= "<script type=\"text/javascript\">
				(function(){ 
				var cc_a = jQuery('#cc_agreed'),
					ds = jQuery('#ds');
					 cc_a.on('click', function(){
						if(cc_a.is(':checked')){	
							jQuery('#kznd').find('.error').text('');
							ds.attr('disabled', false);
						}else{
							cc_a.next().after('<span class=\"error\" style=\"color:red\">Примите условие!</span>');
							ds.attr('disabled', true);
						}
					 });
				})(); 
				</script> ";
				
				print iconv("UTF-8","CP1251",$box);

			}
		}
		
		function setSession($data)
		{
			foreach($data as $key=>$item)
			{
				$_SESSION[$key] = $item;
			}
		}
		
		function home_url(){
			header("Location: ".'http://'.$_SERVER['SERVER_NAME']);
		}
		
		public function success_page($order_id = false) {
			print iconv("UTF-8","CP1251","<style>
			body{background-color: #527496; font: normal 13px Verdana,sans-serif;}
			.message_container{background-color: #fff; width: 50%; text-align:center; margin: auto; margin-top: 100px; padding: 50px;}
			.valid {color: green;}
			.invalid {color: red;}
			</style>
			<div class='message_container'> <h4><p class='invalid'>Ваш заказ №$order_id Спасибо за Ваш заказ №$order_id! Ваш заказ оплачен</p></h4> 
				<input type='button' value=' Закрыть ' onCLick=\"location='http://".$_SERVER['HTTP_HOST']."';\">
			</div> 
			");
		}
		
		public function deferred_page($order_id = false) {
			print iconv("UTF-8","CP1251","<style>
			body{background-color: #527496; font: normal 13px Verdana,sans-serif;}
			.message_container{background-color: #fff; width: 50%; text-align:center; margin: auto; margin-top: 100px; padding: 50px;}
			.valid {color: green;}
			.invalid {color: red;}
			</style>
			<div class='message_container'> <h4><p class='invalid'>Ваш заказ №$order_id Спасибо за Ваш заказ №$order_id! Вы сможете оплатить его после проверки менеджером. Ссылка на оплату будет выслана Вам по электронной почте.</p></h4> 
				<input type='button' value=' Закрыть ' onCLick=\"location='http://".$_SERVER['HTTP_HOST']."';\">
			</div> 
			");
		}
  
}
/**
 * Function pay_go
 **/
function pay_go()
{ 
	$Kaznachey = new Kaznachey();
	$ExternalForm = $Kaznachey->createOrder();
	echo $ExternalForm;
}

/**
 * Function pay_get
 **/
function pay_get(){

	global $gData, $gOptions;
	$Kaznachey = new Kaznachey();
	
	$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');

	$hrpd = json_decode($HTTP_RAW_POST_DATA);
	$order_id = intval($hrpd->MerchantInternalPaymentId); 
	if($order_id == 0){
		if(isset($_GET['show_result']))
		{
			$html = file_get_contents('pay_mod/kaznachey/pay_ok.htm');
			print $html;
			exit; 
		}else{
			$kaznachey->home_url(); exit; 
		}
	}

	if(isset($hrpd->MerchantInternalPaymentId))
	{
		if($hrpd->ErrorCode == 0)
		{
			$gData = data_connect();
			$command = "UPDATE ".DB_NICK."_archive_order SET get_payment=2 WHERE order_id='".addslashes($hrpd->MerchantInternalPaymentId)."'";
			mysql_query($command);

			// Set order ok e-mail for admin
			$tplm = new FastTemplate('./pay_mod/Kaznachey');
			$tplm->DefineTemplate(array('mail_message' => 'pay_ok_mail.htm'));
			$tplm->Assign(array('ORDERCODE'	=> $hrpd->MerchantInternalPaymentId,
							'SUMA'		=> number_format($arr['amt']*1, 2, '.', ''),
							'CURR'		=> $arr['ccy'],
							'SHOPNAMES' 	=> $gOptions['attr_shop_name'],
							'SHOPURL'	=> $gOptions['attr_shop_url'],
							'DATE'		=> date('Y-m-d H:i:s', $arr['date'])
					  ));
			$tplm->Parse('MAIL', 'mail_message');
			$mailer = new Emailer(MAIL_SERVER);
			$mailer->SetCharset($gOptions['attr_admin_charset']);
			$mailer->SetTypeText();
			$all_message = iconv(SHOP_CHARSET, $gOptions['attr_admin_charset'], $tplm->Fetch('MAIL'));
			$subject = substr($all_message, strpos($all_message, 'Message_subject:')+16, strpos($all_message, 'Message_content:')-16);
			$message = substr($all_message, strpos($all_message, 'Message_content:')+16);		
			$mailer->AddMessage($message);
			$mailer->BuildMessage();
			$mailer->Send($gOptions['attr_admin_email'], $gOptions['attr_shop_email'], ltrim($subject, " "));
		}
	}
 	
}



?>