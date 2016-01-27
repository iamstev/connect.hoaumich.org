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
require_once('classes/mailchimp.class.php');
class BannerException extends Exception{}





/* PAGE VARIABLES */
$h1 = null;
$html = null;
$step = (isset($_POST['s'])) ? $_POST['s'] : '0';
$data1 = (isset($_POST['d1'])) ? $_POST['d1'] : null;




try{

    $chimp = new \DrewM\MailChimp\MailChimp($apikey['mailchimp']['connect']);

    switch($step){

        case '1': {
            $error  = '0';
            $h1    = 'Newsletter';
            $html  = '
                <p>Subscribe to our newsletter to stay informed about Connect social events, and Playgroup dates and times.</p>
                <label>Name</label>
                <input type="text" id="newsletter-name">
                <label>Email</label>
                <input type="text" id="newsletter-email">
                <button id="newsletter-submit">Subscribe</button>
            ';
            break;
        }

        case '2': {

            if(filter_var($data1['email'], FILTER_VALIDATE_EMAIL)){
                $email = $data1['email'];
            }else{
                throw new BannerException('<li>You did not enter a valid email address.</li>');
            }

            $name = preg_replace('/[^0-9a-zA-Z\s]/', '', $data1['name']);

            $parts = explode(" ", $name);
            $lastname = array_pop($parts);
            $firstname = implode(" ", $parts);

            $args = array(
                'email_address'	=> $email,
                'status'		=> 'subscribed',
                'merge_fields'	=> array(
                    'FNAME'		=> $firstname,
                    'LNAME'		=> $lastname
                )
            );
            $r = $chimp->post('lists/'.$apikey['mailchimp']['connect_list'].'/members', $args);

            switch ($r['status']){
                case 'subscribed' : {
                    $error  = '0';
                    $h1     = 'Newsletter';
                    $html   = '
                        <p>You have been successfully subscribed to the HOA Connect newsletter.</p>
                        <table>
                            <tr>
                                <th>First Name</th>
                                <td>'.$firstname.'</td>
                            </tr>
                            <tr>
                                <th>Last Name</th>
                                <td>'.$lastname.'</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>'.$email.'</td>
                            </tr>
                        </table>
                    ';
                    break;
                }
                case 400 : {
                    if($r['title'] = 'Member Exists'){
                        $error  = '0';
                        $h1     = 'Newsletter';
                        $html   = '<p>Looks like you might already be subscribed. To be sure, <a href="http://eepurl.com/ysPFz">adust your email preferences here</a>.</p>';
                    }else{
                        $error  = '3';
                        $html  .= '<li>'.$r['status'].'</li>';
                        $html  .= '<li>'.$r['title'].'</li>';
                    }
                    break;
                }
                default : {
                    throw new Exception('There was error with the newsletter form. (ref: mailchimp - '.$r['status'].' - '.$r['title'].')');
                    break;
                }
            }

            break;
        }

        default:
            throw new Exception('There was error with the newsletter form. (ref: invalid step)');
    }

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
