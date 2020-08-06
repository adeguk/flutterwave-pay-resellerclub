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
	session_save_path("./"); //path on your server where you are storing session

	//file which has required functions
	require("inc/functions.php");
	// session_destroy();
	// Prevent direct access to this class

	define("BASEPATH", 1);

	include('library/rave.php');
	include('library/raveEventHandlerInterface.php');

	use Flutterwave\Rave;
	use Flutterwave\EventHandlerInterface;

	$URL = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$getData = $_GET;
	$postData = $_POST;
	$publicKey = $_SERVER['PUBLIC_KEY'];
	$secretKey = $_SERVER['SECRET_KEY'];
	$success_url = $postData['successurl'];
	$failure_url = $postData['failureurl'];
	$env = $_SERVER['ENV'];

	if($postData['amount']){
	    $_SESSION['publicKey'] = $publicKey;
	    $_SESSION['secretKey'] = $secretKey;
	    $_SESSION['env'] = $env;
	    $_SESSION['successurl'] = $success_url;
	    $_SESSION['failureurl'] = $failure_url;
	    $_SESSION['currency'] = $postData['currency'];
	    $_SESSION['amount'] = $postData['amount'];
	}

	$prefix = 'RV'; // Change this to the name of your business or app
	$overrideRef = false;

	// Uncomment here to enforce the useage of your own ref else a ref will be generated for you automatically
	if ($postData['ref']){
	    $prefix = $postData['ref'];
	    $overrideRef = true;
	}

	$payment = new Rave($_SESSION['secretKey'], $prefix, $overrideRef);

	function getURL($url,$data = array()){
	    $urlArr = explode('?',$url);
	    $params = array_merge($_GET, $data);
	    $new_query_string = http_build_query($params).'&'.$urlArr[1];
	    $newUrl = $urlArr[0].'?'.$new_query_string;
	    return $newUrl;
	};

// This is where you set how you want to handle the transaction at different stages
class myEventHandler implements EventHandlerInterface{
    /**
     * This is called when the Rave class is initialized
     * */
    function onInit($initializationData){
        // Save the transaction to your DB.
    }
    
    /**
     * This is called only when a transaction is successful
     * */
    function onSuccessful($transactionData){
        // Get the transaction from your DB using the transaction reference (txref)
        // Check if you have previously given value for the transaction. If you have, redirect to your successpage else, continue
        // Comfirm that the transaction is successful
        // Confirm that the chargecode is 00 or 0
        // Confirm that the currency on your db transaction is equal to the returned currency
        // Confirm that the db transaction amount is equal to the returned amount
        // Update the db transaction record (includeing parameters that didn't exist before the transaction is completed. for audit purpose)
        // Give value for the transaction
        // Update the transaction to note that you have given value for the transaction
        // You can also redirect to your success page from here
        if ($transactionData->chargecode === '00' || $transactionData->chargecode === '0'){
          	if ($transactionData->currency == $_SESSION['currency'] && $transactionData->amount == $_SESSION['amount']){              
              	if ($_SESSION['publicKey']){
                    header('Location: '.getURL($_SESSION['successurl'], array('event' => 'successful')));
                    $_SESSION = array();;
                    session_destroy();
                }
          	} else {
              	if ($_SESSION['publicKey']){
                    header('Location: '.getURL($_SESSION['failureurl'], array('event' => 'suspicious')));
                    $_SESSION = array();
                    session_destroy();
                }
          }
      	} else {
          	$this->onFailure($transactionData);
      	}
    }
    
    /**
     * This is called only when a transaction failed
     * */
    function onFailure($transactionData){
        // Get the transaction from your DB using the transaction reference (txref)
        // Update the db transaction record (includeing parameters that didn't exist before the transaction is completed. for audit purpose)
        // You can also redirect to your failure page from here
        if ($_SESSION['publicKey']){
            header('Location: '.getURL($_SESSION['failureurl'], array('event' => 'failed')));
            $_SESSION = array();            
            session_destroy();
        }
    }
    
    /**
     * This is called when a transaction is requeryed from the payment gateway
     * */
    function onRequery($transactionReference){
        // Do something, anything!
    }
    
    /**
     * This is called a transaction requery returns with an error
     * */
    function onRequeryError($requeryResponse){
        // Do something, anything!
    }
    
    /**
     * This is called when a transaction is canceled by the user
     * */
    function onCancel($transactionReference){
        // Do something, anything!
        // Note: Somethings a payment can be successful, before a user clicks the cancel button so proceed with caution
        if($_SESSION['publicKey']){
            header('Location: '.getURL($_SESSION['failureurl'], array('event' => 'cancelled')));
            $_SESSION = array();
            session_destroy();
        }
    }
    
    /**
     * This is called when a transaction doesn't return with a success or a failure response. This can be a timedout transaction on the Rave server or an abandoned transaction by the customer.
     * */
    function onTimeout($transactionReference, $data){
        // Get the transaction from your DB using the transaction reference (txref)
        // Queue it for requery. Preferably using a queue system. The requery should be about 15 minutes after.
        // Ask the customer to contact your support and you should escalate this issue to the flutterwave support team. Send this as an email and as a notification on the page. just incase the page timesout or disconnects
        if($_SESSION['publicKey']){
            header('Location: '.getURL($_SESSION['failureurl'], array('event' => 'timedout')));
            $_SESSION = array();
            session_destroy();
        }
    }
}

