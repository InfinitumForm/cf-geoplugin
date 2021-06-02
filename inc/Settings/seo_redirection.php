<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(CFGP_U::request_string('action') === 'new' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-new') !== false){
	do_action('cfgp/page/seo_redirection/new');
} else if(CFGP_U::request_string('action') === 'edit' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-edit') !== false) {
	do_action('cfgp/page/seo_redirection/edit');
} else if(CFGP_U::request_string('action') === 'delete' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-delete') !== false) {
	/* TO DO delete */
} else {
	do_action('cfgp/page/seo_redirection/table');
}