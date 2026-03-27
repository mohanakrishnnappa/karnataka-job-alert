<?php

add_action('add_meta_boxes', function() {
    add_meta_box('wzq_json', 'Import Quiz JSON', 'wzq_json_box', 'wz_quiz');
});

function wzq_json_box($post) {
    ?>
    <textarea style="width:100%;height:200px;" name="wzq_json"></textarea>
    <button type="button" class="button button-primary" onclick="wzqImport()">Import</button>

    <script>
    function wzqImport(){
        alert("Save post after pasting JSON. Import logic will trigger.");
    }
    </script>
    <?php
}

add_action('save_post', function($post_id){

    if(get_post_type($post_id) != 'wz_quiz') return;

    if(empty($_POST['wzq_json'])) return;

    $data = json_decode(stripslashes($_POST['wzq_json']), true);

    global $wpdb;

    $quiz_id = $wpdb->insert_id;

    $wpdb->insert($wpdb->prefix.'wz_quizzes', [
        'post_id' => $post_id,
        'time_limit' => $data['time_limit'],
        'random_order' => $data['random_order'],
        'ad_after' => $data['ad_after']
    ]);

    $quiz_id = $wpdb->insert_id;

    foreach($data['questions'] as $i => $q){
        $wpdb->insert($wpdb->prefix.'wz_questions', [
            'quiz_id' => $quiz_id,
            'question' => $q['question'],
            'option_a' => $q['options']['a'],
            'option_b' => $q['options']['b'],
            'option_c' => $q['options']['c'],
            'option_d' => $q['options']['d'],
            'correct' => $q['correct'],
            'explanation' => $q['explanation'],
            'order_index' => $i
        ]);
    }

});