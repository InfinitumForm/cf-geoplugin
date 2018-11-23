<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page SEO Redirection
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 *
**/

global $wpdb;
$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];

if( isset( $_GET['action'] ) && ( $_GET['action'] == 'add-new' || $_GET['action'] == 'edit' ) )
{
    if( file_exists( CFGP_ADMIN . '/seo-redirection-action.php' ) )
    {
        require_once CFGP_ADMIN . '/seo-redirection-action.php';
    }
}
elseif( isset( $_GET['action'] ) && $_GET['action'] == 'import_csv' )
{
	if(isset($CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv']) ? ($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv'] && CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) > 0) : (1 && CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) > 0))
	{
		if( file_exists( CFGP_ADMIN . '/seo-redirection-import.php' ) )
		{
			require_once CFGP_ADMIN . '/seo-redirection-import.php';
		}
	}
}
else
{
    if( isset( $_GET['action'] ) == 'delete' )
    {
        $id = $this->get( 'id', 'int' );
        $table_name = self::TABLE['seo_redirection'];
        $wpdb->query(
            $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$table_name} WHERE id = %s;", $id
        ));
    }

    $table_name = self::TABLE['seo_redirection'];

    if( isset( $_GET['page_num'] ) ) $page_num = $this->get( 'page_num', 'int' );
    else $page_num = 1;

    $records_per_page = 20;
    $offset = ($page_num-1) * $records_per_page;
    $redirection_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}{$table_name};" );
    $total_pages = ceil( $redirection_count / $records_per_page );
