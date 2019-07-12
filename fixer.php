<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
require 'simple_html_dom.php';
$scraper = new Scraper();

$data = $scraper->getNoBrandData();
foreach ($data as $row){
    $id = $row['id'];
    $locale = strtolower($row['locale']);
    $asin = $row['asin'];
    $url = "https://www.amazon.$locale/dp/".$asin;
    echo $url . "\n";
    $htmlData = $scraper->curlTo($url, $locale);
    if($htmlData['html']) {
        $productHtml = str_get_html($htmlData['html']);
        if ($productHtml) {
            $brand = $productHtml->find('#bylineInfo', 0);
            if($brand){
                $brand = trim($brand->plaintext);
                if($brand != ''){
                    // save brand
                    $scraper->setBrand($id, $brand);
                }else{
                    $scraper->setBrand($id, 'NA');
                }
            }else{
                $brandDiv = $productHtml->find('#bylineInfo_feature_div', 0);
                if($brandDiv){
                    $brandA = $brandDiv->find('#a', 0);
                    if($brandA){
                        $brandHref = $brandA->getAttribute('href');
                        $brandExplode = explode('/', $brandHref);
                        $brand = trim($brandExplode[1]);
                        // save brand
                        if($brand != ''){
                            $scraper->setBrand($id, $brand);
                        }else{
                            $scraper->setBrand($id, 'NA');
                        }
                    }else{
                        // save N/A
                        $scraper->setBrand($id, 'NA');
                    }
                }else{
                    // save N/A
                    $scraper->setBrand($id, 'NA');
                }
            }
            echo $id . ' - ' . $brand . "\n";
        }
    }
}
