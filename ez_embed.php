<?php
/**
 * Plugin Name: Easy Code Embed
 * Description: This plugin directly injects raw code into a shortcode.
 * Version: 1.0
 * Author: Hanky Panky
 */


/** Step 2 (from text above). */
add_action( 'admin_menu', 'my_plugin_menu' );

/** Step 1. */
function my_plugin_menu() {
	add_options_page( 'My Plugin Options', 'My Plugin', 'manage_options', 'my-unique-identifier', 'my_plugin_options' );
}

/** Step 3. */
function my_plugin_options() {
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
       var postIndex = titleArray.join('-');
       d.getElementById('post_index').value = postIndex;
       d.getElementById('shortcode_display').textContent = '[ez-embed title="' + postIndex + '"]';
   }
  }
  </script>
  <?php
	echo '<div class="wrap">';
  echo '<div id="shortcode_display"></div>';
	echo '<form id="easy-embed" method="post" action="schema.php">
          <p><label for="easy-embed-title">Title your shortcode:</label></p>
          <p><input id="easy-embed-title" type="text" class="wide-fat" name="title"></p>
          <p><label for="easy-embed-text">Write your code here:</label></p>
          <p><textarea id="easy-embed-text" class="wide-fat" name="content" cols="50" rows="100"></textarea></p>
          <input type="submit" value="submit" />
          <input id="post_index" type="hidden" name="post_index" />
        </form>';
	echo '</div>';
}

if ( isset($_POST['post_index']) && isset($_POST['title']) && isset($_POST['content']) ) {
  if (get_option('easy_code_embed')) {
    $code_embed = maybe_unserialize( get_option('easy_code_embed') );
    $code_embed[$_POST['post_index']]['title'] = $_POST['title'];
    $code_embed[$_POST['post_index']]['content'] = $_POST['content'];
    update_option('easy_code_embed', $code_embed);
  } else {
    $new_code_embed = array(
      $_POST['post_index'] => array(
        'title' => $_POST['title'],
        'content' => $_POST['content']
      ),
    );
    update_option('easy_code_embed', $new_code_embed);
  }
}

function ez_embed_shortcode() {
    $a = shortcode_atts( array(
        'foo' => 'something',
        'bar' => 'something else',
    ), $atts );

    return "foo = {$a['foo']}";
}
add_shortcode( 'bartag', 'ez_embed_shortcode' );
?>
