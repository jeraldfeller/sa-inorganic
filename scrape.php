<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'Model/Init.php';
require 'Model/Scraper.php';
require 'simple_html_dom.php';
$scraper = new Scraper();
$opt = getopt("a:r:");
$locale = trim($opt['a']);
//$rn = trim($opt['r']);

//$locale = 'it';

$dateNow = date('Y-m-d H', strtotime('+1 Hours'));
switch($locale){
    case 'it':
        if($dateNow >= date('Y-m-d 09') && $dateNow <= date('Y-m-d 12')){
            $rn = 1;
        }else if($dateNow >= date('Y-m-d 17') && $dateNow <= date('Y-m-d 19')){
            $rn = 2;
        }else if($dateNow >= date('Y-m-d 20')){
            $rn = 3;
        }else{
            $rn = 3;
        }
        break;
    case 'de':
        if($dateNow >= date('Y-m-d 10') && $dateNow <= date('Y-m-d 12')){
            $rn = 1;
        }else if($dateNow >= date('Y-m-d 18') && $dateNow <= date('Y-m-d 20')){
            $rn = 2;
        }else if($dateNow >= date('Y-m-d 21')){
            $rn = 3;
        }else{
            $rn = 3;
        }
        break;
    case 'com':
        if($dateNow >= date('Y-m-d 10') && $dateNow <= date('Y-m-d 12')){
            $rn = 1;
        }else if($dateNow >= date('Y-m-d 18') && $dateNow <= date('Y-m-d 20')){
            $rn = 2;
        }else if($dateNow >= date('Y-m-d 21')){
            $rn = 3;
        }else{
            $rn = 3;
        }
        break;
    case 'co.uk':
        if($dateNow >= date('Y-m-d 12') && $dateNow <= date('Y-m-d 14')){
            $rn = 1;
        }else if($dateNow >= date('Y-m-d 20') && $dateNow <= date('Y-m-d 22')){
            $rn = 2;
        }else if($dateNow >= date('Y-m-d 23')){
            $rn = 3;
        }else{
            $rn = 3;
        }
        break;
       case 'es':
        if($dateNow >= date('Y-m-d 09')){
            $rn = 1;
        }else if($dateNow >= date('Y-m-d 17')){
            $rn = 2;
        }else if($dateNow >= date('Y-m-d 20')){
            $rn = 3;
        }else{
            $rn = 3;
        }
        break;
}    

