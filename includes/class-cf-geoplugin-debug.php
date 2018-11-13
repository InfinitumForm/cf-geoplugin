<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Debug functionality
 *
 * @since      7.1.1
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 */
if( !class_exists( 'CF_Geoplugin_Debug' ) ) :
class CF_Geoplugin_Debug
{
    // Text for log file
    private $debug = array();

    // Log file path
    private $log_file_path = 'cf-geoplugin-debug.log';

    // Log file 
    private $log_file = NULL;

    // Start microtime
    private $microtime = 0;

    function __construct()
    {
        add_action( 'init', array(&$this, 'download_debug_file') );

        if( !isset( $_GET['action'] ) || $_GET['action'] !== 'debugger' ) return false;
        if( !file_exists( path_join( CFGP_ROOT, $this->log_file_path ) ) ) touch( path_join( CFGP_ROOT, $this->log_file_path ) ); // If not exists
        if( file_exists( path_join( CFGP_ROOT, $this->log_file_path ) ) && !is_writable( path_join( CFGP_ROOT, $this->log_file_path ) ) ) @chmod( path_join( CFGP_ROOT, $this->log_file_path ), 0777 ); // chmod 0777
        
        if(defined('FTP_USER') && FTP_USER !== '' && NULL !== FTP_USER) @chown( path_join( CFGP_ROOT, $this->log_file_path ), FTP_USER);
        
        $this->log_file = fopen( path_join( CFGP_ROOT, $this->log_file_path ), 'w' );

        $this->debug[] = '============ ' . date( 'd:M:Y' ) . ' - ' . date('H:i:s') . ' ============';
        $this->microtime = microtime(true);

    }
    // Saves data into array for later writing into file
    public function save( $text )
    {
        if( !isset( $_GET['action'] ) || $_GET['action'] !== 'debugger' ) return false;
        
        if( is_array( $text ) || is_object( $text ) ) $text = json_encode( $text, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        elseif( is_string( $text ) ) $text = trim( $text );

        $calculated_time = round( microtime(true) - $this->microtime, 4 );
        $this->debug[] = '[ ' . $calculated_time  . 's ] - ' . $text;
    }
	
	public function check()
    {
        return $this->debug;
    }
    // Writes collected data into file
    public function write()
    {
        if( !isset( $_GET['action'] ) || $_GET['action'] !== 'debugger' ) return false;
        $CFGEO = $GLOBALS['CFGEO'];
        if( !is_readable( path_join( CFGP_ROOT, $this->log_file_path ) ) ){ fclose( $this->log_file );  return false; };
        
        CF_Geoplugin_Global::validate(); // Pick up informations about license validation process

        $this->debug[] = '================ '. $CFGEO['host'] .' ================'; // End of file
        fwrite( $this->log_file, join( PHP_EOL, $this->debug ) );
        fclose( $this->log_file );
		chmod( path_join( CFGP_ROOT, $this->log_file_path ), 0644); // Made only readable for the user
        clearstatcache();
    }

    // Start download for debug file
	public function download_debug_file()
	{
        $filename = $this->log_file_path;
        $path = path_join( CFGP_ROOT, $this->log_file_path );

        if( !isset( $_GET['action'] ) || $_GET['action'] !== 'download_debug_log' ) return false;
        
        if( !file_exists( $path ) || !is_readable( $path ) ) return false;

        
        $filesize = filesize($path);
        
        $handle = fopen( $path, 'r' );
            
            header('Content-Description: File Transfer');
            header('Content-Encoding: UTF-8');
            header('Content-type: application/octet-stream; charset=UTF-8');
            header('Content-Disposition: attachment; filename=' . $filename );
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $filesize);

            echo fread( $handle, $filesize );

        fclose( $handle );
        flush();

        if( file_exists( $path ) && is_readable( $path ) ) unlink($path);

		exit;
	}
}
endif;