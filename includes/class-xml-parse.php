<?php
/*------------------------------------------------------------------------
@author    Ivijan-Stefan Stipic
-------------------------------------------------------------------------*/
/*
	PARSE XML
	===================================================================
	Returns a well formed array like the structure of the xml-document

	<root>
		<child1>
			<child1child1><child1child1/>
			<child1child2><child1child2/>
		</child1>
	</root>
	
	create an objects like 
	Object: new parseXML()->fetch->root->child1->child1child1
	
	Example:
	===================================================================
	$xml= new parseXML('my-setup.xml');
	echo $xml->root->child1->child1child2;
	echo $xml->fetch->child1->child1child1;
*/
class parseXML
{
	protected $fread_length=8192;
	public $fetch=false;
	
	function __construct($document=false, $custom_url=false, $get_attributes = 1, $priority = 'tag')
	{
		$this->fetch=$this->__generateObjects($this->__fetchData($document, $custom_url, $get_attributes, $priority), $custom_url);
	}

	protected function __generateObjects($array, $custom_url)
	{
		if($custom_url)
		{
			return $array;
		}
		$json=json_encode($array);
		return json_decode($json);
	}
	protected function __fetchData($document, $custom_url, $get_attributes, $priority)
	{
		$xml_values=$this->__readXML($document, $custom_url);
		if($custom_url)
		{
			return $xml_values;
		}
		if($xml_values!==false)
		{
			$arr = $opened_tags = $parents = $xml_array = $repeated_tag_index = array();
			$current = & $xml_array;
			foreach ($xml_values as $data)
			{
				unset ($attributes, $value);
				extract($data);
				$result = array();
				$attributes_data = array();
				if (isset ($value))
				{
					if ($priority == 'tag')
						$result = $value;
					else
						$result['value'] = $value;
				}
				if (isset ($attributes) and $get_attributes)
				{
					foreach ($attributes as $attr => $val)
					{
						if ($priority == 'tag')
							$attributes_data[$attr] = $val;
						else
							$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
					}
				}
				if ($type == "open")
				{ 
					$parent[$level -1] = & $current;
					if (!is_array($current) or (!in_array($tag, array_keys($current))))
					{
						$current[$tag] = $result;
						if ($attributes_data)
							$current[$tag . '_attr'] = $attributes_data;
						$repeated_tag_index[$tag . '_' . $level] = 1;
						$current = & $current[$tag];
					}
					else
					{
						if (isset ($current[$tag][0]))
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
							$repeated_tag_index[$tag . '_' . $level]++;
						}
						else
						{ 
							$current[$tag] = array ($current[$tag],$result); 
							$repeated_tag_index[$tag . '_' . $level] = 2;
							if (isset ($current[$tag . '_attr']))
							{
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset ($current[$tag . '_attr']);
							}
						}
						$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
						$current = & $current[$tag][$last_item_index];
					}
				}
				elseif ($type == "complete")
				{
					if (!isset ($current[$tag]))
					{
						$current[$tag] = $result;
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $attributes_data)
							$current[$tag . '_attr'] = $attributes_data;
					}
					else
					{
						if (isset ($current[$tag][0]) and is_array($current[$tag]))
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
							if ($priority == 'tag' and $get_attributes and $attributes_data)
									$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							$repeated_tag_index[$tag . '_' . $level]++;
						}
						else
						{
							$current[$tag] = array ($current[$tag], $result); 
							$repeated_tag_index[$tag . '_' . $level] = 1;
							if ($priority == 'tag' and $get_attributes)
							{
								if (isset ($current[$tag . '_attr']))
								{ 
									$current[$tag]['0_attr'] = $current[$tag . '_attr'];
									unset ($current[$tag . '_attr']);
								}
								if ($attributes_data)
									$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
							$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
						}
					}
				}
				elseif ($type == 'close') $current = & $parent[$level -1];
			}
			return ($xml_array);
		}
		return false;
	}
	/* Read XML file */
	protected function __readXML($document, $custom_url)
	{
		if($custom_url)
		{
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $document);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ((bool) get_option("cf_geo_enable_ssl")));
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int)get_option("cf_geo_connection_timeout"));
			curl_setopt($ch, CURLOPT_TIMEOUT, (int)get_option("cf_geo_timeout"));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/xml'));
			
			$data = curl_exec($ch);
			curl_close($ch);
			
			$xml = simplexml_load_string($data);
			return $xml;
		}
		
		$contents = "";
		if (!function_exists('xml_parser_create'))return array ();
		$parser = xml_parser_create('');
		if (!($fp = @ fopen(($custom_url==true?'':$this->root).$document, 'r'))) return array ();
		while (!feof($fp)) $contents .= fread($fp, (int)$this->fread_length);
		fclose($fp);
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'utf-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		if (!$xml_values) return false;
		return $xml_values;
	}
}