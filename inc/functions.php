<?php
/**
*	@package  StripeCheckoutReseller

    Plugin Name: Fluterwave Payment Gateway for Reseller Club 
    Plugin URI: https://loggcity.africa/item/flutterwave-payment-gateway-for-resellerclub-africa
    Description: This extends Reseller Club to accepts money/payments through Fluterwaves Payment gateway on your supersite. 
    File Description: The base configurations of the plugin.
    This file has the following configurations:  Reseller Club Key, Fluterwave Merchant ID, Fluterwave Working Key and Fluterwave Access Code
    Author: Loggcity
    Author URI: https://loggcity.africa
    Version: 1.0.0
    Copyright 2020 Loggcity, Adewale Adegoroye
*/

	function generateChecksum($transId,$sellingCurrencyAmount,$accountingCurrencyAmount,$status, $rkey,$key) {	
		$str = "$transId|$sellingCurrencyAmount|$accountingCurrencyAmount|$status|$rkey|$key";
        $generatedCheckSum = md5($str);
		return $generatedCheckSum;
	}

	function verifyChecksum($paymentTypeId, $transId, $userId, $userType, $transactionType, $invoiceIds, $debitNoteIds, $description, $sellingCurrencyAmount, $accountingCurrencyAmount, $key, $checksum)
	{
		$str = "$paymentTypeId|$transId|$userId|$userType|$transactionType|$invoiceIds|$debitNoteIds|$description|$sellingCurrencyAmount|$accountingCurrencyAmount|$key";
        $generatedCheckSum = md5($str);
//		echo $str."<BR>";
//		echo "Generated CheckSum: ".$generatedCheckSum."<BR>";
//		echo "Received Checksum: ".$checksum."<BR>";
		if($generatedCheckSum == $checksum)
			return true ;
		else
			return false ;
	}	
?>