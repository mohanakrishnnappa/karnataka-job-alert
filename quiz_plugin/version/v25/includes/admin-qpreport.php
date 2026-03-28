<?php

add_action('wp_ajax_wzq_report_question', 'wzq_report_question');
add_action('wp_ajax_nopriv_wzq_report_question', 'wzq_report_question');

function wzq_report_question(){

    global $wpdb;

    $table = $wpdb->prefix . 'wz_reports';

    $result = $wpdb->insert($table, [
        'quiz_id' => intval($_POST['quiz_id']),
        'question_text' => sanitize_textarea_field($_POST['question']),
        'issue' => sanitize_textarea_field($_POST['issue']),
        'user_ip' => $_SERVER['REMOTE_ADDR']
    ]);

    if($result === false){
        echo "DB ERROR: " . $wpdb->last_error;
    } else {
        echo "SUCCESS";
    }

    wp_die();
}

add_action('admin_menu', function(){

    add_submenu_page(
        'edit.php?post_type=wz_quiz', // attach to Quizzes
        'Quiz Reports',
        'Quiz Reports',
        'manage_options',
        'wzq-reports',
        'wzq_reports_page' // function from admin.php
    );

});

add_action('admin_init', function(){

    if(!isset($_GET['delete_report'])) return;

    if(!current_user_can('manage_options')) return;

    $id = intval($_GET['delete_report']);

    // 🔐 nonce check
    if(!isset($_GET['_wpnonce']) || 
       !wp_verify_nonce($_GET['_wpnonce'], 'wzq_delete_report_'.$id)){
        wp_die("Security check failed");
    }

    global $wpdb;

    $table = $wpdb->prefix . 'wz_reports';

    $wpdb->delete($table, ['id' => $id]);

    // 🔁 redirect to avoid repeat delete on refresh
    wp_redirect(admin_url('admin.php?page=wzq-reports&deleted=1'));
    exit;
});

function wzq_reports_page(){

    if(isset($_GET['deleted'])){
        echo "<div class='notice notice-success'><p>Report deleted successfully ✅</p></div>";
    }

    global $wpdb;

    $table = $wpdb->prefix . 'wz_reports';

    $reports = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    echo "<div class='wrap'><h1>🚩 Reported Questions</h1>";

    echo "<table class='widefat striped'>";
    echo "<thead>
    <tr>
        <th>ID</th>
        <th>Question</th>
        <th>Issue</th>
        <th>IP</th>
        <th>Date</th>
        <th>Action</th>
    </tr>
    </thead><tbody>";

    if(empty($reports)){
        echo "<tr>
                <td colspan='6' style='text-align:center;padding:20px;font-size:16px;color:#666;'>
                    📭 No reported questions found
                </td>
            </tr>";
    } else {
        foreach($reports as $r){

            $delete_url = wp_nonce_url(
                admin_url('admin.php?page=wzq-reports&delete_report='.$r->id),
                'wzq_delete_report_'.$r->id
            );

            echo "<tr>";
            echo "<td>{$r->id}</td>";
            echo "<td>".esc_html($r->question_text)."</td>";
            echo "<td>".esc_html($r->issue)."</td>";
            echo "<td>{$r->user_ip}</td>";
            echo "<td>{$r->created_at}</td>";
            echo "<td>
                    <a href='{$delete_url}' 
                    onclick=\"return confirm('Delete this report?')\" 
                    class='button button-danger'>
                    Delete
                    </a>
                </td>";
            echo "</tr>";
        }
    }

    echo "</tbody></table>";

    echo "</div>";
}