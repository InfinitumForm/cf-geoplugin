<?php
/**
 * CSV Parser
 * Import or export CSV class
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.7
 */

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Encoding')) {
	CFGP_U::include_once(CFGP_CLASS . '/Encoding.php');
}
 
if(!class_exists('CFGP_CSV')) :
class CFGP_CSV{
	
	/* Private property */
	private $_delimiter;
	private $_enclosure;
	private $_linebreak;
	private $_csv = '';
	
	/* Import CSV from the file or string */	
	public static function import( $filename_or_data, bool $is_file = false, $delimiter = 'auto', $enclosure = 'auto', $linebreak = 'auto' ) {
		$csv = new static( $delimiter, $enclosure, $linebreak );
		return $csv->toArray( $filename_or_data, $is_file );
	}
	
	/* Export to CSV format */
	public static function export( $items, $delimiter = ',', $enclosure = '"', $linebreak = "\r\n") {
		$csv = new static( $delimiter, $enclosure, $linebreak );
		return $csv->fromArray( $items );
	}
	
	/*
	 * Find Delimiter
	*/
	public function delimiter( $set = false, $delimiters = ["\t", ';', '|', ','] ) {
		
		if ($set !== false) {
			return $this->_delimiter = $set;
		}
		
		if ($this->_delimiter === 'auto') {
			$this->_delimiter = ',';
			$maxCount = 0;
	
			foreach ($delimiters as $delimiter) {
				// Impress your code reviewer by some badass regex ;)
				$pattern = "/(?<!\\\)(?:\\\\\\\)*(?!\B\"[^\\\"]*)\\" . $delimiter . "(?![^\"]*\\\"\B)/";
				$amount = preg_match_all($pattern,  $this->_csv);
	
				if ($maxCount > 0 && $amount > 0) {
					$msg = 'Identifier is not clear: "' . $amount . '" and "' . $delimiter . '" are possible';
					throw new \Exception($msg);
				}
	
				if ($amount > $maxCount) {
					$maxCount = $amount;
					$this->_delimiter = $delimiter;
					break;
				}
			}
	
			// If nothing matches and you don't expect that just the CSV just
			// consists of one single column without a delimeter at the end
			if ($maxCount === 0) {
				throw new \Exception('Unknown delimiter');
			}
		}
		
		return $this->_delimiter;
	}
	
	/* Find Enclosure */
	public function enclosure( $set = false ) {
		if ($set !== false) {
			return $this->_enclosure = $set;
		}
		
		if ($this->_enclosure === 'auto') {
			
			// detect quot
			if (preg_match_all('/(".*?")[,;|\t\n]/mi', $this->_csv) > 0) {
				$this->_enclosure = '"';
			}
			else if (preg_match_all('/(\'.+?\')[,;|\t\n]/mi', $this->_csv) > 0) {
				$this->_enclosure = "'";
			}
			else {
				$this->_enclosure = '';
			}
		}
		return $this->_enclosure;
	}
	
	/* Find linebreak */
	public function linebreak( $set = false ) {
		if ($set !== false) {
			return $this->_linebreak = $set;
		}
		
		if ($this->_linebreak === 'auto') {
			if (preg_match('/\r\n/', $this->_csv)) {
				$this->_linebreak = "\r\n";
			}
			else if (preg_match('/\r/', $this->_csv)) {
				$this->_linebreak = "\r";
			}
			else if (preg_match('/\n/', $this->_csv)) {
				$this->_linebreak = "\n";
			}
			else {
				$this->_linebreak = "\n";
			}
		}
		return $this->_linebreak;
	}
	
	/* Remove BOM shit from the Windows */
	public function remove_utf8_bom($str)
	{
		// Convert to UTF-8
		$str = CFGP_Encoding::toUTF8( $str );
		
		// Remove multiple UTF-8 BOM sequences
		$bom = pack('H*','EFBBBF');
		if(preg_match("/^{$bom}/", $str)) {
			$str = preg_replace("/^{$bom}/", '', $str);
		}
		
		// Trim
		return trim($str);
	}
	
	/* Sanitize data from CSV */
	public function sanitize( $str ){
		
		if( is_array($str) )
		{
			$array = [];
			foreach($str as $key=>$val) {
				$array[$key] = $this->sanitize($val);
			}
			return $array;
		}
		else
		{
			if($str != 0 && empty($str))
			{
				return NULL;
			}
			else if (filter_var($str, FILTER_VALIDATE_URL) !== false)
			{
				return esc_url($str);
			}
			else if(preg_match('/([0-9a-z-_.]+@[0-9a-z-_.]+.[a-z]{2,8})/i', $str))
			{
				$str = trim($str, "&$%#?!.;:,");
				$str = sanitize_email($str);
				

				return CFGP_U::strtolower($str);
			}
			else if(is_numeric($str))
			{
				if((int)$str == $str){
					return (int)$str;
				} else if((float)$str == $str){
					return (float)$str;
				}
				
			}
			else if(is_string($str))
			{
				return sanitize_text_field($str);
			}
		}
		
		return $str;
	}
	
