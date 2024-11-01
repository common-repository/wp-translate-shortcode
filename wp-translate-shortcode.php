<?php
/*
Plugin Name: WP Translate Shortcode
Plugin URI: http://e-joint.jp/works/wp-translate-shortcode/
Description: It is a WordPress plugin that makes translate style easily with Shortcode.
Version: 0.1.0
Author: e-JOINT.jp
Author URI: http://e-joint.jp
Text Domain: wp-translate-shortcode
Domain Path: /languages
License: GPL2
*/

/*  Copyright 2018 e-JOINT.jp (email : mail@e-joint.jp)

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

class wp_translate_shortcode
{

  private $options;
  const VERSION = '0.1.0';

  public function __construct(){

    //翻訳ファイルの読み込み
    load_plugin_textdomain('wp-translate-shortcode', false, basename(dirname(__FILE__)) . '/languages');

    //設定画面を追加
    add_action('admin_menu', array(&$this, 'add_plugin_page'));

    //設定画面の初期化
    add_action('admin_init', array(&$this, 'page_init'));

    //スタイルシートの読み込み
    add_action('wp_enqueue_scripts', array(&$this, 'add_styles'));

    //ショートコードを使えるようにする
    add_shortcode('wptr', array(&$this, 'generate_shortcode'));
    add_shortcode('wptr-original', array(&$this, 'generate_shortcode_origin'));
    add_shortcode('wptr-translated', array(&$this, 'generate_shortcode_translated'));
  }

  //設定画面を追加
  public function add_plugin_page() {

    add_options_page(
      __('WP Translate', 'wp-translate-shortcode' ),
      __('WP Translate', 'wp-translate-shortcode' ),
      'manage_options',
      'wptr-setting',
      array(&$this, 'create_admin_page')
    );
  }

  //設定画面を生成
  public function create_admin_page() {

    $this->options = get_option( 'wptr-setting' );
    ?>
    <div class="wrap">
      <h2>WP Translate Shortcode</h2>
      <?php

      global $parent_file;
      if ( $parent_file != 'options-general.php' ) {
        require(ABSPATH . 'wp-admin/options-head.php');
      }
      ?>

      <form method="post" action="options.php">
      <?php
        settings_fields( 'wptr-setting' );
        do_settings_sections( 'wptr-setting' );
        submit_button();
      ?>
      </form>

      <h3><?php echo __('How to Use', 'wp-translate-shortcode'); ?></h3>
      <ol>
        <li><?php echo __('Please drag and drop the link (bookmarklet) below to the bookmark bar.', 'wp-translate-shortcode'); ?></li>
        <li><?php echo __('Open the web page containing the statement you want to translate in a separate tab (another window).', 'wp-translate-shortcode'); ?></li>
        <li><?php echo __('Drag the sentence you want to translate and select it.', 'wp-translate-shortcode'); ?></li>
        <li><?php echo __('With the sentence selected, click on the bookmarklet to execute it.', 'wp-translate-shortcode'); ?></li>
        <li><?php echo __('A short code will be displayed in the dialog box, please copy and paste it in the WordPress article.', 'wp-translate-shortcode'); ?></li>
      </ol>

      <h3><?php echo __('Bookmarklet', 'wp-translate-shortcode'); ?></h3>

      <p><?php echo __('Please drag and drop the link (bookmarklet) below to the bookmark bar.', 'wp-translate-shortcode'); ?></p>
      <p><a href="javascript:var d=document;var c=d.selection?d.selection.createRange().text:d.getSelection();var t='Wp Translate Shortcode';var s='[wptr label="" uri="'+location.href+'"]\n'+'[wptr-original]'+c+'[/wptr-original]\n'+'[wptr-translated][/wptr-translated]\n'+'[/wptr]';window.prompt(t,s);void(0);">WP Translate Shortcode</a></p>

      <p><?php echo __('If you want to customize it freely, create an empty short code with the bookmarklet below and customize it.', 'wp-translate-shortcode'); ?></p>
      <p><a href="javascript:var d=document;var c=d.selection?d.selection.createRange().text:d.getSelection();var t='Wp Translate Shortcode';var s='[wptr label="" uri=""]\n'+'[wptr-original][/wptr-original]\n'+'[wptr-translated][/wptr-translated]\n'+'[/wptr]';window.prompt(t,s);void(0);">WP Translate Shortcode Empty</a></p>

      <h3><?php echo __('Shortcode customize', 'wp-translate-shortcode'); ?></h3>

      <h4><?php echo __('Sample', 'wp-translate-shortcode'); ?></h4>
      <pre>[wptr label="" uri=""]
[wptr-original][/wptr-original]
[wptr-translated][/wptr-translated]
[/wptr]</pre>

      <h4><?php echo __('Display source link', 'wp-translate-shortcode'); ?></h4>
      <p><?php echo __('To display the source link, enter the URL in the "uri" attribute. The link label automatically shows the domain name.', 'wp-translate-shortcode'); ?></p>

      <h4><?php echo __('Change the notation of the source link', 'wp-translate-shortcode'); ?></h4>
      <p><?php echo __('If you want to change the notation of the source link, enter an arbitrary name in the "label" attribute.', 'wp-translate-shortcode'); ?></p>

      <h4><?php echo __('Display source name without link', 'wp-translate-shortcode'); ?></h4>
      <p><?php echo __('If you want to display the source without link, delete or empty the "uri" attribute and enter the name you want to display in "label" attribute.', 'wp-translate-shortcode'); ?></p>

      <h4><?php echo __('Original text', 'wp-translate-shortcode'); ?></h4>
      <p><?php echo __('Insert the text between [wptr-original] and [/wptr-original].', 'wp-translate-shortcode'); ?></p>

      <h4><?php echo __('Translated Text', 'wp-translate-shortcode'); ?></h4>
      <p><?php echo __('Insert the text between [wptr-translated] and [/wptr-translated].', 'wp-translate-shortcode'); ?></p>

    </div>
  <?php
  }

  //設定画面の初期化
  public function page_init(){
    register_setting('wptr-setting', 'wptr-setting');
    add_settings_section('wptr-setting-section-id', '', '', 'wptr-setting');

    add_settings_field( 'nocss', __('Do not use default CSS', 'wp-translate-shortcode'), array( &$this, 'nocss_callback' ), 'wptr-setting', 'wptr-setting-section-id' );
  }

  public function nocss_callback(){
    $checked = isset($this->options['nocss']) ? checked($this->options['nocss'], 1, false) : '';
    ?><input type="checkbox" id="nocss" name="wptr-setting[nocss]" value="1"<?php echo $checked; ?>><?php
  }

  //スタイルシートの追加
  public function add_styles() {
    $this->options = get_option('wptr-setting');

    if(isset($this->options['nocss'])) {
      if ( !$this->options['nocss'] ) {
        wp_enqueue_style('wptr', plugins_url('assets/css/wp-translate-shortcode.css', __FILE__), array(), null, 'all');
      }
    } else {
      wp_enqueue_style('wptr', plugins_url('assets/css/wp-translate-shortcode.css', __FILE__), array(), null, 'all');
    }
  }

  public function generate_shortcode($atts, $text){
    extract( shortcode_atts( array(
      'uri' => null,
      'label' => ""
    ), $atts ));


    if($uri) {

      if(!$label) {
        $parse = parse_url($uri);
        $domain = $parse['host'];
        $label = $domain;
      }

      $cite = '<a class="wptr__cite__a" href="' . esc_url($uri) . '">' . $label . '</a>';

    } else {

      if($label) {
        // ラベル
        $cite = $label;
      } else {
        // 非表示
        $cite = "";
      }
    }

    $replaced = preg_replace('/\<p\>|\<\/p\>|\<br \/\>/', '', $text); //pとbrを削除

    $html = '<div class="wptr">';
    $html .= do_shortcode($replaced);

    if($cite) {
      $html .= '<p class="wptr__cite"><cite class="wptr__cite__cite">' . __('Original Source', 'wp-translate-shortcode') . ': ' . $cite . '</cite></p>';
    }

    $html .= '</div>';

    return $html;
  }

  public function generate_shortcode_origin($atts, $text) {

    if($text) {
      $html = '<blockquote class="wptr__origin">';
      $html .= $text;
      $html .= '</blockquote>';

      return $html;
    }
  }

  public function generate_shortcode_translated($atts, $text) {

    if($text) {
      $html = '<div class="wptr__translated">';
      $html .= $text;
      $html .= '</div>';

      return $html;
    }
  }
}

$wptr = new wp_translate_shortcode();
