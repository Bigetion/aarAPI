<?php

function segment($nomor) {
    /* $uri = explode('?', $_SERVER['REQUEST_URI']);
    $data = explode('/', $uri[0]);
    if ($nomor > count($data) - 1) return "";
    else return str_replace('.html', '', $data[$nomor]); */
	
	$uri = & load_class('URI');
	return $uri->segment($nomor);
}

/* function redirect($alamat = '', $is_project = true, $back_query_string_session = false) {
	$output = "";
    $output .= "<script>";
    if ($is_project == true)
        $output .= "window.location= '" . base_url_project . $alamat . "'";
    else
        $output .= "window.location= '" . base_url . $alamat . "'";
    $output .= "</script>";
	
	//if($back_get_session) $output .= implode("&&",$_GET);
	
	echo $output;
} */

function get_all_query_string(){
	$page_query_string = "";$arr_qstring[] = array();
		foreach($_GET as $key=>$value)
			$arr_qstring[] = ($key."=".$value);		
		$page_query_string = implode("&",$arr_qstring);	
	
	return $page_query_string;
}

/* function refresh_page(){
	$output = "";
    $output .= "<script>";
    $output .= "window.location= '" . $_SERVER['REQUEST_URI'] . "'";
    $output .= "</script>";
	
	echo $output;
}

function new_tab($alamat = '') {
	$output = "";
    $output .= "<script>";
    $output .= "window.open('". $alamat . "','__blank')";
    $output .= "</script>";
	
	echo $output;
} */

function slugify($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text))
  {
    return 'n-a';
  }

  return $text;
}

function get_url_query_string($url){
	$url_parsed = parse_url($url);
	parse_str($url_parsed['query'], $url_parts);
	return $url_parts;
}

function get_query_string($query){
	parse_str($query, $url_parts);
	return $url_parts;
}

function set_last_status($status){
	$_SESSION["last_status"] = $status;	
}

function get_last_status(){
	return $_SESSION["last_status"];	
}

function utf8_uri_encode( $utf8_string, $length = 0 ) {
	 $unicode = '';
	 $values = array();
	 $num_octets = 1;
	 $unicode_length = 0;
	 $string_length = strlen( $utf8_string );
	 for ($i = 0; $i < $string_length; $i++ ) {
		 $value = ord( $utf8_string[ $i ] );
		 if ( $value < 128 ) {
			 if ( $length && ( $unicode_length >= $length ) )
			 break;
			 $unicode .= chr($value);
			 $unicode_length++;
		 } else {
			if ( count( $values ) == 0 ) $num_octets = ( $value < 224 ) ? 2 : 3;
			$values[] = $value;
			if ( $length && ( $unicode_length + ($num_octets * 3) ) > $length )
			break;
			if ( count( $values ) == $num_octets ) {
				if ($num_octets == 3) {
					$unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
					$unicode_length += 9;
				} else {
					$unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
					$unicode_length += 6;
				}
				$values = array();
				$num_octets = 1;
			}
		 }
	 }
	 return $unicode;
 }
 
function seems_utf8($str) {
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
			return false;
		}
	}
	return true;
}
 
function sanitize_title_with_dashes( $title, $raw_title = '', $context = 'display' ) {
	$title = strip_tags($title);
	// Preserve escaped octets.
	$title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
	// Remove percent signs that are not part of an octet.
	$title = str_replace('%', '', $title);
	// Restore octets.
	$title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
	if (seems_utf8($title)) {
		if (function_exists('mb_strtolower')) {
			$title = mb_strtolower($title, 'UTF-8');
		}
		$title = utf8_uri_encode($title, 200);
	}
	
	$title = strtolower($title);
	$title = preg_replace('/&.+?;/', '', $title); // kill entities
	$title = str_replace('.', '-', $title);
	if ( 'save' == $context ) {
		// Convert nbsp, ndash and mdash to hyphens
		$title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
		// Strip these characters entirely
		$title = str_replace( array(
		// iexcl and iquest
		'%c2%a1', '%c2%bf',
		// angle quotes
		'%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
		// curly quotes
		'%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
		'%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
		// copy, reg, deg, hellip and trade
		'%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
		// acute accents
		'%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
		// grave accent, macron, caron
		'%cc%80', '%cc%84', '%cc%8c',
		), '', $title );
		// Convert times to x
		$title = str_replace( '%c3%97', 'x', $title );
	}
	$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
	$title = preg_replace('/\s+/', '-', $title);
	$title = preg_replace('|-+|', '-', $title);
	$title = trim($title, '-');
	
	return $title;
}
?>