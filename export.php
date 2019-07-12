<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
$scraper = new Scraper();



$action = $_GET['action'];
$date = date('Y-m-d_H-i-s');
if($action == 'products'){
    $export = $scraper->exportProducts();
    $csv = ROOT_DIR.'export-'.$date.'.csv';
    $data[] = implode('","', array(
        'Date Time',
        'Keyword',
        'Message',
        'Asin',
        'Brand',
        'Title',
        'Position',
        'Locale'
    ));
    foreach ($export as $row){
        $data[] = implode('","', array(
            $row['time'],
            stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['keyword']))))),
            stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['message']))))),
            $row['asin'],
            stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['brand']))))),
            stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['title']))))),
            $row['position'],
            $row['locale']
        ));
    }
}else if($action == 'inputs'){
    $export = $scraper->exportInputs();
    $csv = ROOT_DIR.'inputs-'.$date.'.csv';
    $data[] = implode('","', array(
        'Keyword'
    ));
    foreach ($export as $row) {
        $data[] = implode('","', array(
            stripslashes(str_replace(',', ' ', trim(preg_replace('/\s+/', ' ', html_entity_decode($row['keyword'])))))
        ));
    }
}


$file = fopen($csv,"a");
foreach ($data as $line){
    fputcsv($file, explode('","',$line));
}
fclose($file);



// Output CSV-specific headers

header('Content-Type: text/csv; charset=utf-8');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . basename($csv) . "\"");
readfile($csv);
