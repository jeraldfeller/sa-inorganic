<?php
//require_once(ROOT_DIR.'/../ProxyLoader.php');
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 8/20/2018
 * Time: 5:30 AM
 */
class Scraper
{
    public $debug = TRUE;
    protected $db_pdo;

    public function getLocale(){
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `locale` ORDER BY `symbol` ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;

        return $result;
    }

    public function addLocale($locale){
        $locale = trim(strtolower($locale));
        $pdo = $this->getPdo();
        // check table exists
        $sql = 'show tables like "inorganic1_'.$locale.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result =  $stmt->fetch(PDO::FETCH_BOTH);
        if($result[0] !== null){
            return json_encode([
               'success' => false
            ]);
        }else{

            $sql = 'INSERT INTO `locale` SET `symbol` = "'.$locale.'"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();


            $sql = 'CREATE TABLE IF NOT EXISTS `inorganic1_'.$locale.'` (
                  `id` int(5) NOT NULL AUTO_INCREMENT,
                  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  `status` int(2) NOT NULL DEFAULT \'0\',
                  `keyword` text CHARACTER SET utf8 COLLATE utf8_spanish2_ci,
                  PRIMARY KEY (`id`)
                )';

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }


        return json_encode([
            'success' => true
        ]);

    }

	 public function getBrandByAsin($asin){
        $pdo = $this->getPdo();
        $sql = 'SELECT `brand` FROM `inorganic` WHERE `asin` = "'.$asin.'" AND `brand` != "NA" LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }

      //  var_dump($result);
        if(count($result) > 0){
            $brand = $result[0]['brand'];
        }else{
            $brand = false;
        }
        $pdo = null;

        return $brand;
    }
	
    public function getNoBrandData(){
        $date = date('Y-m-d');
        $pdo = $this->getPdo();
        $sql = "SELECT * FROM `inorganic` WHERE time like '".$date."%' and brand = '' and asin != '' LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;

        return $result;
    }

	public function setBrand($id, $brand){
$brand = str_replace("'", "", addslashes($brand));        
$pdo = $this->getPdo();
        $sql = "UPDATE `inorganic` SET `brand` = '".$brand."' WHERE `id` = ".$id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;

        return true;
    }

    public function exportProducts($locale)
    {

        $pdo = $this->getPdo();
        $sql = 'SELECT *
                FROM `inorganic` WHERE `locale` = "'.$locale.'" ORDER BY `id` DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }

//        $sql = 'SELECT *
//                FROM `inorganic_uk` ORDER BY `id` DESC';
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//            $result[] = $row;
//        }
//        $pdo = null;
        return $result;
    }

    public function exportInputs($locale)
    {
        if($locale == 'it'){
            $table = 'inorganic1';
        }else{
            $table = 'inorganic1_'.$locale;
        }
        $pdo = $this->getPdo();
        $sql = 'SELECT `keyword`
                FROM `'.$table.'` ORDER BY `id` DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;
        return $result;
    }

    public function insertKeyword($keyword, $locale)
    {

        if($locale == 'it'){
            $table = 'inorganic1';
        }else{
            $table = 'inorganic1_'.$locale;
        }

        $pdo = $this->getPdo();
        $sql = 'SELECT count(`id`) AS rowCount FROM `'.$table.'` WHERE `keyword` = "' . $keyword . '"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)['rowCount'] == 0) {
            $sql = 'INSERT INTO `'.$table.'` SET `keyword` = "' . $keyword . '"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        $pdo = null;
    }

    public function getKeywords($locale, $offset = 0, $limit = 5)
    {
        $pdo = $this->getPdo();
        switch ($locale) {
            case 'it':
                $table = 'inorganic1';
                break;
            case 'co.uk':
                $table = 'inorganic1_uk';
                break;
            case 'fr':
                $table = 'inorganic1_fr';
                break;
            case 'de':
                $table = 'inorganic1_de';
                break;
            case 'com':
                $table = 'inorganic1_us';
                break;
               case 'es':
                $table = 'inorganic1_es';
                break;
        }
        $sql = 'SELECT * FROM `' . $table . '` WHERE `status` = 0 ORDER BY id ASC LIMIT ' . $offset . ',' . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
            $sql = 'UPDATE `' . $table . '` SET `status` = 1 WHERE `id` = ' . $row['id'];
            $stmtU = $pdo->prepare($sql);
            $stmtU->execute();
        }
        $pdo = null;
        return $result;
    }

    public function isAsinExist($asin, $keyword, $date)
    {
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `inorganic` WHERE `asin` = "' . $asin . '" AND `keyword` = "' . $keyword . '" AND `time` LIKE "' . $date . '%"';


        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdo = null;
        return $result;
    }

    public function insertAsinLink($id, $url)
    {
        $pdo = $this->getPdo();
        $sql = 'UPDATE `asins` SET `asin_review_url` = "' . $url . '" WHERE `id` = ' . $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function addProduct($data)
    {
        $pdo = $this->getPdo();
        switch ($data['locale']) {
            case 'it':
                $table = 'inorganic';
                break;
            case 'co.uk':
                $table = 'inorganic';
                break;
            case 'fr':
                $table = 'inorganic';
                break;
            case 'de':
                $table = 'inorganic';
                break;
            case 'com':
                $table = 'inorganic';
                break;
default:
                $table = 'inorganic';
                break;
        }
        $sql = 'INSERT INTO `' . $table . '` SET `position` = ' . $data['position'] . ', `keyword` = "' . $data['keyword'] . '", `brand` = "' . $data['brand'] . '", `message` = "' . $data['message'] . '", `asin` = "' . $data['asin'] . '", `title` = "' . $data['title'] . '", `locale` = "' . $data['locale'] . '", `run_number` = ' . $data['runNumber'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function addProducts($data, $date)
    {
        $pdo = $this->getPdo();
        $values = '';
        for ($x = 0; $x < count($data); $x++) {

            if ($x == count($data) - 1) {
                $values .= '(' . $data[$x][0] . ',
                "' . $data[$x][1] . '",
                "' . $data[$x][2] . '",
                "' . $data[$x][3] . '",
                "' . $data[$x][4] . '",
                "' . $data[$x][5] . '",
                "' . $data[$x][6] . '",
                "' . $date . '"
                )';
            } else {
                $values .= '(' . $data[$x][0] . ',
                "' . $data[$x][1] . '",
                "' . $data[$x][2] . '",
                "' . $data[$x][3] . '",
                "' . $data[$x][4] . '",
                "' . $data[$x][5] . '",
                "' . $data[$x][6] . '",
                "' . $date . '"
                ),';
            }
        }


        $sql = 'INSERT INTO `inorganic`
                  (`position`, `keyword`, `brand`, `message`, `asin`, `title`, `locale`, `date_executed`)
                VALUES ' . $values . '
               ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    function reset($locale)
    {
        switch ($locale) {
            case 'it':
                $table = 'inorganic1';
                break;
            case 'co.uk':
                $table = 'inorganic1_uk';
                break;
            case 'fr':
                $table = 'inorganic1_fr';
                break;
            case 'de':
                $table = 'inorganic1_de';
                break;
            case 'com':
                $table = 'inorganic1_us';
                break;
        }
        $pdo = $this->getPdo();
        $sql = 'UPDATE `' . $table . '` SET status = 0;';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    function checkReviewId($reviewId)
    {
        $pdo = $this->getPdo();
        $sql = 'SELECT count(`id`) as matchCount FROM `reviews` WHERE `review_id` = "' . $reviewId . '"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['matchCount'];
        $pdo = null;

        return $count;
    }

    public function updateTotalReviewCount($id, $locale, $count)
    {
        $pdo = $this->getPdo();
        $sql = 'SELECT count(id) as rowCount, id FROM `total_reviews` WHERE `asins_id` = ' . $id . ' AND `locale` = "' . $locale . '"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)['rowCount'] == 0) {
            $sql = 'INSERT INTO `total_reviews` SET `asins_id` = ' . $id . ', `locale` = "' . $locale . '", `total_review_count` = ' . $count;
        } else {
            $sql = 'UPDATE  `total_reviews` SET `asins_id` = ' . $id . ', `locale` = "' . $locale . '", `total_review_count` = ' . $count . ' WHERE `id` = ' . $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function curlTo($url, $locale)
    {
        $userpass = 'amznscp:dfab7c358';
        /*
                switch ($locale){
                    case 'it':
                        $userpass = 'jnurmuk:Witailer99';
                        $port = 80;
                        $proxy = array(
                            '91.108.178.122',
                            '196.196.149.180',
                            '185.104.218.163',
                            '37.72.191.137',
                            '82.211.55.71',
                            '50.3.79.71',
                            '5.34.243.37',
                            '191.101.115.232',
                            '165.231.161.45',
                            '198.46.169.49',
                            '91.108.177.83',
                            '5.34.243.239',
                            '196.196.149.13',
                            '185.104.218.168',
                            '23.90.42.74',
                            '130.185.157.63',
                            '50.3.79.98',
                            '82.211.55.97',
                            '23.90.42.247',
                            '191.101.115.224',
                        );
        break;
case 'es':
                        $userpass = 'jnurmuk:Witailer99';
                        $port = 80;
                        $proxy = array(
                            '91.108.178.122',
                            '196.196.149.180',
                            '185.104.218.163',
                            '37.72.191.137',
                            '82.211.55.71',
                            '50.3.79.71',
                            '5.34.243.37',
                            '191.101.115.232',
                            '165.231.161.45',
                            '198.46.169.49',
                            '91.108.177.83',
                            '5.34.243.239',
                            '196.196.149.13',
                            '185.104.218.168',
                            '23.90.42.74',
                            '130.185.157.63',
                            '50.3.79.98',
                            '82.211.55.97',
                            '23.90.42.247',
                            '191.101.115.224',
                        );
        break;

        case 'co.uk':
                        $port = '17843';
                        $proxy = array(
                            '213.184.106.74',
                            '213.184.107.186',
                            '213.184.108.112',
                            '196.17.176.19',
                            '196.17.177.236',
                            '196.17.179.207',
                        );
                        break;
                    case 'de':
                        $port = '17843';
                        $proxy = array(
                            '37.10.68.125',
                            '37.10.68.130',
                            '37.10.68.76',
                            '37.10.68.94',
                            '37.10.69.158',
                            '37.10.69.78'
                        );
                        break;
                    case 'fr':
                        $index = mt_rand(0, 2);
                        $port = ['56362','17843', '17843'];
                        $port = $port[$index];
                        if($index == 0){
                            $proxy = array(
                                '213.184.109.121',
                                '213.184.109.159',
                                '213.184.110.112',
                                '213.184.110.94',
                                '213.184.112.221',
                                '213.184.114.15',
                                '196.16.224.248',
                                '196.16.246.176',
                                '196.19.160.254',
                                '196.19.160.6',
                                '196.19.161.254',
                                '196.19.161.3'
                            );
                        }else if($index == 1){
                            $proxy = array(
                                '213.184.106.74',
                                '213.184.107.186',
                                '213.184.108.112',
                                '196.17.176.19',
                                '196.17.177.236',
                                '196.17.179.207',
                            );
                        }else if($index = 2){
                            $proxy = array(
                                '37.10.68.125',
                                '37.10.68.130',
                                '37.10.68.76',
                                '37.10.68.94',
                                '37.10.69.158',
                                '37.10.69.78'
                            );
                        }

                        break;
        case 'com':

        $index = mt_rand(0, 2);
                        $port = ['56362','17843', '17843'];
                        $port = $port[$index];
                        if($index == 0){
                            $proxy = array(
                                '213.184.109.121',
                                '213.184.109.159',
                                '213.184.110.112',
                                '213.184.110.94',
                                '213.184.112.221',
                                '213.184.114.15',
                                '196.16.224.248',
                                '196.16.246.176',
                                '196.19.160.254',
                                '196.19.160.6',
                                '196.19.161.254',
                                '196.19.161.3'
                            );
                        }else if($index == 1){
                            $proxy = array(
                                '213.184.106.74',
                                '213.184.107.186',
                                '213.184.108.112',
                                '196.17.176.19',
                                '196.17.177.236',
                                '196.17.179.207',
                            );
                        }else if($index = 2){
                            $proxy = array(
                                '37.10.68.125',
                                '37.10.68.130',
                                '37.10.68.76',
                                '37.10.68.94',
                                '37.10.69.158',
                                '37.10.69.78'
                            );
                        }
        break;
        }
        */
        $userpass = 'jnurmuk:Witailer99';
        $port = 80;
/*
$proxy = array(
            '165.231.103.105',
            '196.196.90.189',
            '23.231.15.28',
            '196.196.90.90',
            '165.231.105.5',
            '196.196.88.226',
            '165.231.101.78',
            '23.231.15.222',
            '165.231.101.29',
            '165.231.101.60',
            '196.196.90.47',
            '23.231.15.112',
            '103.215.218.71',
            '165.231.103.78',
            '196.196.88.67',
            '196.196.88.249',
            '103.215.218.198',
            '196.196.88.115',
            '165.231.101.27',
            '103.215.218.21',
            '165.231.105.12',
            '196.196.92.56',
            '165.231.103.167',
            '103.215.216.72',
            '23.90.43.163',
            '165.231.103.88',
            '185.53.130.176',
            '103.215.217.209',
            '196.196.94.107',
            '196.196.88.186',
            '196.196.94.224',
            '165.231.96.105',
            '103.215.218.159',
            '196.196.88.43',
            '165.231.103.145',
            '31.220.44.239',
            '77.81.110.108',
            '23.90.41.27',
            '165.231.103.218',
            '185.147.34.107'
        );*/
$p = new ProxyLoader('inorganic');
$proxy = $p->readProxies();
//print_r($proxy);die();
        $ip = $proxy[mt_rand(0, count($proxy) - 1)];
//         echo $ip . ' - ' . $port;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
            CURLOPT_PROXY => $ip,
            CURLOPT_PROXYPORT => $port,
            CURLOPT_PROXYUSERPWD => $userpass,
          //  CURLOPT_HTTPHEADER => array(
            //    "Cache-Control: no-cache",
              //  "Postman-Token: 8b566d82-6f87-40c1-8cd8-afc22a097d73"
           // ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return array('html' => $err);
        } else {
            return array('html' => $response, 'ip' => $ip);
        }
    }

    public function getPdo()
    {
        if (!$this->db_pdo) {
            if ($this->debug) {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            } else {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD);
            }
        }
        return $this->db_pdo;
    }
}
