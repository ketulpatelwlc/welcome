<?php

echo "welcome to github account";

$environment = 'sandbox';   // or 'beta-sandbox' or 'live'

/**
 * Send HTTP POST Request
 *
 * @param   string  The API method name
 * @param   string  The POST Message fields in &name=value pair format
 * @return  array   Parsed HTTP Response body
 */

function PPHttpPost($methodName_, $nvpStr_) {

    global $environment;

    $API_UserName = urlencode('bhavesh-facilitator_api1.whitelotuscorporation.com');
    $API_Password = urlencode('1365158570');
    $API_Signature = urlencode('AFcWxV21C7fd0v3bYYYRCpSSRl31AxtOtEBHZ-Z5WDjRb-UhWTDjeuG5');
    $API_Endpoint = "https://api-3t.paypal.com/nvp";
    if("sandbox" === $environment || "beta-sandbox" === $environment) {
        $API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
    }
    $version = urlencode('78');

    // setting the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

    // turning off the server and peer verification(TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);  

    // NVPRequest for submitting to server
    $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

    // setting the nvpreq as POST FIELD to curl
    curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

    // getting response from server
    $httpResponse = curl_exec($ch);

    if(!$httpResponse) {
        exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
    }

    // Extract the RefundTransaction response details
    $httpResponseAr = explode("&", $httpResponse);

    $httpParsedResponseAr = array();
    foreach ($httpResponseAr as $i => $value) {
        $tmpAr = explode("=", $value);
        if(sizeof($tmpAr) > 1) {
            $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
        }
    }

    if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
        exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
    }

    return $httpParsedResponseAr;
}


 	$paymentAmount = urlencode(34.00);
	if(!isset($_REQUEST['token']))
	{  
	     // Set request-specific fields.

	    $currencyID = urlencode('USD');                         // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
	    $paymentType = urlencode('Authorization');              // or 'Sale' or 'Order'

	    $returnURL = urlencode('http://www.ilockitout.net/paypal/getpaypal.php');
	    $cancelURL = urlencode('http://www.ilockitout.net/paypal/cancel.php');

	    // Add request-specific fields to the request string.
	    $nvpStr = "	&Amt=$paymentAmount
	    			&ReturnUrl=$returnURL
	    			&CANCELURL=$cancelURL
	    			&PAYMENTACTION=$paymentType
	    			&CURRENCYCODE=$currencyID
	    		";

	    // Execute the API operation; see the PPHttpPost function above.
	    $httpParsedResponseAr = PPHttpPost('SetExpressCheckout', $nvpStr);
	    $response = curl_exec($httpParsedResponseAr);
	    print_r($httpParsedResponseAr);

	    if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
	    {
	        // Redirect to paypal.com.
	        $token = urldecode($httpParsedResponseAr["TOKEN"]);
	        $payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token";
	        if("sandbox" === $environment || "beta-sandbox" === $environment) 
	        {
	            $payPalURL = "https://www.$environment.paypal.com/webscr&cmd=_express-checkout&token=$token";
	        }

	        //header("Location: $payPalURL");
	        echo '<a href="'.$payPalURL.'">PayPal</a>';
	        exit;
		} 
		    else
		    {
		        exit('SetExpressCheckout failed: ' . print_r($httpParsedResponseAr, true));
		    }
	}
	else
	{
	    $token = urlencode($_REQUEST['token']);
	    //Now create recurring profile
?>
    	<h1>Yes!</h1>
<?php

	$currencyID = urlencode("USD");                     // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
	$startDate = urlencode('2009-08-07T00:00:00\Z');
	$billingPeriod = urlencode("Month");                // or "Day", "Week", "SemiMonth", "Year"
	$billingFreq = urlencode("4");                      // combination of this and billingPeriod must be at most a year

	$nvpStr="&TOKEN=$token&AMT=$paymentAmount&CURRENCYCODE=$currencyID&PROFILESTARTDATE=$startDate";
	$nvpStr .= "&BILLINGPERIOD=$billingPeriod&BILLINGFREQUENCY=$billingFreq";

	$httpParsedResponseAr = PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);

		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
		{
		    exit('CreateRecurringPaymentsProfile Completed Successfully: '.print_r($httpParsedResponseAr, true));
		} 
		else
		{
		    exit('CreateRecurringPaymentsProfile failed: ' . print_r($httpParsedResponseAr, true));
		}	
	}

?>
