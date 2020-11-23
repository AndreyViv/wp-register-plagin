<?php
/*
  Plugin Name: Viv Test Reg
  Version: 1.0
  Author: Andrey Viv
 */

function new_reg_form() {
    $form = '
        <div><form id="registerform" action="' . wp_registration_url() . '" method="post">
	        <p>
		        <label for="user_login">
			        Имя пользователя<br>
			        <input type="text" name="user_login" id="user_login" class="input" value="" size="20" style="">
		        </label>
	        </p>
	        <p>
		        <label for="user_email">
			        E-mail<br>
			        <input type="email" name="user_email" id="user_email" class="input" value="" size="25">
		        </label>
            </p>
            <p>
		        <label for="user_password">
			        Пароль<br>
			        <input type="text" name="user_password" id="user_password" class="input" value="" size="25">
		        </label>
	        </p>

	        <br class="clear">
	        <input type="hidden" name="redirect_to" value="' . $_SERVER['REQUEST_URI'] .'">

	        <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Регистрация"></p>
        </form></div>
    ';

    return $form;
}

add_action( 'wp_login_failed', 'viv_login_fail', 10, 2);
function viv_login_fail( $username, $error ) {
    $referrer = $_SERVER['HTTP_REFERER'];

	if( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
		wp_redirect( add_query_arg('errors', $error->get_error_code(), $referrer ) );
		exit;
	}
}

add_action( 'register_post', 'viv_reg_action_function', 10, 3 );
function viv_reg_action_function( $sanitized_user_login, $user_email, $errors ){
    $referrer = explode('?', $_SERVER['HTTP_REFERER'])[0];
    $userdata = array(
        'user_pass'       => $_REQUEST['user_password'],
        'user_login'      => $sanitized_user_login, 
        'user_email'      => $user_email,
        'role'            => 'subscriber'
    );
    
    $errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

    if ( $errors->get_error_code() ){
        $referrer = add_query_arg('action', 'register', $referrer );
        $err_codes = '';
        
        foreach ( $errors->errors as $error => $mes ){
            $err_codes .= $error . ',';
        }
        wp_redirect( add_query_arg('errors', $err_codes, $referrer ) );
        exit;   
    }

    $user_id = wp_insert_user( $userdata );
    wp_redirect( $referrer );
    exit;

}

add_action( 'user_register', 'viv_new_user_action_function' );
function viv_new_user_action_function( $user_id ){
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );
}

add_shortcode( 'viv_custom_login', 'viv_render_login' );
function viv_render_login() {
	if ( is_user_logged_in() ) {
		return;
    }

    if ('register' == $_GET['action']) {
        $action = 'register';
        $title = 'Регистрация';
    } else {
        $action = 'login';
        $title = 'Войти на сайт';
    }
    
	$result = '<div class="login-form-container"><h2>' . $title . '</h2>';
	
	if ( isset( $_REQUEST['errors'] ) ) {
		$error_codes = explode( ',', $_REQUEST['errors'] );
 
		foreach ( $error_codes as $error_code ) {
			switch ( $error_code ) {
                		case 'username_exists':
                    			$result .= '<p class="error">Такой пользователь существует</p>';
                    			break;
                		case 'email_exists':
                    			$result .= '<p class="error">Email занят </p>';
                    			break;
				case 'empty_username':
					$result .= '<p class="error">Вы не забыли указать свой email/имя пользователя?</p>';
					break;
				case 'empty_password':
					$result .= '<p class="error">Пожалуйста, введите пароль.</p>';
					break;
				case 'invalid_username':
					$result .= '<p class="error">На сайте не найдено указанного пользователя.</p>';
					break;
				case 'incorrect_password':
					$result .= sprintf( "<p class='error'>Неверный пароль. <a href='%s'>Забыли</a>?</p>", wp_lostpassword_url() );
					break;
			}
		}
	}
 
    if ('register' == $action) {
        $sign_in_url = explode('?', wp_get_referer())[0];
        $result .= new_reg_form();
        $result .= '<a class="login" href="' . $sign_in_url . '">Войти</a></div>';
    } else {
        $sign_up_url = explode('?', wp_get_referer())[0] . '?action=register';
        $result .= wp_login_form(
            array(
                'echo' => false,
                'redirect' => $_SERVER['REQUEST_URI']
            )
        ); 
        $result .= '<a class="registration" href="' . $_SERVER['REQUEST_URI'] . '?action=register">Зарегестрироваться</a></div>';
        $result .= '<a class="forgot-password" href="' . wp_lostpassword_url() . '">Забыли пароль</a></div>';
    }

	return $result;
}

