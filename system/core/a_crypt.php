<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Crypt {
	
	private function get_ord($str){
		$arr = str_split($str);
		
		$ord = array();
		
		foreach($arr as $val){
			$ord[] = ord($val);	
		}
		
		return $ord;
	}

	private function get_key_session($str){
		$key = implode("",$this->get_ord($str));
		$x = strlen($key) % 2;
		if($x > 0){
			$key .= substr($key, 0, 2-$x);
		}
		$s = strlen($key)/2;
		$t = array();
		for($i=0;$i<$s;$i++){
			$t[] = intval(substr($key,$i*2,2));
		}
		return $t;
	}

	private function get_key($str){
		$k2xrk = array();
		while(count($k2xrk)<128){
			$rk = $this->get_ord($str);
			$k = array();
			$k2 = array();
			$k2xrk = array();
			for($i=count($rk)-1;$i>=0;$i--){
				$k[] = $rk[$i];
			}

			$sum_k = 0;
			for($i=0;$i<count($k);$i++){
				$sum_k += ($k[$i] * ($i+1));
			}

			$mod_sum = $sum_k % count($k);
			for($i=0;$i<count($k);$i++){
				$k2[] = ($mod_sum+$k[$i]);
			}

			for($i=0;$i<count($k);$i++){
				$k2xrk[] = $k2[$i] * $rk[$i] * ($i+1);
			}
			$str = implode("",$k2xrk);
		}
		return $k2xrk;
	}
	
	private function acm($p,$q,$maks_offset){
		$arr_acm = array();	
		$dimensi = 1;
		while (($dimensi * $dimensi) < $maks_offset){
			$dimensi++;
		}
				
		for($i=0;$i<$dimensi;$i++){
			for($j=0;$j<$dimensi;$j++){
				$s = ($dimensi*$i)+$j;
					
				$rx = ((1*$i)+($p*$j))%$dimensi;
				$ry = (($q*$i)+($p*$q+1)*$j)%$dimensi;
				
				$rs = ($dimensi*$rx)+$ry;
				if($rs<=$maks_offset && $rs>0) $arr_acm[] = $rs-1;
			}
		}	
		return $arr_acm;	
	}

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
	
	private function acm_encrypt_coordinat($p,$q,$maks_offset){
		$arr_acm = array();	
		$dimensi = 1;
		while (($dimensi * $dimensi) < $maks_offset){
			$dimensi++;
		}
				
		for($i=0;$i<$dimensi;$i++){
			for($j=0;$j<$dimensi;$j++){
				$s = ($dimensi*$i)+$j;
					
				$rx = ((1*$i)+($p*$j))%$dimensi;
				$ry = (($q*$i)+($p*$q+1)*$j)%$dimensi;
				
				$rs = ($dimensi*$rx)+$ry;
				if($rs<=$maks_offset && $rs>0) $arr_acm[] = $rs-1;
			}
		}	
		return $arr_acm;
	}

	private function acm_decrypt_coordinat($p,$q,$maks_offset){
		$arr_acm = array();	
		$dimensi = 1;
		while (($dimensi * $dimensi) < $maks_offset){
			$dimensi++;
		}
		
		$h = 0;
		for($i=0;$i<$dimensi;$i++){
			for($j=0;$j<$dimensi;$j++){
				$s = ($dimensi*$i)+$j;
					
				$rx = ((1*$i)+($p*$j))%$dimensi;
				$ry = (($q*$i)+($p*$q+1)*$j)%$dimensi;
				
				$rs = ($dimensi*$rx)+$ry;
				if($rs<=$maks_offset && $rs>0) {
					$arr_acm[$rs-1] = $h;
					$h++;
				}
			}
		}	
		return $arr_acm;
	}

	private function acm_encrypt($p, $q, $str){
		$arr_str = str_split($str);
		$l = count($arr_str);
		$acm = $this->acm_coordinat($p, $q, $l);

		$arr_result = array();
		for($i=0;$i<$l;$i++){
			$arr_result[$i] = $arr_str[$acm[$i]];
		}
		return implode('',$arr_result);
	}

	private function acm_decrypt($p, $q, $str){
		$arr_str = str_split($str);
		$l = count($arr_str);
		$acm = $this->acm_coordinat($p, $q, $l, false);

		$arr_result = array();
		for($i=0;$i<$l;$i++){
			$arr_result[$i] = $arr_str[$acm[$i]];
		}
		return implode('',$arr_result);
	}

	function acm_rand_encrypt($str, $key, $p=8279, $q=6371){
		$str = str_pad($str,10," ");

		$arr_str = $this->get_ord($str);
		$arr_key = $this->get_key($key);

		$ls = count($arr_str);
		$lk = count($arr_key);
		
		$arr_result = array();
		for($i=0; $i<$ls; $i++){
			$j = $i*2;
			$rand = rand();

			$c1 = ($arr_key[$i % $lk] + (2*$arr_str[$i]) + $rand) % 127;
			$c2 = ((2*$arr_key[$i % $lk]) + $arr_str[$i] + $rand) % 127;

			$arr_result[$j] = chr($c1);
			$arr_result[$j+1] = chr($c2);
		}
		return base64_encode($this->acm_encrypt($p, $q, implode('',$arr_result)));
	}

	function acm_rand_decrypt($str, $key, $p=8279, $q=6371){
		$arr_str = $this->get_ord($this->acm_decrypt($p, $q, base64_decode($str)));

		$arr_key = $this->get_key($key);

		$ls = count($arr_str);
		$lk = count($arr_key);
		
		$result = "";
		for($i=0; $i<$ls/2; $i++){
			$j = $i*2;

			$c1 = $arr_str[$j];
			$c2 = $arr_str[$j+1];
			$r = (($c1-$arr_key[$i%count($arr_key)] % 127))-(($c2-(2*$arr_key[$i%count($arr_key)])) % 127);
			if($r<0) $r+=127; else if($r>127) $r-=127;
				
			$result .= chr($r);
		}
		if(strlen($result)==10) $result = trim($result);
		return $result;
	}
}

?>
