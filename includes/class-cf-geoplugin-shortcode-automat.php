<?php
/**
 * class ShortCodeAutomat
 */
if(!class_exists('CF_Geoplugin_Shortcode_Automat')) :
class CF_Geoplugin_Shortcode_Automat
{
    protected $settings = array();

    public function  __construct( $settings = array() )
    {
        $this->settings = $settings;
    }

    public function __call( $name, $arguments )
    {
        if( in_array( $name, array_keys( $this->settings ) ) )
        {
            return $this->settings[$name];
        }
    }

    public function generate()
    {
        foreach( $this->settings as $shortcode => $option )
        {
            add_shortcode( $shortcode, array(&$this, $shortcode));
        }
    }
}
endif;