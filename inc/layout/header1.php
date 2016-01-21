<?php

print'
<!DOCTYPE html>
<html>
	<head>
		<title>';if(isset($title)){print $title;}else{print'HOA Connect';}print'</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta name="viewport" content="width=device-width">
		<meta name="viewport" content="initial-scale=1.0">
		<link href="http://fonts.googleapis.com/css?family=Questrial" rel="stylesheet" type="text/css">
		<link href="http://fonts.googleapis.com/css?family=Sanchez:400italic,400" rel="stylesheet" type="text/css">
		<link href="http://fonts.googleapis.com/css?family=Source+Code+Pro" rel="stylesheet" type="text/css">
		<link rel="stylesheet" type="text/css" media="all" href="/css/connect.css" />
		<script type="text/javascript" src="/js/scripts.js"></script>
		<script type="text/javascript">
			Modernizr.load({
				test: Modernizr.mq(\'only all\'),
				nope: \'/js/respond.min.js\'
			});
		</script>
		<!--[if (gte IE 6)&(lte IE 8)]>
			<script type="text/javascript" src="js/selectivizr-min.js"></script>
		<![endif]-->
		'.$head.'
	</head>
	<body>
	<div id="sitebuttons">
		<ul>
			<li class="sitebutton_main">
				<a href="http://hoaumich.org/">
					<img src="/img/seal.png" >
				</a>
			</li>
			<li class="sitebutton_housing">
				<a href="http://housing.hoaumich.org/">
					<img src="/img/housing-logo@2x.png" >
				</a>
			</li>
		</ul>
	</div>
	<header>
		<h1><a href="'.$protocol.$site.'/">HOA Connect</a></h1>
		<div class="above-nav">
			<p class="mission">HOA Connect provides social opportunities and support to house officers, their families, and significant others.</p>
		</div>
		<nav class="main-nav">
			<a href="/news/archive/">News</a>
			<a href="/calendar/">Calendar</a>
			<a onclick="contact(1)">Contact Us</a>
			<a onclick="newsletter(1)">Newsletter</a>
			<a href="https://www.facebook.com/HOAUmich/" target="_blank">Facebook</a>
		</nav>
	</header>
';
?>
