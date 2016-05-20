<?php

// Include bootstrap loader instead of files if you want
require_once '../src/Zoho/CRM/Common/HttpClientInterface.php';
require_once '../src/Zoho/CRM/Common/FactoryInterface.php';
require_once '../src/Zoho/CRM/Request/HttpClient.php';
require_once '../src/Zoho/CRM/Request/Factory.php';
require_once '../src/Zoho/CRM/Request/Response.php';
require_once '../src/Zoho/CRM/ZohoClient.php';
require_once '../src/Zoho/CRM/Wrapper/Element.php';
require_once '../src/Zoho/CRM/Entities/Lead.php';

use Zoho\CRM\ZohoClient;

define('DIR_OUTPUT', 'C:xampp/htdocs/zohocrm/tests/_output/');

$token = '';
if(isset($_GET['token'])){
    $token = $_GET['token'];
} else {
    echo 'Take me a token as ?token="Your token"';
    die;
}
$ZohoClient = new ZohoClient($token); // Make the connection to zoho api
$ZohoClient->setModule('Leads'); // Selecting the module
$leads = $ZohoClient->getRecords();
$file = DIR_OUTPUT.'file.csv';

if(file_exists($file)) {
    mssafe_csv($file,$leads->getRecords());
} else {
    echo 'File doesn`t exist!';
}

function mssafe_csv($filepath, $data, $header = array()) {
    if ( $fp = fopen($filepath, 'w') ) {
        $show_header = true;
        if ( empty($header) ) {
            $show_header = false;
            reset($data);
            $line = current($data);
            if ( !empty($line) ) {
                reset($line);
                $first = current($line);
                if ( substr($first, 0, 2) == 'ID' && !preg_match('/["\\s,]/', $first) ) {
                    array_shift($data);
                    array_shift($line);
                    if ( empty($line) ) {
                        fwrite($fp, "\"{$first}\"\r\n");
                    } else {
                        fwrite($fp, "\"{$first}\",");
                        fputcsv($fp, $line);
                        fseek($fp, -1, SEEK_CUR);
                        fwrite($fp, "\r\n");
                    }
                }
            }
        } else {
            reset($header);
            $first = current($header);
            if ( substr($first, 0, 2) == 'ID' && !preg_match('/["\\s,]/', $first) ) {
                array_shift($header);
                if ( empty($header) ) {
                    $show_header = false;
                    fwrite($fp, "\"{$first}\"\r\n");
                } else {
                    fwrite($fp, "\"{$first}\",");
                }
            }
        }
        if ( $show_header ) {
            fputcsv($fp, $header);
            fseek($fp, -1, SEEK_CUR);
            fwrite($fp, "\r\n");
        }
        $line_number = 0;
        foreach ( $data as $line ) {
            if(!$line_number){
                $headers = array();
                foreach($line as $key=>$value){
                    $headers[] = $key;
                }
                fputcsv($fp, $headers);
            }
            fputcsv($fp, $line);
            fseek($fp, -1, SEEK_CUR);
            fwrite($fp, "\r\n");
            $line_number++;
        }
        fclose($fp);
    } else {
        return false;
    }
    return true;
}
print "\n";