$keywords = $scraper->getKeywords($locale);
    foreach($keywords as $row){
        $lists = array();
        $keyword = $row['keyword'];
        $id = $row['id'];
        $pg = 1; 

        echo $keyword."\n";
        $s = 2;
        $keywordEncoded = urlencode($keyword);
        while ($s < 8){
sleep(5);
            $url = "https://www.amazon.$locale/s?field-keywords=$keywordEncoded&page=$pg";
            echo $url . "\n";
            if($pg == 4) break;
            $htmlData = $scraper->curlTo($url, $locale);

           // echo $htmlData['html'];
	$file = fopen("site.html","w");
            echo fwrite($file,$htmlData['html']);
            fclose($file);	   
            if($htmlData['html']){
		
                $html = str_get_html($htmlData['html']);
				
                if($html){

                    // Banner Ads
                    if($pg == 1){
 if($locale == 'de'){
                            $bannerContainer = $html->find('#pdagDesktopSparkleBrandingContainer', 0);
                        }else{
                            $bannerContainer = $html->find('.sky', 0);
                        }                        
//echo $bannerContainer->plaintext;
if($bannerContainer){
//echo "1\n";
                            $block = $bannerContainer->find('.block', 0);
                            if($block){
//echo "2\n";
                                $brand = $block->find('#hsaSponsoredByBrandName', 0)->plaintext;
                                $branding = $block->find('.desktopSparkle__branding', 0);
								echo 'BRAND: ' . $brand . "\n";
                                if($branding){
                                    $message = $branding->find('.block', 1)->plaintext;
                                    $listData = array(
                                        'position' => 1,
                                        'keyword' => $keyword,
                                        'brand' => trim($brand),
                                        'message' => trim($message),
                                        'asin' => '',
                                        'title' => '',
                                        'locale' => $locale,
                                        'dateExecuted' => $dateNow,
                                        'runNumber' => $rn
                                    );
                                }else{
                                    $listData = array(
                                        'position' => 1,
                                        'keyword' => $keyword,
                                        'brand' => '',
                                        'message' => '',
                                        'asin' => '',
                                        'title' => '',
                                        'locale' => $locale,
                                        'dateExecuted' => $dateNow,
                                        'runNumber' => $rn
                                    );
                                }

                            }
                        }else{
                            $listData = array(
                                'position' => 1,
                                'keyword' => $keyword,
                                'brand' => '',
                                'message' => '',
                                'asin' => '',
                                'title' => '',
                                'locale' => $locale,
                                'dateExecuted' => $dateNow,
                                'runNumber' => $rn
                            );
                        }

                        $scraper->addProduct($listData);
                    }


                    // end banner adds

                    // regular add

//                    $resultsCol = $html->find('#resultsCol', 0); 
$resultsCol = $html->find('.s-result-list', 0); 
                   if($resultsCol){
//var_dump('rc');   
                     $itemList = $resultsCol->find('.s-result-item');
                        if(count($itemList) > 0){
//var_dump('il');
                            if($s <= 8){
                                for($i = 0; $i < count($itemList); $i++){
//var_dump($i);
                                    if($s <= 8){
                                        $asin = $itemList[$i]->getAttribute('data-asin');
//$title = ($itemList[$i]->find('.s-access-title', 0) ? $itemList[$i]->find('.s-access-title', 0)->plaintext : '');
$h5 = $itemList[$i]->find('h5', 0);
if($h5){
                                            $productUrl = $itemList[$i]->find('h5', 0)->find('a', 0);
                                            $title = ($itemList[$i]->find('h5', 0) ? $itemList[$i]->find('h5', 1)->plaintext : '');
                                        }else{
                                            $productUrl = $itemList[$i]->find('h2', 0)->find('a', 0);
                                            $title = ($itemList[$i]->find('h2', 0) ? $itemList[$i]->find('h2', 0)->plaintext : '');
                                       } 
$productUrl = $productUrl->getAttribute('href');
                                        $finalProductUrl = "https://www.amazon.$locale/dp/$asin";

                                       $sponsored = $itemList[$i]->plaintext;
//echo $sponsored;
if($locale == 'it'){
                                            $sponsored = strpos($sponsored, 'Sponsorizzato');
                                        }else if($locale == 'co.uk'){
                                            $sponsored = strpos($sponsored, 'Sponsored');
                                        }else if($locale == 'com'){
                                            $sponsored = strpos($sponsored, 'Sponsored');
                                        }else if($locale == 'fr'){
                                            $sponsored = strpos($sponsored, 'Sponsorisé');
                                        }else if($locale == 'de'){
                                            $sponsored = strpos($sponsored, 'Gesponsert');
                                        }else if($locale == 'es'){
                                           $sponsored = strpos($sponsored, 'Patrocinado');

}
                                        
if($sponsored == false){
	$sponsored = $itemList[$i]->find('h5', 0);
}
if($sponsored !== false){

  //                                          echo $asin . "\n";
                                            
                                            if($scraper->isAsinExist($asin,$keyword, $dateNow) == false){
//						echo "ADD\n";
                                            echo $finalProductUrl . "\n";
// check if brand exist for asin
                                                $brand = $scraper->getBrandByAsin(trim($asin));
var_dump($brand);
                                                if($brand == false){
   sleep(6);
$productPage = $scraper->curlTo($finalProductUrl, $locale);
                                                if($productPage['html']) {
                                                    $productHtml = str_get_html($productPage['html']);
$brand = $productHtml->find('#bylineInfo', 0);
                                                    if($brand){
                                                        $hasA = $brand->find('a', 0);
                                                        if($hasA){
                                                            $brand = trim($hasA->plaintext);
                                                        }else{
                                                            $brand = trim($brand->innertext());
                                                        }
                                                    }else{
                                                        $brand = '';
                                                    }
if($brand == ''){
echo 'alpha';
                                                        $brandDiv = $productHtml->find('#bylineInfo_feature_div', 0);
                                                        if($brandDiv){
echo 'beta';
                                                            $brandA = $brandDiv->find('a', 0);
                                                            if($brandA){
echo 'charlie';
                                                                $brandHref = $brandA->getAttribute('href');
                                                                $brandExplode = explode('/', $brandHref);
                                                                $brand = $brandExplode[1];
                                                                echo 'BRRAAAANNNDD' . $brand;
                                                            }else{
echo 'delta';
                                                                $brand = '';
                                                            }
                                                        }else{
echo 'echo';
                                                            $brand = '';
                                                        }


                                                    }                                                
}else{
                                                    $brand = '';
                                                }
}
     /*$brand = $itemList[$i]->find('.a-color-secondary', 0)->plaintext;
                                                 if(trim($brand) == 'by'){
                                                    $brand = $itemList[$i]->find('.a-color-secondary', 1)->plaintext;
                                                }
                                                   if(trim($brand) == 'de'){
                                                    $brand = $itemList[$i]->find('.a-color-secondary', 1)->plaintext;
                                                }*/

echo $pg . ' ' .  $s . ' ' .$asin .' - '. $title . "\n";
						 $listData = array(
                                                    'position' => $s,
                                                    'keyword' => $keyword,
                                                    'brand' => trim($brand),
                                                    'message' => '',
                                                    'asin' => trim($asin),
                                                    'title' => trim(replaceSponsorText($title, $locale)),
                                                    'locale' => $locale,
                                                    'dateExecuted' => $dateNow,
                                                    'runNumber' => $rn
                                                );
                                                $scraper->addProduct($listData);
                                                $s++;
  //                                                   echo 'COUNT: ' . $s . "\n";
                                            }
                                        }
                                    }
				//sleep(1);
                                }
                            }
                        }
                    }
                }
            }
            $pg++;
			sleep(mt_rand(8, 15));
        }

        /*
        if(count($lists) > 0){
            $scraper->addProducts($lists, $dateNow);
        }
        */
        sleep(mt_rand(8, 15));
    }

    function replaceSponsorText($title, $locale){
        switch($locale){
            case 'it':
                return str_replace('[Sponsorizzato]', '', $title);
                break;
            case 'co.uk':
                return str_replace('[Sponsored]', '', $title);
                break;
			case 'fr':
                return str_replace('[Sponsorisé]', '', $title);
                break;
            case 'de':
                return str_replace('[Gesponsert]', '', $title);
                break;
 	   case 'com':
                return str_replace('[Sponsored]', '', $title);
                break;
case 'es':
  return str_replace('[Patrocinado]', '', $title);

break;
        }
    }

    function translateMonth($month, $locale){
        switch ($locale){
            case 'it':
                switch ($month){
                    case 'gennaio':
                        return 'january';
                        break;
                    case 'febbraio':
                        return 'february';
                        break;
                    case 'marzo':
                        return 'march';
                        break;
                    case 'aprile':
                        return 'april';
                        break;
                    case 'maggio':
                        return 'may';
                        break;
                    case 'giugno':
                        return 'june';
                        break;
                    case 'luglio':
                        return 'july';
                        break;
                    case 'agosto':
                        return 'august';
                        break;
                    case 'settembre':
                        return 'september';
                        break;
                    case 'ottobre':
                        return 'october';
                        break;
                    case 'novembre':
                        return 'november';
                        break;
                    case 'dicembre':
                        return 'december';
                        break;
                }
                break;
        }
    }
