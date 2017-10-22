

// tạo hiệu ứng nạp dữ liệu từ từ -> chỉ nạp khi người dùng có nhu cầu
var ESE_load_data_after_click_search_box = false;

function ESE_javascript_running ( i ) {
	if ( typeof i != 'number' ) {
		i = 50;
	}
	else if ( i < 0 ) {
		console.log('[EchBay Search Everything] jQuery not found');
		return false;
	}
	
	if ( typeof jQuery != 'function' ) {
		setTimeout(function () {
			ESE_javascript_running( i - 1);
		}, 200);
		return false;
	}
	
	//
	jQuery('form input[name="s"]').click(function () {
		if ( ESE_load_data_after_click_search_box == false ) {
			ESE_load_data_after_click_search_box = true;
			
			//
			console.log('[EchBay Search Everything] load site data');
			
			// nạp file JS và CSS
			(function(d, o, rc) {
			var a = d.createElement(o),
				m = d.getElementsByTagName(o)[0];
				a.async = 1;
			a.src = rc;
			m.parentNode.insertBefore(a, m)
			})(document, 'script', ESE_cache_file_url);
			
			jQuery('body').append('<link rel="stylesheet" href="' + ESE_plugin_url + 'guest.css?v=' + ESE_search_version + '" type="text/css" />');
			
			// nạp dữ liệu
			ESE_javascript_load_data();
		}
	});
}


function ESE_non_mark_seo ( str ) {
	if ( typeof g_func != 'undefined' && g_func.non_mark_seo == 'function' ) {
		str = g_func.non_mark_seo(str);
		return str.replace(/[^0-9a-zA-Z]/g, '');
	}
	
	// non mark
	str = str.toLowerCase();
	str = str.replace(/\u00e0|\u00e1|\u1ea1|\u1ea3|\u00e3|\u00e2|\u1ea7|\u1ea5|\u1ead|\u1ea9|\u1eab|\u0103|\u1eb1|\u1eaf|\u1eb7|\u1eb3|\u1eb5/g, "a");
	str = str.replace(/\u00e8|\u00e9|\u1eb9|\u1ebb|\u1ebd|\u00ea|\u1ec1|\u1ebf|\u1ec7|\u1ec3|\u1ec5/g, "e");
	str = str.replace(/\u00ec|\u00ed|\u1ecb|\u1ec9|\u0129/g, "i");
	str = str.replace(/\u00f2|\u00f3|\u1ecd|\u1ecf|\u00f5|\u00f4|\u1ed3|\u1ed1|\u1ed9|\u1ed5|\u1ed7|\u01a1|\u1edd|\u1edb|\u1ee3|\u1edf|\u1ee1/g, "o");
	str = str.replace(/\u00f9|\u00fa|\u1ee5|\u1ee7|\u0169|\u01b0|\u1eeb|\u1ee9|\u1ef1|\u1eed|\u1eef/g, "u");
	str = str.replace(/\u1ef3|\u00fd|\u1ef5|\u1ef7|\u1ef9/g, "y");
	str = str.replace(/\u0111/g, "d");
	
	// non mark seo
	str = str.replace(/\s/g, "-");
	str = str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'|\"|\&|\#|\[|\]|~|$|_/g, "");
	str = str.replace(/-+-/g, "-");
	str = str.replace(/^\-+|\-+$/g, "");
	for (var i = 0; i < 5; i++) {
		str = str.replace(/--/g, '-');
	}
	str = (function(s) {
		var str = '',
			re = /^\w+$/,
			t = '';
		for (var i = 0; i < s.length; i++) {
			t = s.substr(i, 1);
			if (t == '-' || t == '+' || re.test(t) == true) {
				str += t;
			}
		}
		return str;
	})(str);
	
	//
	return str.replace(/[^0-9a-zA-Z]/g, '');
}


function ESE_number_only (str) {
	if (typeof str == 'undefined' || str == '') {
		return 0;
	}
	str = str.toString().replace(/[^0-9]/g, '');
	
	if (str == '') {
		return 0;
	}
	
	return str;
}

function ESE_get_num_margin_padding ( str ) {
	var a = ESE_number_only( str );
	console.log(a);
	if ( a > 0 ) {
		return 0 - a;
	}
	return 0;
}


