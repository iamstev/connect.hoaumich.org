<?php

class ElasticEmailException extends Exception{}

class stev_eemail{

    private $apikey;
    private $cert;
    public $params = array();


    function __construct($apikey = NULL, $cert = NULL){
		if($apikey === NULL){
            throw new ElasticEmailException('No API key was provided.');
        }else{
            $this->apikey = $apikey;
        }
        if($cert === NULL){
            throw new ElasticEmailException('The path to the elastic email certificate was not provided.');
        }else{
            $this->cert = $cert;
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $cert);
        curl_setopt($ch, CURLOPT_URL, 'https://api.elasticemail.com/v2/account/load?apikey='.$apikey);
        $result = curl_exec($ch);
        $error = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        if($result === false){
            throw new ElasticEmailException($error);
        }else{
            $test = json_decode($result);
        }
        if($test->success !== true){
            throw new ElasticEmailException('Unable to log in to your Elastic Email account.');
        }

	}


    function call($call = NULL, $method = 'get'){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $this->cert);

        $data = 'apikey='.urlencode($this->apikey);
        foreach($this->params as $k => $v){
            $data .= '&'.$k.'='.urlencode($v);
        }

        switch($method){
            case 'get':
                curl_setopt($ch, CURLOPT_URL, 'https://api.elasticemail.com/v2/'.$call.'?'.$data);
            break;

            case 'post':
                curl_setopt($ch, CURLOPT_URL, 'https://api.elasticemail.com/v2/'.$call);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $header = "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: ".strlen($data)."\r\n\r\n";
                curl_setopt($ch, CURLOPT_HEADER, $header);
            break;

            case 'upload':
                $this->params['apikey'] = $this->apikey;
                $this->params['file_contents'] = curl_file_create($this->params['file_contents']);
                curl_setopt($ch, CURLOPT_URL, 'https://api.elasticemail.com/v2/'.$call);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
            break;

            default:
                throw new ElasticEmailException('Invalid method.');
            break;
        }

        $result = curl_exec($ch);
        // if($method === 'post'){ $result = gzdecode($result); }
        $error = 'Curl error: ' . curl_error($ch);

        curl_close($ch);

        if($result === false){
            throw new ElasticEmailException($error);
        }else{
            return json_decode($result);
        }

    }

    function mailer($mailer_call){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.elasticemail.com/mailer/'.$mailer_call);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data  = 'username='.urlencode($this->apikey);
        $data .= '&api_key='.urlencode($this->apikey);
        foreach($this->params as $k => $v){
            $data .= '&'.$k.'='.urlencode($v);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $header = "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($data)."\r\n\r\n";
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $this->cert);

        $result = curl_exec($ch);
        $error = 'Curl error: ' . curl_error($ch);

        curl_close($ch);

        if($result === false){
            throw new ElasticEmailException($error);
        }else{
            return $result;
        }
    }

    function attach($filepath, $filename) {

        $data  = 'username='.urlencode($this->apikey);
        $data .= '&api_key='.urlencode($this->apikey);
        $data .= '&file='.urlencode($filename);

        $file = file_get_contents($filepath);
        $result = '';

        $fp = fsockopen('ssl://api.elasticemail.com', 443, $errno, $errstr, 30);

        if ($fp){
            fputs($fp, "PUT /attachments/upload?".$data." HTTP/1.1\r\n");
            fputs($fp, "Host: api.elasticemail.com\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ". strlen($file) ."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $file);
            while(!feof($fp)) {
                $result .= fgets($fp, 128);
            }
        } else {
            return array(
                'status'=>false,
                'error'=>$errstr.'('.$errno.')',
                'result'=>$result);
        }
        fclose($fp);
        $result = explode("\r\n\r\n", $result, 2);
        return array(
            'status' => true,
            'ID' => isset($result[1]) ? $result[1] : ''
        );
    }





}


?>
