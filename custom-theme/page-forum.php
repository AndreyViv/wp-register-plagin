<?php
/*
Template Name: Forum tpl
*/
get_header(); ?>

    <div id="primary" class="content-area" style="position: relative">
		<main id="main" class="site-main">

        <?php if ( is_user_logged_in() ) : ?>
            
            <div id="message-div" class ="container-fluid">
                <form action="'. esc_url( admin_url('admin-post.php') ) .'" method="post">
                    <p for="message">Ваше сообщение</p>
                    <p><textarea rows="4" name="message" id="message"></textarea></p>
                    <input type="hidden" name="action" value="message_form">
                    <p><input type="submit" value="Отправить сообщение"></p>
                </form>
            </div>

            <?php viv_message_list(); ?>

        <?php else : ?>
            
            <div id="message-div">
                <div class="container-fluid">
                    <h1><p> Только для авторизованых пользователей! Авторизуйтесь</p></h1>
                    <?php echo viv_render_login(); ?>
                </div>
            </div>

        <?php endif; ?>
        
        </main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>