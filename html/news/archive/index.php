<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* WHICH DATABASES DO WE NEED */
$db2use = array(
    'db_auth'		=> TRUE,
    'db_main'		=> TRUE,
    'db_house'      => FALSE,
    'db_membership' => FALSE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */



/* PAGE VARIABLES */
$currentpage = 'news/archive/';



$m = date("n");
$y = date("Y");
$now = time();
$R_news = $db_main->query("SELECT * FROM news WHERE published=1 AND `pubdate` < $now AND (push2connect=1 OR sectionID=2) ORDER BY `pubdate` DESC");
$news = array();
$R_news->data_seek(0);
while($ARRY_news = $R_news->fetch_assoc()){
	$month = date("Y",$ARRY_news['pubdate']).'_'.date("m",$ARRY_news['pubdate']);
	$article = $ARRY_news['article'];
	$news[$month][$article] = array(
		'article'	=> $article,
		'date'		=> $ARRY_news['pubdate'],
		'guest'		=> $ARRY_news['guest'],
		'sectionID'	=> $ARRY_news['sectionID'],
		'section'	=> $ARRY_news['sectionShort'],
		'headline'	=> $ARRY_news['headline'],
		'strapline'	=> $ARRY_news['strapline'],
		'post'		=> $ARRY_news['post']
	);
	unset($month, $article);
}
$R_news->free();

/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ //$title='';
/* HEADER */ require('layout/header1.php');

print'
	<div class="content" onclick="nav(\'0\')">
		<section class="content-box newsarchivehold">
';
foreach($news as $mo => $articles){
	$ARRY_mo = explode('_', $mo);
	print'
		<section class="newsmonth">
			<h2 class="newsmonth_title">'.date("F", mktime(0, 0, 0, $ARRY_mo[1], 1)).' '.$ARRY_mo[0].'</h2>
			<ul id="'.$mo.'">
	';
	$i3=0;
	$i4=0;
	$i5=0;
	foreach($articles as $artcl){
		$i3++;
		$i4++;
		$i5++;
		print'
				<li class="archive_link';if($i3 == (3+1)){print' by3'; $i3=1;}if($i4 == (4+1)){print' by4'; $i4=1;}if($i5 == (5+1)){print' by5'; $i5=1;}print'">
					<a href="http://'.$site.'/news/article/'.$artcl['article'].'/">
						<h1>'.$artcl['headline'].'</h1>
						<h2>'.$artcl['strapline'].'</h2>
						<h3>'.date("Y-m-d",$artcl['date']).' at '.date("g:ia",$artcl['date']).'</h3>
					</a>
				</li>
		';
	}
	print'
			</ul>
		</section>
	';
	unset($moTitle, $artcl, $articles, $i3, $i4, $i5);
}

print'
		</section>
	</div>
';

/* FOOTER */ require('layout/footer1.php');
$db_main->close();
?>
