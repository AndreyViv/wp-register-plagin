<?php
function viv_email_filter( $email_data, $user, $blogname ) {
    $email_data['subject'] = 'Новый комментарий';
    $email_data['message'] = 'У Вас новый коментарий';
    
    return $email_data;
}



add_action( 'admin_post_nopriv_message_comment_form', 'viv_message_comment_form_action' );
add_action( 'admin_post_message_comment_form', 'viv_message_comment_form_action' );

function viv_message_comment_form_action() {
    $current_user = wp_get_current_user();
    $current_post = get_post( $_POST['comented_id'] )->to_array();
    
    $comment_data = array(
        'comment_post_ID'      => $_POST['comented_id'],
	    'comment_author'       => $current_user->user_login,
	    'comment_content'      => $_POST['comment'],
	    'comment_type'         => 'comment',
	    'user_id'              => $current_user->ID,
    );

    $succees = wp_insert_comment( wp_slash($comment_data) );

    if ($succees) {
        add_filter( 'wp_new_user_notification_email', 'viv_email_filter', 10, 3 );
        wp_new_user_notification( $current_post['post_author'], 'user' );
    }
    
    wp_safe_redirect( $url = site_url('/forum') );
    exit;
}

add_action( 'admin_post_nopriv_comment_comment_form', 'viv_comment_comment_form_action' );
add_action( 'admin_post_comment_comment_form', 'viv_comment_comment_form_action' );

function viv_comment_comment_form_action() {
    $current_user = wp_get_current_user();
    $current_comment = get_comment( $_POST['comented_id'] );
    
    $comment_data = array(
	    'comment_author'       => $current_user->user_login,
	    'comment_content'      => $_POST['comment'],
	    'comment_type'         => 'comment',
	    'comment_parent'       => $_POST['comented_id'],
	    'user_id'              => $current_user->ID,
    );

    $succees = wp_insert_comment( wp_slash($comment_data) );

    if ($succees) {
        add_filter( 'wp_new_user_notification_email', 'viv_email_filter', 10, 3 );
        wp_new_user_notification( $current_comment->user_id, 'user' );
    }
    
    wp_safe_redirect( $url = site_url('/forum') );
    exit;
}

add_action( 'admin_post_nopriv_message_form', 'viv_message_form_action' );
add_action( 'admin_post_message_form', 'viv_message_form_action' );

function viv_message_form_action() {
    $cur_user_id = get_current_user_id();
    $post_data = array(
        'comment_status' => 'open',
        'post_content'  => $_POST['message'],
        'post_status'   => 'publish',
        'post_author'   => $cur_user_id,
        'post_type'     => 'message'
    );
    
    $post_id = wp_insert_post( $post_data );

    wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
    exit;
}

function viv_coment_form() {
    
    if ($_GET['comment']) {
        $commented_type = 'comment';
        $commented_id = $_GET['comment'];
    } else {
        $commented_type = 'message';
        $commented_id = $_GET['message'];
    }
    
    $result = '<div id="message-div" class ="container-fluid">
                <form action="'. esc_url( admin_url('admin-post.php') ) .'" method="post">
                    <p for="comment">Ваш коментарий</p>
                    <p><textarea rows="4" name="comment" id="comment"></textarea></p>
                    <input type="hidden" name="comented_id" id="comented_id" value="'. $commented_id .'">
                    <input type="hidden" name="action" value="'. $commented_type .'_comment_form">
                    <p><input type="submit" value="Отправить коментарий"></p>
                </form>
            </div>';
    
    echo $result;
}

function viv_get_coments($message_id) {
    $result = '';
    $link = 'http://www.socialtest.space/forum?message=' . $message_id;

    $comments = get_comments( 
        array(
            'post_id' => intval($message_id)
        )
    );
    
    foreach ($comments as $comment) {
        $content = $comment->comment_content;
        $author = $comment->comment_author;
        $id = $comment->comment_ID;

        $link .= '&comment=' . $id;
        
        $result .= '<li class="tags-entry__item">
                        <p><h4>'. $author .'</h4></p>
                        <p><a class="tags-entry__link">'. $content .'</a></p>
                        <p><h6><a href="'. $link .'">Коментировать коментарий</a></h6></p>
                        <ul class="tags-entry__list">';
        
        $com_comments = get_comments( 
            array(
                'parent' => $comment->comment_ID
            )
        );

        foreach ($com_comments as $com_comment) {
            $com_content = $com_comment->comment_content;
            $com_author = $com_comment->comment_author;

            $result .= '<li class="tags-entry__item">
                            <p><h4>'. $author .'</h4></p>
                            <p><a class="tags-entry__link">'. $com_content .'</a></p>
                        </li>';
        }

        $result .= '</ul></li>';
    }

    return $result;
}

add_filter( 'body_class', 'viv_nosidebar_function', 10, 2 );

function viv_nosidebar_function( $classes, $class ){
    if (substr( $_SERVER['REQUEST_URI'], 0, 6 ) == '/forum') {
        $classes[] = 'no-sidebar';
    }
	return $classes;
}

function viv_message_list() {
    $message_id = $_GET['message'];
    $comment_id = $_GET['comment'];

    $posts = get_posts( 
        array( 
            'post_type' => 'message', 
            'posts_per_page' => -1, 
            'post_status' => null, 
            'post_parent' => null
        ) 
    );

    $result .=  '<section id="forum-section" class="forum-area">
                        <div id="messages">';
        
    if ($posts) {
        foreach ( $posts as $post ) { 
            $post_data = $post->to_array();
            $author_data = get_userdata( $post_data['post_author'] );

            $result .= '<article class="entry">
                            <header class="entry__header">
                                <h3 class="entry__title title-entry" style="color: #a52a2a; font-size: 24pt; font-family: Georgia, Times, serif; font-weight: normal;">
                                    <a class="title-entry__link" href="#">
                                        '. $author_data->user_login .'
                                    </a>
                                </h3>
                            </header>
                                
                            <div class="entry__content">
                                '. $post_data['post_content'] .'
                            </div>
              
                            <div class="entry__tags tags-entry">
                                <h6 class="tags-entry__tilte"><a href="http://www.socialtest.space/forum?message='. $post_data['ID'] .'">Коиентировать сообщение</a></h6>
              
                                <ul class="tags-entry__list">
                                    '. viv_get_coments($post_data['ID']) .'
                                </ul>
                            </div>
                        </article>';
        }
    }
    echo $result;
}
