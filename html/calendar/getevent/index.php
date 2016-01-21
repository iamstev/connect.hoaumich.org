<?php
ob_start();
/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps($use_https);

/* WHICH DATABASES DO WE NEED */
$db2use = array(
    'db_auth'		=> FALSE,
    'db_main'		=> TRUE,
    'db_house'      => FALSE,
    'db_membership' => FALSE
);

/* GET KEYS TO SITE */ require($path_to_keys);
$uid = $_GET['uid'];
$R_cal = $db_main->query("SELECT * FROM calendar WHERE uid='$uid' LIMIT 1");
if($R_cal!=FALSE){
	$e = $R_cal->fetch_assoc();
	print'
		<table>
			<tr>
				<th style="vertical-align:bottom;">
					<figure>
						<div class="minical-mo">'.strtoupper(date("M", $e['start_unix'])).'</div>
						<div class="minical-da">'.date("j", $e['start_unix']).'</div>
					</figure>
				</th>
				<td style="vertical-align:bottom;"><h2>'.$e['summary'].'</h2></td>
			</tr>
			<tr>
				<th class="space">Calendar</th>
				<td class="space"></td>
			</tr>
			<tr>
				<th class="space">Date</th>
				<td class="space">'.date("l, F j, Y", $e['start_unix']).'</td>
			</tr>
			<tr>
				<th>Start Time</th>
				<td>'.date("g:ia", $e['start_unix']).'</td>
			</tr>
			<tr>
				<th>End Time</th>
				<td>'.date("g:ia", $e['end_unix']).'</td>
			</tr>
			<tr>
				<th>Location</th>
				<td>'.$e['location'].'</td>
			</tr>
			<tr>
				<th class="space">Attachment</th>
				<td class="space link"><a href="'.$e['link'].'">'.$e['link'].'</a></td>
			</tr>
			<tr>
				<th class="space">Description</th>
				<td class="space">'.$e['desc'].'</td>
			</tr>
		</table>
	';
	$R_cal->free();
}else{
	print'<p>There was a problem getting the event details.</p>';
}
ob_end_flush();
$db_main->close();
?>
