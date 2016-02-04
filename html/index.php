<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps($use_https);

/* WHICH DATABASES DO WE NEED */
$db2use = array(
    'db_auth'		=> TRUE,
    'db_main'		=> TRUE,
    'db_house'      => FALSE,
    'db_membership' => FALSE
);

/* GET KEYS TO SITE */ require($path_to_keys);


/* LOAD FUNC-CLASS-LIB */
require_once('classes/hoa-user.class.php');
$user = new hoa_user;



/* PAGE VARIABLES */
$currentpage = '';





// get news articles
$now = time();
$home_news = array();
$R_home_news = $db_main->query("SELECT news.*, news_covers.image FROM news LEFT JOIN news_covers ON news.article = news_covers.article WHERE published=1 AND (push2connect=1 OR sectionID=2) AND `pubdate` < $now ORDER BY `pubdate` DESC LIMIT 3");
if($R_home_news != FALSE){
	$R_home_news->data_seek(0);
	$i = 0;
	while($art = $R_home_news->fetch_object()){
		$home_news[$i]['article'] = $art->article;
		$home_news[$i]['headline'] = $art->headline;
		$home_news[$i]['strapline'] = $art->strapline;
		$home_news[$i]['pubdate'] = $art->pubdate;
		$home_news[$i]['image'] = $art->image;
		$i++;
	}
	$R_home_news->free();
}



ob_end_flush();
/* <HEAD> */ $head='';
/* PAGE TITLE */ $title='HOA Connect';
/* HEADER */ require('layout/header1.php');

print'
<div class="content">
    <section class="events">
        <ul>
            <h2>Upcoming Events</h2>
';

$m = date("n");
$y = date("Y");
$d = date("j");
$f = mktime(0,0,0,$m,$d,$y);
$R_cal = $db_main->query("SELECT * FROM calendar WHERE (calendar='2' OR calendar='3' OR calendar='4' OR calendar='5') AND (start_unix >= $f ) ORDER BY start_unix LIMIT 3");
if($R_cal!=FALSE){
	$R_cal->data_seek(0);
	$i = 0;
	while($event = $R_cal->fetch_assoc()){
		$i++;
		print'
			<li';if($i==3){print' class="third"';}print'>
				<div id="cal'.$event['uid'].'" class="modal-holder caldet">
					<div class="modal-wrap">
						<div class="modal">
							<input class="close" type="image" src="/img/cancel.png" value="Close" onclick="hideModal(\'cal'.$event['uid'].'\');">
							<h1>Event Details</h1>
							<div class="fancy-modal-1"><div class="fancy-modal-2">
								<table>
									<tr>
										<th style="vertical-align:bottom;">
											<figure>
												<div class="minical-mo">'.strtoupper(date("M", $event['start_unix'])).'</div>
												<div class="minical-da">'.date("j", $event['start_unix']).'</div>
											</figure>
										</th>
										<td style="vertical-align:bottom;"><h2>'.$event['summary'].'</h2></td>
									</tr>
									<tr>
										<th class="space">Calendar</th>
										<td class="space"></td>
									</tr>
									<tr>
										<th class="space">Date</th>
										<td class="space">'.date("l, F j, Y", $event['start_unix']).'</td>
									</tr>
									<tr>
										<th>Start Time</th>
										<td>'.date("g:ia", $event['start_unix']).'</td>
									</tr>
									<tr>
										<th>End Time</th>
										<td>'.date("g:ia", $event['end_unix']).'</td>
									</tr>
									<tr>
										<th>Location</th>
										<td>'.$event['location'].'</td>
									</tr>
									<tr>
										<th class="space">Attachment</th>
										<td class="space link"><a href="'.$event['link'].'">'.$event['link'].'</a></td>
									</tr>
									<tr>
										<th class="space">Description</th>
										<td class="space">'.$event['desc'].'</td>
									</tr>
								</table>
							</div></div>
						</div>
					</div>
				</div>
				<div class="minical-holder">
					<a onclick="showModal(\'cal'.$event['uid'].'\');">
						<figure>
							<div class="minical-mo">'.strtoupper(date("M", $event['start_unix'])).'</div>
							<div class="minical-da">'.date("j", $event['start_unix']).'</div>
						</figure>
						<dl>
							<dt>'.$event['summary'].'</dt>
							<dd> '.date("g:ia", $event['start_unix']).' - '.date("g:ia", $event['end_unix']).'</dd>
							<dd>'.$event['location'].'</dd>
						</dl>
					</a>
				</div>
			</li>
		';
	}
	$R_cal->free();
}
print'
        </ul>
    </section>
	<ul class="promos">
		<li id="promo1">
    		<a href="'.$protocol.$site.'/news/article/'.$home_news[0]['article'].'" style="background-image:url(\''.$protocol.'hoaumich.org/img/user/'.$home_news[0]['image'].'\');">
    			<div class="promo-text">
    				<div class="headline">'.$home_news[0]['headline'].'</div>
    				<div class="deck">'.$home_news[0]['strapline'].'</div>
    			</div>
    		</a>
		</li>
        <li id="promo2">
    		<a href="'.$protocol.$site.'/news/article/'.$home_news[1]['article'].'" style="background-image:url(\''.$protocol.'hoaumich.org/img/user/'.$home_news[1]['image'].'\');">
    			<div class="promo-text">
    				<div class="headline">'.$home_news[1]['headline'].'</div>
    				<div class="deck">'.$home_news[1]['strapline'].'</div>
    			</div>
    		</a>
		</li>
        <li id="promo3">
    		<a href="'.$protocol.$site.'/news/article/'.$home_news[2]['article'].'" style="background-image:url(\''.$protocol.'hoaumich.org/img/user/'.$home_news[2]['image'].'\');">
    			<div class="promo-text">
    				<div class="headline">'.$home_news[2]['headline'].'</div>
    				<div class="deck">'.$home_news[2]['strapline'].'</div>
    			</div>
    		</a>
		</li>
	</ul>
    <section class="content-box">
        <article>
        <div class="header-holder">
            <hgroup>
                <h1>What is HOA Connect?</h1>
            </hgroup>
        </div>
            <div class="news-post">
                <p>HOA Connect is primarily a volunteer organization that facilitates social opportunities for house officers and their families. While the HOA\'s mission is to support the membership in the workplace, supporting those special people in a house officer\'s life is also important.</p>
                <p>The Association has a long history of organizing socials open to the entire membership. These events provide an opportunity for house officers to mix and mingle outside of their respective programs.</p>
                <p>Each July, we kick-off the year in true maize and blue fashion, with a Stadium Tour and Tailgate. Both new and current house officers along with their immediate families look forward to attending this event. Those new to the area will find representatives from Side-By-Side, Newcomers, as well as the HOA Connect Playgroup organizers, ready to take your contact information to help get you <em>connected</em> while at Michigan.</p>
                <p>Additional events are planned through out the year so be sure to sign up to receive our emails.</p>
            </div>
        </article>
    </section>
</div>
';







/* FOOTER */ require('layout/footer1.php');



$db_main->close();
$db_auth->close();


?>
