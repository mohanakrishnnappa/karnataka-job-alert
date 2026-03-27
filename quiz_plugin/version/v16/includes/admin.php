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
/* Tabs */
.wzq-tabs {
    margin-bottom: 15px;
}

.wzq-tabs button {
    background: #f1f1f1;
    border: none;
    padding: 10px 18px;
    margin-right: 8px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
}

.wzq-tabs button:hover {
    background: #e2e6ea;
}

.wzq-tab-active-btn {
    background: #007cba !important;
    color: #fff;
}

/* Tab Content */
.wzq-tab-content {
    display: none;
}

.wzq-tab-active {
    display: block;
}

/* Question Box */
.wzq-question-box {
    border: 1px solid #e5e5e5;
    padding: 18px;
    margin-bottom: 15px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.wzq-question-box:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Labels */
.wzq-question-box p {
    margin: 10px 0 5px;
    font-weight: 600;
}

/* Inputs */
.wzq-question-box textarea,
.wzq-question-box input,
.wzq-question-box select {
    width: 100%;
    padding: 8px 10px;
    margin-bottom: 8px;
    border-radius: 6px;
    border: 1px solid #ccd0d4;
    font-size: 14px;
}

.wzq-question-box textarea:focus,
.wzq-question-box input:focus,
.wzq-question-box select:focus {
    border-color: #007cba;
    outline: none;
    box-shadow: 0 0 0 1px #007cba;
}

/* Options Grid */
.wzq-question-box input[type="text"] {
    margin-bottom: 6px;
}

.wzq-options-grid div {
    display: flex;
    align-items: center;
    margin-bottom: 6px;
}

.wzq-options-grid span {
    width: 30px;
    font-weight: 600;
}

.wzq-options-grid input {
    flex: 1;
}

/* Remove Button */
.wzq-question-box button {
    background: #dc3545;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 8px;
}

.wzq-question-box button:hover {
    background: #c82333;
}

/* Add Question Button */
.button.button-primary {
    border-radius: 6px;
    padding: 8px 16px;
}

/* JSON Box */
#wzq-import textarea {
    border-radius: 8px;
    padding: 10px;
    font-family: monospace;
}
</style>

<div class="wzq-tabs">
    <button type="button" onclick="wzqShowTab('manual', this)">➕ Manual Add</button>
    <button type="button" onclick="wzqShowTab('import', this)">📥 Import JSON</button>
    <button type="button" onclick="wzqShowTab('settings', this)">⚙️ Settings</button>
</div>

<!-- MANUAL BUILDER -->
<div id="wzq-manual" class="wzq-tab-content">

    <div id="wzq-questions">

    <?php if(!empty($questions)): ?>

        <?php foreach($questions as $i => $q): ?>

            <div class="wzq-question-box">

                <p><strong>Question</strong></p>
                <textarea name="questions[<?php echo $i; ?>][question]" style="width:100%"><?php echo esc_textarea($q->question); ?></textarea>

                <p>Options</p>
                <div class="wzq-options-grid">
                    <div><span>A)</span><input type="text" name="questions[<?php echo $i; ?>][a]" value="<?php echo esc_attr($q->option_a); ?>"></div>
                    <div><span>B)</span><input type="text" name="questions[<?php echo $i; ?>][b]" value="<?php echo esc_attr($q->option_b); ?>"></div>
                    <div><span>C)</span><input type="text" name="questions[<?php echo $i; ?>][c]" value="<?php echo esc_attr($q->option_c); ?>"></div>
                    <div><span>D)</span><input type="text" name="questions[<?php echo $i; ?>][d]" value="<?php echo esc_attr($q->option_d); ?>"></div>
                </div>

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
    <p><button type="button" class="button button-primary" onclick="wzqImportJSON()">🚀 Import Now</button></p>
</div>

<!-- SETTINGS -->
<div id="wzq-settings" class="wzq-tab-content">

    <div class="wzq-question-box">

        <p><strong>⏱ Time Limit (seconds)</strong></p>
        <input type="number" name="wzq_settings[time_limit]" 
            value="<?php echo $quiz->time_limit ?? 300; ?>">

        <p><strong>🔀 Random Question Order</strong></p>
        <select name="wzq_settings[random_order]">
            <option value="0" <?php selected($quiz->random_order ?? 0, 0); ?>>No</option>
            <option value="1" <?php selected($quiz->random_order ?? 0, 1); ?>>Yes</option>
        </select>

        <p><strong>📢 Show Ad After (question no.)</strong></p>
        <input type="number" name="wzq_settings[ad_after]" 
            value="<?php echo $quiz->ad_after ?? 3; ?>">
        
        <hr>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <button type="button" class="button button-primary" onclick="wzqExportJSON()">
                ⬇️ Export JSON
            </button>

            <input type="text" readonly 
                value="[wz_quiz id='<?php echo $post->ID; ?>']"
                style="flex:1;min-width:250px;font-family:monospace;"
                onclick="this.select();">
        </div>

    </div>

</div>

<script>

