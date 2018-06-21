<?php
/*
 * Returns gelocation info from IP adress
 *
 * @since      4.0.0
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 */
 
class CF_Geoplugin_Encrypt extends CF_Geoplugin
{

	protected $private_key = 'Vivwna-Fgrsna Fgvcvp';
	private $move = 2;
	
	public function __construct()
	{
		// ENCODED: join("|<>|", array($this->get_host(),$this->private_key,$this->ip_server()));
	}
	/*
	function encrypt($str1)
	{
		$len = strlen($str1);
		
		$encrypt_str = "";
		for($i=0; $i < $len; $i++) 
		{
			$char = substr($str1, $i, 1);
			
			$keychar = substr($this->private_key, ($i % strlen($this->private_key))-1, $this->move);
			
			$char = chr((ord($char)+ord($keychar))+ord('&'));
			
			$encrypt_str .= $char;
		}
		
		$encrypt_str = $this->encoding($encrypt_str);
		
		return $encrypt_str;
	}
	*/
	public function decrypt($string)
	{
		$string = $this->decoding($string);
		
		$len = strlen($string);
		
		$decrypt_str = "";
	
		for($i=0; $i < $len; $i++) 
		{
			$char = substr($string, $i, 1);
			
			$keychar = substr($this->private_key, ($i % strlen($this->private_key))-1, $this->move);
			
			$char = chr((ord($char)-ord($keychar))-ord('&'));
			
			$decrypt_str.=$char;
		}
	
		return $decrypt_str;
	}
	/*
	private function encoding($str)
	{
		return str_rot13(base64_encode(str_rot13(gzdeflate($str))));
	}
	*/
	private function decoding($str)
	{
		return gzinflate(str_rot13(base64_decode(str_rot13($str))));
	}
	
	protected function get_host(){
		$homeURL = get_home_url();
		$hostInfo = parse_url($homeURL);
		return strtolower($hostInfo['host']);
	}
	
	protected function get_ip(){
		$this->ip_server();
	}
}
 
class CF_Geoplugin_Defender extends CF_Geoplugin_Encrypt
{
	private $header = 'HTTP/1.0 403 Forbidden';
	public $enable = false;
	
	function __construct(){
		$this->enable=$this->parse();	
	}	
	
	public function protect(){
		if($this->check() && $this->enable){
			header($this->header);
			$blockMessage=get_option("cf_geo_block_country_messages");
			die(html_entity_decode(stripslashes($blockMessage)));exit;
		}
	}
	
	private function check(){
		$i=array();
		foreach($this->data() as $field=>$check)
		{
			if(is_string($field) && !empty($field) && !empty($check) && strlen($check)>2 && $this->enable){
				$data=strtolower($check);
			//	$allData=preg_replace("#[^a-z\;\,\ ]#Ui","",$data);
				$allData=str_replace(";",",",$allData);
				$arrayData=explode(",", $allData);
				$arrayData=array_map("trim",$arrayData);
				$arrayData=array_map("strtolower",$arrayData);
				
				$geo=trim(strtolower(do_shortcode('[cf_geo return="'.$field.'"]')));
				
				if(!is_null($geo) && !empty($geo) && count($arrayData)>0 && in_array($geo, $arrayData)) $i[]=1;
			}
		}
		if(count($i)>0) return true; else return false;
	}
	
	private function data(){
		return array(
			'country' => get_option("cf_geo_block_country"),
			'state' => get_option("cf_geo_block_state"),
			'ip' => get_option("cf_geo_block_ip"),
			'city' => get_option("cf_geo_block_city"),
		);
	}
	
	private function parse(){	
		return true;
	}
	
}