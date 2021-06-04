<?php
/**
 * Web Forms
 *
 * Input, select, textarea and special forms
 *
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Form')) :
class CFGP_Form {
	
	// Select HTTP code
	public static function select_http_code($attr = array(), $selected = '', $echo = true){
		$http_forms = CFGP_U::get_http_codes();
		$return = self::select($http_forms, $attr, $selected, false);
		if($echo) {
			echo $return;
		} else {
			return $return;
		}
	}
	
	// Select countries
	public static function select_countries($attr = array(), $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = array();
		} else {
			$options = array(
				'' => '-'
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose countries...', CFGP_NAME );
			}
		}
		if($data = get_terms(array(
			'taxonomy'		=> 'cf-geoplugin-country',
			'hide_empty'	=> false
		))){
			foreach( $data as $key => $fetch ){
				$options[$fetch->slug] = $fetch->name.' - '.$fetch->description;
			}
		}
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		if($echo) {
			echo $return;
		} else {
			return $return;
		}
	}
	
	// Select regions
	public static function select_regions($attr = array(), $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = array();
		} else {
			$options = array(
				'' => '-'
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose regions...', CFGP_NAME );
			}
		}
		if($data = get_terms(array(
			'taxonomy'		=> 'cf-geoplugin-region',
			'hide_empty'	=> false
		))){
			foreach( $data as $key => $fetch ){
				$options[$fetch->slug] = $fetch->name.' - '.$fetch->description;
			}
		}
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		if($echo) {
			echo $return;
		} else {
			return $return;
		}
	}
	
	// Select cities
	public static function select_cities($attr = array(), $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = array();
		} else {
			$options = array(
				'' => '-'
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose cities...', CFGP_NAME );
			}
		}
		if($data = get_terms(array(
			'taxonomy'		=> 'cf-geoplugin-city',
			'hide_empty'	=> false
		))){
			foreach( $data as $key => $fetch ){
				$options[$fetch->slug] = $fetch->name;
			}
		}
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		if($echo) {
			echo $return;
		} else {
			return $return;
		}
	}
	
	// Select postcodes
	public static function select_postcodes($attr = array(), $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = array();
		} else {
			$options = array(
				'' => '-'
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose postcodes...', CFGP_NAME );
			}
		}
		if($data = get_terms(array(
			'taxonomy'		=> 'cf-geoplugin-postcode',
			'hide_empty'	=> false
		))){
			foreach( $data as $key => $fetch ){
				$options[$fetch->slug] = $fetch->name;
			}
		}
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		if($echo) {
			echo $return;
		} else {
			return $return;
		}
	}
	
	// Multiple select
	public static function select_multiple($options=array(), $attr = array(), $selected = '', $echo = true){
		$options_render = array();
		
		if(!empty($options) && is_array($options))
		{
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose options...', CFGP_NAME );
			}
			
			if(!isset($attr['id'])){
				if(!isset($attr['name'])){
					$attr['name']='cfgp_form_select_multiple_'.CFGP_U::generate_token(mt_rand(10,15));
				}
				$attr['id'] = $attr['name'];	
			}
			
			if(!isset($attr['name'])){
				$attr['name']='cfgp_form_select_multiple_'.CFGP_U::generate_token(mt_rand(10,15));
			}
			$attr['name']=$attr['name'].'[]';
			
			if(isset($attr['class'])){
				$attr['class'] = $attr['class'] . ' chosen-select';
			} else {
				$attr['class'] = 'chosen-select';
			}
			
			foreach($options as $val=>$name)
			{
				$find = array_map( 'trim', explode( ']|[', $selected ) );
				$current = (in_array($val, $find) ? ' selected' : '');
				$options_render[] = apply_filters('cfgp/form/select_multiple/option/raw', "<option value=\"{$val}\"{$current}>{$name}</option>", $val, $name, $selected);
			}
		}
		
		$options_render = apply_filters('cfgp/form/select_multiple/options', $options_render, $options);
		$select = apply_filters('cfgp/form/select_multiple/raw', '<select %1$s multiple>%2$s</select>');
		$attr = self::parse_attributes($attr);
		$options_render = join(PHP_EOL, $options_render);
		$select = apply_filters('cfgp/form/select_multiple', sprintf($select, $attr, $options_render), $attr, $options_render);
		
		if($echo) {
			echo $select;
		} else {
			return $select;
		}
	}
	
	// Select option
	public static function select($options=array(), $attr = array(), $selected = '', $echo = true){
		$options_render = array();
		
		if(!empty($options) && is_array($options))
		{
			foreach($options as $val=>$name)
			{
				$current = ($selected == $val ? ' selected' : '');
				$options_render[] = apply_filters('cfgp/form/select/option/raw', "<option value=\"{$val}\"{$current}>{$name}</option>", $val, $name, $selected);
			}
		}
		
		$options_render = apply_filters('cfgp/form/select/options', $options_render, $options);
		$select = apply_filters('cfgp/form/select/raw', '<select %1$s>%2$s</select>');
		$attr = self::parse_attributes($attr);
		$options_render = join(PHP_EOL, $options_render);
		$select = apply_filters('cfgp/form/select', sprintf($select, $attr, $options_render), $attr, $options_render);
		
		if($echo) {
			echo $select;
		} else {
			return $select;
		}
	}
	
	// Checkbox
	public static function checkbox($options=array(), $attr = array(), $checked = '', $disabled = '', $echo = true){
		$input_radio = array();
		
		if(!empty($options) && is_array($options))
		{
			if(!isset($attr['id'])){
				if(!isset($attr['name'])){
					$attr['name']='cfgp_form_checkbox_'.CFGP_U::generate_token(mt_rand(10,15));
				}
				$attr['id'] = $attr['name'];	
			}
			
			$attr['type'] = 'checkbox';
			
			$id = $attr['id'];
			$i = 0;
			foreach($options as $val=>$name)
			{
				$attr['id'] = $id . '-' . $i;
				$attr['value'] = $val;
				$attributes = self::parse_attributes($attr);
				$control = ($checked === $val ? ' checked' : '').(($disabled === true) || ($disabled === $val) ? ' disabled' : '');
				$radio = "<input {$attributes}{$control}> <span>{$name}</span>";
				$input_radio[] = apply_filters('cfgp/form/checkbox/input/raw', $radio, $val, $name, $control);
				++$i;
			}
		}
		
		$options_render = apply_filters('cfgp/form/checkbox/inputs', $input_radio, $options);
		$input_radio = join(PHP_EOL, $input_radio);
		$return = apply_filters('cfgp/form/checkbox', "<span class=\"input-checkbox\">{$input_radio}</span>", $attr, $input_radio);

		if($echo) {
			echo $return;
		} else {
			return $return;
		}
	}
	
	// Radio buttons
	public static function radio($options=array(), $attr = array(), $checked = '', $disabled = '', $echo = true){
		$input_radio = array();
		
		if(!empty($options) && is_array($options))
		{
			if(!isset($attr['id'])){
				if(!isset($attr['name'])){
					$attr['name']='cfgp_form_radio_'.CFGP_U::generate_token(mt_rand(10,15));
				}
				$attr['id'] = $attr['name'];	
			}
			
			$attr['type'] = 'radio';
			
			$id = $attr['id'];
			$i = 0;
			foreach($options as $val=>$name)
			{
				$attr['id'] = $id . '-' . $i;
				$attr['value'] = $val;
				$attributes = self::parse_attributes($attr);
				$control = ($checked === $val ? ' checked' : '').(($disabled === true) || ($disabled === $val) ? ' disabled' : '');
				$radio = "<input {$attributes}{$control}> <span>{$name}</span>";
				$input_radio[] = apply_filters('cfgp/form/radio/input/raw', $radio, $val, $name, $control);
				++$i;
			}
		}
		
		$options_render = apply_filters('cfgp/form/radio/inputs', $input_radio, $options);
		$input_radio = join(PHP_EOL, $input_radio);
		$return = apply_filters('cfgp/form/radio', "<span class=\"input-radio\">{$input_radio}</span>", $attr, $input_radio);

		if($echo) {
			echo $return;
		} else {
			return $return;
		}
	}
	
	// Input feld
	public static function input($type, $attr, $disabled = false, $readonly = false, $echo = true){
		if(!in_array($type, array('date','datetime-local','email','file','hidden','image','month','number','password','radio','range','search','tel','text','time','url','week'))){
			$type = 'text';
		}
		
		if(isset($attr['type'])){
			unset($attr['type']);
		}
		$attr = self::parse_attributes($attr);
		$attr = $attr.($disabled ? ' disabled' : '').($readonly ? ' readonly' : '');
		
		$input = "<input type=\"{$type}\" {$attr}>";
		
		if($echo) {
			echo $input;
		} else {
			return $input;
		}
	}
	
	// Parse input attributes
	public static function parse_attributes($attr, $quote='"', $echo = false){
		$attributes = array();
		if(!empty($attr) && is_array($attr))
		{
			if(isset($attr['id'])){
				if(!isset($attr['name'])){
					$attr['name']=$attr['id'];
				}
			} else {
				if(!isset($attr['name'])){
					$attr['name']='cfgp_form_select_'.CFGP_U::generate_token(mt_rand(10,15));
				}
				$attr['id']=$attr['name'];
			}
			
			foreach($attr as $key=>$val)
			{
				$attributes[] = apply_filters('cfgp/form/parse_attributes/raw', "{$key}={$quote}{$val}{$quote}");
			}
		}
		$attributes = apply_filters('cfgp/form/parse_attributes', $attributes);
		$attributes = join(' ', $attributes);
		
		if($echo) {
			echo $attributes;
		} else {
			return $attributes;
		}
	}
}
endif;