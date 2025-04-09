<?php

if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

do_action('cfgp/page/seo_redirection/response');

if (CFGP_U::request_string('action') === 'import' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-import-csv') !== false) {
    do_action('cfgp/page/seo_redirection/import');
} elseif (CFGP_U::request_string('action') === 'new' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-new') !== false) {
    do_action('cfgp/page/seo_redirection/form');
} elseif (CFGP_U::request_string('action') === 'edit' && wp_verify_nonce(CFGP_U::request_string('nonce'), CFGP_NAME.'-seo-edit') !== false) {
    do_action('cfgp/page/seo_redirection/form');
} else {
    do_action('cfgp/page/seo_redirection/table');
}
