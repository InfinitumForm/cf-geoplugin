<?php
/**
 * Main API class
 *
 * @version       2.0.0
 *
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC'))
{
    die("Don't mess with us.");
}
if (!defined('ABSPATH'))
{
    exit;
}

if (!class_exists('WP_List_Table'))
{
    require_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
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

        /**
         * Add extra markup in the toolbars before or after the list
         * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
         */
        function extra_tablenav($which)
        {
            if ($which === 'top')
            {
                //The code that goes before the table is here
				_e('Here you can setup default site redirection based on the geo-location of your visitors. This functionality allow you to redirect your visitors on the any custom location. Please use this carefuly and wise.', CFGP_NAME);
            }
            if ($which === 'bottom')
            {
                //The code that goes after the table is there
				printf('<button type="button" class="button button-cfgeo-seo-import-csv"><i class="fa fa-file"></i> %s</button> ', __('Import From CSV', CFGP_NAME));
				printf('<button type="button" class="button button-cfgeo-seo-export-csv"><i class="fa fa-arrow-circle-right"></i> %s</button> ', __('Export CSV', CFGP_NAME));
            }
        }

        /**
         * Define the columns that are going to be used in the table
         * @return array $columns, the array of columns to use with the table
         */
        function get_columns()
        {
            return array(
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
            $screen = get_current_screen();

            /* -- Preparing your query -- */
            $seo_redirection_table = $wpdb->prefix . CFGP_Defaults::TABLE['seo_redirection'];
            $query = "SELECT * FROM {$seo_redirection_table}";

            /* -- Ordering parameters -- */
            //Parameters that are going to be used to order the result
            $orderby = CFGP_U::request_string('orderby', 'ID');
            $order = CFGP_U::request_string('order', 'DESC');
            if (!empty($orderby) & !empty($order))
            {
                $query .= " ORDER BY {$orderby} {$order}";
            }

            /* -- Pagination parameters -- */
            //Number of elements in your table?
            $totalitems = $wpdb->query($query); //return the total number of affected rows
            //How many to display per page?
            $perpage = 20;
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
                $offset = ($paged - 1) * $perpage;
                $query .= ' LIMIT ' . (int)$offset . ',' . (int)$perpage;
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
                array() ,
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
                foreach ($records as $rec)
                {

                    //Open the line
                    echo '<tr id="cfgp_seo_row_' . $rec->ID . '">';
                    foreach ($columns as $column_name => $column_display_name)
                    {

                        //Style attributes for each col
						if($column_name == 'cfgp_seo_url')
                       	 	$class = "class='$column_name column-$column_name has-row-actions column-primary'";
						else
							$class = "class='$column_name column-$column_name'";
                        $style = ' style="';
						if($column_name == 'cb'){
							$style.=  'width:10%;';
						}
                        if (in_array($column_name, $hidden)) $style.= 'display:none;';
						$style.='"';
                        $attributes = $class . $style;
											
                        //edit link
                        $edit_link = admin_url('admin.php?page='.CFGP_U::request_string('page').'&action=edit&id=' . (int)$rec->ID . '&nonce='.wp_create_nonce(CFGP_NAME.'-seo-edit'));
						$delete_link = admin_url('admin.php?page='.CFGP_U::request_string('page').'&action=delete&id=' . (int)$rec->ID . '&nonce='.wp_create_nonce(CFGP_NAME.'-seo-delete'));

                        //Display the cell
                        switch ($column_name)
                        {
                            case "cfgp_seo_url":
                                echo '<td ' . $attributes . '>';
									echo '<strong><a href="'.$rec->url.'" target="_blank" >'.$rec->url.'</a></strong>';
									echo '<div class="row-actions"><span class="edit"><a href="'.$edit_link.'">'.__('Edit').'</a> | </span><span class="trash"><a href="'.$delete_link.'" class="submitdelete">'.__('Delete').'</a></span></div>';
								echo '</td>';
                            break;
							case "cfgp_seo_country":
                                echo '<td ' . $attributes . '>'.$rec->country.'</td>';
                            break;
                            case "cfgp_seo_region":
                                echo '<td ' . $attributes . '>' . $rec->region . '</td>';
                            break;
                            case "cfgp_seo_city":
                                echo '<td ' . $attributes . '>' . $rec->city . '</td>';
                            break;
                            case "cfgp_seo_postcode":
                                echo '<td ' . $attributes . '>' . $rec->postcode . '</td>';
                            break;
							case "cfgp_seo_http_code":
                                echo '<td ' . $attributes . '>HTTP ' . $rec->http_code . '</td>';
                            break;
							case "cfgp_seo_only_once":
                                echo '<td ' . $attributes . '>' . ($rec->only_once ? __('Only once', CFGP_NAME) : __('Always', CFGP_NAME) ). '</td>';
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
            global $cfgp_cache;
            $class = self::class;
            $instance = $cfgp_cache->get($class);
            if (!$instance)
            {
                $instance = $cfgp_cache->set($class, new self());
            }
            return $instance;
        }
    }
endif;