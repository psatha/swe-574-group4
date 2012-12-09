<?php
    echo "<div class='wpfp-span'>";
    if (!empty($user)):
        if (!wpfp_is_user_favlist_public($user)):
            echo "$user's Favorite Posts.";
        else:
            echo "$user's list is not public.";
        endif;
    endif;

    if ($wpfp_before):
        echo "<p>".$wpfp_before."</p>";
    endif;

    echo "<ul>";
    if ($favorite_post_ids):
        foreach ($favorite_post_ids as $post_id) {
            $p = get_post($post_id);
            echo "<li><h2>";
            echo "<a href='".get_permalink($post_id)."' title='". $p->post_title ."'>" . $p->post_title . "</a></h2> ";
            echo "<a href='".get_permalink($post_id)."' title='". $p->post_title ."'>";
            if(get_post_meta($post_id, 'images', true)) cp_single_image_legacy($post_id, get_option('medium_size_w'), get_option('medium_size_h')); else cp_get_image($post_id, 'medium', 1);
            echo "</a>";
            echo substr(strip_tags($p->post_content), 0, 250)."...";
            echo "<br />";
            wpfp_remove_favorite_link($post_id);
            echo "</li>";
           }

    else:
        echo "<li>";
        echo $wpfp_options['favorites_empty'];
        echo "</li>";
    endif;
    echo "</ul>";
    wpfp_clear_list_link();
    echo "</div>";
    wpfp_cookie_warning();
?>
