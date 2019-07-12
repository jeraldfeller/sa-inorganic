<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'Model/Init.php';
require 'Model/Scraper.php';
require 'simple_html_dom.php';
$scraper = new Scraper();
$opt = getopt("a:r:");
$locale = trim($opt['a']);
$rn = trim($opt['r']);

//$locale = 'it';

$dateNow = date('Y-m-d H', strtotime('+1 Hours'));
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

                    $resultsCol = $html->find('#resultsCol', 0); 
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
$title = ($itemList[$i]->find('.s-access-title', 0) ? $itemList[$i]->find('.s-access-title', 0)->plaintext : '');
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
                                        }
                                        
if($sponsored == false){
  $sponsored = $itemList[$i]->find('h5', 0);
}
if($sponsored !== false){

  //                                          echo $asin . "\n";
                                            
                                            if($scraper->isAsinExist($asin,$keyword, $dateNow) == false){
//						echo "ADD\n";
                                                $brand = $itemList[$i]->find('.a-color-secondary', 1)->plaintext;
                                                 if(trim($brand) == 'by'){
                                                    $brand = $itemList[$i]->find('.a-color-secondary', 2)->plaintext;
                                                }
                                                   if(trim($brand) == 'de'){
                                                    $brand = $itemList[$i]->find('.a-color-secondary', 2)->plaintext;
                                                }

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
