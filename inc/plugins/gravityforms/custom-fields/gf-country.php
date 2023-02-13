<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }

if( !class_exists( 'CFGP__Plugin__gravityforms__GF_Country' ) ):
class CFGP__Plugin__gravityforms__GF_Country extends GF_Field {

    public $type = 'cfgp_gf_country';

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
			'icon' => 'gform-icon--list'
		];
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
		return 'gform-icon--list';
	}
	
	/**
	 * Returns the field title.
	 *
	 * @since 2.4
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__('Select Country', 'cf-geoplugin');
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
		}
		$countries = CFGP_Library::get_countries();
		$value = $countries[strtolower($value)] ?? esc_attr__('(empty)', 'cf-geoplugin');
		return $value;
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
		
        if ( ! empty( $countries ) ) {
			$select = ['<div class="ginput_container ginput_container_select">'];
            $select[]= '<select name="' . esc_attr( $field_id ) . '" id="' . esc_attr( $select_form_id ) . '" class="' . esc_attr( $css_class ) . ' cfgp-gf-select cfgp-gf-input-' . esc_attr( $field_id ) . '"' . $tabindex . $required_attribute . $invalid_attribute . $disabled . '>';
            foreach ( $countries as $country_code => $country ) {
				$country_code = strtoupper( $country_code );
                $select[]= '<option value="' . esc_attr( $country_code ) . '"' . selected( $value, $country_code, false ) . '>' . esc_html( $country ) . '</option>';
            }
            $select[]= '</select>';
			$select[]= '</div>';
        }

        return join("\n\r", $select);
    }

}
endif;