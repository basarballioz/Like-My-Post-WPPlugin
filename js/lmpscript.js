jQuery(document).ready(function () {
   jQuery('.getPostLiked a').click(function () {
       
       let likeButton = jQuery(this);
       let post_id = likeButton.data('post_id');
       let event = likeButton.data('event');
       
       if (event == 'like') {
          likeButton.text('Dislike this post!');
          likeButton.data('event','unlike');
          console.log("Successfully Disliked")
       }
       
       else {
          likeButton.text('Like this post!');
          likeButton.data('event','like');
          console.log("Successfully Liked")
       }
       
       jQuery.ajax({
           type : 'post',
           url : LMPajax.ajax_url,
           data : {
               action : 'like',
               post_id : post_id,
               event : event,
               nonce : LMPajax.nonce
           },
           success :                        //When ends
                    function (response) {
                        jQuery('.count').text(response);
                        console.log("Done...");
                    }
        });
    });
});
