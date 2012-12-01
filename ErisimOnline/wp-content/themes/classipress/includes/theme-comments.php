<?php

// Custom callback to list comments
function cp_custom_comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment;
   $GLOBALS['comment_depth'] = $depth;
   switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
?>
            <li class="pingback">
                    <?php comment_author_link(); ?>
            <?php
			break;
		default :
	    ?>

            <li <?php comment_class(); ?>>
            
                <a name="comment-<?php comment_ID() ?>"></a>
            
                    <?php if ( get_comment_type() == 'comment' ) { ?>
            
                        <div class="avatar"><?php commenter_avatar(); ?></div>
            
                    <?php } ?>
            
                <div class="comment-head">
            
                    <div class="user-meta">
            
                        <strong class="name"><?php commenter_link() ?></strong> <?php _e('on', 'appthemes') ?>
            
                        <?php if ( get_comment_type() == 'comment' ) { ?>
            
                            <a class="comment-permalink" href="<?php echo get_comment_link(); ?>"><?php echo get_comment_date( get_option('date_format') ) ?> @ <?php echo get_comment_time( get_option('time_format') ); ?></a> <?php edit_comment_link(__('Edit', 'appthemes'), ' <span class="edit-link">(', ')</span>'); ?>
            
                        <?php }?>
                    
                    </div> <!-- /user-meta -->
            
                </div> <!-- /comment-head -->
            
            
                <div class="comment-entry"  id="comment-<?php comment_ID(); ?>">
            
                    <?php comment_text() ?>
            
                    <?php if ( $comment->comment_approved == '0' ) { ?>
                    
                        <p class='unapproved'><?php _e('Your comment is awaiting moderation.','appthemes') ?></p>
            
                    <?php } ?>
            
                    <div class="clr"></div>
            
                    <div class="reply">
            
                        <?php comment_reply_link( array_merge( $args, array( 'reply_text' => __('Reply','appthemes'),
                                                                            'login_text' => __('Log in to reply.','appthemes'),
                                                                            'depth' => $depth,
                                                                            'max_depth' => $args['max_depth'],
                                                                            'before' => '<div class="comment-reply-link">',
                                                                            'after' => '</div>',
                                                                            ) ) ); ?>
            
                    </div><!-- /reply -->
            
                </div><!-- /comment-entry -->
            
            <?php 
            break;
        endswitch;
}


// list out comments 
function cp_list_comments() {
    global $post;

    wp_list_comments( array( 'callback' => 'cp_custom_comment', 'type' => 'comment' ) );
    
}
add_action('appthemes_list_comments', 'cp_list_comments');
add_action('appthemes_list_blog_comments', 'cp_list_comments');
add_action('appthemes_list_page_comments', 'cp_list_comments');


// list out pings 
function cp_list_pings() {
    global $post;

    wp_list_comments( array( 'callback' => 'cp_custom_comment', 'type' => 'pings' ) );
    
}
add_action('appthemes_list_pings', 'cp_list_pings');
add_action('appthemes_list_blog_pings', 'cp_list_pings');
add_action('appthemes_list_page_pings', 'cp_list_pings');


// main comments form 
function cp_main_comment_form() {
    global $post;
?>

    <script type="text/javascript">
    <!--//--><![CDATA[//><!--
    jQuery(document).ready(function($) {
        /* initialize the form validation */
        $(function() {
            $("#commentform").validate({
                errorClass: "invalid",
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                   }
            });
	    $("#commentform").fadeIn();
        });
        
    });
    //-->!]]>
    </script>

    <div id="respond">

        <h2 class="dotted"><?php comment_form_title( __('Leave a Reply','appthemes'), __('Leave a Reply to %s','appthemes') ); ?></h2>

        <div class="cancel-comment-reply">

                <small><?php cancel_comment_reply_link(); ?></small>

        </div>


        <?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>

            <p><?php printf( __("You must be <a href='%s'>logged in</a> to post a comment.", 'appthemes'), get_option('siteurl').'/login.php?redirect_to='.urlencode( get_permalink() ) ); ?></p>

        <?php else : ?>

            <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform" class="commentform">

								<?php do_action( 'comment_form_top' ); ?>

                <fieldset class="form-comments">

                <?php if ( is_user_logged_in() ) : global $user_identity; ?>

                    <p><?php _e('Logged in as','appthemes'); ?> <a href="<?php echo CP_PROFILE_URL; ?>"><?php echo $user_identity; ?></a>. <a href="<?php echo cp_logout_url(); ?>" title="<?php _e('Logout of this account','appthemes'); ?>"><?php _e('Logout','appthemes'); ?> &raquo;</a></p>

                <?php else : ?>
                
                    <?php 
                        $commenter = wp_get_current_commenter();
                        $req = get_option( 'require_name_email' ); 
                    ?>

                    <p class="comments">
                        <label for="author"><?php _e('Name','appthemes'); ?> <?php if ( $req ) _e('(required)','appthemes'); ?></label>
                        <input type="text" name="author" id="author" class="text required" value="<?php echo esc_attr( $commenter['comment_author'] ); ?>" size="22" tabindex="1" />
                    </p>
    
                    <div class="clr"></div>
    
                    <p class="comments">
                        <label for="email"><?php _e('Email (will not be visible)','appthemes'); ?> <?php if ( $req ) _e('(required)','appthemes'); ?></label>
                        <input type="text" name="email" id="email" class="text required email" value="<?php echo esc_attr(  $commenter['comment_author_email'] ); ?>" size="22" tabindex="2" />                                
                    </p>
    
                    <div class="clr"></div>
    
                   <p class="comments">
                        <label for="url"><?php _e('Website','appthemes'); ?></label>
                        <input type="text" name="url" id="url" class="text" value="<?php echo esc_attr( $commenter['comment_author_url'] ); ?>" size="22" tabindex="3" />
                    </p>
    
                   <div class="clr"></div>

                <?php endif; ?>
                
                <!--<li><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small><div class="clr"></div></li>-->

                <p class="comments-box">
                    <textarea name="comment" rows="" cols="" id="comment" class="required" tabindex="4"></textarea>
                </p>

                <div class="clr"></div>

                <p class="comments">
                    <input name="submit" type="submit" id="submit" tabindex="5" class="btn_orange" value="<?php _e('Leave a Reply','appthemes'); ?>" />
                    <input type="hidden" name="comment_post_ID" value="<?php echo $post->ID; ?>" />
                </p>

                <?php comment_id_fields(); ?>
                <?php do_action( 'comment_form', $post->ID ); ?>

                 </fieldset>
                
            </form>

        <?php endif; // if logged in ?>

        <div class="clr"></div>

    </div> <!-- /respond -->

<?php
}
add_action('appthemes_comments_form', 'cp_main_comment_form');
add_action('appthemes_blog_comments_form', 'cp_main_comment_form');
add_action('appthemes_page_comments_form', 'cp_main_comment_form');


function commenter_link() {
    $commenter = get_comment_author_link();

    if ( strstr( ']* class=[^>]+>', $commenter ) ) {
        $commenter = str_replace( '(]* class=[\'"]?)', '\\1url ' , $commenter );

    } else {

        $commenter = str_replace( '(<a )/', '\\1class="url "' , $commenter );
    }

    echo $commenter;
}

function commenter_avatar() {
    $avatar_email = get_comment_author_email();
    $avatar = str_replace( 'class="avatar', 'class="photo avatar', get_avatar( $avatar_email, 60 ) );

    echo $avatar;
}

?>