if($postData['amount']){
    // Make payment
    $payment
    ->eventHandler(new myEventHandler)
    ->setAmount($postData['amount'])
    ->setPaymentOptions($postData['payment_options']) // value can be card, account or both
    ->setDescription($postData['description'])
    ->setLogo($postData['logo'])
    ->setTitle($postData['title'])
    ->setCountry($postData['country'])
    ->setCurrency($postData['currency'])
    ->setEmail($postData['email'])
    ->setFirstname($postData['firstname'])
    ->setLastname($postData['lastname'])
    ->setPhoneNumber($postData['phonenumber'])
    ->setPayButtonText($postData['pay_button_text'])
    ->setRedirectUrl($URL)
    // ->setMetaData(array('metaname' => 'SomeDataName', 'metavalue' => 'SomeValue')) // can be called multiple times. Uncomment this to add meta datas
    // ->setMetaData(array('metaname' => 'SomeOtherDataName', 'metavalue' => 'SomeOtherValue')) // can be called multiple times. Uncomment this to add meta datas
    ->initialize();
}else{
    if($getData['cancelled'] && $getData['tx_ref']){
        // Handle canceled payments
        $payment
        ->eventHandler(new myEventHandler)
        ->requeryTransaction($getData['tx_ref'])
        ->paymentCanceled($getData['tx_ref']);
    }elseif($getData['tx_ref']){
        // Handle completed payments
        $payment->logger->notice('Payment completed. Now requerying payment.');
        
        $payment
        ->eventHandler(new myEventHandler)
        ->requeryTransaction($getData['tx_ref']);
    }else{
        $payment->logger->warn('Stop!!! Please pass the txref parameter!');
        echo 'Stop!!! Please pass the txref parameter!';
    }
} ?>
<html>
<head>
	<title>F-Pay Post Payment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
<?php
	$key = "KzCI2jYAlIRB6AtEbJFiVt9LH7LinaNl"; //replace ur 32 bit secure key , Get your secure key from your Reseller Control panel	    

	$redirectUrl = $_SESSION['redirecturl'];  // redirectUrl received from foundation
	$transId = $_SESSION['transid'];		 //Pass the same transid which was passsed to your Gateway URL at the beginning of the transaction.
	$sellingCurrencyAmount = $_SESSION['sellingcurrencyamount'];
	$accountingCurrencyAmount = $_SESSION['accountingcurencyamount'];
	$email =  $_SESSION['email'];

	$status = $_REQUEST["status"];	 // Transaction status received from your Payment Gateway
    //This can be either 'Y' or 'N'. A 'Y' signifies that the Transaction went through SUCCESSFULLY and that the amount has been collected.
    //An 'N' on the other hand, signifies that the Transaction FAILED.

    if ($status == 'successful') {
    	$responseMessage = "<div class='alert-message alert-message-success text-center'>
                        <h4>Thanks for choosing us!</h4>
                        <p>You payment was successful and has been received by Resellerclub.Africa</p>
                        <h3>N$sellingCurrencyAmount</h3>
                        <p>A copy of your receipts has been sent your email, <strong>$email</strong>. This transaction will appear on your statement as RAVEM.</p>
                        <p style='color:dd3333'><em>(Please do not use 'Refresh' or 'Back' button)</em></p>
                    </div>";
        $button = '<input type="submit" value="Return to Merchant"><br/>';
    } elseif ($status == 'failed'){
    	$responseMessage = '<div id ="notificationBar" class="alert alert-danger text-center" role="alert">
                            <b>Alert </b>Something went wrong!
                        </div>';
        $button = '<a class="btn btn-danger" style="color:#fff" href="https://resellerclub.africa/">&laquo; Return to Merchant Site</a>';
    }

	/**
	 * HERE YOU HAVE TO VERIFY THAT THE STATUS PASSED FROM YOUR PAYMENT GATEWAY IS VALID.
	 * And it has not been tampered with. The data has not been changed since it can * easily be done with HTTP request. 
	 **/
		
	srand((double)microtime()*1000000);
	$rkey = rand();

	$checksum =generateChecksum($transId,$sellingCurrencyAmount,$accountingCurrencyAmount,$status, $rkey,$key);
?>
	<section id="transMessageSec" class="container">
		<!--TRANSACTION MESSAGE-->  
		<div class="row">
	        <div id="messageDiv" class="card mt-4">
		        <div class="card-body">
					<?= $responseMessage ?>
				</div>
				<div class="card-footer">
					<form name="f1" action="<?= $redirectUrl;?>" id="postpayment">	    
					    <input type="hidden" name="transid" value="<?= $transId; ?>">
	            		<input type="hidden" name="rkey" value="<?= $rkey; ?>">
	            	    <input type="hidden" name="checksum" value="<?= $checksum; ?>">
	            	    <input type="hidden" name="sellingamount" value="<?= $sellingCurrencyAmount; ?>">
	            		<input type="hidden" name="accountingamount" value="<?= $accountingCurrencyAmount; ?>">
	            		<input type="hidden" name="status" value="<?= $status; ?>">
	            		<?= $button ?>
					</form>
					<p class="small"><em>RESELLERCLUB.AFRICA is part of Loggcity Digital, a division of <?= $_GET["brandName"] ?>. By continue, you agree to all <a href="https://resellerclub.africa/support/legal.php"><?= $_GET["brandName"] ?> terms and policies</a> of service.</em></p>
				</div>
			</div>
		</div>
	</section>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>