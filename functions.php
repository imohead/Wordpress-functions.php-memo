<?php
/* more-linkのハッシュ消し */
function remove_more_jump_link($link) {
  $offset = strpos($link, '#more-');
  if ($offset) {
    $end = strpos($link, '"',$offset);
  }
  if ($end) {
    $link = substr_replace($link, '', $offset, $end-$offset);
  }
  return $link;
}
add_filter('the_content_more_link', 'remove_more_jump_link');


//不要なもの
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'index_rel_link' );
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action( 'wp_head', 'wp_generator' );
// remove wp version param from any enqueued scripts
function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'vc_remove_wp_ver_css_js', 9999 );


// 不要な投稿ページの枠
function remove_default_post_screen_metaboxes() {
    remove_meta_box( 'trackbacksdiv','post','normal' );     // トラックバック送信
    remove_meta_box( 'commentstatusdiv','post','normal' );  // ディスカッション
    remove_meta_box( 'commentsdiv','post','normal' );       // コメント
    //remove_meta_box( 'slugdiv','post','normal' );           // スラッグ
    remove_meta_box( 'formatdiv','post','normal' );         // フォーマット
    //remove_meta_box( 'categorydiv','post','normal' ); // カテゴリ
    //remove_meta_box( 'tagsdiv-post_tag','post','normal' ); // カテゴリ
}
add_action('admin_menu','remove_default_post_screen_metaboxes');
// 不要な固定ページ
function remove_default_page_screen_metaboxes() {
	remove_meta_box( 'commentstatusdiv','page','normal' );  // ディスカッション
	remove_meta_box( 'commentsdiv','page','normal' );       // コメント
	//remove_meta_box( 'slugdiv','page','normal' );           // スラッグ
}
add_action('admin_menu','remove_default_page_screen_metaboxes');

//リビジョン数
define(WP_POST_REVISION, 10);

// 使用しないメニューを非表示にする
function remove_admin_menus() {
global $menu;
 // unsetで非表示にするメニューを指定
 unset($menu[25]);       // コメント
 //unset($menu[5]);        // 投稿
}
add_action('admin_menu', 'remove_admin_menus');

// 使用しない管理画面サブメニューを非表示にする
/*
function remove_submenus() {
	remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category'); // 投稿 -> カテゴリ
	remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag'); // 投稿 -> タグ
}
add_action('admin_menu', 'remove_submenus', 102);
*/


//アップデート通知OFF
add_filter( 'pre_site_transient_update_core', '__return_zero' );
remove_action( 'wp_version_check', 'wp_version_check' );
remove_action( 'admin_init', '_maybe_update_core' );

//プラグインアップデート通知OFF
add_action('admin_menu', 'remove_counts');
function remove_counts(){
	global $menu,$submenu;
	$menu[65][0] = 'プラグイン';
	$submenu['index.php'][10][0] = '更新';
}

//管理バーのアップデート通知OFF
add_action( 'wp_before_admin_bar_render', 'hide_before_admin_bar_render' );
function hide_before_admin_bar_render() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'updates' );
}

