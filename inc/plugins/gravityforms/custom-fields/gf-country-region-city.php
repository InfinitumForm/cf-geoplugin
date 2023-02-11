<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }

if( !class_exists( 'CFGP__Plugin__gravityforms__GF_Country_Region_City' ) ):
class CFGP__Plugin__gravityforms__GF_Country_Region_City extends GF_Field {

    public $type = 'cfgp_gf_country_region_city';

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
        );
    }
	
	public function is_value_submission_array() {
		return true;
	}
	
	public function get_form_editor_button() {
		return [
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
			'icon' => 'gform-icon--multi-select'
		];
	}
	
	public function get_form_editor_field_title() {
		return esc_attr__('Select Country Region City', 'cf-geoplugin');
	}
	
	
	public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead) {
		return empty($value) ? '' : serialize($value);
	}
	
	public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen') {
		$value = maybe_unserialize($value);		
		if (empty($value)) {
			return esc_attr__('(empty)', 'cf-geoplugin');
		} else {
			$value = array_map(function($s){
				return mb_convert_encoding($s, 'UTF-8', mb_detect_encoding($s, 'UTF-8, ISO-8859-1', true));			
			}, $value);
		}
		$str = $this->prettyListOutput($value);
		return $str;
	}
	
	private function prettyListOutput($value) {
		$countries = CFGP_Library::get_countries();
		$value[0] = $countries[strtolower($value[0])] ?? NULL;
		$value = array_filter($value);
		return join(', ', $value);
	}

    public function get_field_input( $form, $value = '', $entry = null ) {
		/*if ($this->is_form_editor()) {
			return '';
		}*/
		
		wp_enqueue_script(CFGP_NAME . '-gform-cfgp');
		
        $form_id = $form['id'];

        $id = absint( $this->id );

		$field_id = "input_{$id}";

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

		ob_start();
		if( (sanitize_text_field($_GET['gf_page'] ?? '') === 'preview') || is_admin() ) : ?>
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
<div class="ginput_container ginput_container_list ginput_list cfgp_gfield_group cfgp_gfield_group__autocomplete_location" id="cfgp_gfield_group_{{form_id}}">
	<table class="gfield_list gfield_list_container">
		<colgroup>
			<col id="gfield_list_{{form_id}}_col_1" class="gfield_list_col gfield_list_col_odd">
			<col id="gfield_list_{{form_id}}_col_2" class="gfield_list_col gfield_list_col_even">
			<col id="gfield_list_{{form_id}}_col_3" class="gfield_list_col gfield_list_col_odd">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e('Country', 'cf-geoplugin'); ?></th>
				<th scope="col"><?php esc_html_e('Region', 'cf-geoplugin'); ?></th>
				<th scope="col"><?php esc_html_e('City', 'cf-geoplugin'); ?></th>
			</tr>
		</thead>
		<tbody> 
			<tr class="gfield_list_row gfield_list_row_odd">
				<td class="gfield_list_cell gfield_list_{{form_id}}_cell1" data-label="country">
					<select class="widefat gfield_select gfield_select_country" id="{{field_id}}_1" name="{{field_id}}[]">
						<?php
						foreach ( $countries as $country_code => $country ) :
						$country_code = strtoupper($country_code);
						?>
						<option value="<?php echo esc_attr( $country_code ); ?>" <?php echo selected( ($value[0] ?? $default_country), $country_code, false ); ?>><?php echo esc_html( $country ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td class="gfield_list_cell gfield_list_{{form_id}}_cell2" data-label="region">
					<select class="widefat gfield_select gfield_select_region" id="{{field_id}}_2" name="{{field_id}}[]">
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
				</td>
				<td class="gfield_list_cell gfield_list_{{form_id}}_cell3" data-label="city">
					<select class="widefat gfield_select gfield_select_city" id="{{field_id}}_3" name="{{field_id}}[]">
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
				</td>
			</tr>
		</tbody>
	</table>
</div>
		<?php return strtr(
			ob_get_clean(),
			[
				'{{form_id}}' => esc_attr($form_id),
				'{{field_id}}' => esc_attr($field_id)
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