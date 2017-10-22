<?php
/**
 * Plugin Name: EchBay Search Everything
 * Description: Search Everything increases WordPress' default search functionality in three easy steps.
 * Plugin URI: https://www.facebook.com/groups/wordpresseb
 * Plugin Facebook page: https://www.facebook.com/webgiare.org
 * Author: Dao Quoc Dai
 * Author URI: https://www.facebook.com/ech.bay/
 * Version: 1.0.0
 * Text Domain: echbayese
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

// Exit if accessed directly
if (! defined ( 'ABSPATH' )) {
	exit ();
}

define ( 'ESE_DF_VERSION', '1.0.0' );
// echo ESE_DF_VERSION . "\n";

// define( 'ESE_DF_MAIN_FILE', __FILE__ );
// echo ESE_DF_MAIN_FILE . "\n";

define ( 'ESE_DF_DIR', dirname ( __FILE__ ) . '/' );
// echo ESE_DF_DIR . "\n";

// define( 'ESE_DF_TEXT_DOMAIN', 'echbayese' );
// echo ESE_DF_TEXT_DOMAIN . "\n";

//define ( 'ESE_DF_ROOT_DIR', basename ( ESE_DF_DIR ) );
// echo ESE_DF_ROOT_DIR . "\n";

//define ( 'ESE_DF_NONCE', ESE_DF_ROOT_DIR . ESE_DF_VERSION );
// echo ESE_DF_NONCE . "\n";

//define ( 'ESE_DF_URL', plugins_url () . '/' . ESE_DF_ROOT_DIR . '/' );
// echo ESE_DF_URL . "\n";

//define ( 'ESE_DF_PREFIX_OPTIONS', '___ese___' );
// echo ESE_DF_PREFIX_OPTIONS . "\n";

define ( 'ESE_THIS_PLUGIN_NAME', 'EchBay Search Everything' );
// echo ESE_THIS_PLUGIN_NAME . "\n";




// global echbay plugins menu name
// check if not exist -> add new
if ( ! defined ( 'EBP_GLOBAL_PLUGINS_SLUG_NAME' ) ) {
	define ( 'EBP_GLOBAL_PLUGINS_SLUG_NAME', 'echbay-plugins-menu' );
	define ( 'EBP_GLOBAL_PLUGINS_MENU_NAME', 'Webgiare Plugins' );
	
	define ( 'ESE_ADD_TO_SUB_MENU', false );
}
// exist -> add sub-menu
else {
	define ( 'ESE_ADD_TO_SUB_MENU', true );
}









/*
* class.php
*/
// check class exist
if (! class_exists ( 'ESE_Actions_Module' )) {
	
	// my class
	class ESE_Actions_Module {
		
		/*
		* config
		*/
		var $default_setting = array (
				// License -> donate or buy pro version
				'license' => '',
				
				// Hide Powered by ( 0 -> show, 1 -> hide. This is trial version -> default hide )
				'hide_powered' => 1,
				
				// thời gian xóa cache
				'cache_time' => 3600,
				
				// số lượng sản phẩm, bài viết tối đa của mỗi post type
				'limit_post_search' => 500,
				
				//
				'search_for_taxonomy' => 'category,post_tag,post_options,blogs,blog_tag',
				
				//
				'search_for_post_type' => 'post,blog,page,product'
		);
		
		var $custom_setting = array ();
		
		var $eb_plugin_media_version = ESE_DF_VERSION;
		
		var $eb_plugin_prefix_option = '___ese___';
		
		var $eb_plugin_root_dir = '';
		
		var $eb_plugin_url = '';
		
		var $eb_plugin_nonce = '';
		
		var $eb_plugin_admin_dir = 'wp-admin';
		
		var $web_link = '';
		
		
		/*
		* begin
		*/
		function load() {
			
			/*
			* test in localhost
			*/
			/*
			if ( $_SERVER['HTTP_HOST'] == 'localhost:8888' ) {
				$this->eb_plugin_media_version = time();
			}
			*/
			
			
			/*
			* Check and set config value
			*/
			// root dir
			$this->eb_plugin_root_dir = basename ( ESE_DF_DIR );
			
			// Get version by time file modife
			$this->eb_plugin_media_version = filemtime( ESE_DF_DIR . 'guest.css' );
			
			// URL to this plugin
//			$this->eb_plugin_url = plugins_url () . '/' . ESE_DF_ROOT_DIR . '/';
			$this->eb_plugin_url = plugins_url () . '/' . $this->eb_plugin_root_dir . '/';
			
			// nonce for echbay plugin
//			$this->eb_plugin_nonce = ESE_DF_ROOT_DIR . ESE_DF_VERSION;
			$this->eb_plugin_nonce = $this->eb_plugin_root_dir . ESE_DF_VERSION;
			
			//
			if ( defined ( 'WP_ADMIN_DIR' ) ) {
				$this->eb_plugin_admin_dir = WP_ADMIN_DIR;
			}
			
			
			/*
			* Load custom value
			*/
			$this->get_op ();
		}
		
		// get options
		function get_op() {
			global $wpdb;
			
			//
			$pref = $this->eb_plugin_prefix_option;
			
			$sql = $this->q ( "SELECT option_name, option_value
			FROM
				`" . $wpdb->options . "`
			WHERE
				option_name LIKE '{$pref}%'
			ORDER BY
				option_id" );
			
			foreach ( $sql as $v ) {
				$this->custom_setting [str_replace ( $this->eb_plugin_prefix_option, '', $v->option_name )] = $v->option_value;
			}
			// print_r( $this->custom_setting ); exit();
			
			
			/*
			* https://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
			*/
			// set default value if not exist or NULL
			foreach ( $this->default_setting as $k => $v ) {
				if (! isset ( $this->custom_setting [$k] )
				|| $this->custom_setting [$k] == ''
//				|| $this->custom_setting [$k] == 0
				|| $this->custom_setting [$k] == '0') {
					$this->custom_setting [$k] = $v;
				}
			}
			
			// esc_ custom value
			foreach ( $this->custom_setting as $k => $v ) {
				$v = esc_html( $v );
				$this->custom_setting [$k] = $v;
			}
			
//			print_r( $this->custom_setting ); exit();
		}
		
		// add checked or selected to input
		function ck($v1, $v2, $e = ' checked') {
			if ($v1 == $v2) {
				return $e;
			}
			return '';
		}
		
		function get_web_link () {
			if ( $this->web_link != '' ) {
				return $this->web_link;
			}
			
			//
			if ( defined('WP_SITEURL') ) {
				$this->web_link = WP_SITEURL;
			}
			else if ( defined('WP_HOME') ) {
				$this->web_link = WP_HOME;
			}
			else {
				$this->web_link = get_option ( 'siteurl' );
			}
			
			//
			$this->web_link = explode( '/', $this->web_link );
//			print_r( $this->web_link );
			
			$this->web_link[2] = $_SERVER['HTTP_HOST'];
//			print_r( $this->web_link );
			
			// ->
			$this->web_link = implode( '/', $this->web_link );
			
			//
			if ( substr( $this->web_link, -1 ) == '/' ) {
				$this->web_link = substr( $this->web_link, 0, -1 );
			}
//			echo $this->web_link; exit();
			
			//
			return $this->web_link;
		}
		
		// update custom setting
		function update() {
			if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset( $_POST['_ebnonce'] )) {
				
				// check nonce
				if( ! wp_verify_nonce( $_POST['_ebnonce'], $this->eb_plugin_nonce ) ) {
					wp_die('404 not found!');
				}

				
				// print_r( $_POST );
				
				//
				foreach ( $_POST as $k => $v ) {
					// only update field by eqs
					if (substr ( $k, 0, 5 ) == '_ese_') {
						
						// add prefix key to option key
						$key = $this->eb_plugin_prefix_option . substr ( $k, 5 );
						// echo $k . "\n";
						
						//
						delete_option ( $key );
						
						// text value
						$v = stripslashes ( stripslashes ( stripslashes ( $v ) ) );
						
						// remove all HTML tag, HTML code is not support in this plugin
						$v = strip_tags( $v );
						
						//
						$v = sanitize_text_field( $v );
						
						//
						add_option( $key, $v, '', 'no' );
//						add_option ( $key, $v );
					}
				}
				
				//
				die ( '<script type="text/javascript">
// window.location = window.location.href;
alert("Update done!");
</script>' );
				
				//
				// wp_redirect( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
				
				//
				// exit();
			} // end if POST
		}
		
		function q ( $sql ) {
			global $wpdb;
			
			return $wpdb->get_results ( $sql, OBJECT );
		}
		
		// form admin
		function admin() {
			global $wpdb;
			
			
			//
			$a = get_taxonomies();
//			print_r( $a );
			$str_all_taxonomy = '';
			if ( ! empty( $a ) ) {
				foreach ( $a as $k => $v ) {
					$str_all_taxonomy .= ',' . $v;
				}
				$str_all_taxonomy = substr( $str_all_taxonomy, 1 );
			}
			
			
			//
			$sql = get_post_types();
//			print_r( $sql );
			$str_all_post_type = '';
			if ( ! empty( $sql ) ) {
				foreach ( $sql as $k=> $v ) {
					$str_all_post_type .= ',' . $v;
				}
				$str_all_post_type = substr( $str_all_post_type, 1 );
			}

			
			// admin -> used real time version
			$this->eb_plugin_media_version = time();
			$this->get_web_link();
			
			//
			$main = file_get_contents ( ESE_DF_DIR . 'admin.html', 1 );
			
			$main = $this->template ( $main, $this->custom_setting + array (
				'_ebnonce' => wp_create_nonce( $this->eb_plugin_nonce ),
				
				'str_all_taxonomy' => $str_all_taxonomy,
				'str_all_post_type' => $str_all_post_type,
				
				'ese_plugin_url' => $this->eb_plugin_url,
				'ese_plugin_version' => $this->eb_plugin_media_version,
			) );
			
			$main = $this->template ( $main, $this->default_setting, 'aaa' );
			
			echo $main;
			
			echo '<p>* Other <a href="' . $this->web_link . '/' . $this->eb_plugin_admin_dir . '/plugin-install.php?s=itvn9online&tab=search&type=author" target="_blank">WordPress Plugins</a> written by the same author. Thanks for choose us!</p>';
			
		}
		
		function deline ( $str, $reg = "/\r\n|\n\r|\n|\r|\t/i", $re = "" ) {
			// v2
			$a = explode( "\n", $str );
			$str = '';
			foreach ( $a as $v ) {
				$v = trim( $v );
				if ( $v != '' ) {
					if ( strstr( $v, '//' ) == true ) {
						$v .= "\n";
					}
					$str .= $v;
				}
			}
			return $str;
			
			// v1
			return preg_replace( $reg, $re, $str );
		}
		
		//
		function js_taxonomy ($this_id = 0, $taxx = 'category') {
			global $web_link;
			
			//
			$arr = get_categories( array(
				'taxonomy' => $taxx,
//				'hide_empty' => 0,
				'parent' => $this_id
			) );
//			print_r($arr);
			
			//
			if ( empty( $arr ) ) {
				return '';
			}
			
			//
			$str = '';
			
			//
			foreach ( $arr as $v ) {
//				print_r($v);
				$str .= ',{id:' . $v->term_id . ',ten:"' . ESE_str_block_fix_content ( $v->name ) . '",arr:[' . $this->js_taxonomy ( $v->term_id, $taxx ) . ']}';
			}
			$str = substr ( $str, 1 );
			
			//
			return $str;
		}
		
		// get html for theme
		function guest() {
			global $wpdb;
			
			//
			$arr = wp_upload_dir();
//			print_r( $arr );
			
			// thư mục lưu file cache
			$base_dir = $arr['basedir'];
			$base_url = $arr['baseurl'] . '/ebcache/';
			$cache_dir = $base_dir . '/ebcache';
			$cache_file_name = 'echbay-search-everything.js';
			$cache_file_path = $cache_dir . '/' . $cache_file_name;
			$cache_file_url = $base_url . $cache_file_name;
			
			// tạo thư mục cache nếu chưa có
			if ( ! is_dir( $cache_dir ) ) {
				if ( ! mkdir($cache_dir, 0777) ) {
					echo '<!-- ERROR create dir cache: ' . $cache_dir . ' -->';
					return false;
				}
				chmod($cache_dir, 0777) or die('ERROR chmod dir: ' . $cache_dir);
			}
			
			//
			$first_create_cache_file = false;
			if ( ! file_exists( $cache_file_path ) ) {
				$filew = fopen( $cache_file_path, 'x+' );
				if ( ! $filew ) {
					echo '<!-- ERROR create file cache: ' . $cache_file_path . ' -->';
					return false;
				}
				chmod($cache_file_path, 0777) or die('ERROR chmod file: ' . $cache_file_path);
				fclose($filew);
				
				// lần đầu tạo file thì định nghĩa để có nội dung cho file
				$first_create_cache_file = true;
			}
			
			
			
			//
			$last_update = time () - filemtime( $cache_file_path );
			if ( $this->custom_setting['cache_time'] > 60 ) {
				$last_update += rand( 0, 600 );
			}
			else {
				echo '<!-- [EchBay Search Everything] TEST -->';
			}
//			echo $last_update . '<br>' . "\n";
			$time_update = $this->custom_setting['cache_time'];
			
			//
			if ( $last_update > $time_update || $first_create_cache_file == true ) {
				// xóa nội dung cũ trươc skhi thêm mới
				if ( ! file_put_contents( $cache_file_path, 'var eb_ese_site_js_data=[];' . "\n" ) ) {
					
					echo '<!-- ERROR reset file content: ' . $cache_file_path . ' -->';
					return false;
				}
				else {
					echo '<!-- Reset file content: ' . $cache_file_path . ' -->';
				}
				
				
				// thêm nội dung mới
				/*
				$sql = $this->q("SELECT taxonomy
				FROM
					`" . $wpdb->term_taxonomy . "`
				GROUP BY
					taxonomy");
					*/
				$sql = explode( ',', $this->custom_setting['search_for_taxonomy'] );
//				print_r( $sql );
				if ( ! empty( $sql ) ) {
					foreach ( $sql as $k => $v ) {
//						$v = $v->taxonomy;
						
						//
						/*
						if ( $v == 'nav_menu' || $v == 'link_category' || $v == 'post_format' ) {
						}
						else {
							*/
							$str = $this->js_taxonomy( 0, $v );
							if ( $str != '' ) {
								$taxonomy_name = get_taxonomy($v);
	//							print_r( $taxonomy_name );
								
								// không lấy các taxonomy trống
								if ( $taxonomy_name->query_var != '' ) {
									$str = 'eb_ese_site_js_data[ eb_ese_site_js_data.length ]=[{name:"' . ESE_str_block_fix_content ( $taxonomy_name->label ) . '",taxonomy:"' . $v . '"},' . $str . '];';
									
									if ( ! file_put_contents( $cache_file_path, $str . "\n", FILE_APPEND ) ) {
										
										echo '<!-- ERROR add file content: ' . $cache_file_path . ' -->';
										return false;
									}
								}
							}
//						}
					}
				}
				
				
				//
				/*
				$sql = $this->q("SELECT post_type
				FROM
					`" . $wpdb->posts . "`
				GROUP BY
					post_type");
					*/
				$sql = explode( ',', $this->custom_setting['search_for_post_type'] );
//				print_r( $sql );
				if ( ! empty( $sql ) ) {
					foreach ( $sql as $k=> $v ) {
//						$v = $v->post_type;
						
						//
						/*
						if ( $v == 'attachment' || $v == 'revision' || $v == 'nav_menu_item' || $v == 'custom_css' || $v == 'customize_changeset' ) {
						}
						else {
							*/
							$strsql = $this->q("SELECT ID, post_title
							FROM
								`" . $wpdb->posts . "`
							WHERE
								post_type = '" . $v . "'
								AND post_status = 'publish'
							ORDER BY
								ID DESC
							LIMIT 0, " . $this->custom_setting['limit_post_search']);
//							print_r( $strsql );
							
							if ( ! empty( $strsql ) ) {
								$str = '';
								foreach ( $strsql as $v2 ) {
									$str .= ',{id:' . $v2->ID . ',ten:"' . ESE_str_block_fix_content ( $v2->post_title ) . '"}';
								}
								$str = substr ( $str, 1 );
								
								//
								$str = 'eb_ese_site_js_data[ eb_ese_site_js_data.length ]=[{name:"' . ESE_str_block_fix_content ( $v ) . '",post_type:"' . $v . '"},' . $str . '];';
								
								if ( ! file_put_contents( $cache_file_path, $str . "\n", FILE_APPEND ) ) {
									
									echo '<!-- ERROR add file content: ' . $cache_file_path . ' -->';
									return false;
								}
							}
//						}
					}
				}
				
			}
			
			
			// style auto create
			$main = file_get_contents ( ESE_DF_DIR . 'guest.html', 1 );
			
			
			// another value
			$main = $this->template ( $main, array (
				'js' => 'var ESE_cache_file_url = "' . $cache_file_url . '?v=' . filemtime( $cache_file_path ) . '",ESE_plugin_url="' . $this->eb_plugin_url . '",ESE_search_version="' . $this->eb_plugin_media_version . '",ESE_web_link="' . $this->get_web_link() . '";',
				
				//
//				'cache_file_url' => $cache_file_url . '?v=' . filemtime( $cache_file_path ),
				'ese_plugin_url' => $this->eb_plugin_url,
				'ese_plugin_version' => $this->eb_plugin_media_version,
			) );
			
			echo $main;
		}
		
		// add value to template file
		function template($temp, $val = array(), $tmp = 'tmp') {
			foreach ( $val as $k => $v ) {
				$temp = str_replace ( '{' . $tmp . '.' . $k . '}', $v, $temp );
			}
			
			return $temp;
		}
	} // end my class
} // end check class exist



// Chuyển ký tự UTF-8 -> ra bảng mã mới
function ESE_str_block_fix_content ($str) {
	if ( function_exists('_eb_str_block_fix_content') ) {
		return _eb_str_block_fix_content( $str );
	}
	
	//
	if ($str == '') {
		return '';
	}
	
//	$str = iconv('UTF-16', 'UTF-8', $str);
//	$str = mb_convert_encoding($str, 'UTF-8', 'UTF-16');
//	$str = mysqli_escape_string($str);
//	$str = htmlentities($str, ENT_COMPAT, 'UTF-16');
	// https://www.google.com/search?q=site:charbase.com+%E1%BB%9D#q=site:charbase.com+%E1%BA%A3
	$arr = array(
		'á' => '\u00e1',
		'à' => '\u00e0',
		'ả' => '\u1ea3',
		'ã' => '\u00e3',
		'ạ' => '\u1ea1',
		'ă' => '\u0103',
		'ắ' => '\u1eaf',
		'ặ' => '\u1eb7',
		'ằ' => '\u1eb1',
		'ẳ' => '\u1eb3',
		'ẵ' => '\u1eb5',
		'â' => '\u00e2',
		'ấ' => '\u1ea5',
		'ầ' => '\u1ea7',
		'ẩ' => '\u1ea9',
		'ẫ' => '\u1eab',
		'ậ' => '\u1ead',
		'Á' => '\u00c1',
		'À' => '\u00c0',
		'Ả' => '\u1ea2',
		'Ã' => '\u00c3',
		'Ạ' => '\u1ea0',
		'Ă' => '\u0102',
		'Ắ' => '\u1eae',
		'Ặ' => '\u1eb6',
		'Ằ' => '\u1eb0',
		'Ẳ' => '\u1eb2',
		'Ẵ' => '\u1eb4',
		'Â' => '\u00c2',
		'Ấ' => '\u1ea4',
		'Ầ' => '\u1ea6',
		'Ẩ' => '\u1ea8',
		'Ẫ' => '\u1eaa',
		'Ậ' => '\u1eac',
		'đ' => '\u0111',
		'Đ' => '\u0110',
		'é' => '\u00e9',
		'è' => '\u00e8',
		'ẻ' => '\u1ebb',
		'ẽ' => '\u1ebd',
		'ẹ' => '\u1eb9',
		'ê' => '\u00ea',
		'ế' => '\u1ebf',
		'ề' => '\u1ec1',
		'ể' => '\u1ec3',
		'ễ' => '\u1ec5',
		'ệ' => '\u1ec7',
		'É' => '\u00c9',
		'È' => '\u00c8',
		'Ẻ' => '\u1eba',
		'Ẽ' => '\u1ebc',
		'Ẹ' => '\u1eb8',
		'Ê' => '\u00ca',
		'Ế' => '\u1ebe',
		'Ề' => '\u1ec0',
		'Ể' => '\u1ec2',
		'Ễ' => '\u1ec4',
		'Ệ' => '\u1ec6',
		'í' => '\u00ed',
		'ì' => '\u00ec',
		'ỉ' => '\u1ec9',
		'ĩ' => '\u0129',
		'ị' => '\u1ecb',
		'Í' => '\u00cd',
		'Ì' => '\u00cc',
		'Ỉ' => '\u1ec8',
		'Ĩ' => '\u0128',
		'Ị' => '\u1eca',
		'ó' => '\u00f3',
		'ò' => '\u00f2',
		'ỏ' => '\u1ecf',
		'õ' => '\u00f5',
		'ọ' => '\u1ecd',
		'ô' => '\u00f4',
		'ố' => '\u1ed1',
		'ồ' => '\u1ed3',
		'ổ' => '\u1ed5',
		'ỗ' => '\u1ed7',
		'ộ' => '\u1ed9',
		'ơ' => '\u01a1',
		'ớ' => '\u1edb',
		'ờ' => '\u1edd',
		'ở' => '\u1edf',
		'ỡ' => '\u1ee1',
		'ợ' => '\u1ee3',
		'Ó' => '\u00d3',
		'Ò' => '\u00d2',
		'Ỏ' => '\u1ece',
		'Õ' => '\u00d5',
		'Ọ' => '\u1ecc',
		'Ô' => '\u00d4',
		'Ố' => '\u1ed0',
		'Ồ' => '\u1ed2',
		'Ổ' => '\u1ed4',
		'Ỗ' => '\u1ed6',
		'Ộ' => '\u1ed8',
		'Ơ' => '\u01a0',
		'Ớ' => '\u1eda',
		'Ờ' => '\u1edc',
		'Ở' => '\u1ede',
		'Ỡ' => '\u1ee0',
		'Ợ' => '\u1ee2',
		'ú' => '\u00fa',
		'ù' => '\u00f9',
		'ủ' => '\u1ee7',
		'ũ' => '\u0169',
		'ụ' => '\u1ee5',
		'ư' => '\u01b0',
		'ứ' => '\u1ee9',
		'ừ' => '\u1eeb',
		'ử' => '\u1eed',
		'ữ' => '\u1eef',
		'ự' => '\u1ef1',
		'Ú' => '\u00da',
		'Ù' => '\u00d9',
		'Ủ' => '\u1ee6',
		'Ũ' => '\u0168',
		'Ụ' => '\u1ee4',
		'Ư' => '\u01af',
		'Ứ' => '\u1ee8',
		'Ừ' => '\u1eea',
		'Ử' => '\u1eec',
		'Ữ' => '\u1eee',
		'Ự' => '\u1ef0',
		'ý' => '\u00fd',
		'ỳ' => '\u1ef3',
		'ỷ' => '\u1ef7',
		'ỹ' => '\u1ef9',
		'ỵ' => '\u1ef5',
		'Ý' => '\u00dd',
		'Ỳ' => '\u1ef2',
		'Ỷ' => '\u1ef6',
		'Ỹ' => '\u1ef8',
		'Ỵ' => '\u1ef4',
		// Loại bỏ dòng trắng
//			';if (' => ';if(',
//			'{if (' => '{if(',
//			'}if (' => '}if(',
//			'} else if (' => '}else if(',
//			'} else {' => '}else{',
//			'}else {' => '}else{',
//			';for (' => ';for(',
//			'}for (' => '}for(',
//			'function (' => 'function(',
//			//
//			' != ' => '!=',
//			' == ' => '==',
//			' || ' => '||',
//			' -= ' => '-=',
//			' += ' => '+=',
//			' && ' => '&&',
//			//
//			') {' => '){',
//			';}' => '}',
//			' = \'' => '=\'',
//			'\' +' => '\'+',
//			'+ \'' => '+\'',
//			' = ' => '=',
//			'}, {' => '},{',
//			'}, ' => '},',
		'\'' => '\\\'',
		'"' => '&quot;',
//		'' => ''
	);
	foreach ($arr as $k => $v) {
		if ($v != '') {
			$str = str_replace($k, $v, $str);
		}
	}
//	$str = str_replace('\\', '/', str_replace("'", "\'", $str) );
	return $str;
}





/*
 * Show in admin
 */
function ESE_show_setting_form_in_admin() {
	global $ESE_func;
	
	$ESE_func->update ();
	
	$ESE_func->admin ();
}

function ESE_add_menu_setting_to_admin_menu() {
	// only show menu if administrator login
	if ( ! current_user_can('manage_options') )  {
		return false;
	}
	
	// menu name
	$a = ESE_THIS_PLUGIN_NAME;
	
	// add main menu
	if ( ESE_ADD_TO_SUB_MENU == false ) {
		add_menu_page( $a, EBP_GLOBAL_PLUGINS_MENU_NAME, 'manage_options', EBP_GLOBAL_PLUGINS_SLUG_NAME, 'ESE_show_setting_form_in_admin', NULL, 99 );
	}
	
	// add sub-menu
	add_submenu_page( EBP_GLOBAL_PLUGINS_SLUG_NAME, $a, trim( str_replace( 'EchBay', '', $a ) ), 'manage_options', strtolower( str_replace( ' ', '-', $a ) ), 'ESE_show_setting_form_in_admin' );
}



/*
 * Show in theme
 */
function ESE_show_echbay_search_box_in_site() {
	global $ESE_func;
	
	$ESE_func->guest ();
}




// Add settings link on plugin page
function ESE_plugin_settings_link ($links) { 
	$settings_link = '<a href="admin.php?page=' . strtolower( str_replace( ' ', '-', ESE_THIS_PLUGIN_NAME ) ) . '">Settings</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}


// end class.php






//
$ESE_func = new ESE_Actions_Module ();

// load custom value in database
$ESE_func->load ();

// check and call function for admin
if (is_admin ()) {
	add_action ( 'admin_menu', 'ESE_add_menu_setting_to_admin_menu' );
	
	
	//
	if ( strstr( $_SERVER['REQUEST_URI'], 'plugins.php' ) == true ) {
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'ESE_plugin_settings_link' );
	}
}
// or guest (public in theme)
else {
	add_action ( 'wp_footer', 'ESE_show_echbay_search_box_in_site' );
}




