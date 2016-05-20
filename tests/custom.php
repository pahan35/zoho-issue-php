<?php

define('DIR_OUTPUT', 'C:xampp/htdocs/zohocrm/tests/_output/');

if(isset($_GET['token'])){
    $token = $_GET['token'];
} else {
    echo 'Take me a token as ?token="Your token"';
    die;
}
$url = "https://crm.zoho.com/crm/private/xml/Leads/getRecords";
$param= "authtoken=".$token."&scope=crmapi";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result = curl_exec($ch);
curl_close($ch);
$response_xml = simplexml_load_string($result);
$file = DIR_OUTPUT . 'file_custom.csv';
if(file_exists($file)) {
    $array_to_processing = createCsv($response_xml)[0][0];
    my_csv($file,$array_to_processing);
} else {
    echo 'File doesn`t exist!';
}

function createCsv($xml) {
    $result_array = array();
    $current_index = 0;

    foreach ($xml->children() as $item) {
        $hasChild = (count($item->children()) > 0) ? true : false;
        if( ! $hasChild) {
            $put_arr = array((string) $item->attributes()['val'], xml2array($item));
            $result_array[] = $put_arr;
        } else {
            $result_array[$current_index] = createCsv($item);
            $current_index++;
        }
    }

    return $result_array;
}

function my_csv($filepath, $data){
    $handler = fopen($filepath, 'w');
    $line_number = 0;
    foreach ( $data as $line ) {
        if(!$line_number){
            $headers = array();
            foreach($line as $value){
                $headers[] = $value[0];
            }
            fputcsv($handler, $headers);
        }

        $values = array();
        foreach($line as $value){
            if(isset($value[1]['val'])){
                $values[] = $value[1]['val'];
            } else {
                $values[] = (string) $value[1];
            }
        }
        fputcsv($handler, $values);
        fseek($handler, -1, SEEK_CUR);
        fwrite($handler, "\r\n");
        $line_number++;
    }
    fclose($handler);
}

function xml2array ( $xmlObject, $out = array () ) {
    foreach ( (array) $xmlObject as $index => $node ) {
        $out = (is_object($node)) ? xml2array($node) : $node;
    }
    return $out;
}
?>