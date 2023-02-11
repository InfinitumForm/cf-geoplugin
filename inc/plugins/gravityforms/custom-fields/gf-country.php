<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }

if( !class_exists( 'CFGP__Plugin__gravityforms__GF_Country' ) ):
class CFGP__Plugin__gravityforms__GF_Country extends GF_Field {

    public $type = 'cfgp_gf_country';

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
	
	public function get_form_editor_button() {
		return [
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
			'icon' => 'gform-icon--place'
		];
	}
	
	public function get_form_editor_field_title() {
		return esc_attr__('Select Country', 'cf-geoplugin');
	}
	
	public function get_choices() {
		return CFGP_Library::get_countries();
	}

    public function get_field_input( $form, $value = '', $entry = null ) {
        $form_id         = $form['id'];
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $id       = absint( $this->id );
        $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        $value    = esc_attr( $value );

        $choices = $this->get_choices();
        if ( ! empty( $choices ) ) {
            $select = '<select name="' . esc_attr( $field_id ) . '" id="' . esc_attr( $field_id ) . '" class="widefat cfgp-gf-input cfgp-gf-select cfgp-gf-input-' . esc_attr( $field_id ) . '">';
            foreach ( $choices as $country_code => $country ) {
                $select .= '<option value="' . esc_attr( $country_code ) . '"' . selected( $value, $country_code, false ) . '>' . esc_html( $country ) . '</option>';
            }
            $select .= '</select>';
        }

        return $select;
    }

}
endif;