?>
<div class="clearfix"></div>
<div class="container-fluid">
    <div class="row">
    	<div class="col-12">
        	<h1 class="h5 mt-3"><i class="fa fa-location-arrow"></i> <?php _e('SEO Redirection',CFGP_NAME); ?></h1>
            <hr>
            <p><?php _e('Here you can setup default site redirection based on the geo-location of your visitors. This functionality allow you to redirect your visitors on the any custom location. Please use this carefuly and wise.',CFGP_NAME); ?></p>
        </div>
        <div class="col-12" id="alert"></div>
    </div>
    <div class="row">
		<div class="col-12">
        	<a class="btn btn-primary btn-sm" href="<?php echo self_admin_url( 'admin.php?page='. $_GET['page'] .'&action=add-new&page_num='. $page_num ); ?>"><span class="fa fa-plus"></span> <?php _e( 'Add new redirection', CFGP_NAME ); ?></a>
            <?php if(CF_Geoplugin_Global::access_level($CF_GEOPLUGIN_OPTIONS) > 0): ?>
				<?php if(isset($CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv']) ? ($CF_GEOPLUGIN_OPTIONS['enable_beta'] && $CF_GEOPLUGIN_OPTIONS['enable_beta_seo_csv']) : 1) : ?>
                <a class="btn btn-outline-secondary btn-sm pull-right ml-2 mr-2" href="<?php echo self_admin_url( 'admin.php?page='. $_GET['page'] .'&action=export_csv' ); ?>"><span class="fa fa-arrow-circle-right"></span> <?php _e( 'Export as CSV' ); ?></a>
                <a class="btn btn-success btn-sm pull-right" href="<?php echo self_admin_url( 'admin.php?page='. $_GET['page'] .'&action=import_csv' ); ?>"><span class="fa fa-file"></span> <?php _e( 'Import from CSV' ); ?></a>
                <?php endif; ?>
            <?php else :  ?>
            	<a class="btn btn-outline-secondary btn-sm pull-right ml-2 mr-2" href="javascript:void(0);" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" data-content="<?php _e('Export as CSV is enabled only with valid license.',CFGP_NAME); ?>"><span class="fa fa-arrow-circle-right"></span> <?php _e( 'Export as CSV' ); ?></a>
                <a class="btn btn-success btn-sm pull-right" href="javascript:void(0);" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" data-content="<?php _e('Import from CSV is enabled only with valid license.',CFGP_NAME); ?>"><span class="fa fa-file"></span> <?php _e( 'Import from CSV' ); ?></a>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <table width="100%" class="table table-striped mt-3 bg-white border-white">
                <thead>
                    <tr>
                        <th scope="col"><?php _e('URL',CFGP_NAME); ?></th>
                        <th scope="col"><?php _e('Country',CFGP_NAME); ?></th>
                        <th scope="col"><?php _e('Region',CFGP_NAME); ?></th>
                        <th scope="col"><?php _e('City',CFGP_NAME); ?></th>
                        <th class="text-center" scope="col"><?php _e('Status Code',CFGP_NAME); ?></th>
                        <th class="text-right" scope="col"><?php _e('Action',CFGP_NAME); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $redirects = $wpdb->get_results( "SELECT id, url, country, region, city, http_code, active FROM {$wpdb->prefix}{$table_name} LIMIT {$offset}, $records_per_page;", ARRAY_A );
                    if( $redirects !== NULL && $wpdb->num_rows > 0 )
                    {

                        foreach( $redirects as $redirect )
                        {
                            $country = get_term_by( 'slug', $redirect['country'], 'cf-geoplugin-country', ARRAY_A );
                            $region = get_term_by( 'slug', $redirect['region'], 'cf-geoplugin-region', ARRAY_A );
                            $city = get_term_by( 'slug', $redirect['city'], 'cf-geoplugin-city', ARRAY_A );
                            $disabled = ( (int)$redirect['active'] == 0 ? '<small class="text-danger">'. __( 'Disabled', CFGP_NAME ) .'</small>' : '' );
							
							$c_name = array_filter(array($country['name'], $country['description']));
							$r_name = array_filter(array($region['name'], $region['description']));
							$ct_name = array_filter(array($city['name'], $city['description']));
							
                            printf('
                                <tr id="cf-geoplugin-seo-redirection-%10$d">
                                    <td>
                                        <a href="%1$s">%2$s</a>
                                        %12$s
                                    </td>
                                    <td>%7$s</td>
                                    <td>%8$s</td>
                                    <td>%9$s</td>
									<td class="text-center">%11$s</td>
                                    <td class="text-right">
                                        <a class="btn btn-info btn-sm" href="%3$sadmin.php?page=%4$s&action=edit&id=%10$d&page_num=%13$d"><span class="fa fa-pencil"></span> %5$s</a>
                                        <a class="btn btn-danger btn-sm cf_geo_redirect_delete" href="%3$sadmin.php?page=%4$s&action=delete&id=%10$d&page_num=%13$d"><span class="fa fa-ban"></span> %6$s</a>
                                    </td> 
                                </tr>
								',
								$redirect['url'],
								$redirect['url'],
								self_admin_url(),
								$_GET['page'],
								__('Edit', CFGP_NAME),
								__('Delete', CFGP_NAME),
								end($c_name),
								end($r_name),
								end($ct_name),
								$redirect['id'], 
								$redirect['http_code'],
								$disabled,
								$page_num 
							);
                        }
                    }
                    else {
                    ?>
					<tr>
                        <td scope="col" colspan="6"><?php _e('There is not yet SEO redirections.',CFGP_NAME); ?></td>
                    </tr>	
					<?php } ?>
                </tbody>
                <thead>
                    <tr>
                        <th scope="col"><?php _e('URL',CFGP_NAME); ?></th>
                        <th scope="col"><?php _e('Country',CFGP_NAME); ?></th>
                        <th scope="col"><?php _e('Region',CFGP_NAME); ?></th>
                        <th scope="col"><?php _e('City',CFGP_NAME); ?></th>
                        <th class="text-center" scope="col"><?php _e('Status Code',CFGP_NAME); ?></th>
                        <th class="text-right" scope="col"><?php _e('Action',CFGP_NAME); ?></th>
                    </tr>
                </thead>
            </table>
            <?php if( $redirection_count >= 20 ) { ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                <?php
                    $base_link = self_admin_url( 'admin.php?page=' . $_GET['page'] );
                ?>
                    <li class="page-item <?php echo ( $page_num == 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?php echo ( $page_num == 1 ) ? '#' : $base_link . '&page_num=1' ?>"><span class="fa fa-angle-double-left"></span></a>
                    </li>
                    <li class="page-item <?php echo ( $page_num <= 1 ) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?php echo ( $page_num <= 1 )  ? '#' : $base_link . '&page_num=' . ($page_num - 1); ?>"><span class="fa fa-angle-left"></span></a>
                    </li>
                    <?php
                        for( $i = 1; $i <= $total_pages; $i++ )
                        {
                            $active = ( $i == $page_num ) ? 'active' : '';
                            $href = ( $i == $page_num ) ? '#' : $base_link . '&page_num=' . $i;
                            $number = ( $i == $page_num ) ? '<span class="sr-only"></span>' . $i : (string)$i;

                            printf(
                                '
                                    <li class="page-item %1$s">
                                        <a class="page-link" href="%2$s">%3$s</a>
                                    </li>
                                ', $active, $href, $number
                            );
                        }
                    ?>
                    <li class="page-item <?php echo ( $page_num >= $total_pages ) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?php echo ( $page_num >= $total_pages )  ? '#' : $base_link . '&page_num=' . ($page_num + 1); ?>"><span class="fa fa-angle-right"></span></a>
                    </li>
                    <li class="page-item <?php echo ( $page_num == $total_pages ) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?php echo ( $page_num == $total_pages ) ? '#' : $base_link . '&page_num=' . $total_pages; ?>"><span class="fa fa-angle-double-right"></span></a>
                    </li>
                </ul>
            </nav>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>