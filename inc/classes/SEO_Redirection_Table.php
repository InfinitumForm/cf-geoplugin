<?php
/**
 * Main API class
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if (!class_exists('WP_List_Table'))
{
    require_once ABSPATH . DIRECTORY_SEPARATOR . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wp-list-table.php';
}

if (!class_exists('CFGP_SEO_Table')):
    class CFGP_SEO_Table extends WP_List_Table
    {

        public function __construct()
        {
            parent::__construct(array(
                'singular' => 'cfgp_seo_redirection', //Singular label
                'plural' => 'cfgp_seo_redirections', //plural label, also this well be one of the table css class
                'ajax' => false
                
            ));
            $this->prepare_items();
            $this->display();
        }
		
		public function get_bulk_actions() {

			return array(
				'enable' => __( 'Enable Redirection', CFGP_NAME ),
				'disable' => __( 'Disable Redirection', CFGP_NAME ),
				'only_once' => __( 'Redirect Only Once', CFGP_NAME ),
				'always' => __( 'Always Redirect', CFGP_NAME ),
				'delete' => __( 'Delete', CFGP_NAME )
			);
	
		}
		
		public static function get_filter_links() {
			global $wpdb;
			
			if(!self::table_exists()) {
				return;
			}
			
			$count = [
				'enabled' => 0,
				'disabled' => 0
			];
			
			$filter = CFGP_U::request_string('filter', NULL);
			
			$query = "SELECT COUNT(*) FROM `{$wpdb->cfgp_seo_redirection}`";
			
			/* -- Search -- */
			if(wp_verify_nonce(($_GET['_wpnonce'] ?? NULL), CFGP_NAME.'-seo-search') && ($s = CFGP_U::request_string('s', ''))){
				$query.=$wpdb->prepare(
					" WHERE (
						`{$wpdb->cfgp_seo_redirection}`.`url` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`country` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`region` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`city` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`postcode` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`http_code` = %d
					) ",
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					$s
				);
				$count['enabled'] = absint( $wpdb->get_var( $query . " AND `{$wpdb->cfgp_seo_redirection}`.`active` = 1" ) );
				$count['disabled'] = absint( $wpdb->get_var( $query . " AND `{$wpdb->cfgp_seo_redirection}`.`active` = 0" ) );
			} else {			
				$count['enabled'] = absint( $wpdb->get_var( $query . " WHERE `{$wpdb->cfgp_seo_redirection}`.`active` = 1" ) );
				$count['disabled'] = absint( $wpdb->get_var( $query . " WHERE `{$wpdb->cfgp_seo_redirection}`.`active` = 0" ) );
			}
			
			if($count['enabled']) :
			?>
			<ul class="subsubsub">
				<li class="all"><a href="<?php echo add_query_arg('filter',NULL); ?>"<?php echo add_query_arg('filter','enabled'); ?>"<?php
					if($filter == NULL) {
						echo ' class="current" aria-current="page"';
					}
				?>><?php _e('All', CFGP_NAME); ?> <span class="count">(<?php echo ($count['enabled']+$count['disabled']); ?>)</span></a> |</li>
				<li class="enabled"><a href="<?php echo add_query_arg('filter','enabled'); ?>"<?php
					if($filter == 'enabled') {
						echo ' class="current" aria-current="page"';
					}
				?>><?php _e('Enabled', CFGP_NAME); ?> <span class="count">(<?php echo $count['enabled']; ?>)</span></a> 
				<?php if($count['disabled']) : ?>|</li>
				<li class="disabled"><a href="<?php echo add_query_arg('filter','disabled'); ?>"<?php echo add_query_arg('filter','enabled'); ?>"<?php
					if($filter == 'disabled') {
						echo ' class="current" aria-current="page"';
					}
				?>><?php _e('Disabled', CFGP_NAME); ?> <span class="count">(<?php echo $count['disabled']; ?>)</span></a></li>
				<?php else : ?>
				</li>
				<?php endif; ?>
			</ul>
			<?php endif;
		}
		
		public function process_bulk_action() {

			// security check!
			if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
	
				$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
				$action = 'bulk-' . $this->_args['plural'];
	
				if ( ! wp_verify_nonce( $nonce, $action ) )
					wp_die( __( 'Nope! Security check failed!', CFGP_NAME ) );
	
			}
	
			$action = $this->current_action();
	
			switch ( $action ) {
	
				case 'delete':
					$checkboxes = CFGP_U::request_array('seo_redirection');
					if(!empty($checkboxes))
					{
						$checkboxes = array_map('absint', $checkboxes);
						if($checkboxes = array_filter($checkboxes))
						{
							global $wpdb;
							$checkboxes_prepare = implode( ',', array_fill( 0, count( $checkboxes ), '%d' ) );
							$wpdb->query( $wpdb->prepare($query = "DELETE FROM `{$wpdb->cfgp_seo_redirection}` WHERE `{$wpdb->cfgp_seo_redirection}`.`ID` IN ({$checkboxes_prepare})", $checkboxes) );
						}
					}
					break;
					
				case 'enable':
				case 'disable':
					$checkboxes = CFGP_U::request_array('seo_redirection');
					if(!empty($checkboxes))
					{
						$checkboxes = array_map('absint', $checkboxes);
						if($checkboxes = array_filter($checkboxes))
						{
							global $wpdb;
							$checkboxes_prepare = implode( ',', array_fill( 0, count( $checkboxes ), '%d' ) );
							$enable_disable = ($action === 'enable' ? 1 : 0);
							$wpdb->query( $wpdb->prepare($query = "UPDATE `{$wpdb->cfgp_seo_redirection}` SET `active` = {$enable_disable} WHERE `{$wpdb->cfgp_seo_redirection}`.`ID` IN ({$checkboxes_prepare})", $checkboxes) );
						}
					}
					break;
				
				case 'only_once':
				case 'always':
					$checkboxes = CFGP_U::request_array('seo_redirection');
					if(!empty($checkboxes))
					{
						$checkboxes = array_map('absint', $checkboxes);
						if($checkboxes = array_filter($checkboxes))
						{
							global $wpdb;
							$checkboxes_prepare = implode( ',', array_fill( 0, count( $checkboxes ), '%d' ) );
							$enable_disable = ($action === 'only_once' ? 1 : 0);
							$wpdb->query( $wpdb->prepare($query = "UPDATE `{$wpdb->cfgp_seo_redirection}` SET `only_once` = {$enable_disable} WHERE `{$wpdb->cfgp_seo_redirection}`.`ID` IN ({$checkboxes_prepare})", $checkboxes) );
						}
					}
					break;
			}
	
			return;
		}

        /**
         * Add extra markup in the toolbars before or after the list
         * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
         */
        function extra_tablenav($which)
        {
			if(CFGP_Options::get('enable_seo_csv', 0) && in_array($which, array('top', 'bottom')))
			{
				global $wpdb;
				$exists = CFGP_U::has_seo_redirection();
				echo '<div class="alignleft actions bulkactions">';
				
					$seo_import_csv = add_query_arg(array(
						'action' => 'import',
						'nonce' => wp_create_nonce(CFGP_NAME.'-seo-import-csv')
					));
				
					printf('<a aria="button" href="%s" class="button"><i class="fa fa-upload"></i> %s</a> ', $seo_import_csv, __('Import From CSV', CFGP_NAME));
					
					if($exists){
						
						$seo_export_csv = add_query_arg(array(
							'action' => 'export',
							'nonce' => wp_create_nonce(CFGP_NAME.'-seo-export-csv')
						));
						
						printf('<a aria="button" href="%s" class="button"><i class="fa fa-table"></i> %s</a> ', $seo_export_csv, __('Export CSV', CFGP_NAME));
					}
					
				echo '</div>';
			}
        }

        /**
         * Define the columns that are going to be used in the table
         * @return array $columns, the array of columns to use with the table
         */
        function get_columns()
        {
            return array(
				'cb'    => '<input type="checkbox">',
                'cfgp_seo_url' => __('URL', CFGP_NAME),
                'cfgp_seo_country' => __('Country', CFGP_NAME),
                'cfgp_seo_region' => __('Region', CFGP_NAME),
                'cfgp_seo_city' => __('City', CFGP_NAME),
                'cfgp_seo_postcode' => __('Postcode', CFGP_NAME),
				'cfgp_seo_http_code' => __('Status Code', CFGP_NAME),
				'cfgp_seo_only_once' => __('Redirect', CFGP_NAME)
            );
        }

        /**
         * Decide which columns to activate the sorting functionality on
         * @return array $sortable, the array of columns that can be sorted by the user
         */
        public function get_sortable_columns()
        {
            return array(
                'cfgp_seo_country' => array('country', true),
				'cfgp_seo_region' => array('region', true),
				'cfgp_seo_city' => array('city', true),
                'cfgp_seo_postcode' => array('postcode', true),
				'cfgp_seo_http_code' => array('http_code', true),
				'cfgp_seo_only_once' => array('only_once', true)
            );
        }

        /**
         * Prepare the table with different parameters, pagination, columns and table elements
         */
        function prepare_items()
        {
            global $wpdb, $_wp_column_headers;
			
			if(!self::table_exists()) {
				return;
			}
			
			// Set bulk actions
			$this->process_bulk_action();
			// get the current user ID
			$user = get_current_user_id();
			// get the current admin screen
			$screen = get_current_screen();
			// retrieve the "per_page" option
			$screen_option = $screen->get_option('per_page', 'option');
			// retrieve the value of the option stored for the current user
			$perpage = get_user_meta($user, $screen_option, true);
			if ( empty ( $perpage) || $perpage < 1 ) {
				// get the default value if none is set
				$perpage = $screen->get_option( 'per_page', 'default' );
			}
			
			// Make it absolute integer
			if( empty($perpage) ) {
				$perpage = 0;
			} else {
				$perpage = (int)$perpage;
			}
			
            /* -- Preparing your query -- */
            $query = "SELECT * FROM `{$wpdb->cfgp_seo_redirection}`";
			
			/* -- Search -- */
			if(wp_verify_nonce(($_GET['_wpnonce'] ?? NULL), CFGP_NAME.'-seo-search') && ($s = CFGP_U::request_string('s', ''))){
				$query.=$wpdb->prepare(
					" WHERE (
						`{$wpdb->cfgp_seo_redirection}`.`url` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`country` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`region` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`city` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`postcode` LIKE %s 
						OR `{$wpdb->cfgp_seo_redirection}`.`http_code` = %d
					) ",
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					'%'.$wpdb->esc_like($s).'%',
					$s
				);
				
				if($filter = CFGP_U::request_string('filter', NULL))
				{
					if($filter == 'enabled') {
						$query.= " AND `{$wpdb->cfgp_seo_redirection}`.`active` = 1";
					} else if($filter == 'disabled') {
						$query.= " AND `{$wpdb->cfgp_seo_redirection}`.`active` = 0";
					}
				}
			} else {
				if($filter = CFGP_U::request_string('filter', NULL))
				{
					if($filter == 'enabled') {
						$query.= " WHERE `{$wpdb->cfgp_seo_redirection}`.`active` = 1";
					} else if($filter == 'disabled') {
						$query.= " WHERE `{$wpdb->cfgp_seo_redirection}`.`active` = 0";
					}
				}
			}

            /* -- Ordering parameters -- */
            //Parameters that are going to be used to order the result
            $orderby = CFGP_U::request_string('orderby', 'ID');
            $order = CFGP_U::request_string('order', 'desc');
            if (!empty($orderby) & !empty($order))
            {
				if(
					in_array(strtolower($order), array('asc', 'desc'))
					&& in_array($orderby, array(
						'ID',
						'country',
						'region',
						'city',
						'postcode',
						'http_code',
						'only_once'
					))
				){
                	$query .= " ORDER BY `{$wpdb->cfgp_seo_redirection}`.`{$orderby}` {$order}";
				}
            }

            /* -- Pagination parameters -- */
            //Number of elements in your table?
            $totalitems = $wpdb->query($query); //return the total number of affected rows
            //Which page is this?
            $paged = CFGP_U::request_int('paged', 0);
            //Page Number
            if (empty($paged) || !is_numeric($paged) || $paged <= 0)
            {
                $paged = 1;
            }
            //How many pages do we have in total?
            $totalpages = ceil($totalitems / $perpage);
            //adjust the query to take pagination into account
            if (!empty($paged) && !empty($perpage))
            {
                $offset = (int)(($paged - 1) * $perpage);
				$query .= " LIMIT {$offset},{$perpage}";
            }
            /* -- Register the pagination -- */
            $this->set_pagination_args(array(
                'total_items' => $totalitems,
                'total_pages' => $totalpages,
                'per_page' => $perpage,
            ));
            //The pagination links are automatically built according to those parameters
            /* -- Register the Columns -- */
            $columns = $this->get_columns();
            $sortable = $this->get_sortable_columns();
            $_wp_column_headers[$screen->id] = $columns;

            /* -- Fetch the items -- */
            $this->_column_headers = array(
                $columns,
                array('ID'),
                $sortable
            );
			
            $this->items = $wpdb->get_results($query);
        }

        /**
         * Display the rows of records in the table
         * @return string, echo the markup of the rows
         */
        function display_rows()
        {

            //Get the records registered in the prepare_items method
            $records = $this->items;

            //Get the columns registered in the get_columns and get_sortable_columns methods
            list($columns, $hidden) = $this->get_column_info();

            //Loop for each record
            if (!empty($records))
            {
				$data_countries = CFGP_Library::get_countries();
				
                foreach ($records as $rec)
                {
                    //Open the line
                    echo '<tr id="cfgp_seo_row_' . (int)$rec->ID . '"'.($rec->active ? '' : ' class="cfgp-seo-table-row-inactive"').'>';
					
                    
					foreach ($columns as $column_name => $column_display_name)
                    {

                        //Style attributes for each col
						if($column_name == 'cfgp_seo_url')
                       	 	$class = 'class="'.esc_attr($column_name).' column-'.esc_attr($column_name).' has-row-actions column-primary"';
						else
							$class = 'class="'.esc_attr($column_name).' column-'.esc_attr($column_name).'"';
                        $style = ' style="';
						if($column_name == 'cb'){
							$style.=  'width:10%;';
						}
                        if (in_array($column_name, $hidden)) $style.= 'display:none;';
						$style.='"';
                        $attributes = $class . $style;
						
						//edit link
						$edit_link = add_query_arg(array(
							'action' => 'edit',
							'id' => (int)$rec->ID,
							'nonce' => wp_create_nonce(CFGP_NAME.'-seo-edit')
						));
						
						// Delete link
						$delete_link = add_query_arg(array(
							'action' => 'delete',
							'id' => (int)$rec->ID,
							'nonce' => wp_create_nonce(CFGP_NAME.'-seo-delete')
						));

                        //Display the cell
                        switch ($column_name)
                        {
							case "cb":
								echo '<th scope="row" class="check-column">' . sprintf(
									'<input type="checkbox" id="cb-select-%1$d" name="seo_redirection[]" value="%1$d" />', $rec->ID
								). '</th>';
							break;
                            case "cfgp_seo_url":
                                echo '<td ' . $attributes . '>';
									echo ($rec->active ? '' : '<sup>' . __('DISABLED', CFGP_NAME) . '</sup> ') . '<strong>' . esc_url($rec->url) . '</strong>';
									echo '<div class="row-actions">
										<span class="edit"><a href="' . esc_url($edit_link).'">' 
											. __('Edit', CFGP_NAME) 
										. '</a> | </span>
										<span class="trash"><a href="' . esc_url($delete_link) . '" class="submitdelete"  onclick="if (confirm(\'' 
											. esc_attr__('Are you sure you want to delete this redirection?', CFGP_NAME) 
										. '\')){return true;}else{event.stopPropagation(); event.preventDefault();};">' 
											. __('Delete', CFGP_NAME) 
										. '</a></span>
									</div>';
								echo '</td>';
                            break;
							case "cfgp_seo_country":
                                $country_code = '';
								if($term = get_term_by('name', $rec->country, 'cf-geoplugin-country')){
									if(!empty($term->description)) $country_code = ' (' . $term->description . ') ';
								} else {
									if(isset($data_countries[$rec->country])) {
										$country_code = ' (' . $data_countries[$rec->country] . ') ';
									}
								}
								echo '<td ' . $attributes . '>' . esc_html($rec->country ? $rec->country . $country_code : '-') . '</td>';
                            break;
                            case "cfgp_seo_region":
								$region_code = '';
								if($term = get_term_by('name', $rec->region, 'cf-geoplugin-region')){
									if(!empty($term->description)) $region_code = ' (' . $term->description . ') ';
								}
                                echo '<td ' . $attributes . '>' . esc_html($rec->region ? $rec->region . $region_code : '-') . '</td>';
                            break;
                            case "cfgp_seo_city":
								$city_code = '';
								$city_name = $rec->city;
								if($term = get_term_by('name', $rec->city, 'cf-geoplugin-city')){
									if(!empty($term->description)) $city_code = ' (' . $term->description . ') ';
									$city_name = $term->name;
								}
                                echo '<td ' . $attributes . '>' . esc_html($city_name ? $city_name.$city_code : '-') . '</td>';
                            break;
                            case "cfgp_seo_postcode":
								$postcode = $rec->postcode;
								if($term = get_term_by('name', $rec->postcode, 'cf-geoplugin-postcode')){
									$postcode = $term->name;
								}
                                echo '<td ' . $attributes . '>' . esc_html($postcode ? $postcode : '-') . '</td>';
                            break;
							case "cfgp_seo_http_code":
                                echo '<td ' . $attributes . '>HTTP ' . esc_html($rec->http_code) . '</td>';
                            break;
							case "cfgp_seo_only_once":
                                echo '<td ' . $attributes . '>' . esc_html($rec->only_once ? __('Only once', CFGP_NAME) : __('Always', CFGP_NAME) ). '</td>';
                            break;
                        }
                    }

                    //Close the line
                    echo '</tr>';
                }
            }
        }

        /*
         * Instance
         * @verson    1.0.0
        */
        public static function print ()
        {
            $class = self::class;
            $instance = CFGP_Cache::get($class);
            if (!$instance)
            {
                $instance = CFGP_Cache::set($class, new self());
            }
            return $instance;
        }
		
		public static function table_exists() {
			static $cache = NULL;
			global $wpdb;
			
			if(NULL === $cache) {
				if($wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->cfgp_seo_redirection}'" ) != $wpdb->cfgp_seo_redirection) {
					error_log(sprintf(__('The database table "%s" not exists! You can try to reactivate the WordPress Geo Plugin to correct this error.', CFGP_NAME), $wpdb->cfgp_seo_redirection));
					$cache = false;
				} else {
					$cache = true;
				}
			}
			
			return $cache;
		}
    }
endif;