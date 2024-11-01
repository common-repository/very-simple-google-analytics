<?php
/*
Plugin Name: Very Simple Google Analytics
Plugin URI: http://roidayan.com
Description: Google analytics plugin
Version: 1.0
Author: Roi Dayan
Author URI: http://roidayan.com
License: GPLv2

Copyright (C) 2011  Roi Dayan  (email : roi.dayan@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class WPVerySimpleGoogleAnalytics {
    function WPVerySimpleGoogleAnalytics() {
        $this->pfx = strtolower(__CLASS__.'_');
        $this->ga_id = 'UA-0000000-0';
        
        register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));

        if (is_admin()) {
            add_action('admin_init', array(&$this, 'admin_init_settings'));
            add_action('admin_menu', array(&$this, 'add_admin_page'));
        } else {
            add_action('wp_head', array(&$this, 'add_ga_code'));
        }
    }
    
    function deactivate() {
        unregister_setting($this->pfx.'og', $this->pfx.'og', array(&$this, 'validate_settings'));
    }

    function admin_init_settings() {
        register_setting($this->pfx.'og', $this->pfx.'og', array(&$this, 'validate_settings'));
        
        add_settings_section($this->pfx.'s_settings',
                             'Settings',
                             array(&$this, 'cb_s_settings'),
                             $this->pfx.'page');
        add_settings_field($this->pfx.'o_id',
                           'Google analytics ID',
                           array(&$this, 'cb_o_id'),
                           $this->pfx.'page',
                           $this->pfx.'s_settings');
    }
    
    function validate_settings($input) {
        $id = trim($input[$this->pfx.'o_id']);
        $valid_input = array();
        if (empty($id) || preg_match('/^UA-[0-9]+-[0-9]+$/i', $id)) {
            $valid_input[$this->pfx.'o_id'] = $id;
        } else {
            add_settings_error(  
                        $id,
                        $this->pfx.'id_error', // error ID
                        'Invalid google analytics id.',
                        'error'
                        );
        }
        return $valid_input;
    }
    
    function cb_s_settings() {
        //echo '<p>Intro</p>';
    }
    
    function cb_o_id() {
    	$options = get_option($this->pfx.'og');
    ?>
         <input id="<?php echo $this->pfx.'o_id'; ?>" 
            name="<?php echo "{$this->pfx}og[{$this->pfx}o_id]"; ?>" 
            class="regular-text" 
            value="<?php echo $options[$this->pfx.'o_id']; ?>" />
    <?php
    }
    
    function show_settings() {
    ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br></div>
            <h2>Google Analytics Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields($this->pfx.'og'); ?>
                <?php do_settings_sections($this->pfx.'page'); ?>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
                </p>
            </form>
        </div>
    <?php
    }

    function add_admin_page() {
        add_options_page('Google Analytics', 'Google Analytics',
                         /* cap */ 'manage_options',
                         /* slug */ $this->pfx.'page',
                         array(&$this, 'show_settings'));
    }

    function add_ga_code() { 
        // TODO settings API
        //$web_property_id = get_option('web_property_id');
        //$asynchronous_tracking = (get_option('asynchronous_tracking') == 'yes');
        $options = get_option($this->pfx.'og');
        $ga_id = $options[$this->pfx.'o_id'];
        if (!empty($ga_id)):
    ?>
        <script type="text/javascript">
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', '<?php echo $ga_id; ?>']);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        </script>
    <?php
        endif;
    }
}

new WPVerySimpleGoogleAnalytics();
?>