function wzqShowTab(tab, el){
    document.querySelectorAll('.wzq-tab-content').forEach(elm => 
        elm.classList.remove('wzq-tab-active')
    );
    document.getElementById('wzq-' + tab).classList.add('wzq-tab-active');
    // active button
    document.querySelectorAll('.wzq-tabs button').forEach(btn => 
        btn.classList.remove('wzq-tab-active-btn')
    );
    el.classList.add('wzq-tab-active-btn');
}

function wzqAddQuestion(){

    let container = document.getElementById('wzq-questions');

    let index = container.querySelectorAll('.wzq-question-box').length;

    let html = `
    <div class="wzq-question-box">
        <p><strong>Question</strong></p>
        <textarea name="questions[`+index+`][question]" style="width:100%"></textarea>

        <p>Options</p>
        <div class="wzq-options-grid">
            <div><span>A)</span><input type="text" name="questions[`+index+`][a]" placeholder="Option A"></div>
            <div><span>B)</span><input type="text" name="questions[`+index+`][b]" placeholder="Option B"></div>
            <div><span>C)</span><input type="text" name="questions[`+index+`][c]" placeholder="Option C"></div>
            <div><span>D)</span><input type="text" name="questions[`+index+`][d]" placeholder="Option D"></div>
        </div>

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

document.addEventListener("DOMContentLoaded", function () {
    const firstTabBtn = document.querySelector('.wzq-tabs button');
    if (firstTabBtn) {
        wzqShowTab('manual', firstTabBtn);
    }
});

function wzqImportJSON() {

    const textarea = document.querySelector('[name="wzq_json"]');
    let jsonText = textarea.value;

    if (!jsonText.trim()) {
        alert("Paste JSON first");
        return;
    }

    let data;

    try {
        data = JSON.parse(jsonText);
    } catch (e) {
        alert("Invalid JSON");
        return;
    }

    // Support both formats
    let questions = data.questions ? data.questions : data;

    const container = document.getElementById('wzq-questions');

    questions.forEach((q) => {

        let index = container.querySelectorAll('.wzq-question-box').length;

        let html = `
        <div class="wzq-question-box">

            <p><strong>Question</strong></p>
            <textarea name="questions[`+index+`][question]">${q.question}</textarea>

            <p>Options</p>
            <div class="wzq-options-grid">
                <div><span>A)</span><input type="text" name="questions[`+index+`][a]" value="${q.options.A || q.options.a}"></div>
                <div><span>B)</span><input type="text" name="questions[`+index+`][b]" value="${q.options.B || q.options.b}"></div>
                <div><span>C)</span><input type="text" name="questions[`+index+`][c]" value="${q.options.C || q.options.c}"></div>
                <div><span>D)</span><input type="text" name="questions[`+index+`][d]" value="${q.options.D || q.options.d}"></div>
            </div>

            <p>Correct Answer</p>
            <select name="questions[`+index+`][correct]">
                <option value="a" ${q.answer == 'A' || q.correct == 'a' ? 'selected' : ''}>A</option>
                <option value="b" ${q.answer == 'B' || q.correct == 'b' ? 'selected' : ''}>B</option>
                <option value="c" ${q.answer == 'C' || q.correct == 'c' ? 'selected' : ''}>C</option>
                <option value="d" ${q.answer == 'D' || q.correct == 'd' ? 'selected' : ''}>D</option>
            </select>

            <p>Explanation</p>
            <textarea name="questions[`+index+`][explanation]">${q.explanation || ''}</textarea>

            <button type="button" onclick="this.parentElement.remove()">❌ Remove</button>

            <hr>

        </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
    });

    // 🔥 Switch to manual tab automatically
    const manualBtn = document.querySelector('.wzq-tabs button');
    wzqShowTab('manual', manualBtn);

    alert("Imported successfully ✅");
}

// Export Questions as JSON
function wzqExportJSON() {

    const questions = [];

    document.querySelectorAll('#wzq-questions .wzq-question-box').forEach(box => {

        const qEl = box.querySelector('textarea[name*="[question]"]');
        const aEl = box.querySelector('input[name*="[a]"]');
        const bEl = box.querySelector('input[name*="[b]"]');
        const cEl = box.querySelector('input[name*="[c]"]');
        const dEl = box.querySelector('input[name*="[d]"]');
        const correctEl = box.querySelector('select');
        const expEl = box.querySelector('textarea[name*="[explanation]"]');

        // Skip if invalid box (extra safety)
        if (!qEl || !aEl || !bEl || !cEl || !dEl || !correctEl) return;

        questions.push({
            question: qEl.value,
            options: {
                a: aEl.value,
                b: bEl.value,
                c: cEl.value,
                d: dEl.value
            },
            correct: correctEl.value,
            explanation: expEl ? expEl.value : ""
        });
    });

    const data = { questions };

    const json = JSON.stringify(data, null, 2);

    const blob = new Blob([json], { type: "application/json" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "quiz-" + Date.now() + ".json";
    a.click();

    URL.revokeObjectURL(url);
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

    $settings = $_POST['wzq_settings'] ?? [];

    $wpdb->insert($quiz_table, [
        'post_id' => $post_id,
        'time_limit' => intval($settings['time_limit'] ?? 300),
        'random_order' => intval($settings['random_order'] ?? 0),
        'ad_after' => intval($settings['ad_after'] ?? 3)
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