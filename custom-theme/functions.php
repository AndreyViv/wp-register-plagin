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

function viv_render_forum(){
    if ( is_user_logged_in() ) {
        $result =   '<section id="message-form-section class="form-area"">
                        <div id="message-div">
                            <form action="'. esc_url( admin_url('admin-post.php') ) .'" method="post">
                                <p for="message">Your Message</p>
                                <p><textarea rows="4" name="message" id="message"></textarea></p>
                                <input type="hidden" name="action" value="message_form">
                                <p><input type="submit" value="Send My Message"></p>
                            </form>
                        </div>
                    </section>';
        
        $args = array( 'post_type' => 'message', 'posts_per_page' => -1, 'post_status' => null, 'post_parent' => null );
        $posts = get_posts( $args );

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
        $result .= '</div></section>';
    } else {
        $result =   '<div id="message-div">
                        <div>
                            <a> Только для авторизованых пользователей! Авторизуйтесь </a>
                        </div>
                        '. viv_render_login() .'                         
                    </div>';
    }
    echo $result;
}
