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
$currentpage = 'calendar/';
$m = date("n");
$y = date("Y");

ob_end_flush();
/* <HEAD> */ $head='';
/* PAGE TITLE */ $title='HOA Connect - Calendar';
/* HEADER */ require('layout/header1.php');
?>
	<div id="calEvent" class="modal-holder caldet">
		<div class="modal-wrap">
			<div class="modal">
				<input class="close" type="image" src="/img/cancel.png" value="Close" onclick="hideModal('calEvent');">
				<h1>Event Details</h1>
				<div class="fancy-modal-1">
					<div id="calEventContent" class="fancy-modal-2">
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		window.nm = new Number;
		window.ny = new Number;
		window.nm = <?php echo $m; ?>;
		window.ny = <?php echo $y; ?>;
		window.pm = new Number;
		window.py = new Number;
		window.pm = <?php echo $m; ?>;
		window.py = <?php echo $y; ?>;
	</script>
	<div class="content">
		<div class="calhold">
			<a class="nextmonth" onclick="AJAXprevMonth();">
				<span><i class="fa fa-chevron-circle-up"></i><br>PREVIOUS</span>
			</a>
		</div>
		<div class="calhold" id="calhold">
		<?php require('getcal.php'); ?>
		</div>
		<div class="calhold">
			<a class="nextmonth" onclick="AJAXnextMonth();">
				<span>NEXT<br><i class="fa fa-chevron-circle-down"></i></span>
			</a>
		</div>
	</div>

<?php
/* FOOTER */ require('layout/footer1.php');
$db_main->close();
?>
