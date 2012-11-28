<?php
/*
Plugin Name: Network Plugin Overview
Plugin URI: http://davidsword.ca/network-plugin-overview
Description: View which plugins are being used on which sites in a wordpress network
Version: 1.0
Author: davidsword
Author URI: http://davidsword.ca/
License: GPL2
*/

/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General 
Public License, version 2, as published by the Free Software Foundation. This program is distributed in the 
hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
received a copy of the GNU General Public License along with this program; if not, write to the Free Soft-
ware Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301  USA 
*/

// hook into menu
add_action('admin_menu', 'nwpov_create_optionspage');

// add new html page
function nwpov_create_optionspage() {
	global $menu, $submenu;
	add_menu_page('Network Plugin Overview', 'Plugin Overview', 'activate_plugins', 'network-plugin-overview', 'nwpov_page','');
}

// html page
function nwpov_page() {
	global $wpdb;		
	
	if (is_multisite()) {

		// get all plugins..
		// this from wp-admin/includes/class-wp-plugins-list-table.php
		$plugins = apply_filters( 'all_plugins', get_plugins() );
		
		// get all sites, their name, and their plugins, put into array
		$get_sites = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}blogs` ORDER BY `blog_id`");
		$sites_plugins = array();
		$all_sites = array();
		foreach ($get_sites as $k => $site) {
			$getsite_name = $wpdb->get_row("SELECT option_value FROM {$wpdb->prefix}{$site->blog_id}_options WHERE option_name = 'blogname'");
			$getsite_plugins = $wpdb->get_row("SELECT option_value FROM {$wpdb->prefix}{$site->blog_id}_options WHERE option_name = 'active_plugins'");
			$sites_plugins[$getsite_name->option_value] = (array)unserialize($getsite_plugins->option_value);		
			$all_sites[] = $getsite_name->option_value;
		}
	
		// main site
		$getsite_name = $wpdb->get_row("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'blogname'");
		$getsite_plugins = $wpdb->get_row("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'active_plugins'");
		$sites_plugins[$getsite_name->option_value] = (array)unserialize($getsite_plugins->option_value);		
		$all_sites[] = $getsite_name->option_value;

		// get network-wide actived plugins
		$getnetwork_plugins = $wpdb->get_row("SELECT meta_value FROM {$wpdb->prefix}sitemeta WHERE meta_key = 'active_sitewide_plugins'");
		$network_plugins = array_flip((array)unserialize($getnetwork_plugins->meta_value));		
		
		?>
		<div class='wrap'>
			<div id="icon-edit" class="icon32" ><br /></div>
			<h2>Network Plugin Overview</h2>
			<p>Below is a list of your network's plugins (left column) and the corroponding sites their active in (right column).</p>
			<style>
				#nwpov_table tr:nth-child(odd) { background: #efefef; }
				#nwpov_table td { padding: 5px; text-shadow: 1px 1px rgba(255,255,255,0.6) }
			</style>
			<table cellpadding=2 cellspacing="2" id='nwpov_table'>
				<tr>
					<td style='width: 350px;border-bottom: 1px solid #aaa;'><h4>Plugin</h4></td>
					<td style='border-bottom: 1px solid #aaa;'><h4>Active in site(s)</h4></td>
				</tr>
				<?php
				// cycle through plugins
				foreach ($plugins as $a_plugin_slug => $a_plugin) {
					echo "
					<tr>
						<td valign=top>{$a_plugin['Name']}</td>
						<td>";
						$count = 0;
						// if it's on entire network, highlight just that
						if (in_array($a_plugin_slug,$network_plugins)) {
							echo "<span style='color:green;'>active on entire network</span>";
							$count = 99;
						} 
						// cycle through sites, see if sites have this plugin
						else {
							foreach ($sites_plugins as $this_sites_name => $this_sites_plugins) {
								if ( in_array($a_plugin_slug,$this_sites_plugins) ) {
									echo "{$this_sites_name}<br />";
									$count++;
								}
							}
						}
						// not on any site
						if ($count == 0)
							echo "<span style='color:red'>(Not active in any sites)</span>";
						echo "
						</td>
					</tr>";
				}
				?>                 
			</table>
		</div>    
		<?php
	} else {
		?>
		<p style='text-align:center; background: #fbdddd; padding: 10px; font-family: Arial; border: 1px solid #e45757; text-shadow: 1px 1px #fff; margin-bottom:0; font-size:14px;'>
			This is not a network site, this is a plugin for Wordpress network installations.
		</p>
		<?php
	}
}
?>