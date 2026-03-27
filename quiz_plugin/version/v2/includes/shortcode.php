<?php

add_shortcode('wz_quiz', function($atts){

    global $wpdb;

    $post_id = $atts['id'];

    $quiz = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wz_quizzes WHERE post_id = %d",
            $post_id
        )
    );

    $questions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wz_questions WHERE quiz_id = %d ORDER BY order_index ASC",
            $quiz->id
        )
    );

    ob_start();
    ?>

    <div class="wzq-container" data-time="<?php echo $quiz->time_limit; ?>">

        <div class="wzq-progress"></div>

        <?php foreach($questions as $i => $q): ?>
            <div class="wzq-question" data-index="<?php echo $i; ?>">
                <p><?php echo $q->question; ?></p>

                <?php foreach(['a','b','c','d'] as $opt): ?>
                    <button class="wzq-option" data-correct="<?php echo $q->correct; ?>" data-opt="<?php echo $opt; ?>">
                        <?php echo $q->{'option_'.$opt}; ?>
                    </button>
                <?php endforeach; ?>

                <div class="wzq-explanation"><?php echo $q->explanation; ?></div>
            </div>
        <?php endforeach; ?>

        <button class="wzq-next">Next</button>

    </div>

    <?php
    return ob_get_clean();
});

add_filter('the_content', function($content){

    if(get_post_type() == 'wz_quiz'){

        return do_shortcode('[wz_quiz id="'.get_the_ID().'"]');
    }

    return $content;

});