	/* Convert to array */
	public function toArray( $filename, $is_csv_content = false ) {
		$r = [];
		
		if(function_exists('str_getcsv'))
		{
			$this->_csv = ($is_csv_content ? $filename : file_get_contents($filename));
			$this->_csv = $this->remove_utf8_bom($this->_csv);
			
			$CSV_LINEBREAK = $this->linebreak();
			$CSV_ENCLOSURE = $this->enclosure();
			$CSV_DELIMITER = $this->delimiter();
			
			foreach(str_getcsv($this->_csv, $CSV_LINEBREAK, $CSV_ENCLOSURE) as $row){
				$col = str_getcsv($row, $CSV_DELIMITER, $CSV_ENCLOSURE);
				$col = array_map('trim', $col);
				$col = array_map(function($match) use (&$CSV_ENCLOSURE) {
					$match = trim($match, $CSV_ENCLOSURE);
					$match = $this->sanitize($match);
					return $match;
				}, $col);
				$r[] = $col;
			}
		}
		else if(!$is_csv_content && function_exists('fgetcsv'))
		{
			$header=get_headers($filename, 1);
			
			// Set chunking offset
			$offset = 500;
			
			$this->_csv = file_get_contents($filename);
			$this->_csv = $this->remove_utf8_bom($this->_csv);
			
			$CSV_LINEBREAK = $this->linebreak();
			$CSV_ENCLOSURE = $this->enclosure();
			$CSV_DELIMITER = $this->delimiter();
	
			$this->_csv = NULL;
			
			if(isset($header['Content-Type']) && ($header['Content-Type'] == 'text/csv' || $header['Content-Type'] == 'application/vnd.ms-excel'))
			{
				// IF we can open and read the file
				if (($handle = fopen($filename, "r")) !== FALSE) {
					$i = 0;
					$chunk = $offset;
					
					// while data exists loop over data
					while ( ( $ceil = fgetcsv($handle, (isset($header['Content-Length']) ? $header['Content-Length'] : 2000), $CSV_DELIMITER) ) !== false ) {
						$data = [];
						foreach($ceil as $i=>$col) {
							$data[$i]=$this->sanitize($col);
						}
						
						$r[$i][] = $data;
							
						--$chunk;
						if( $chunk < 0 )
						{
							$chunk = $offset;
							++$i;
						}
					} 			
					// close our file
					fclose($handle);
				}
				else
				{
					throw new \Exception('Can not open the file.');
				}
			}
			else
			{
				throw new \Exception('Not CSV file.');
			}
		}
		else
		{
			$this->_csv = ($is_csv_content ? $filename : file_get_contents($filename));
			$this->_csv = $this->remove_utf8_bom($this->_csv);
	
			$CSV_LINEBREAK = $this->linebreak();
			$CSV_ENCLOSURE = $this->enclosure();
			$CSV_DELIMITER = $this->delimiter();
	
			$cnt = strlen($this->_csv); 
			
			$esc = $escesc = false; 
			$i = $k = $n = 0;
			$r[$k][$n] = '';
			
			while ($i < $cnt) { 
				$ch = $this->_csv[$i];
				$chch = ($i < $cnt-1) ? $ch.$this->_csv[$i+1] : $ch;
	
				if ($ch === $CSV_LINEBREAK) {
					if ($esc) {
						$r[$k][$n] .= $this->sanitize($ch); 
					} else {
						$k++;
						$n = 0;
						$esc = $escesc = false;
						$r[$k][$n] = '';
					}
				} else if ($chch === $CSV_LINEBREAK) {
					if ($esc) {
						$r[$k][$n] .= $this->sanitize($chch);
					} else {
						$k++;
						$n = 0;
						$esc = $escesc = false;
						$r[$k][$n] = '';
					}
					$i++;
				} else if ($ch === $CSV_DELIMITER) { 
					if ($esc) { 
						$r[$k][$n] .= $this->sanitize($ch); 
					} else { 
						$n++;
						$r[$k][$n] = '';
						$esc = $escesc = false; 
					}
				} else if ( $chch === $CSV_ENCLOSURE.$CSV_ENCLOSURE && $esc ) {
					$r[$k][$n] .= $CSV_ENCLOSURE;
					$i++;
				} elseif ($ch === $CSV_ENCLOSURE) {
					
					$esc = !$esc;
					
				} else {
					$r[$k][$n] .= $this->sanitize($ch);
				}
				$i++; 
			}
		}
		
		return $r;
	}
	
	/* Get from the array */
	public function fromArray( $items ) {
		
		if (!is_array($items)) {
			trigger_error('CSV::export array required', E_USER_WARNING);
			return false;
		}
		
		$CSV_DELIMITER = $this->delimiter();
		$CSV_ENCLOSURE = $this->enclosure();
		$CSV_LINEBREAK = $this->linebreak();
		
		$result = '';
		foreach( $items as $i) {
			$line = '';
			
			foreach ($i as $v) { 
				if (strpos($v, $CSV_ENCLOSURE) !== false) { 
					$v = str_replace($CSV_ENCLOSURE, $CSV_ENCLOSURE . $CSV_ENCLOSURE, $v); 
				} 
			
				if ((strpos($v, $CSV_DELIMITER) !== false) 
					|| (strpos($v, $CSV_ENCLOSURE) !== false) 
					|| (strpos($v, $CSV_LINEBREAK) !== false))
				{
					$v = $CSV_ENCLOSURE . $v . $CSV_ENCLOSURE; 
				}
				$line .= ($line ? $CSV_DELIMITER . $v : $v);
			}
			$result .= ($result ? $CSV_LINEBREAK . $line : $line);
		}
		
		return $result;
	}
	
	public function __construct( $delimiter = 'auto', $enclosure = 'auto', $linebreak = 'auto' ) {
		$this->_delimiter = $delimiter;
		$this->_enclosure = $enclosure;
		$this->_linebreak = $linebreak;
	}
}
endif;