<?php
//Easy Link (https://www.wpbeginner.com/wp-tutorials/how-to-create-a-custom-wordpress-widget/  https://codex.wordpress.org/Widgets_API)

class likeMyPostWidget extends WP_Widget {

    //WIDGET CONSTRUCTOR
    public function __construct() {
        $widget_options = array('classname' => 'likeMyPostWidget', 'description' => 'A widget to list top 10 tags');
        parent::__construct('likeMyPostWidget', 'Likemypost - Top 10 Tags', $widget_options);
    }

    //CREATING  WIDGET
    public function widget($args, $instance) {

        //Using Wordpress Database Class
        global $wpdb;

        $limiter = 0;
        $title = apply_filters('widget_title', $instance['title']);
        $maxTag = $instance['maxTag'];
        $firstSentence = $instance['firstSentence'];
        $secondSentence = $instance['secondSentence'];
        
        $sqlQuery = "SELECT A.term_id AS tag_id, A.name, SUM(D.meta_value) AS like_count     /*tableName.columName*/
                FROM $wpdb->terms AS A, 
                $wpdb->term_taxonomy AS B, 
                $wpdb->term_relationships AS C,
                 $wpdb->postmeta AS D
                WHERE A.term_id = B.term_id
                  AND B.taxonomy = 'post_tag'
                  AND C.term_taxonomy_id = A.term_id
                  AND D.post_id = C.object_id
                  AND D.meta_key = '_likes_count'
                GROUP BY A.term_id, A.name
                ORDER BY like_count DESC
                LIMIT $limiter, $maxTag";           //From 0 to $limit value

        //Fix widget appearance
        echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];
        
        
        $getTags = $wpdb->get_results($sqlQuery, OBJECT);
        echo "<ul>";
        foreach ($getTags as $tag) {
            //echo "<li>" .$tag->name. " tag is liked </a> " .$tag->like_count." times </li>";  //For without filling widget fields
            echo "<li> <a target=_blank href='" .get_tag_link($tag->tag_id). "'>" .$tag->name. "</a> $firstSentence " .$tag->like_count." $secondSentence </li>";
        }
        echo "</ul>";
        echo $args['after_widget'];
    }

    //BACK-END WIDGER ($this = class likeMyPostWidget extends WP_Widget)
    public function form($instance) {
        echo $this->createInput($instance,'maxTag');
        echo $this->createInput($instance,'title');
        echo $this->createInput($instance,'firstSentence');
        echo $this->createInput($instance,'secondSentence');
    }

    //UPDATE INSTANCES
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['maxTag'] = strip_tags($new_instance['maxTag']);
        $instance['firstSentence'] = strip_tags($new_instance['firstSentence']);
        $instance['secondSentence'] = strip_tags($new_instance['secondSentence']);
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    //CREATING INPUT FOR FORM
    public function createInput($instance, $field) {
        $value = !empty($instance[$field]) ? $instance[$field] : ' ';
        
        $input = "<p style= text-decoration:underline;> <label for='".$this->get_field_id($field)."'>".$field." : <p> </p></label>";
        $input .= "<input type='text' id='".$this->get_field_id($field)."'";           //Append
        $input .= "name='".$this->get_field_name($field)."'";
        $input .= "value='".esc_attr($value)."'> </p>";
        return $input;
    }
}

//INITIALIZING WIDGET
add_action('widgets_init', function(){
	register_widget('likeMyPostWidget');
});