<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class ACM extends Main {

    function getTodayOrder(){
        function acm_coordinat($var_p = 234, $var_q = 567, $var_l = 10, $is_encrypt=true) {
            function bigToInteger($int) {
                return (int)($int->toString());
            }
            function getMX($x, $y, $p, $n) {
                $bigOne = new BigInteger(1);
                $bigX = new BigInteger($x);
                $bigY = new BigInteger($y);
                $bigP = new BigInteger($p);
                $bigN = new BigInteger($n);
                $bigOneX = $bigOne->multiply($bigX);
                $bigPY = $bigP->multiply($bigY);
                $bigMX = $bigOneX->add($bigPY);
                $bigMX = $bigMX->modPow($bigOne, $bigN);
                return $bigMX->toString();
            }
            function getMY($x, $y, $p, $q, $n) {
                $bigOne = new BigInteger(1);
                $bigX = new BigInteger($x);
                $bigY = new BigInteger($y);
                $bigP = new BigInteger($p);
                $bigQ = new BigInteger($q);
                $bigN = new BigInteger($n);
                $bigQX = $bigQ->multiply($bigX);
                $bigPQY = $bigP->multiply($bigQ)->add($bigOne)->multiply($bigY);
                $bigMY = $bigQX->add($bigPQY);
                $bigMY = $bigMY->modPow($bigOne, $bigN);
                return $bigMY->toString();
            }
            function getMS($mx, $my, $n) {
                $bigMX = new BigInteger($mx);
                $bigMY = new BigInteger($my);
                $bigN = new BigInteger($n);
                return $bigN->multiply($bigMX)->add($bigMY)->toString();
            }

            $p = new BigInteger($var_p);
            $q = new BigInteger($var_q);
            $l = new BigInteger($var_l + 1);
            $n = (int)(sqrt(bigToInteger($l)) + 1);
            
            $acm = array();
            $h = 0;
            for($x=1;$x<=$n;$x++){
                for($y=1;$y<=$n;$y++){
                    $mx = getMX($x, $y, $p, $n);
                    $my = getMY($x, $y, $p, $q, $n);
                    $ms = getMS($mx, $my, $n);
    
                    if($is_encrypt === true) {
                        if($ms < $l && $ms > 0 ) {
                            $acm[] = $ms-1;
                        }
                    } else {
                        if($ms < $l && $ms > 0 ) {
                            $acm[(int)$ms - 1] = $h;
                            $h++;
                        }
                    }
                }
            }
            return $acm;
        }

        $p = substr(strtotime(date('d-m-Y')), 3);
        $q = substr(strtotime(date('d-m-Y')), 3);
        $l = 10;
        
        if(isset($_GET['len'])){
            if(is_numeric($_GET['len'])) {
                $l = $_GET['len'];
            }
        }
		$arr_acm = acm_coordinat($p, $q, $l);
		$this->render->json($arr_acm);
	}
}    
?>