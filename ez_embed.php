<?php

/**
 * Plugin Name: Easy Code Embed
 * Description: This plugin directly injects raw code into a shortcode.
 * Version: 1.0.3
 * Author: Jake Cox
 */

add_action( 'admin_menu', 'ez_embed_menu' );

/** Step 1. */
function ez_embed_menu() {
	add_menu_page( 'Easy Code Embed', 'EZ Code Embed', 'manage_options', 'easy_code_embed', 'easy_code_embed', 'dashicons-schedule', '100.3' );
}

/** Step 3. */
function easy_code_embed() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    <script type="text/javascript">
		window.onload = function() {
			document.getElementById('easy-embed-title').addEventListener('keyup', updateTitle);

			function updateTitle() {
				var d = document;
				var ezTitle = d.getElementById('easy-embed-title').value;
				var titleArray = ezTitle.split(' ');
				var postIndex = titleArray.join('_');
				d.getElementById('post_index').value = postIndex;
				d.getElementById('shortcode_display').textContent = '[ez_embed title="' + postIndex + '"]';
			}
		} 
    </script> 
	<?php
    $shortcodes = maybe_unserialize( get_option('easy_code_embed') );
    echo '<h1>Your Shortcodes</h1>';
    $scHTML = '<div id="shortcode_html">';
    foreach ($shortcodes as $key => $sc_array) {
        $scHTML .= '<div style="display: inline-block; padding: 8px 15px; box-shadow: 1px 1px 7px #333; background: #fff; margin-right: 12px; border-radius: 5px;"><h4 style="text-align: center;">[ez_embed title="' . $key . '"]' . '</h4>
        <a style="display: inline-block; padding: 5px 10px;" href="javascript:void()" class="edit_link" data-index="' . $key . '">Edit</a>
        <a style="display: inline-block; padding: 5px 10px;" href="javascript:void()" class="delete_link" data-index="' . $key . '">Delete</a></div>';
    }
    $scHTML .= '</div>';
    echo $scHTML;
	echo '<div class="wrap">';
	echo '<div id="shortcode_display"></div>';
	echo '<form id="easy-embed" method="post">
			<p><label for="easy-embed-title">Title your shortcode:</label></p>
			<p><input id="easy-embed-title" type="text" class="wide-fat" name="title" /></p>
			<p><label for="easy-embed-text">Write your code here:</label></p>
			<p><textarea id="easy-embed-text" class="wide-fat" name="content" cols="100" rows="20"></textarea></p>			
			<input id="post_index" type="hidden" name="post_index" />
		  </form>';
    echo '<button id="submit">Submit</button>';
    echo '<div id="response"></div>';
	echo '</div>';
}

add_action( 'admin_footer', 'ez_embed_js' );

function ez_embed_js() { 
$ajax_nonce = wp_create_nonce( "ez_embed_nonce" );
?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('#submit').on('click', function() {
                var data = {
                    'action': 'ez_embed_update',
                    'security': '<?php echo $ajax_nonce; ?>',
                    'title': $('#easy-embed-title').val(),
                    'content': $('#easy-embed-text').val(),
                    'post_index': $('#post_index').val()
                };
                $.post(ajaxurl, data, function (response) {
                    $('#response').html(response);
                });
                setTimeout(function(){location.reload()}, 1000);
            });
            $('.edit_link').on('click', function() {
                var data = {
                    'action': 'ez_embed_get',
                    'security': '<?php echo $ajax_nonce; ?>',
                    'shortcode': $(this).attr('data-index')
                };
                $.post(ajaxurl, data, function (response) {
                    var returnArray = JSON.parse(response);
                    for (var i in returnArray) {
                       $('#' + i).val(returnArray[i]);
                    }
                });
            });
            $('.delete_link').on('click', function() {
                var data = {
                    'action': 'ez_embed_delete',
                    'security': '<?php echo $ajax_nonce; ?>',
                    'shortcode': $(this).attr('data-index')
                };
                $.post(ajaxurl, data, function (response) {
                    $('#response').html(response);
                });
                $(this).parent().remove();
            });
        });
    </script>
<?php }
    
add_action('wp_ajax_ez_embed_update', 'ez_embed_callback');
    
function ez_embed_callback() {
    check_ajax_referer( 'ez_embed_nonce', 'security' );
    if (get_option('easy_code_embed')) {
        $code_embed = maybe_unserialize( get_option('easy_code_embed') );
        $code_embed[$_POST['post_index']]['title'] = $_POST['title'];
        $code_embed[$_POST['post_index']]['content'] = $_POST['content'];
        update_option('easy_code_embed', $code_embed);
    } else {
        $new_easy_code_embed = array(
            $_POST['post_index'] => array (
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'post_index' => $_POST['post_index']
            ),
        );
        update_option('easy_code_embed', $new_easy_code_embed);
    }
    echo '<h2>Your shortcode was saved!</h2>';
    die();
}

add_action('wp_ajax_ez_embed_get', 'ez_embed_get_callback');
    
function ez_embed_get_callback() {
    check_ajax_referer( 'ez_embed_nonce', 'security' );
    $content = maybe_unserialize( get_option('easy_code_embed') );
    $return_array = array(
        'easy-embed-title' => $content[$_POST['shortcode']]['title'],
        'easy-embed-text' => stripslashes($content[$_POST['shortcode']]['content']),
        'post_index' => $_POST['shortcode']
    );
    echo json_encode($return_array);
    die();
}

add_action('wp_ajax_ez_embed_delete', 'ez_embed_delete_callback');
    
function ez_embed_delete_callback() {
    check_ajax_referer( 'ez_embed_nonce', 'security' );
    $content = maybe_unserialize( get_option('easy_code_embed') );    
    unset($content[$_POST['shortcode']]);
    update_option('easy_code_embed', $content);
    echo '<h2>Your shortcode was removed!</h2>';
    die();
}
    
add_shortcode( 'ez_embed', 'ez_embed_shortcode' );

function ez_embed_shortcode($atts) {
    $ez_embed = maybe_unserialize( get_option('easy_code_embed') );
    return stripslashes($ez_embed[$atts['title']]['content']);
}