function ESE_javascript_load_data ( i ) {
	if ( typeof i != 'number' ) {
		i = 50;
	}
	else if ( i < 0 ) {
		console.log('[EchBay Search Everything] eb_ese_site_js_data not found');
		return false;
	}
	
	if ( typeof eb_ese_site_js_data == 'undefined' ) {
		setTimeout(function () {
			ESE_javascript_load_data( i - 1);
		}, 200);
		return false;
	}
	
	//
//	console.log( eb_ese_site_js_data );
//	console.log( eb_ese_site_js_data.length );
	
	//
	if ( eb_ese_site_js_data.length == 0 ) {
		console.log('[EchBay Search Everything] eb_ese_site_js_data empty');
		return false;
	}
	
	// khi người dùng bấm vào ô tìm kiếm
	jQuery('form input[name="s"]').each(function() {
		
		// tắt chức năng tự động hoàn thành
		jQuery(this).attr({
			autocomplete: 'off'
		});
		
		//
		var jd = jQuery(this).attr('id') || '';
		if ( jd == '' ) {
			jd = 'ese-id' + Math.random() + Math.random();
//			console.log(jd);
			jd = jd.replace( /\./g, '_' );
//			console.log(jd);
		}
		jQuery(this).attr({
			id: jd
		});
		
		//
		jQuery(this).before('<div data-id="' + jd + '" class="echbay-search-everything"><div class="echbay-search-everything-margin"></div></div>').click(function () {
			
			//
			var search_id = jQuery(this).attr('id') || '';
			
			//
			if ( search_id == '' ) {
				console.log('id not found');
				return false;
			}
//			console.log(search_id);
			
			//
			var search_input = jQuery('#' + search_id);
			
			//
			jQuery('.echbay-search-everything').hide();
			jQuery('div[data-id="' + search_id + '"]').show();
			
		}).focus(function () {
			jQuery(this).click();
		}).blur(function () {
			jQuery('div[data-id="' + jd + '"]').fadeOut();
		}).off('keyup').keyup(function (e) {
			if (e.keyCode == 13) {
				return false;
			}
			
			//
			var key = jQuery(this).val() || '';
			if (key != '') {
				key = ESE_non_mark_seo(key);
//				console.log(key);
			}
			
			//
			var search_result = jQuery('div[data-id="' + jd + '"] li');
			
			//
			if (key != '') {
				search_result.hide().each(function() {
					if (a != '') {
						var a = jQuery(this).attr('data-key') || '';
						if (a != '' && a.split(key).length > 1) {
							jQuery(this).show();
						}
					}
				});
				
				jQuery('div[data-id="' + jd + '"] li[data-show="1"]').show()
			} else {
				jQuery('div[data-id="' + jd + '"] li').show()
			}
			
		});
	});
	
	
	// nạp dữ liệu
	var str = '',
		menu_url = '',
		post_type = '';
	for ( var i = 0; i < eb_ese_site_js_data.length; i++ ) {
		var arr_node = eb_ese_site_js_data[i];
//		console.log(arr_node);
		
		//
		for ( var j = 0; j < arr_node.length; j++ ) {
			if ( j == 0 ) {
				str += '<li data-show="1" class="ese-posttype-taxonomy">' + arr_node[j].name + '</li>';
				
				if ( typeof arr_node[j].taxonomy != 'undefined' ) {
					post_type = arr_node[j].taxonomy;
				}
				else {
					post_type = 'post';
				}
			}
			else {
				menu_url = ESE_web_link + '/?';
				if ( post_type == 'post' ) {
					menu_url += 'p=' + arr_node[j].id;
				}
				else if ( post_type == 'category' ) {
					menu_url += 'cat=' + arr_node[j].id;
				}
				else {
					menu_url += 'taxonomy=' + post_type + '&cat=' + arr_node[j].id;
				}
				
				//
				str += '<li data-key="' + ESE_non_mark_seo( arr_node[j].ten ) + '"><a href="' + menu_url + '">' + arr_node[j].ten + '</a></li>';
			}
		}
	}
	jQuery('.echbay-search-everything-margin').html('<div class="echbay-search-everything-padding"><ul>' + str + '</ul></div>');
	
	
	//
	jQuery('form').each(function() {
		if ( jQuery('input[name="s"]', this).length > 0 ) {
			var apadding = jQuery(this).height(),
				awidth = jQuery(this).width();
			
			//
			jQuery('.echbay-search-everything-margin', this).css({
				'top': apadding + 'px'
			}).width( awidth );
		}
	});
	
}
ESE_javascript_running();



