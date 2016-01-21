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

//


/* PAGE VARIABLES */
$currentpage = 'news/';
//

if(isset($_GET['article'])){
	$article = $_GET['article'];
	$R_news = $db_main->query("SELECT news.*, news_covers.image FROM news LEFT JOIN news_covers ON news.article = news_covers.article WHERE news.article = '$article' LIMIT 1");
	if($R_news != FALSE){
		$news = $R_news->fetch_assoc();
		$R_news->free();
		$uname = $news['author'];
		$R_author = $db_main->query("SELECT * FROM users WHERE username='$uname' LIMIT 1");
		$author = $R_author->fetch_assoc();
		$R_author->free();
	}
}else{
	header("Location: $protocol.$site/news/archive/",TRUE,303);
	$db_main->close();
}


/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ //$title='';
/* HEADER */ require('layout/header1.php');

print'
	<div class="content">
		<section class="content-box" id="article-content-box">
			<article>
';

if($news['image'] !== null){
	print'
		<div class="header-holder covered" style="background-image: url(\'http://hoaumich.org/img/user/'.$news['image'].'\')">
	';
}else{
	print'
		<div class="header-holder">
	';
}
print'
					<hgroup>
						<h1>'.$news['headline'].'</h1>
						<h2>'.$news['strapline'].'</h2>
						<div class="byline">';if($news['byline'] == '1'){print $author['firstname'].' '.$author['lastname'].' | ';}print date("j M Y", $news['pubdate']).' at '.date("g:ia",$news['pubdate']).'</div>
					</hgroup>
				</div>
				<div class="news-post">'.$news['post'].'</div>
			</article>
		</section>
	</div>
';

/* FOOTER */ require('layout/footer1.php');
$db_main->close();
?>
