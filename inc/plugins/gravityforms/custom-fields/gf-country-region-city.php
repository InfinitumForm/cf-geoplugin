<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }

if( !class_exists( 'CFGP__Plugin__gravityforms__GF_Country_Region_City', false ) ):
class CFGP__Plugin__gravityforms__GF_Country_Region_City extends GF_Field {

	/**
	 * Declare the field type.
	 *
	 * @since 2.4
	 *
	 * @var string
	 */
    public $type = 'cfgp_gf_country_region_city';

	/**
	 * Returns the class names of the settings which should be available on the field in the form editor.
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
    public function get_form_editor_field_settings() {
        return array(
        //    'choices_setting',
            'description_setting',
            'label_setting',
            'admin_label_setting',
            'rules_setting',
            'error_message_setting',
            'visibility_setting',
            'label_placement_setting',
            'description_placement_setting',
            'conditional_logic_field_setting',
            'css_class_setting',
			'size_setting'
        );
    }
	
	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a gform-icon class.
	 *
	 * @since 2.5
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return 'gform-icon--place';
	}
	
	public function is_value_submission_array() {
		return true;
	}
	
	/**
	 * Returns the field button properties for the form editor. The array contains two elements:
	 * 'group' => 'standard_fields' // or  'advanced_fields', 'post_fields', 'pricing_fields'
	 * 'text'  => 'Button text'
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return [
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
			'icon' => 'gform-icon--place'
		];
	}
	
	/**
	 * Returns the field title.
	 *
	 * @since 2.4
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__('Select Country Region City', 'cf-geoplugin');
	}
	
	/**
	 * Sanitize and format the value before it is saved to the Entry Object.
	 * We also add the value of inputs .2 and .3 here since they are not displayed in the form.
	 *
	 * @since 2.4
	 *
	 * @param string $value      The value to be saved.
	 * @param array  $form       The Form Object currently being processed.
	 * @param string $input_name The input name used when accessing the $_POST.
	 * @param int    $lead_id    The ID of the Entry currently being processed.
	 * @param array  $lead       The Entry Object currently being processed.
	 *
	 * @return array|string The safe value.
	 */
	public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead) {
		return empty($value) ? '' : serialize($value);
	}
	
	/**
	 * Format the entry value for display on the entry detail page and for the {all_fields} merge tag.
	 *
	 * @since 2.4
	 *
	 * @param string|array $value    The field value.
	 * @param string       $currency The entry currency code.
	 * @param bool|false   $use_text When processing choice based fields should the choice text be returned instead of the value.
	 * @param string       $format   The format requested for the location the merge is being used. Possible values: html, text or url.
	 * @param string       $media    The location where the value will be displayed. Possible values: screen or email.
	 *
	 * @return string
	 */
	public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen') {
		return $this->prettyListOutput($value);
	}
	
	/**
	 * Format the entry value for display on the entry list.
	 *
	 * @since 2.4
	 *
	 * @param string|array $value    The field value.
	 * @param array        $entry    Current entry.
	 * @param string       $field_id Field indentifier.
	 * @param array        $columns  Array of the columns
	 * @param array        $form     The Form Object currently being processed.
	 *
	 * @return string
	 */
	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
		return $this->prettyListOutput($value);
	}
	
	private function prettyListOutput($value) {
		$value = maybe_unserialize($value);		
		if (empty($value)) {
			return esc_attr__('(empty)', 'cf-geoplugin');
		} else {
			$value = array_map(function($s){
				return mb_convert_encoding($s, 'UTF-8', mb_detect_encoding($s, 'UTF-8, ISO-8859-1', true));			
			}, $value);
		}
		$countries = CFGP_Library::get_countries();
		$value[0] = $countries[strtolower($value[0])] ?? NULL;
		$value = array_filter($value);
		return join(', ', $value);
	}

	/**
	 * Returns the field inner markup.
	 *
	 * @since 2.4
	 *
	 * @param array      $form  The Form Object currently being processed.
	 * @param array      $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
    public function get_field_input( $form, $value = '', $entry = null ) {

		wp_enqueue_script(CFGP_NAME . '-gform-cfgp');
		wp_enqueue_style(CFGP_NAME . '-gform-cfgp');
		
        $form_id = $form['id'];
		$id = absint( $this->id );
		$field_id = "input_{$id}";
		$select_form_id = "input_{$form_id}_{$id}";

		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$disabled = ($is_form_editor ? ' disabled="disabled"' : '');
		$size                   = $this->size;
		$class_suffix           = $is_entry_detail ? '_admin' : '';
		$class                  = $size . $class_suffix;
		$css_class              = trim( esc_attr( $class ) . ' gfield_select' );
		
		$tabindex               = $this->get_tabindex();
		$required_attribute     = $this->isRequired ? ' aria-required="true"' : '';
		$invalid_attribute      = $this->failed_validation ? ' aria-invalid="true"' : ' aria-invalid="false"';

        $countries = CFGP_Library::get_countries();		
		$value = maybe_unserialize($value);

		if(empty($value)) {
			$value = [];
		} else {
			$value = array_map(function($s){
				return mb_convert_encoding($s, 'UTF-8', mb_detect_encoding($s, 'UTF-8, ISO-8859-1', true));			
			}, $value);
		}
		
		$default_country = CFGP_U::api('country_code', strtoupper(array_key_first($countries)));
		
		$placeholder_attribute = $this->get_field_placeholder_attribute();

		ob_start();
		if( (sanitize_text_field($_GET['gf_page'] ?? '') === 'preview') || is_admin() || $is_form_editor ) : ?>
<style>
	.cfgp_gfield_group > table.gfield_list,
	.cfgp_gfield_group > table.gfield_list > tbody td > select{
		width:100%;
	}
	.cfgp_gfield_group > table.gfield_list > thead th{
		text-align:left;
	}
	.cfgp_gfield_group > table.gfield_list > colgroup > .gfield_list_col{
		width: calc(100%/3);
	}
</style>
<?php endif; ?>
<div class="ginput_container ginput_container_list ginput_list cfgp_gfield_group cfgp_gfield_group__autocomplete_location" id="cfgp_gfield_group_{{form_id}} {{class}}">
	<table class="gfield_list gfield_list_container {{class}}">
		<colgroup>
			<col id="gfield_list_{{form_id}}_col_1" class="gfield_list_col gfield_list_col_odd">
			<col id="gfield_list_{{form_id}}_col_2" class="gfield_list_col gfield_list_col_even">
			<col id="gfield_list_{{form_id}}_col_3" class="gfield_list_col gfield_list_col_odd">
		</colgroup>
		<thead>
			<tr>
				<th scope="col" class="{{class}}"><?php esc_html_e('Country', 'cf-geoplugin'); ?></th>
				<th scope="col" class="{{class}}"><?php esc_html_e('Region', 'cf-geoplugin'); ?></th>
				<th scope="col" class="{{class}}"><?php esc_html_e('City', 'cf-geoplugin'); ?></th>
			</tr>
		</thead>
		<tbody> 
			<tr class="gfield_list_row gfield_list_row_odd">
				<td class="gfield_list_cell gfield_list_{{form_id}}_cell1 {{class}}" data-label="country">
					<div class="ginput_container ginput_container_select">
						<select class="{{css_class}} gfield_select_country" id="{{select_form_id}}" name="{{field_id}}[]"<?php echo $placeholder_attribute; ?>{{tabindex}}{{invalid_attribute}}{{required_attribute}}{{disabled}}>
							<?php
							foreach ( $countries as $country_code => $country ) :
							$country_code = strtoupper($country_code);
							?>
							<option value="<?php echo esc_attr( $country_code ); ?>" <?php echo selected( ($value[0] ?? $default_country), $country_code, false ); ?>><?php echo esc_html( $country ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</td>
				<td class="gfield_list_cell gfield_list_{{form_id}}_cell2 {{class}}" data-label="region">
					<div class="ginput_container ginput_container_select">
						<select class="{{css_class}} gfield_select_region" id="{{select_form_id}}_region" name="{{field_id}}[]"<?php echo $placeholder_attribute; ?>{{tabindex}}{{invalid_attribute}}{{required_attribute}}{{disabled}}>
							<?php
							if( $regions = CFGP_Library::get_regions(strtolower($value[0] ?? $default_country)) ) :
							foreach ( $regions as $i => $region ) :
							?>
							<option value="<?php echo esc_attr( $region ); ?>" <?php echo selected( ($value[1] ?? CFGP_U::api('region')), $region, false ); ?>><?php echo esc_html( $region ); ?></option>
							<?php endforeach; ?>
							<?php else : ?>
							<option value="" selected disabled><?php esc_html_e('No regions!', 'cf-geoplugin'); ?></doption>
							<?php endif; ?>
						</select>
					</div>
				</td>
				<td class="gfield_list_cell gfield_list_{{form_id}}_cell3 {{class}}" data-label="city">
					<div class="ginput_container ginput_container_select">
						<select class="{{css_class}} gfield_select_city" id="{{select_form_id}}_city" name="{{field_id}}[]"<?php echo $placeholder_attribute; ?>{{tabindex}}{{invalid_attribute}}{{required_attribute}}{{disabled}}>
							<?php
							if( $cities = CFGP_Library::get_cities(strtolower($value[0] ?? $default_country)) ) :
							foreach ( $cities as $i => $city ) :
							?>
							<option value="<?php echo esc_attr( $city ); ?>" <?php echo selected( ($value[2] ?? CFGP_U::api('city')), $city, false ); ?>><?php echo esc_html( $city ); ?></option>
							<?php endforeach; ?>
							<?php else : ?>
							<option value="" selected disabled><?php esc_html_e('No cities!', 'cf-geoplugin'); ?></doption>
							<?php endif; ?>
						</select>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
		<?php return strtr(
			ob_get_clean(),
			[
				'{{form_id}}' => esc_attr($form_id),
				'{{field_id}}' => esc_attr($field_id),
				'{{css_class}}' => esc_attr($css_class),
				'{{class}}' => esc_attr($class),
				'{{disabled}}' => $disabled,
				'{{invalid_attribute}}' => $invalid_attribute,
				'{{required_attribute}}' => $required_attribute,
				'{{tabindex}}' => $tabindex,
				'{{select_form_id}}' => $select_form_id
			]
		);
    }
	
	public function is_value_submission_empty($form_id) {
		return empty( $this->get_record($form_id) );
	}
	
	private function get_record($form_id) {
		$id       = absint( $this->id );
		$field_id = "input_{$id}";
		return rgpost($field_id);
	}
}
endif;