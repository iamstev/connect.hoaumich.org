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


/* LOAD FUNC-CLASS-LIB */
require_once('libraries/drill/drill.php');
$drill = new \Gajus\Drill\Client($apikey['mandrill']);
class BannerException extends Exception{}




/* PAGE VARIABLES */
$h1 = null;
$html = null;
$step = (isset($_POST['s'])) ? $_POST['s'] : '0';
$data1 = (isset($_POST['d1'])) ? $_POST['d1'] : null;




try{

    switch($step){

        case '1': {
            $error  = '0';
            $h1    = 'Newsletter';
            $html  = '

            ';
            break;
        }

        case '2': {



        }

        default:
            throw new Exception('There was error with the newsletter form. (ref: invalid step)');
    }
} catch (\Gajus\Drill\Exception\RuntimeException\ValidationErrorException $e) {
    // @see https://mandrillapp.com/api/docs/messages.html
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\UserErrorException $e) {
    // @see https://mandrillapp.com/api/docs/messages.html
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\UnknownSubaccountException $e) {
    // @see https://mandrillapp.com/api/docs/messages.html
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\PaymentRequiredException $e) {
    // @see https://mandrillapp.com/api/docs/messages.html
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\GeneralErrorException $e) {
    // @see https://mandrillapp.com/api/docs/messages.html
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\ValidationErrorException $e) {
    // @see https://mandrillapp.com/api/docs/messages.html
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\RuntimeException $e) {
    // All possible API errors.
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\InvalidArgumentException $e) {
    // Invalid SDK use errors.
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
} catch (\Gajus\Drill\Exception\DrillException $e) {
    // Everything.
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: mandrill)</p>';
}catch(mysqli_sql_exception $e){
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>There was error with the newsletter form. (ref: data)</p>';
}catch(BannerException $e){
    $error  = '3';
    $html   = $e->getMessage();
}catch(Exception $e){
    $error  = '1';
    $h1     = 'Newsletter - Error';
    $html   = '<p>'.$e->getMessage().'</p>';
}


$json = array(
    'error' => $error,
    'h1'    => $h1,
    'html'  => $html
);

$db_main->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();
?>
