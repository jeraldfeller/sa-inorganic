<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
require 'simple_html_dom.php';
$scraper = new Scraper();
$locale = 'it';
$keywords = $scraper->getKeywords();
$dateNow = date('Y-m-d');


foreach($keywords as $row){
    $lists = array();
    $keyword = $row['keyword'];
    $id = $row['id'];
    $pg = 2;

    echo $keyword."\n";
    $s = 2;
	$keywordEncoded = urlencode($keyword);
    while ($s < 7){
        $url = "https://www.amazon.$locale/s?field-keywords=$keywordEncoded&page=$pg";
        echo $url . "\n";
        if($pg == 7) break;  
        $htmlData = $scraper->curlTo($url);
        if($htmlData['html']){
            $html = str_get_html($htmlData['html']);
            if($html){
				echo $html;
                // Banner Ads
                if($pg == 1){
                    $bannerContainer = $html->find('.sky', 0);
                    if($bannerContainer){
                        $block = $bannerContainer->find('.block', 0);
                        if($block){
                            $brand = $block->find('#hsaSponsoredByBrandName', 0)->plaintext;
                            $branding = $block->find('.desktopSparkle__branding', 0);
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
                                    'dateExecuted' => $dateNow
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
                                    'dateExecuted' => $dateNow
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
                            'dateExecuted' => $dateNow
                        );
                    }

                    $scraper->addProduct($listData);
                }


                // end banner adds

                // regular add

                $resultsCol = $html->find('#resultsCol', 0);
                if($resultsCol){
                    $itemList = $resultsCol->find('.s-result-item');
                    if(count($itemList) > 0){
                        if($s <= 7){
                            for($i = 0; $i < count($itemList); $i++){
                                if($s <= 7){
                                    $asin = $itemList[$i]->getAttribute('data-asin');
                                    $sponsored = $itemList[$i]->find('h5', 0);
                                    $title = ($itemList[$i]->find('.s-access-title', 0) ? $itemList[$i]->find('.s-access-title', 0)->plaintext : '');
                                    if($sponsored){
										echo $asin . "\n";
                                        if($scraper->isAsinExist($asin, $dateNow) == false){
                                            $brand = $itemList[$i]->find('.a-color-secondary', 1)->plaintext;
                                            $listData = array(
                                                'position' => $s,
                                                'keyword' => $keyword,
                                                'brand' => trim($brand),
                                                'message' => '',
                                                'asin' => trim($asin),
                                                'title' => trim(replaceSponsorText($title, $locale)),
                                                'locale' => $locale,
                                                'dateExecuted' => $dateNow
                                            );
                                            $scraper->addProduct($listData);
                                            $s++;
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
		break;
    }

    /*
    if(count($lists) > 0){
        $scraper->addProducts($lists, $dateNow);
    }
    */
    sleep(mt_rand(1, 3));
}

function replaceSponsorText($title, $locale){
    switch($locale){
        case 'it':
            return str_replace('[Sponsorizzato]', '', $title);
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