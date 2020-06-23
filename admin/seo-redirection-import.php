<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page SEO Import
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 *
**/
$CFGEO = $GLOBALS['CFGEO'];
wp_enqueue_media();
?>
<div class="clearfix"></div>
<div class="container mt-5 pb-5">
    <div id="alert-success"></div>
    <div id="alert-fail"></div>
    <div class="card mx-auto mt-5">
        <div class="card-header bg-transparent">
            <?php _e( 'SEO Redirection Upload', CFGP_NAME ); ?>
            <a href="<?php echo CF_Geoplugin_Global::add_admin_url( 'admin.php?page='. $_GET['page'] .'&action=export_csv'); ?>" id="backup-seo" class="btn btn-outline-secondary btn-sm pull-right"><span class="fa fa-download"></span> <?php _e( 'Backup Prevous Settings', CFGP_NAME); ?></a>
        </div>
        <div class="card-footer bg-transparent">
            <p><?php _e( 'If you want to make large amounts of redirects easier, we give you this option. Here you can easily enter a thousand redirects by the rules you define in your CSV file with just a few clicks. Before proceeding with this, you need to be informed about the structure of the CSV file that we expect.', CFGP_NAME); ?></p>
            <p><strong><?php _e( 'Please carefully follow this manual to avoid unnecessary problems and waste of time.', CFGP_NAME); ?></strong></p>
            <p><?php _e( 'The file must be a standard comma separated CSV with a maximum of 7 columns. The order of the column is extremely important and its content is strict. If you do not follow the format and column order, CSV will be rejected.', CFGP_NAME); ?></p>
            <dl class="row mb-3">
                <dt class="col-sm-3 text-right"><?php _e( 'Column 1', CFGP_NAME); ?>:</dt>
                <dd class="col-sm-9"><?php _e( 'Country Code - standard 2 letter country code' ); ?> (<?php printf(__('example: %s', CFGP_NAME), $CFGEO['country_code']); ?>)</dd>
                <dt class="col-sm-3 text-right"><?php _e( 'Column 2', CFGP_NAME); ?>:</dt>
                <dd class="col-sm-9"><?php _e( 'Region Name', CFGP_NAME); ?> (<?php printf(__('example: %s', CFGP_NAME), $CFGEO['region']); ?>)</dd>
                <dt class="col-sm-3 text-right"><?php _e( 'Column 3', CFGP_NAME); ?>:</dt>
                <dd class="col-sm-9"><?php _e( 'City Name', CFGP_NAME); ?> (<?php printf(__('example: %s', CFGP_NAME), $CFGEO['city']); ?>)</dd>
                <dt class="col-sm-3 text-right"><?php _e( 'Column 4', CFGP_NAME); ?>:</dt>
                <dd class="col-sm-9"><?php _e( 'Redirect URL - valid URL format', CFGP_NAME); ?></dd>
                <dt class="col-sm-3 text-right"><?php _e( 'Column 5', CFGP_NAME); ?>:</dt>
                <dd class="col-sm-9"><?php _e( 'HTTP Status Code - Accept 301, 302, 303 and 404', CFGP_NAME); ?></dd>
                <dt class="col-sm-3 text-right"><?php _e( 'Column 6', CFGP_NAME); ?>:</dt>
                <dd class="col-sm-9"><?php _e( 'Active - Optional, accept integer (1-Enable, 0-Disable)', CFGP_NAME); ?></dd>
                <dt class="col-sm-3 text-right"><?php _e( 'Column 7', CFGP_NAME); ?>:</dt>
                <dd class="col-sm-9"><?php _e( 'Redirect only once - Optional, accept integer (1-Enable, 0-Disable)', CFGP_NAME); ?></dd>
            </dl>
        </div>
        <div class="card-body">
            <form id="import-form">
                <p><?php _e( 'This properly prepared CSV can either upload or place direct URL through the form below:', CFGP_NAME); ?></p>                
                <div class="input-group" id="csv-import">
                    <input type="text" name="import_file_url" class="form-control file-url" accept="csv">
                    <div class="input-group-append" id="button-addon4">
                        <button type="button" name="upload_btn" class="btn btn-secondary"><span class="fa fa-file"></span> <?php _e( 'Choose File', CFGP_NAME); ?></button>
                        <button type="button" name="upload_btn" class="btn btn-success"><span class="fa fa-database"></span> <?php _e( 'Import', CFGP_NAME); ?></button>
                    </div>
                </div>
                <p class="text-danger"><?php _e( 'PLEASE NOTE: After upload, all previous records will be deleted and new data will be placed. This process is impossible to avoid and, therefore, it is important to make a preliminary backup. Please use backup button at top of this page for that action.', CFGP_NAME); ?></p> 
            </form>
            <table class="table table-striped mt-3 bg-white border-white w-100" id="failed-import-table"></table>
        </div>
    </div>
</div>