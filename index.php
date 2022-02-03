<?php

/*
Plugin Name: SDEV Featured Star
Description: adds featured star to case studies admin columns
Version: 1.0
Author: SDEV
*/
if ( !class_exists('featured_star')){
    class featured_star {

        //constarctor
        public function __construct() {
            global $pagenow,$typenow; //&& $typenow =='page'
            if (is_admin()  && $pagenow=='edit.php'){
                add_filter('admin_footer',array($this,'insert_ajax_status_script'));
            }

            add_filter( 'manage_edit-case-studies_columns', array($this,'add_new_columns'));
            add_action( 'manage_case-studies_posts_custom_column', array($this, 'manage_columns'), 10, 2);

            //manage columns
            add_filter('manage_case-studies_columns', array($this,'add_new_columns'));
            add_action('manage_case-studies_custom_column', array($this, 'manage_columns'), 10, 2);

            //ajax function
            add_action('wp_ajax_change_status', array($this,'ajax_change_status'));
        }

        /*
        * the function that will actually change the post status
        $post_id - The ID of the post you'd like to change.
        $status -  The post status publish|pending|draft|private|static|object|attachment|inherit|future|trash.
        */
        public function change_post_status($post_id,$status){
            if ($status == 'true') {
                update_post_meta($post_id, 'featured', true);
            } else {
                update_post_meta($post_id, 'featured', false);
            }
        }


        /* 
            ****************************
            * manage columns functions *
            ****************************
            */

        //add new columns function 
        public function add_new_columns($columns){
            $columns['featured'] = __('Featured');
            return $columns;
        }

        //rander columns function 
        public function manage_columns($column_name, $id) {
            global $wpdb,$post;
            if ("featured" == $column_name){
                echo '<div id="featured-toggles">';
                switch ($post->featured) {
                    case true:
                        echo '<a href="#" class="pb" change_to="false" pid="'.$id.'"><span class="dashicons dashicons-star-filled"></span></a>';
                        break;
                    case false:
                        echo '<a href="#" class="pb" change_to="true" pid="'.$id.'"><span class="dashicons dashicons-star-empty"></span></a>';
                        break;
                    default:
                        echo 'unknown';
                        break;
                } // end switch
                echo '</div>';
            }
        }


        //js/jquery code to call ajax
        public function insert_ajax_status_script(){
            ?>
            <div id="status_update_working" style="z-index:20;background-color:rgba(0,0,0,.5);color:#fff;text-align:center;font-wieght:bolder;font-size:22px;height:33px;left:50%;transform:translateX(-50%);padding:24px 32px;border-radius:6px;position:fixed;top:100px;width:350px;display:none!important">Changing featured status...</div>
            <script type="text/javascript">
            function ajax_change_status(p){
                jQuery("#status_update_working").fadeIn(200);
                jQuery.getJSON(ajaxurl,
                    {   post_id: p.attr("pid"),
                        action: "change_status",
                        change_to: p.attr("change_to")
                    },
                    function(data) {
                        if (data.error){
                            alert(data.error);                      
                        }else{
                            p.html(data.html);
                            p.attr("change_to",data.change_to);
                            jQuery("#status_update_working").fadeOut(200);
                        }
                    }
                );
            }
            jQuery(document).ready(function(){
                jQuery(".pb").click(function(){
                    ajax_change_status(jQuery(this));
                });
            });
            </script>
            <?php
        }

        //ajax callback function
        public function ajax_change_status(){
            if (!isset($_GET['post_id'])){
                $re['data'] = 'something went wrong ...';
                echo json_encode($re);
                die();
            }
            if (isset($_GET['change_to'])){
                $this->change_post_status($_GET['post_id'],$_GET['change_to']);
                if ($_GET['change_to'] == "false"){
                    $re['html'] = '<span class="dashicons dashicons-star-empty"></span>';
                    $re['change_to'] = "true";
                }else{
                    $re['html'] = '<span class="dashicons dashicons-star-filled"></span>';
                    $re['change_to'] = "false";
                }
            }else{
                $re['data'] = 'something went wrong ...';
            }
            echo json_encode($re);
            die();
        }
    }
}

new featured_star();
?>