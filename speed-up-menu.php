<?php
/*
 Plugin Name: Speed Up - Menu Cache
 Plugin URI: http://wordpress.org/plugins/speed-up-menu/
 Description: The menu reduces speed of Wordpress. This plugin offers a caching solution that reduces this effects on performance.
 Version: 1.0.18
 Author: Simone Nigro
 Author URI: https://profiles.wordpress.org/nigrosimone
 License: GPLv2 or later
 License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( !defined('ABSPATH') ) exit;


class SpeedUp_Menu {
    
    const CACHE_PREFIX      = 'SpeedUp_Menu::';
    const CACHE_KEY_VERSION = 'CacheVersion';

    /**
     * Instance of the object.
     * 
     * @since  1.0.0
     * @static
     * @access public
     * @var null|object
     */
    public static $instance = null;
    
    /**
     * Cache vary.
     *
     * @since  1.0.5
     * @access private
     * @var string
     */
    private $vary = null;
    
    /**
     * Local cache.
     *
     * @since  1.0.5
     * @access private
     * @var string
     */
    private $cache = null;
    
    /**
     * Cache version.
     *
     * @since  1.0.5
     * @access private
     * @var integer
     */
    private $version = null;

    /**
     * Access the single instance of this class.
     *
     * @since  1.0.0
     * @return SpeedUp_Menu
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     *
     * @since  1.0.0
     * @return SpeedUp_Menu
     */
    private function __construct(){

        if( is_admin() ){
            add_action('wp_update_nav_menu', array($this, 'inc_cache_version'));
            add_action('admin_menu',         array($this, 'admin_menu'));
        } else {
            add_filter('pre_wp_nav_menu',    array($this, 'return_cached_menu'), PHP_INT_MAX, 2);
            add_filter('wp_nav_menu',        array($this, 'save_menu'),          PHP_INT_MAX, 2);
        }
    }
    
    /**
     * Returns menu from cache
     *
     * @since  1.0.0
     * @param  $nav_menu string|null
     * @param  $args object
     * @return string
     */
    public function return_cached_menu($nav_menu, $args ){
    
        if( $key = $this->get_cache_key($args) ){
            
            $vary = $this->get_vary();
            
            $key = $this->get_varied_key($key, $vary);
            
            if( isset($this->cache[$key]) ){
                return $this->cache[$key];
            }
            
            if( $contents = get_transient($key) ){
                
                // set local cache
                $this->cache[$key] = $contents;
                
                return $contents;
            }
        }
    
        return $nav_menu;
    }
    
    /**
     * Save menu in cache
     * 
     * @since  1.0.0
     * @param  $nav_menu string|null
     * @param  $args object
     * @return string
     */
    public function save_menu($nav_menu, $args){
        
        // check if $nav_menu have no-cache
        if( false !== strpos($nav_menu, 'no-cache') ){
            return $nav_menu;
        }
        
        if( $key = $this->get_cache_key($args) ){
            
            $vary = $this->get_vary();
            
            $key = $this->get_varied_key($key, $vary);
            
            // if already cached.
            if( isset($this->cache[$key]) ){
                 return $this->cache[$key];
            }
            
            // un-style active item
            $nav_menu = str_replace(array(
                'current-menu-item', 'current_page_item', 'current-menu-ancestor',
                'current-menu-parent','current_page_parent', 'current_page_ancestor'
                ), '',
                $nav_menu
            );
            
            $nav_menu_cache = '<!-- Start "Speed Up - Menu Cache" info: key="'.$key.'"; vary="'.$vary.'"; date="'.date(DATE_RFC2822).'" -->' . $nav_menu . '<!-- End "Speed Up - Menu Cache" -->';
    
            if( set_transient($key, $nav_menu_cache, HOUR_IN_SECONDS) ){
                $this->cache[$key] = $nav_menu_cache;
            }
        }
    
        return $nav_menu;
    }
    
    /**
     * Increment cache version.
     *
     * @since  1.0.5
     * @return integer
     */
    public function inc_cache_version(){
       
        $transient = self::CACHE_PREFIX.self::CACHE_KEY_VERSION;
        
        $value = (int)get_transient($transient);
        
        if( $value >= (PHP_INT_MAX-1) ){
            $value = 0;
        }
        
        $value++;
        
        if( set_transient($transient, $value) ){
            $this->version = $value;
        }
        
        return $this->version;
    }
    
    /**
     * Get cache version.
     *
     * @since  1.0.5
     * @return integer
     */
    public function get_cache_version(){
    
        if( $this->version === null ){
            $this->version = (int)get_transient(self::CACHE_PREFIX.self::CACHE_KEY_VERSION);
        }

        return $this->version;
    }
    
    /**
     * Admin link in appearance section
     *
     * @since  1.0.3
     * @return int    Number of menu cache deleted
     */
    public function admin_menu(){
        $title = __( 'Purge menu cache', 'purge-menu-cache' );
        add_theme_page($title, $title, 'manage_options', 'speed-up-menu-purge', array( $this, 'render_admin_purge_page' ));
    }
    
    /**
     * Render the plugin page
     *
     * @since  1.0.3
     * @return void
     */
    public function render_admin_purge_page(){

        $old_version = $this->get_cache_version();
        
        $html  = '';
        
        $html .= '<div class="wrap">';
        $html .= '<div class="icon32" id="icon-options-general"><br /></div>';
        $html .= '<h1>'. __( 'Speed Up - Menu', 'speed-up-menu' ) .'</h1><br class="clear"/>';
        
        if( $this->inc_cache_version() > $old_version ){
            $html .= '<div class="notice notice-success is-dismissible"><p>'. __( 'Success! Menu cache purged.', 'menu-cache-purged' ) .'</p></div>';
        } else {
            $html .= '<div class="notice notice-error is-dismissible"><p>'. __( 'Fail! Menu cache not purged.', 'menu-cache-not-purged' ) .'</p></div>';
        }
        
        $html .= '</div>';
        
        echo $html;
    }
    
    /**
     * Return the key for the cache
     *
     * @since  1.0.0
     * @param  object  $args
     * @param  integer $id
     * @return string
     */
    private function get_cache_key( $args ){
    
        $menu_name = '';
        $cache_vr  = $this->get_cache_version();

        // check $args->menu is object or string
        if( is_object($args) && property_exists($args, 'menu') ){
            
            if( is_object($args->menu) ){
                
               /** @var $args->menu WP_Term */
               $menu_name = $args->menu->term_id;
               
               if( property_exists($args->menu, 'theme_location') && !empty($args->menu->theme_location) ){
                   $menu_name .= '_'.$args->menu->theme_location;
               } elseif( property_exists($args, 'theme_location') && !empty($args->theme_location) ){
                   $menu_name .= '_'.$args->theme_location;
               }
               
            } else {

               $menu_name = $args->menu;
                      
               if( !empty($args->theme_location) ){
                   $theme_locations = get_nav_menu_locations();
            
                   if( isset($theme_locations[$args->theme_location]) ){
                       $menu_obj  = get_term( $theme_locations[$args->theme_location], 'nav_menu' );
                       if( is_object($menu_obj) && !is_wp_error($menu_obj) ){
                           $menu_name = $menu_obj->term_id;
                       }
                       unset($menu_obj);
                       
                       if( !empty($args->theme_location) ){
                           $menu_name .= '_'. $args->theme_location;
                       }
                   }
                   unset($theme_locations);
               } 
            }
        }
    
        if( empty($menu_name) ){
            return null;
        }
    
        return self::CACHE_PREFIX.$cache_vr.'::'.$menu_name;
    }
    
    /**
     * Vary cache on client user-agent.
     * 
     * @since  1.0.5
     * @return string
     */
    private function get_vary(){
    
        if( $this->vary !== null ){
            return $this->vary;
        }
        
        $this->vary = '';
        
        $variants = $this->get_all_variants();
    
        foreach ($variants as $variant){
            if( call_user_func(array($this, $variant)) === true ){
                $this->vary = $variant;
                break;
            }
        }
        
        return $this->vary;
    }
    
    /**
     * Vary the cache $key on $variant.
     *
     * @since  1.0.5
     * @param  string $key
     * @param  string $variant
     * @return string
     */
    private function get_varied_key($key, $variant = ''){
        
        if( !empty($variant) ){
            return '_'.$variant.'_'.$key;
        }
        
        return $key;
    }
    
    /**
     * Return all variants for cache.
     * 
     * @since  1.0.5
     * @return array
     */
    private function get_all_variants(){
        return array('is_mobile', 'is_old_ie');
    }
    
    /**
     * Check if user-agent is mobile
     * 
     * @since  1.0.5
     * @return boolean
     */
    private function is_mobile(){
        if( isset($_SERVER['HTTP_USER_AGENT']) ){
            return preg_match('/(android|webos|avantgo|iphone|ipad|ipod|bla‌​ckberry|iemobile|bol‌​t|boost|cricket|doco‌​mo|fone|hiptop|mini|‌​opera mini|kitkat|mobi|palm|phone|pie|tablet|up\.browser|up\.link|‌​webos|wos)/i', $_SERVER['HTTP_USER_AGENT']);
        }
        return false;
    }
    
    /**
     * Check if user-agent is IE 5-9.
     * 
     * @since  1.0.5
     * @return boolean
     */
    private function is_old_ie(){
        if( isset($_SERVER['HTTP_USER_AGENT']) ){
            return preg_match('/msie [5-9]/i', $_SERVER['HTTP_USER_AGENT']);
        }
        return false;
    }
}

// Init
SpeedUp_Menu::get_instance();