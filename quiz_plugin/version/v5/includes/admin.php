<?php

add_action('add_meta_boxes', function() {
    add_meta_box('wzq_builder', 'Quiz Builder', 'wzq_builder_ui', 'wz_quiz');
});

function wzq_builder_ui($post) {

    global $wpdb;

    $quiz_table = $wpdb->prefix.'wz_quizzes';
    $question_table = $wpdb->prefix.'wz_questions';

    // ✅ Load quiz
    $quiz = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $quiz_table WHERE post_id = %d", $post->ID)
    );

    // ✅ Load questions
    $questions = [];

    if($quiz){
        $questions = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $question_table WHERE quiz_id = %d ORDER BY order_index ASC", $quiz->id)
        );
    }

?>
<style>
.wzq-tabs button {
    margin-right:10px;
    padding:8px 15px;
    cursor:pointer;
}
.wzq-tab-content { display:none; margin-top:15px; }
.wzq-tab-active { display:block; }

.wzq-question-box {
    border:1px solid #ddd;
    padding:15px;
    margin-bottom:10px;
    background:#fafafa;
}
</style>

<div class="wzq-tabs">
    <button type="button" onclick="wzqShowTab('manual')">➕ Manual Add</button>
    <button type="button" onclick="wzqShowTab('import')">📥 Import JSON</button>
</div>

<!-- MANUAL BUILDER -->
<div id="wzq-manual" class="wzq-tab-content wzq-tab-active">

    <div id="wzq-questions">

    <?php if(!empty($questions)): ?>

        <?php foreach($questions as $i => $q): ?>

            <div class="wzq-question-box">

                <p><strong>Question</strong></p>
                <textarea name="questions[<?php echo $i; ?>][question]" style="width:100%"><?php echo esc_textarea($q->question); ?></textarea>

                <p>Options</p>
                <input type="text" name="questions[<?php echo $i; ?>][a]" value="<?php echo esc_attr($q->option_a); ?>"><br>
                <input type="text" name="questions[<?php echo $i; ?>][b]" value="<?php echo esc_attr($q->option_b); ?>"><br>
                <input type="text" name="questions[<?php echo $i; ?>][c]" value="<?php echo esc_attr($q->option_c); ?>"><br>
                <input type="text" name="questions[<?php echo $i; ?>][d]" value="<?php echo esc_attr($q->option_d); ?>"><br>

                <p>Correct Answer</p>
                <select name="questions[<?php echo $i; ?>][correct]">
                    <option value="a" <?php selected($q->correct, 'a'); ?>>A</option>
                    <option value="b" <?php selected($q->correct, 'b'); ?>>B</option>
                    <option value="c" <?php selected($q->correct, 'c'); ?>>C</option>
                    <option value="d" <?php selected($q->correct, 'd'); ?>>D</option>
                </select>

                <p>Explanation</p>
                <textarea name="questions[<?php echo $i; ?>][explanation]" style="width:100%"><?php echo esc_textarea($q->explanation); ?></textarea>

                <button type="button" onclick="this.parentElement.remove()">❌ Remove</button>

                <hr>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

    </div>

    <button type="button" class="button button-primary" onclick="wzqAddQuestion()">+ Add Question</button>

</div>

<!-- IMPORT JSON -->
<div id="wzq-import" class="wzq-tab-content">
    <textarea style="width:100%;height:200px;" name="wzq_json"></textarea>
    <p><button type="button" class="button" onclick="alert('Click Update/Publish to import')">Import</button></p>
</div>

<script>

function wzqShowTab(tab){
    document.querySelectorAll('.wzq-tab-content').forEach(el=>el.classList.remove('wzq-tab-active'));
    document.getElementById('wzq-'+tab).classList.add('wzq-tab-active');
}

function wzqAddQuestion(){

    let container = document.getElementById('wzq-questions');

    let index = container.querySelectorAll('.wzq-question-box').length;

    let html = `
    <div class="wzq-question-box">
        <p><strong>Question</strong></p>
        <textarea name="questions[`+index+`][question]" style="width:100%"></textarea>

        <p>Options</p>
        <input type="text" name="questions[`+index+`][a]" placeholder="Option A"><br>
        <input type="text" name="questions[`+index+`][b]" placeholder="Option B"><br>
        <input type="text" name="questions[`+index+`][c]" placeholder="Option C"><br>
        <input type="text" name="questions[`+index+`][d]" placeholder="Option D"><br>

        <p>Correct Answer</p>
        <select name="questions[`+index+`][correct]">
            <option value="a">A</option>
            <option value="b">B</option>
            <option value="c">C</option>
            <option value="d">D</option>
        </select>

        <p>Explanation</p>
        <textarea name="questions[`+index+`][explanation]" style="width:100%"></textarea>

        <button type="button" onclick="this.parentElement.remove()">❌ Remove</button>
    </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

</script>

<?php
}

add_action('save_post', function($post_id){

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (get_post_type($post_id) != 'wz_quiz') return;
    if (!current_user_can('edit_post', $post_id)) return;

    $post_status = get_post_status($post_id);

    if (in_array($post_status, ['trash', 'auto-draft', 'inherit'])) {
        return;
    }

    if (empty($_POST['questions']) && empty($_POST['wzq_json'])) {
        return;
    }

    global $wpdb;

    $quiz_table = $wpdb->prefix.'wz_quizzes';
    $question_table = $wpdb->prefix.'wz_questions';

    // delete old
    $old_quiz = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $quiz_table WHERE post_id = %d", $post_id)
    );

    if($old_quiz){
        $wpdb->delete($question_table, ['quiz_id' => $old_quiz->id]);
        $wpdb->delete($quiz_table, ['id' => $old_quiz->id]);
    }

    // insert quiz
    $wpdb->insert($quiz_table, [
        'post_id' => $post_id,
        'time_limit' => 300,
        'random_order' => 0,
        'ad_after' => 3
    ]);

    $quiz_id = $wpdb->insert_id;

    // JSON
    if(!empty($_POST['wzq_json'])){
        $data = json_decode(stripslashes($_POST['wzq_json']), true);

        if(!empty($data['questions'])){
            foreach($data['questions'] as $i => $q){
                $wpdb->insert($question_table, [
                    'quiz_id' => $quiz_id,
                    'question' => sanitize_textarea_field($q['question']),
                    'option_a' => sanitize_text_field($q['options']['a']),
                    'option_b' => sanitize_text_field($q['options']['b']),
                    'option_c' => sanitize_text_field($q['options']['c']),
                    'option_d' => sanitize_text_field($q['options']['d']),
                    'correct' => sanitize_text_field($q['correct']),
                    'explanation' => sanitize_textarea_field($q['explanation']),
                    'order_index' => $i
                ]);
            }
        }
    }

    // manual
    if(!empty($_POST['questions'])){
        foreach($_POST['questions'] as $i => $q){
            $wpdb->insert($question_table, [
                'quiz_id' => $quiz_id,
                'question' => sanitize_textarea_field($q['question']),
                'option_a' => sanitize_text_field($q['a']),
                'option_b' => sanitize_text_field($q['b']),
                'option_c' => sanitize_text_field($q['c']),
                'option_d' => sanitize_text_field($q['d']),
                'correct' => sanitize_text_field($q['correct']),
                'explanation' => sanitize_textarea_field($q['explanation']),
                'order_index' => $i
            ]);
        }
    }

});