//抜粋のカスタマイズ
function new_excerpt_mblength($length) {
     return 150;
}
add_filter('excerpt_mblength', 'new_excerpt_mblength');
function new_excerpt_more($more) {
	return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');

//SEOカスタムフィールド
add_action('admin_menu', 'add_custom_fields');
add_action('save_post', 'save_custom_fields');

function add_custom_fields() {
  add_meta_box( 'my_sectionid', 'SEO用入力フィールド', 'my_custom_fields', 'post');
  add_meta_box( 'my_sectionid', 'SEO用入力フィールド', 'my_custom_fields', 'page');
}

function my_custom_fields() {
  global $post;
  $meta_keywords = get_post_meta($post->ID,'meta_keywords',true);
  $meta_description = get_post_meta($post->ID,'meta_description',true);
  $h1 = get_post_meta($post->ID,'h1',true);
  $pagetitle = get_post_meta($post->ID,'pagetitle',true);

  echo '<p>ページタイトル(titleタグ)<br />';
  echo '<input type="text" name="pagetitle" value="'.esc_html($pagetitle).'" size="40" /></p>';
  echo '<p>キーワード（meta keyword）カンマ区切り。2〜4つまで<br />';
  echo '<input type="text" name="meta_keywords" value="'.esc_html($meta_keywords).'" size="40" /></p>';
  echo '<p>ディスクリプション（meta description）100文字まで<br />';
  echo '<input type="text" name="meta_description" value="'.esc_html($meta_description).'" size="40" /></p>';
  echo '<p>ヘッダーテキスト（h1）100文字以内を推奨<br />';
  echo '<input type="text" name="h1" value="'.esc_html($h1).'" size="50" /></p>';
}
// カスタムフィールドの値を保存
function save_custom_fields( $post_id ) {
  if(!empty($_POST['pagetitle']))
    update_post_meta($post_id, 'pagetitle', $_POST['pagetitle'] );
  else delete_post_meta($post_id, 'pagetitle');

  if(!empty($_POST['meta_keywords']))
    update_post_meta($post_id, 'meta_keywords', $_POST['meta_keywords'] );
  else delete_post_meta($post_id, 'meta_keywords');

  if(!empty($_POST['meta_description']))
    update_post_meta($post_id, 'meta_description', $_POST['meta_description'] );
  else delete_post_meta($post_id, 'meta_description');

  if(!empty($_POST['h1']))
    update_post_meta($post_id, 'h1', $_POST['h1'] );
  else delete_post_meta($post_id, 'h1');
}


//管理画面URL変更
function redirect_login(){
  $uri = getenv('REQUEST_URI');
  if(!strpos($uri, 'hogehogehogehoge')){
    if( !is_user_logged_in() ){
      wp_redirect( home_url( '/' ) );
    }
  }
}
add_action( 'login_form_login', 'redirect_login' );


//カスタム投稿タイプ ニュース
register_post_type(
'news',
array(
'label'=>'ニュース',
'description'=>'ニュース',
'hierarchical'=>false,
'public'=>true,
'has_archive'=>true,
'supports'=>array(
			'title',
			),
			'rewrite'=>array('with_front'=>false,
			)
	)
);

//カスタムタクソノミー（カテゴリ–形式）
register_taxonomy(
	'news_cat',
	'news',
	array(
		'label'=>'ニュースのカテゴリー',
		'hierarchical'=>true,
		'rewrite'=>array(
		'slug'=>'news/cat',
		'with_front'=>false
		)
	)
);

// パンくずリスト
function breadcrumb(){
    global $post;
    $str ='';
    if(!is_home()&&!is_admin()){
        $str.= '<div id="breadcrumb" class="cf"><ul>';
        $str.= '<li><a href="'. home_url() .'" itemprop="url"><span itemprop="title">ホーム</span></a></li>';

        if(is_category()) {
            $cat = get_queried_object();
            if($cat -> parent != 0){
                $ancestors = array_reverse(get_ancestors( $cat -> cat_ID, 'category' ));
                foreach($ancestors as $ancestor){
                    $str.='<li><a href="'. get_category_link($ancestor) .'" itemprop="url"><span itemprop="title">'. get_cat_name($ancestor) .'</span></a> &gt;</li>';
                }
            }
        $str.='<li><a href="'. get_category_link($cat -> term_id). '" itemprop="url"><span itemprop="title">'. $cat-> cat_name . '</span></a></li>';
        } elseif(is_page()){
            if($post -> post_parent != 0 ){
                $ancestors = array_reverse(get_post_ancestors( $post->ID ));
                foreach($ancestors as $ancestor){
                    $str.='<li><a href="'. get_permalink($ancestor).'" itemprop="url"><span itemprop="title">'. get_the_title($ancestor) .'</span></a></li>';
                }
            }
        } elseif(is_single()){
            $categories = get_the_category($post->ID);
            $cat = $categories[0];
            if($cat -> parent != 0){
                $ancestors = array_reverse(get_ancestors( $cat -> cat_ID, 'category' ));
                foreach($ancestors as $ancestor){
                    $str.='<li><a href="'. get_category_link($ancestor).'" itemprop="url"><span itemprop="title">'. get_cat_name($ancestor). '</span></a> &gt;</li>';
                }
            }
            $str.='<li><a href="'. get_category_link($cat -> term_id). '" itemprop="url"><span itemprop="title">'. $cat-> cat_name . '</span></a></li>';
        } else{
            $str.='<li>'. wp_title('', false) .'</li>';
        }
        $str.='<li>'. wp_title('', false) .'</li>';
        $str.='</ul></div>';
    }
    echo $str;
}

//子ページ判定
function is_parent_slug() {
  global $post;
  if ($post->post_parent) {
    $post_data = get_post($post->post_parent);
    return $post_data->post_name;
  }
}

//記事表示ショートコード
function getCatItems($atts, $content = null) {
	extract(shortcode_atts(array(
		"num" => '5',
		"cat" => '',
	), $atts));
	global $post;
	$oldpost = $post;
  $args=array(
    'numberposts'=>$num,
    'order'=>'DESC',
    'orderby'=>'post_date',
    'post_type'=>'news',
    'tax_query' => array(
      array(
      'taxonomy' => 'news_cat',
      'field' => 'slug',
      'terms' => $cat
      )
    )
  );
	//$myposts = get_posts('numberposts='.$num.'&order=DESC&orderby=post_date&category_name='.$cat.'&post_type=news');
  $myposts=get_posts($args);
  $retHtml='<section class="newsArchive cf"><div class="inner cf">';
  $retHtml.='<div class="newsArea cf">';
  $retHtml.='<h1 class="title1">施設情報</h1>';
	$retHtml.='<table class="newsTable">';
	foreach($myposts as $post) :
		setup_postdata($post);
    $retHtml.='<tr>';
    $retHtml.='<td class="time">'.get_the_time('Y年m月j日').'</time></td>';
    $retHtml.='<td>';
    if(get_post_meta($post->ID,"URL", true)){

      $retHtml.='<p class="topic"><a href="'.get_post_meta($post->ID,"URL", true).'"';
      if(get_post_meta($post->ID,"window", true)=="ON"){
        $retHtml.=' "target=_blank" >';
      }else{
        $retHtml.='>';
      }
      $retHtml.=the_title("","",false).'</a></p>';
    }elseif(get_post_meta($post->ID,"pdf", true)){
      $files =  get_post_meta($post->ID, pdf, false);
      foreach($files as $file){
        $file = wp_get_attachment_url($file);
      }
      $retHtml.='<p class="topic"><a href="'.$file.'"';
      if(get_post_meta($post->ID,"window", true)=="ON"){
        $retHtml.=' "target=_blank" >';
      }else{
        $retHtml.='>';
      }
      $retHtml.=the_title("","",false).'</a></p>';
    }else{
      $retHtml.='<p class="topic">';
      $retHtml.=the_title("","",false).'</p>';
    }
    $retHtml.='</td>';
    $retHtml.='</tr>';
	endforeach;
	$retHtml.='</table>';
  $retHtml.='</div>';
  $retHtml.='</div>';
  $retHtml.='</section>';
	$post = $oldpost;
	return $retHtml;
}
add_shortcode("list", "getCatItems");

//カスタムポストnews
function change_posts_per_page($query) {
    if ( is_admin() || ! $query->is_main_query() )
        return;

    if ( $query->is_post_type_archive('news') ) {
        $query->set( 'posts_per_page', '10' );
    }
}
add_action( 'pre_get_posts', 'change_posts_per_page' );

?>
