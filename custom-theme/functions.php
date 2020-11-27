<?php
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

add_filter( 'body_class', 'viv_nosidebar_function', 10, 2 );

function viv_nosidebar_function( $classes, $class ){
    if (substr( $_SERVER['REQUEST_URI'], 0, 6 ) == '/forum') {
        $classes[] = 'no-sidebar';
    }
	return $classes;
}

function viv_message_list() {
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
            $user_data = get_userdata( $post_data['post_author'] );
                
            $result .= '<article class="entry">
                            <header class="entry__header">
                                <h3 class="entry__title title-entry" style="color: #a52a2a; font-size: 24pt; font-family: Georgia, Times, serif; font-weight: normal;">
                                    <a class="title-entry__link" href="#">
                                        '. $user_data->user_login .'
                                    </a>
                                </h3>
                            </header>
                                
                            <div class="entry__content">
                                '. $post_data['post_content'] .'
                            </div>
              
                            <div class="entry__tags tags-entry">
                                <h4 class="tags-entry__tilte">Тут будут коменты</h4>
              
                                <ul class="tags-entry__list">
                                    <li class="tags-entry__item">
                                        <a class="tags-entry__link" href="#">Комент первого уровня</a>
                                        <ul class="tags-entry__list">
                                            <li class="tags-entry__item">
                                                <a class="tags-entry__link" href="#">Комент второго уровня</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </article>';
        }
    }
    echo $result;
}
