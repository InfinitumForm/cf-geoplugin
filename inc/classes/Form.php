<?php
/**
 * Web Forms
 *
 * Input, select, textarea and special forms
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       3.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Form')) :
class CFGP_Form {
	
	// Select HTTP code
	public static function select_http_code($attr = [], $selected = '', $echo = true){
		$http_forms = CFGP_U::get_http_codes();
		$return = self::select($http_forms, $attr, $selected, false);
		if($echo) {
			echo wp_kses_post($return);
		} else {
			return $return;
		}
	}
	
	// Select countries
	public static function select_countries($attr = [], $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = [];
		} else {
			$options = array(
				'' => ''
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose countries...', 'cf-geoplugin');
			}
		}
		if($data = CFGP_Library::get_countries()){
			foreach( $data as $country_code => $country ){
				$options[$country_code] = strtoupper($country_code).' - '.$country;
			}
		}
		
		if(isset($attr['class'])){
			$attr['class'] = trim($attr['class']) . ' cfgp-select-country';
		} else {
			$attr['class'] = 'cfgp-select-country';
		}
		
		$attr = array_merge($attr, array(
			'data-type' => 'country',
			'data-country_codes' => (is_array($selected) ? join(',', $selected) : $selected)
		));
		
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		if($echo) {
			echo wp_kses_post($return);
		} else {
			return $return;
		}
	}
	
	// Select regions
	public static function select_regions($attr = [], $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = [];
		} else {
			$options = array(
				'' => ''
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose regions...', 'cf-geoplugin');
			}
		}
		
		if(!isset($attr['country_code'])){
			$attr['country_code'] = [];
		}
		
		if($data = CFGP_Library::get_regions($attr['country_code'])){
			foreach( $data as $key => $fetch ){
				$options[sanitize_title( CFGP_U::transliterate($fetch) )] = $fetch;
			}
		}
		
		if(isset($attr['class'])){
			$attr['class'] = trim($attr['class']) . ' cfgp-select-region';
		} else {
			$attr['class'] = 'cfgp-select-region';
		}
		
		$attr = array_merge($attr, array(
			'data-type' => 'region',
			'data-country_codes' => (is_array($attr['country_code']) ? join(',', $attr['country_code']) : $attr['country_code'])
		));
		
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		
		if($echo) {
			echo wp_kses_post($return);
		} else {
			return $return;
		}
	}
	
	// Select cities
	public static function select_cities($attr = [], $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = [];
		} else {
			$options = array(
				'' => ''
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose cities...', 'cf-geoplugin');
			}
		}
		
		if(!isset($attr['country_code'])){
			$attr['country_code'] = [];
		}
		
		if(is_array($selected)){
			foreach($selected as $select){
				$new_name = explode('-', $select);
				$new_name = array_map('ucfirst', $new_name);
				$new_name = join(' ', $new_name);
				$options[sanitize_title( CFGP_U::transliterate($select) )] = $new_name;
			}
		} else if( !empty($selected) ) {
			$new_name = explode('-', $selected);
			$new_name = array_map('ucfirst', $new_name);
			$new_name = join(' ', $new_name);
			$options[sanitize_title( CFGP_U::transliterate($selected) )] = $new_name;
		}
		
		if($data = CFGP_Library::get_cities($attr['country_code'])){
			foreach( $data as $fetch ){
				$options[sanitize_title( CFGP_U::transliterate($fetch) )] = $fetch;
			}
		}
		
		if(isset($attr['class'])){
			$attr['class'] = trim($attr['class']) . ' cfgp-select-city';
		} else {
			$attr['class'] = 'cfgp-select-city';
		}
		
		$attr = array_merge($attr, array(
			'data-type' => 'city',
			'data-country_codes' => (is_array($attr['country_code']) ? join(',', $attr['country_code']) : $attr['country_code'])
		));
		
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		if($echo) {
			echo wp_kses_post($return);
		} else {
			return $return;
		}
	}
	
	// Select postcodes
	public static function select_postcodes($attr = [], $selected = '', $multiple=false, $echo = true){
		if($multiple) {
			$options = [];
		} else {
			$options = array(
				'' => ''
			);
		}
		if($multiple) {
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose postcodes...', 'cf-geoplugin');
			}
		}
		
		if(!isset($attr['country_code'])){
			$attr['country_code'] = [];
		}
		
		if(!empty($selected)) {
			if(is_array($selected)){
				foreach($selected as $select){
					$options[$select] = $select;
				}
			} else {
				$options[sanitize_title(CFGP_U::transliterate($selected))] = $selected;
			}
		}
		
		if($data = CFGP_Library::get_postcodes($attr['country_code'])){
			foreach( $data as $city=>$postcode ){
				$options[$postcode] = $postcode;
			}
		}
		
		if(isset($attr['class'])){
			$attr['class'] = trim($attr['class']) . ' cfgp-select-postcode';
		} else {
			$attr['class'] = 'cfgp-select-postcode';
		}
		
		$attr = array_merge($attr, array(
			'data-type' => 'postcode',
			'data-country_codes' => (is_array($attr['country_code']) ? join(',', $attr['country_code']) : $attr['country_code'])
		));
		
		if($multiple) {
			$return = self::select_multiple($options, $attr, $selected, false);
		} else {
			$return = self::select($options, $attr, $selected, false);
		}
		if($echo) {
			echo wp_kses_post($return);
		} else {
			return $return;
		}
	}
	
	// Multiple select
	public static function select_multiple($options=[], $attr = [], $selected = '', $echo = true){
		$options_render = [];
		
		if(empty($options)){
			$options = [];
		}
		
		if(is_array($options))
		{
			if(!isset($attr['data-placeholder'])) {
				$attr['data-placeholder'] = __( 'Choose options...', 'cf-geoplugin');
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
				$attr['class'] = trim($attr['class']) . ' cfgp_select2';
			} else {
				$attr['class'] = 'cfgp_select2';
			}

			foreach($options as $val=>$name)
			{
				if(is_array($selected)) {
					$find = array_map( 'trim', $selected );
				} else {
					if(preg_match('/\]\|\[/', $selected)) {
						$find = array_map( 'trim', explode( "]|[", $selected ) );
					} else {
						$find = array_map( 'trim', explode( ',', $selected ) );
					}
				}
				$current = (in_array($val, $find) ? ' selected' : '');

				$options_render[] = apply_filters(
					'cfgp/form/select_multiple/option/raw',
					"<option value=\"{$val}\"{$current}>{$name}</option>",
					$val,
					$name,
					$selected
				);
			}
		}
		
		$options_render = apply_filters('cfgp/form/select_multiple/options', $options_render, $options);
		$select = apply_filters('cfgp/form/select_multiple/raw', '<select %1$s multiple>%2$s</select>');
		$attr = self::parse_attributes($attr);

		$options_render = join(PHP_EOL, $options_render);
		$select = apply_filters('cfgp/form/select_multiple', sprintf($select, $attr, $options_render), $attr, $options_render);
		
		if($echo) {
			echo wp_kses_post($select);
		} else {
			return $select;
		}
	}
	
	// Select option
	public static function select($options=[], $attr = [], $selected = '', $echo = true){
		$options_render = [];
		
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
			echo wp_kses_post($select);
		} else {
			return $select;
		}
	}
	
	// Checkbox
	public static function checkbox($options=[], $echo = true){
		$input_radio = [];
		
		if(!empty($options) && is_array($options))
		{
			$i = 0;
			foreach($options as $name=>$option)
			{
				$attr = [];
				
				$attr['type'] = 'checkbox';
				$attr['name'] = $name;
				
				$name = $option['label'];
				
				$checked = false;
				if(isset($option['checked'])){
					$checked = $option['checked'];
				}
				
				$disabled = false;
				if(isset($option['disabled'])){
					$disabled = $option['disabled'];
				}
				
				$attr['value'] = '';
				if(isset($option['value'])){
					$attr['value'] = $option['value'];
				}
				
				if(isset($option['id'])){
					$attr['id'] = $option['id'];
				} else {
					$attr['id'] = 'cfgp_form_checkbox_' . CFGP_U::generate_token(mt_rand(10,15)) . "-{$i}";
				}
				
				$label = '';
				if(isset($option['label'])){
					$label = $option['label'];
				}
				
				$attributes = self::parse_attributes($attr);

				$control = ($checked ? ' checked' : '').(($disabled === true) ? ' disabled' : '');
				$radio = "<input {$attributes}{$control}> <span>{$label}</span>";
				$input_radio[] = apply_filters('cfgp/form/checkbox/input/raw', $radio, $attr['value'], $name, $control);
				++$i;
			}
		}
		
		$options_render = apply_filters('cfgp/form/checkbox/inputs', $input_radio, $options);
		$input_radio = join(PHP_EOL, $input_radio);
		$return = apply_filters('cfgp/form/checkbox', "<span class=\"input-checkbox\">{$input_radio}</span>", $options, $input_radio);

		if($echo) {
			echo wp_kses_post($return);
		} else {
			return $return;
		}
	}
	
	// Radio buttons
	public static function radio($options=[], $attr = [], $checked = '', $disabled = '', $echo = true){
		$input_radio = [];
		
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
				$label_class = 'cfgp-form-radio' . $control;
				$radio = "<label for=\"{$attr['id']}\" class=\"{$label_class}\"><input {$attributes}{$control}> <span>{$name}</span></label>";
				$input_radio[] = apply_filters('cfgp/form/radio/input/raw', $radio, $val, $name, $control);
				++$i;
			}
		}
		
		$options_render = apply_filters('cfgp/form/radio/inputs', $input_radio, $options);
		$input_radio = join(PHP_EOL, $input_radio);
		$return = apply_filters('cfgp/form/radio', "<span class=\"input-radio\">{$input_radio}</span>", $attr, $input_radio);

		if($echo) {
			echo wp_kses_post($return);
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
			echo wp_kses_post($input);
		} else {
			return $input;
		}
	}
	
	// Parse input attributes
	public static function parse_attributes($attr, $quote='"', $echo = false){
		
		if(isset($attr['country_code'])) unset($attr['country_code']);
		
		$attributes = [];
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
			echo wp_kses_post($attributes);
		} else {
			return $attributes;
		}
	}
}
endif;