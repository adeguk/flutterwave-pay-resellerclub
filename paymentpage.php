<?php
/**
*	@package  FpayCheckoutReseller

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
	session_start();
	require("inc/functions.php");	//file which has required functions
?>	 	
		
<html>
<head>
	<title>Payment Page </title>
	<style>
       #explain1{padding:10px;margin:2em;}
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>

<?php
	$key = "KzCI2jYAlIRB6AtEbJFiVt9LH7LinaNl"; //replace ur 32 bit secure key , Get your secure key from your Reseller Control panel
		
		//This filter removes data that is potentially harmful for your application. It is used to strip tags and remove or encode unwanted characters.
	$_GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);
		
		//Below are the  parameters which will be passed from foundation as http GET request
	$paymentTypeId = $_GET["paymenttypeid"];  //payment type id
	$transId = $_GET["transid"];			   //This refers to a unique transaction ID which we generate for each transaction
	$userId = $_GET["userid"];               //userid of the user who is trying to make the payment
	$userType = $_GET["usertype"];  		   //This refers to the type of user perofrming this transaction. The possible values are "Customer" or "Reseller"
	$transactionType = $_GET["transactiontype"];  //Type of transaction (ResellerAddFund/CustomerAddFund/ResellerPayment/CustomerPayment)

	$invoiceIds = $_GET["invoiceids"];		   //comma separated Invoice Ids, This will have a value only if the transactiontype is "ResellerPayment" or "CustomerPayment"
	$debitNoteIds = $_GET["debitnoteids"];	   //comma separated DebitNotes Ids, This will have a value only if the transactiontype is "ResellerPayment" or "CustomerPayment"

	if (isset($_GET["description"])) {
		$description = $_GET["description"];
	} else {
		$description = $transId;
	}
		
	$sellingCurrencyAmount = $_GET["sellingcurrencyamount"]; //This refers to the amount of transaction in your Selling Currency
    $accountingCurrencyAmount = $_GET["accountingcurrencyamount"]; //This refers to the amount of transaction in your Accounting Currency

	$redirectUrl = $_GET["redirecturl"];  //This is the URL on our server, to which you need to send the user once you have finished charging him
						
	$checksum = $_GET["checksum"];	 //checksum for validation

	$cancel_url = $redirect_url;
	$billing_name = $_GET["name"];
	$billing_company = $_GET["company"];
	$billing_email = $_GET["emailAddr"];
	$billing_tel = $_POST["telNoCc"] . $_GET["telNo"];

	if(verifyChecksum($paymentTypeId, $transId, $userId, $userType, $transactionType, $invoiceIds, $debitNoteIds, $description, $sellingCurrencyAmount, $accountingCurrencyAmount, $key, $checksum))
	{
		//YOUR CODE GOES HERE

		/** 
		* since all these data has to be passed back to foundation after making the payment you need to save these data
		*	
		* You can make a database entry with all the required details which has been passed from foundation.  
		*
		*							OR
		*	
		* keep the data to the session which will be available in postpayment.php as we have done here.
		*
		* It is recommended that you make database entry.
		**/
			
		$_SESSION['redirecturl']=$redirectUrl;
		$_SESSION['transid']=$transId;
		$_SESSION['sellingcurrencyamount']=$sellingCurrencyAmount;
		$_SESSION['accountingcurencyamount']=$accountingCurrencyAmount;
		$_SESSION['invoice'] = 'INV'.$invoiceIds;
		$_SESSION['amount'] = $amount;
		$_SESSION['description'] = $description;
		$_SESSION['email']=$billing_email;
		$checksumStatus = 1;
?>
<section id="transMessageSec" class="container">
	<!--TRANSACTION MESSAGE-->  
	<div class="row">
        <div id="messageDiv" class="card mt-4">
	        <div class="card-header">
				<h2>Total Pay <?= 'N' . $sellingCurrencyAmount ?></h2>			
				<h3><?= $description ?></h3>
			</div>
			<div class="card-body">
				<p>Please confirm the above payment information is correct. Loggcity Limited and/or any of its sister companies/divisions is PCI-compliant and does not keep your payment information on their system. <strong>Click "Pay Now" below to Continue</strong></p>
			</div>
			<div class="card-footer">
				<form name="paymentpage" action="postpayment.php" method="POST"  id="paymentForm">	    
				    <input type="hidden" name="amount" value="<?= $sellingCurrencyAmount ?>" /> <!-- Replace the value with your transaction amount -->
				    <input type="hidden" name="payment_options" value="<?= $payment_options ?>" /> <!-- Can be card, account, ussd, qr, mpesa, mobilemoneyghana  (optional) -->
				    <input type="hidden" name="description" value="<?= $description ?>" /> <!-- Replace the value with your transaction description -->
				    <input type="hidden" name="logo" value="https://cdnassets.com/ui/resellerdata/750000_779999/769336/supersite2/supersite/themes/EliteGreen-cloudphire/images/logo.gif" /> <!-- Replace the value with your logo url (optional) -->
				    <input type="hidden" name="title" value="<?= 'Payment for inv '.$invoiceIds ?>" /> <!-- Replace the value with your transaction title (optional) -->
				    <input type="hidden" name="country" value="NG" /> <!-- Replace the value with your transaction country -->
				    <input type="hidden" name="currency" value="NGN" /> <!-- Replace the value with your transaction currency -->
				    <input type="hidden" name="email" value="<?= $billing_email ?>" /> <!-- Replace the value with your customer email -->
				    <input type="hidden" name="firstname" value="Adewale" /> <!-- Replace the value with your customer firstname (optional) -->
				    <input type="hidden" name="lastname"value="Adegoroye" /> <!-- Replace the value with your customer lastname (optional) -->
				    <input type="hidden" name="phonenumber" value="<?= $billing_tel ?>" /> <!-- Replace the value with your customer phonenumber (optional if email is passes) -->
				    <input type="hidden" name="pay_button_text" value="Complete Payment" /> <!-- Replace the value with the payment button text you prefer (optional) -->
				    <?php //Replace the value with your transaction reference. It must be unique per transaction. You can delete this line if you want one to be generated for you
				    if (! empty($transId)) {
				    	echo "<input type='hidden' name='ref' value='$transId'>";
				    }?>
				    <input type="hidden" name="successurl" value="http://request.lendlot.com/13b9gxc1?status=success"> <!-- Put your success url here -->
				    <input type="hidden" name="failureurl" value="http://request.lendlot.com/13b9gxc1?status=failed"> <!-- Put your failure url here -->
				    <input id="btn-of-destiny" class="btn btn-warning"type="submit" value="Pay Now" />
                    <a class="btn btn-danger" style="color:#fff" onClick="javascript:history.go(-1)">&laquo; Go back</a>
				</form>
				<p class="small"><em>RESELLERCLUB.AFRICA is part of Loggcity Digital, a division of <?= $_GET["brandName"] ?>. By continue, you agree to all <a href="https://resellerclub.africa/support/legal.php"><?= $_GET["brandName"] ?> terms and policies</a> of service.</em></p>
			</div>
		</div>
	</div>
</section>
<?php }
	else {
		/**This message will be dispayed in any of the following case
		*
		* 1. You are not using a valid 32 bit secure key from your Reseller Control panel
		* 2. The data passed from foundation has been tampered.
		*
		* In both these cases the customer has to be shown error message and shound not
		* be allowed to proceed  and do the payment.
		*
		**/

		echo "Checksum mismatch !";	
	}
?>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>
