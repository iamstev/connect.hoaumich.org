<?php

/* WHICH DATABASES DO WE NEED */ $use_db_house = FALSE; $use_db_main = TRUE;

/* GET KEYS TO SITE */ require('/home/174108/domains/connect.hoaumich.org/html/inc/keys.php');

/* REQUIRED FUNCTIONS */
require($gv_path.'inc/commonfunctions.php');
require($gv_path.'inc/classes/MCAPI.class.php');
require_once($gv_path.'inc/libraries/drill/Client.php');
//

if(siteSetting('connect_email_direct_signup')==1){
	// DIRECT SIGN UP FOR NEWSLETTER
	
	$email = $_GET['e'];
	$merge_vars = array(
		'FNAME'	=> $_GET['f'],
		'LNAME'	=> $_GET['l']
	);
	$chimp = new MCAPI($apikey['mp_connect']);
	$chimp->useSecure;
	$id = $apikey['mp_connect_listid'];
	$ret = $chimp->listSubscribe($id, $email, $merge_vars);
	if ($chimp->errorCode){
		print '<p>There was an error, unable to subscribe. (ref: '.$chimp->errorCode.' - '.$chimp->errorMessage.')</p><label>First Name</label><input type="text" id="fname" /><label>Last Name</label><input type="text" id="lname" /><label>Email Address</label><input type="text" id="email" /><input type="button" value="Subscribe" onclick="AJAXsubscribe(getElementById(\'email\').value,getElementById(\'fname\').value,getElementById(\'lname\').value)" />
		';
	}else{
		print '<p>You have been subscribed to the HOA Connect weekly update. Check your inbox, you will need to click a confirmation link before you will receive any emails.</p>';
		
		
		

			$html  = "<p><strong>".$_GET['f']." ".$_GET['l']."</strong> has signed up to receive the Weekly Update with the email address <strong>".$email."</strong>. They will be automatically been added to the list as soon as they confirm their email address.</p>";
			$html .= "<p>There is no need to take further action.</p>";
			

			$to = 	array(
					array("email" => "connect@hoaumich.org", "name" => "HOA Connect"),
					array("email" => "stevenls@staff.hoaumich.org", "name" => "Steven Smith", "type" => "cc")
				);
			$args = array(
				'key' => $apikey['mandrill'],
				'message' => array(
					"html" => $html,
					"from_email" => "no-reply@hoaumich.org",
					"from_name" => "HOA.bot",
					"subject" => "HOA Connect Weekly Update Subscription [Ticket #: ".time()."]",
					"to" => $to,
					"headers" =>$headers,
					"track_opens" => true,
					"track_clicks" => false,
					"auto_text" => true
				)   
			);

			$mandrill = new \Gajus\Drill\Client($apikey['mandrill']);
			$r = $mandrill->api('messages/send', $args);

			if($r['status']== 'error'){
				
			}else{
				
			}
		
		
		
		
		
		
		
	}
}else{
	// SEND REQUEST TO BE ADDED TO CONNECT@HOAUMIHC.ORG INSTEAD OF DIRECTLY SIGNING UP FOR EMAIL
	
	if($_GET['f']=='' || $_GET['l']=='' || $_GET['e']==''){
		print'<p>All fields required, please enter your name and email address.</p><label>First Name</label><input type="text" id="fname" /><label>Last Name</label><input type="text" id="lname" /><label>Email Address</label><input type="text" id="email" /><input type="button" value="Subscribe" onclick="AJAXsubscribe(getElementById(\'email\').value,getElementById(\'fname\').value,getElementById(\'lname\').value)" />';
	}elseif(!filter_var($_GET['e'], FILTER_VALIDATE_EMAIL)){
			print'<p>The email address you entered was invalid, please try again.</p><label>First Name</label><input type="text" id="fname" /><label>Last Name</label><input type="text" id="lname" /><label>Email Address</label><input type="text" id="email" /><input type="button" value="Subscribe" onclick="AJAXsubscribe(getElementById(\'email\').value,getElementById(\'fname\').value,getElementById(\'lname\').value)" />';
	}else{
		$to = 	array(
					array("email" => "connect@hoaumich.org", "name" => "HOA Connect"),
					array("email" => "stevenls@staff.hoaumich.org", "name" => "Steven Smith", "type" => "cc")
				);
		$args = array(
			'key' => $apikey['mandrill'],
			'message' => array(
				"html" => "<p>Name: ".$_GET['f']." ".$_GET['l']."<br/>Email: ".$_GET['e']."</p>",
				"from_email" => "no-reply@hoaumich.org",
				"from_name" => "HOA.bot",
				"subject" => "Weekly Update Request [Ticket #: ".time()."]",
				"to" => $to,
				"headers" =>$headers,
				"track_opens" => true,
				"track_clicks" => false,
				"auto_text" => true
			)   
		);
		// Open a curl session for making the call
		$curl = curl_init('https://mandrillapp.com/api/1.0/messages/send.json');
		// Tell curl to use HTTP POST
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		// Tell curl not to return headers, but do return the response
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		// Set the POST arguments to pass on
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
		// Make the REST call, returning the result
		$response = curl_exec($curl);
		// Close the connection
		curl_close( $curl );
		$mailerror = json_decode($repsonse, true);
		if($mailerror['status']== 'error'){
			print '<p>There was(ref: madrill error)</p>';
		}else{
			print '<p>Your request to be added to the HOA Connect Weekly Update has been submitted.</p>';
		}
	}
}
$db_main->close();
?>