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
require_once('classes/ElasticEmail.class.php');
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
            $h1    = 'Contact Us';
            $html  = '
                <label>Name</label>
                <input type="text" id="contact-name" />
                <label>Email</label>
                <input type="text" id="contact-email" />
                <label>Who do you want to contact?</label>
                <select id="contact-dest">
                    <option value="connect" >HOA Connect</option>
                    <option value="playgroup" >Connect Playgroup - Tots of Docs</option>
                    <option value="hoa" >House Officers Association</option>
                </select>
                <label>Message</label>
                <textarea id="contact_message"></textarea>
                <button id="contact-submit">Send Message</button>
            ';
            break;
        }

        case '2': {

            $ee = new stev_eemail($apikey['elastic']['key'], $apikey['elastic']['cert']);

            if(filter_var($data1['email'], FILTER_VALIDATE_EMAIL)){
                $email = $data1['email'];
            }else{
                throw new BannerException('<li>You did not enter a valid email address.</li>');
            }

            $name = preg_replace('/[^0-9a-zA-Z\s]/', '', $data1['name']);

            switch($data1['dest']){
                case 'connect' : {
                    $dest_name = 'HOA Connect';
                    $to = $emailaddress['connect'];
                    break;
                }
                case 'hoa' : {
                    $dest_name = 'House Officers Association';
                    $to = $emailaddress['hoa'];
                    break;
                }
                case 'playgroup' : {
                    $dest_name = 'Connect Playgroup - Tots of Docs';
                    $to = $emailaddress['playgroup'];
                    break;
                }
                default : { throw new Exception('There was an error with the contact form. (ref: invalid destination)');}
            }

            $message_html .= '<p>Someone used the contact form on the HOA Connect website.</p>';
            $message_html .= '
                <table>
                    <tr>
                        <td style="text-align:right;">Name: </td>
                        <td style="font-weight:bold;">'.$name.'</td>
                    </tr>
                    <tr>
                        <td style="text-align:right;">Email: </td>
                        <td style="font-weight:bold;">'.$email.'</td>
                    </tr>
                </table>';
            $message_html .= $data1['msg'];

            $ee->params = array(
                'to'        => $to,
                'from'      => 'bot@hoaumich.org',
                'from_name' => 'HOA.bot',
                'reply_to'  => $email,
                'subject'   => "HOA Connect Contact Form (Ticket #".time()." )",
                'body_html' => $message_html
            );

            $r = $ee->mailer('send');

            $error  = '0';
            $h1     = 'Contact Us';
            $html   = '<p>Your message has been sent to, <strong>'.$dest_name.'</strong>. Thank you.</p>';

            break;

        }

        default:
            throw new Exception('There was error with the contact form. (ref: invalid step)');
    }
}catch (ElasticEmailException $e){
    $error  = '1';
    $h1     = 'Contact Us - Error';
    $html   = '<p>There was error with the contact form. (ref: elastic)</p>';
}catch(mysqli_sql_exception $e){
    $error  = '1';
    $h1     = 'Contact Us - Error';
    $html   = '<p>There was error with the contact form. (ref: data)</p>';
}catch(BannerException $e){
    $error  = '3';
    $html   = $e->getMessage();
}catch(Exception $e){
    $error  = '1';
    $h1     = 'Contact Us - Error';
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
