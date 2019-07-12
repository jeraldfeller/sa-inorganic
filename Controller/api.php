<?php
require '../Model/Init.php';
require '../Model/Scraper.php';
$scraper = new Scraper();

switch ($_GET['action']){
    case 'import':
        $locale = $_GET['locale'];
        if (isset($_FILES['importFile']['tmp_name'])) {
            if (pathinfo($_FILES['importFile']['name'], PATHINFO_EXTENSION) == 'csv') {
                $file = $_FILES['importFile']['tmp_name'];
                $fileName = $_FILES['importFile']['name'];
                $flag = true;
                $fileHandle = fopen($_FILES['importFile']['tmp_name'], "r");
                while (($data = fgetcsv($fileHandle, 10000, ",")) !== FALSE) {
                    if ($flag) {
                        $flag = false;
                        continue;
                    }
                    $keyword = $data[0];
                    $scraper->insertKeyword($keyword, $locale);
                }

                fclose($fileHandle);
            }
            echo true;
        }else{
            echo false;
        }
    break;
    case 'getLocale':
        echo json_encode($scraper->getLocale());
        break;
    case 'addLocale':
        echo $scraper->addLocale($_POST['locale']);
        break;
}