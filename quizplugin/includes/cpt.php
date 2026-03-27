<?php

add_action('init', function() {

    register_post_type('wz_quiz', [
        'label' => 'Quizzes',
        'public' => true,
        'rewrite' => ['slug' => 'quiz'],
        'supports' => ['title'],
        'menu_icon' => 'dashicons-welcome-learn-more'
    ]);

    register_taxonomy('wz_quiz_category', 'wz_quiz', [
        'label' => 'Quiz Categories',
        'hierarchical' => true
    ]);

});