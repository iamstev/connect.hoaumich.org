<?php

$f = mktime(0,0,0,$m,1,$y);
$l = mktime(23,59,59,$m+1,0,$y);
$ld = date("d",$l);
if(date("D",$f)=='Sun'){
	$fb = 1;
}elseif(date("D",$f)=='Mon'){
	$fb = 2;
}elseif(date("D",$f)=='Tue'){
	$fb = 3;
}elseif(date("D",$f)=='Wed'){
	$fb = 4;
}elseif(date("D",$f)=='Thu'){
	$fb = 5;
}elseif(date("D",$f)=='Fri'){
	$fb = 6;
}elseif(date("D",$f)=='Sat'){
	$fb = 7;
}
$lb = $fb + $ld - 1;

print'
	<section class="month">
		<h1>'.date("F",$f).' '.date("Y",$f).'</h1>
';


$R_cal = $db_main->query("SELECT * FROM calendar WHERE (calendar='2' OR calendar='3' OR calendar='4' OR calendar='5') AND (start_unix BETWEEN $f AND $l) ORDER BY start_unix");
$month = array();
if($R_cal!=FALSE){
	while($event = $R_cal->fetch_assoc()){
		$day = substr($event['start'],6,2)+0; // add zero to convert to int and drop preceeding zeros, or else events on single digit days do not show up
		$uid = $event['uid'];
		$month[$day][$uid] = array(
			'uid'		=> $uid,
			'day'		=> $day,
			'start'		=> $event['start_unix'],
			'end'		=> $event['end_unix'],
			'location'	=> $event['location'],
			'summary'	=> $event['summary']
		);
	}
	$R_cal->free();
}

for($box = 1; $box <= 35; $box++) {

	$date = $box - $fb + 1;

	/* start week */
	if($box == 1 || $box == 8 || $box == 15 || $box == 22 || $box == 29){
		print'<ul class="week">';
	}
	
	/* check for first or last day of week and empty*/
	if($box == 7 || $box == 14 || $box == 21 || $box == 28 || $box == 35){
		$extraclass = ' lastday';
	}elseif($box == 1 || $box == 8 || $box == 15 || $box == 22 || $box == 29){
		$extraclass = ' firstday';
	}
	if($month[$date]==''){
		$extraclass .= ' empty';
	}
	
	
	print'<li class="day'.$extraclass.'">';
	
	/* print dates*/
	if($box >= $fb && $box <= $lb){
		print'<h3>'.$date.'</h3>';
	}
	
	/* print events */
	if($month[$date]!=''){
		print'<ul>';
		foreach($month[$date] as $e){
			print '
				<li>
					<a onclick="AJAXcalEvent(\''.$e['uid'].'\')">'.date("g:ia",$e['start']).' '.$e['summary'].'</a>
				</li>
			';
		}
		print'</ul>';
	}
	
	/* end day */
	print'</li>';
	
	/* end week */
	if($box == 7 || $box == 14 || $box == 21 || $box == 28 || $box == 35){
		print'</ul>';
	}
	
	unset($extraclass, $date);
	
}
		
print'
	</section>
';

?>