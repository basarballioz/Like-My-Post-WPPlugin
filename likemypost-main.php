<?php
/**
Plugin Name: LikeMyPost
Plugin URI:  https://github.com/basarballioz/Like-My-Post-WPPlugin
Description: It allows users to create a button that will make them like the posts, and monitor which tags are mostly liked.
Version:     1.0
Author:      Başar Ballıöz
Author URI:  https://github.com/basarballioz
License:     GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.tr.html
 **/


//This will prevent public user to access your hidden files directly by using URL.
if (!defined('ABSPATH') ) {
    exit;
}

// Load plugin files
require_once plugin_dir_path(__FILE__) . 'likemypost-widget.php';           // plugin widget
require_once plugin_dir_path( __FILE__ ) . 'likemypost-admin.php';          // admin menu entry and page content

class likeMyPost {
    
    //REGISTER STYLE AND JQUERY
    public function register_script() {
        //($handle, $src, $deps, $ver, $media) 
        wp_register_script('lmpScript', plugins_url('js/lmpscript.js', __FILE__), array('jquery'), '3.5.1' );       
        wp_localize_script('lmpScript', 'LMPajax',
        array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('worldsBestPluginEver'))); //SAFETY
    }

    public function loadScripts(){
        wp_enqueue_script('lmpScript');
    }

    //ADD A LIKE BUTTON TO BOTTOM OF THE POSTS
    public function addLikeButton($content) {
        if (get_post_type() == is_singular()) {
            $getPost = '<p class="getPostLiked" style="font-size: 1.1em; border-style: solid; border-width: thin; width: 200px"> You can, ';
        if ($this->alreadyLiked(get_the_ID())) {
            $getPost .= '<br> <a style="color: pink;" data-event="dislike" data-post_id="'.get_the_ID().'" href="#/"> Dislike this post ';
        } else {
            $getPost .= '<br> <a style="color: pink;" data-event="like" data-post_id="'.get_the_ID().'" href="#/"> Like this post! ';
        }
            $getPost .= '</a><span class="count">'.$this->likeCounter(get_the_ID());
            $getPost .= ' Likes <p></p></span></p>';
            $content .= $getPost;
        }
        return $content;
    }

    //AJAX RESPONSE
    public function like() {
        //SAFETY
        check_ajax_referer('worldsBestPluginEver', 'nonce');

        $post_id = $_POST['post_id'];
        $event = $_POST['event'];
        if ($event == "like") {
            $this->likePost($post_id);
        } else {
            $this->dislikePost($post_id);
        }
        die();
    }

    //IP CONTROL FOR ALREADY LIKED
    public function alreadyLiked($post_id) {
        $user_IP = $_SERVER['REMOTE_ADDR'];
        $meta_IP = get_post_meta($post_id, '_likers_IP');
        $likers_IP = $meta_IP[0];
        
        //SAFE ARRAYS (allows us to display the counter as zero when creating a new post - in order to prevent errors)
        if (!is_array($likers_IP)) {
            $likers_IP = array();
        }
        if (in_array($user_IP, $likers_IP)) {
            return true;                        //SHOW "DISLIKE" BUTTON FOR USERS WHO ALREADY LIKED
        } else {
            return false;                       //SHOW "LIKE" BUTTON FOR USERS WHO HAVE NOT LIKED THE POST YET 
        }
    }

    //LIKING POSTS BY USING $POSTID
    public function likePost($post_id) {
        $likes_count = $this->likeCounter($post_id);
        $user_IP = $_SERVER['REMOTE_ADDR'];
        $meta_IP = get_post_meta($post_id,'_likers_IP');
        $likers_IP = $meta_IP[0];

        //SAFE ARRAYS
        if (!is_array($likers_IP)) {
            $likers_IP = array();
        }
        $likers_IP[] = $user_IP;
        
        if (update_post_meta($post_id, '_likes_count', ++$likes_count)) {
            update_post_meta($post_id, '_likers_IP', $likers_IP);
            echo " ";
            echo "$likes_count Likes";
        } else {
            echo "Try again please...";
        }
    }

    //DISLIKING POSTS BY USING $POSTID
    public function dislikePost($post_id) {
        
        $likes_count = $this->likeCounter($post_id);
        $user_IP = $_SERVER['REMOTE_ADDR'];
        $meta_IP = get_post_meta($post_id,'_likers_IP');
        $likers_IP = $meta_IP[0];

        //SAFE ARRAYS
        if (!is_array($likers_IP)) {
            $likers_IP = array();
        }
        if ($this->alreadyLiked($post_id)) {
            $key = array_search($user_IP,$likers_IP);
            unset($likers_IP[$key]);
        }
        if (update_post_meta($post_id, '_likes_count', --$likes_count)) {
            update_post_meta($post_id, '_likers_IP', $likers_IP);
            echo " ";
            echo "$likes_count Likes";
        } else {
            echo "Try again please...";
        }
    }

    public function likeCounter($post_id) {
        return get_post_meta($post_id, '_likes_count', true);
    }

    //HOOKS
    public function run() {      
        add_action('init', array($this,'register_script'));
        add_action('wp_enqueue_scripts', array($this,'loadScripts'));

        add_filter('the_content', array($this, 'addLikeButton' ));

        add_action('wp_ajax_nopriv_like', array($this,'like'));
        add_action('wp_ajax_like', array($this,'like'));
    }
}

//LIKE MY POST PLUGIN INITIALIZER
$plugin = new likeMyPost();  //Plugin object
$plugin->run();              //Call run function