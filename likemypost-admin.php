<?php
//Builds admin panel page for LikeMyPost
add_action( 'admin_menu', 'admin_menu' );


//Add LikeMyPost tab under posts menu
function admin_menu() {
    add_posts_page('Like My Post Dashboard', 'Like My Post', 'activate_plugins', 'likemypost_admin', 'admin_panel');
}

function admin_panel() {
    if (!current_user_can('activate_plugins')) {                                    //CHECK IF USER ADMIN OR NOT
        wp_die(__( 'You are not allowed to inspect content of this page.'));        //IF NOT THEN SHOW AN ERROR MESSAGE
    }

?>



    <div class="wrap" style="font-size: 15px; text-align:center;">
        <h3 style="color: #00BFFF; font-size: 30px;"> =Like My Post - Mostly Liked Tags= </h3>
        <p style="font-size: 20px;" > Most liked tags are listed by descending order. You can easily monitor them from this page. </p>

        <?php

        //Using Wordpress Database Class
        global $wpdb;



        //BUILDING PAGES (https://codex.wordpress.org/Class_Reference/wpdb)
        $counter = "SELECT COUNT(DISTINCT terms.term_id)
                          FROM $wpdb->terms AS terms, 
                          $wpdb->term_taxonomy AS taxonomy, 
                          $wpdb->term_relationships AS relationships, 
                          $wpdb->postmeta AS postmeta
                          WHERE terms.term_id = taxonomy.term_id
                                AND taxonomy.taxonomy = 'post_tag'
                                AND relationships.term_taxonomy_id = terms.term_id
                                AND postmeta.post_id = relationships.object_id
                                AND postmeta.meta_key = '_likes_count'";



        $tags = $wpdb->get_var($counter);
        $tag_limit_per_page = 10;
        $page_number = ceil($tags / $tag_limit_per_page);
        $page = isset($_GET['pages']) ? (int) $_GET['pages'] : 1;


        if($page < 1) {
            $page = 1;
        }
        else if($page > $page_number) {
            $page = $page_number;
        }

        

        $limiter = ($page - 1) * $tag_limit_per_page;
        $current_page = add_query_arg(NULL, NULL);
        ?>




        <table class="widefat fixed" style="text-align: center;">
        <thead>
                <tr>
                <th style='font-weight: bold; text-align: center;'>ID number of tag</th>
                <th style='font-weight: bold; text-align: center;'>Tag Names</th>
                <th style='font-weight: bold; text-align: center;'>Total Number Of Posts</th>
                <th style='font-weight: bold;text-align: center;'>Likes</th>
                </tr>
            </thead>
        <tbody>


            
            <?php
            // LISTING TAGS
            $SQLquery = "SELECT A.term_id AS tag_id, A.name, B.count, SUM(C.meta_value) AS like_count
                    FROM $wpdb->terms AS A, $wpdb->term_taxonomy AS B, $wpdb->term_relationships AS P, $wpdb->postmeta AS C
                    WHERE A.term_id = B.term_id
                          AND B.taxonomy = 'post_tag'
                          AND P.term_taxonomy_id = A.term_id
                          AND C.post_id = P.object_id
                          AND C.meta_key = '_likes_count'
                    GROUP BY A.term_id, A.name, B.count
                    ORDER BY like_count DESC
                    LIMIT $limiter, $tag_limit_per_page";    //1-10, 10-20 gibi listeleme

            $tags = $wpdb->get_results($SQLquery, OBJECT);
            foreach ($tags as $tag) {
                echo "<tr>";
                echo "<td> $tag->tag_id </td>";
                echo "<td> <a href='".get_tag_link($tag->tag_id)."'> $tag->name </a> </td>";
                echo "<td> $tag->count </td>";
                echo "<td> $tag->like_count </td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        


        
        <?php
        //SELECTING PAGES FROM FOOTER
        for($i = 1; $i <= $page_number; $i++) {
            if($page == $i) {
                echo $i.' ';
            } 
            else {
               echo '<a href="'.$current_page.'&pages=' . $i . '">' . $i . '</a> ';
            }
        }
        ?>


    </div>
    
<?php 

}