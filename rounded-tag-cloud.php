<?php
/*
Plugin Name: Rounded Tag Cloud
Plugin URI: http://suhanto.net/rounded-tag-cloud-widget-wordpress/
Description: Display 'rounded' tags in tag cloud on the sidebar for your blog. This 'rounded' tag cloud is displayed as widget that can be placed anywhere within your blog.
Author: Agus Suhanto
Version: 1.0
Author URI: http://suhanto.net/

Copyright 2010 Agus Suhanto (email : agus@suhanto.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// wordpress plugin action hook
add_action('plugins_loaded', 'rounded_tag_cloud_init');

// initialization function
global $rounded_tag_cloud;
function rounded_tag_cloud_init() {
   $rounded_tag_cloud = new rounded_tag_cloud();
}

/*
 * This is the namespace for the 'rounded_tag_cloud' plugin / widget.
 */
class rounded_tag_cloud {

   protected $_name = "Rounded Tag Cloud";
   protected $_folder;
   protected $_path;
   protected $_width = 320;
   protected $_height = 320;
   protected $_link = 'http://suhanto.net/rounded-tag-cloud-widget-wordpress/';
   
   /*
    * Constructor
    */
   function __construct() {
      $path = __FILE__;
      if (!$path) { $path = $_SERVER['PHP_SELF']; }
         $current_dir = dirname($path);
      $current_dir = str_replace('\\', '/', $current_dir);
      $current_dir = explode('/', $current_dir);
      $current_dir = end($current_dir);
      if (empty($current_dir) || !$current_dir)
         $current_dir = 'rounded-tag-cloud';
      $this->_folder = $current_dir;
      $this->_path = '/wp-content/plugins/' . $this->_folder . '/';

      $this->init();
   }
   
   /*
    * Initialization function, called by plugin_loaded action.
    */
   function init() {
      add_action('template_redirect', array(&$this, 'template_redirect'));
      add_filter("plugin_action_links_$plugin", array(&$this, 'link'));
      load_plugin_textdomain($this->_folder, false, $this->_folder);      
      
      if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control'))
         return;
      register_sidebar_widget($this->_name, array(&$this, "widget"));
      register_widget_control($this->_name, array(&$this, "control"), $this->_width, $this->_height);
   }

   /*
    * Inserts the style into the head section.
    */
   function template_redirect() {
      $options = get_option($this->_folder);
      $this->validate_options($options);
      
      if (!isset($options['use_style']) || $options['use_style'] != 'checked')
         wp_enqueue_style($this->_folder, $this->_path . 'style.css', null, '1.0');
   }

   /*
    * Options validation.
    */
   function validate_options(&$options) {
      if (!is_array($options)) {
         $options = array(
            'title' => 'Tag Cloud',
            'font_size' => 10,
            'number' => 45,
            'use_style' => '');
      }      
   }
      
   /*
    * Called by register_sidebar_widget() function.
    * Rendering of the widget happens here.
    */
   function widget($args) {
      
      extract($args);
   
      $options = get_option($this->_folder);
      $this->validate_options($options);
      
      // call the wp_tag_cloud in the core WP functions
      echo $before_widget;
      echo $before_title;
      echo $options['title'];
      echo $after_title;
      
      echo '<div class="rtc-div">';
      wp_tag_cloud('smallest='. $options['font_size'] .'&largest='. $options['font_size'] .'&number='. $options['number']);
      if ($options['link_to_us'] == 'checked') {
         echo '<div class="rtc-link"><a href="' . $this->_link . '" target="_blank">'. __('Get this widget for your own blog free!', $this->_folder) . '</a></div>';
      }
      echo '</div>';
      echo $after_widget;
   }
   
   /*
    * Plugin control funtion, used by admin screen.
    */
   function control() {
      $options = get_option($this->_folder);
      $this->validate_options($options);
   
      if ($_POST[$this->_folder . '-submit']) {
         $options['title'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-title']));
         $options['font_size'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-font_size']));
         $options['number'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-number']));
         $options['use_style'] = htmlspecialchars($_POST[$this->_folder . '-use_style']);
         $options['link_to_us'] = htmlspecialchars($_POST[$this->_folder . '-link_to_us']);
   
         update_option($this->_folder, $options);
      }
?>      
      <p>
         <label for="<?php echo($this->_folder) ?>-title"><?php _e('Title: ', $this->_folder); ?></label> (<a href="<?php echo $this->_link?>#title" target="_blank">?</a>)
         <input type="text" id="<?php echo($this->_folder) ?>-title" name="<?php echo($this->_folder) ?>-title" value="<?php echo $options['title']; ?>" size="50"></input>
      </p>
      <p>
         <label for="<?php echo($this->_folder) ?>-font_size"><?php _e('Font Size: ', $this->_folder); ?></label>
         <input type="text" id="<?php echo($this->_folder) ?>-font_size" name="<?php echo($this->_folder) ?>-font_size" value="<?php echo $options['font_size']; ?>" size="2"></input> (<?php _e('default 10', $this->_folder) ?>) (<a href="<?php echo $this->_link?>#font-size" target="_blank">?</a>)
      </p>
      <p>
         <label for="<?php echo($this->_folder) ?>-font_size"><?php _e('Number: ', $this->_folder); ?></label>
         <input type="text" id="<?php echo($this->_folder) ?>-number" name="<?php echo($this->_folder) ?>-number" value="<?php echo $options['number']; ?>" size="2"></input> (<?php _e('default 45', $this->_folder) ?>) (<a href="<?php echo $this->_link?>#number" target="_blank">?</a>)
      </p>
      <p>
          <input type="checkbox" id="<?php echo($this->_folder) ?>-use_style" name="<?php echo($this->_folder) ?>-use_style" value="checked" <?php echo $options['use_style'];?> /> <?php _e('Use custom style', $this->_folder) ?> (<a href="<?php echo $this->_link?>#custom-style" target="_blank">?</a>) 
      </p>
      <p>
          <input type="checkbox" id="<?php echo($this->_folder) ?>-link_to_us" name="<?php echo($this->_folder) ?>-link_to_us" value="checked" <?php echo $options['link_to_us'];?> /> <?php _e('Link to us (optional)', $this->_folder) ?> (<a href="<?php echo $this->_link?>#link-to-us" target="_blank">?</a>) 
      </p>
      <p><?php printf(__('More details about these options, visit <a href="%s" target="_blank">Plugin Home</a>', $this->_folder), $this->_link) ?></p>
      <input type="hidden" id="<?php echo($this->_folder) ?>-submit" name="<?php echo($this->_folder) ?>-submit" value="1" />
<?php 
   }

   /*
    * Add extra link to widget list.
    */
   function link($links) {
      $options_link = '<a href="' . $this->_link . '">' . __('Donate', $this->_folder) . '</a>';
      array_unshift($links, $options_link);
      return $links;
   }
   
}