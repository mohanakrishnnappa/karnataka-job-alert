<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_wzq_report_question', 'wzq_report_question');
add_action('wp_ajax_nopriv_wzq_report_question', 'wzq_report_question');

function wzq_report_question(){

    global $wpdb;

    $table = WZQ_TABLE_REPORTS;

    $result = $wpdb->insert($table, [
        'quiz_id' => intval($_POST['quiz_id']),
        'question_text' => sanitize_textarea_field($_POST['question']),
        'issue' => sanitize_textarea_field($_POST['issue'])
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

    $table = WZQ_TABLE_REPORTS;

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

    $table = WZQ_TABLE_REPORTS;

    $reports = $wpdb->get_results("
        SELECT r.*, q.post_id, p.post_title
        FROM {$table} r
        LEFT JOIN " . WZQ_TABLE_QUIZZES . " q ON r.quiz_id = q.id
        LEFT JOIN {$wpdb->posts} p ON q.post_id = p.ID
        ORDER BY r.created_at DESC
    ");

    echo "<div class='wrap'><h1>🚩 Reported Questions</h1>";

    echo "<table class='widefat striped'>";
    echo "<thead>
    <tr>
        <th>ID</th>
        <th>Quiz</th>
        <th>Question</th>
        <th>Issue</th>
        <th>Date</th>
        <th>Action</th>
    </tr>
    </thead><tbody>";

    if(empty($reports)){
        echo "<tr>
                <td colspan='5'>
                    <div style='padding:30px;text-align:center;'>
                        <h3 style='margin:0;'>📭 Nothing to Show</h3>
                        <p style='color:#777;'>Users haven't reported any questions.</p>
                    </div>
                </td>
            </tr>";
    } else {
        foreach($reports as $r){

            $delete_url = wp_nonce_url(
                admin_url('admin.php?page=wzq-reports&delete_report='.$r->id),
                'wzq_delete_report_'.$r->id
            );

            // Links
            $edit_link = get_edit_post_link($r->post_id);

            echo "<tr>";

            // ID
            echo "<td>{$r->id}</td>";

            echo "<td>".esc_html($r->post_title ?? '—')."
            </td>";

            // ✅ Question (plain text)
            echo "<td>
                ".esc_html($r->question_text)."
            </td>";

            // Issue
            echo "<td>".esc_html($r->issue)."</td>";

            // Date
            echo "<td>" . esc_html($r->created_at) . "</td>";

            // Action
            echo "<td>
                <a href='{$edit_link}' target='_blank' class='button button-primary'>Edit</a>
                <a href='{$delete_url}' onclick=\"return confirm('Delete this report?')\" class='button button-danger'>Delete</a>
            </td>";

            echo "</tr>";
        }
    }

    echo "</tbody></table>";

    echo "</div>";
}