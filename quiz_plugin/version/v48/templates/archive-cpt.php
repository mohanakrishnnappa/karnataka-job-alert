<?php
if (!defined('ABSPATH')) exit;

get_header();
?>

<style>
/* FULL WIDTH WRAPPER */
.wzq-fullwidth {
    width: 100%;
    max-width: 100%;
    padding: 20px 40px;
}

/* HEADING */
.wzq-heading {
    text-align: center;
    margin-bottom: 30px;
}

/* GRID */
.wzq-quiz-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

/* CARD */
.wzq-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e5e5e5;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: 0.2s ease;
}

.wzq-card:hover {
    transform: translateY(-4px);
}

/* BUTTON */
.wzq-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #007cba, #005a87);
    color: #fff !important;
    border-radius: 6px;
    text-decoration: none;
}

/* Quiz Meta Box */
.wzq-meta {
    font-size: 13px;
    color: #555;
    margin: 10px 0;
    display: flex;
    gap: 15px;
}

/* Category Filter */
.wzq-filters {
    text-align: center;
    margin-bottom: 20px;
}

.wzq-filters a {
    margin: 5px;
    padding: 6px 12px;
    background: #f1f1f1;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
}

.wzq-filters a:hover {
    background: #007cba;
    color: #fff;
}

.wzq-filters a.active {
    background: #007cba;
    color: #fff;
}

/* EXTRA */
div.wzq-fullwidth {
    background-color: #ffffff;
    margin: 20px auto;
}
</style>

<div class="wzq-fullwidth">

    <h1 class="wzq-heading">All Quizzes</h1>

    <?php
        $terms = get_terms([
            'taxonomy' => 'wz_quiz_category',
            'hide_empty' => true
        ]);

        if (!empty($terms)) {

            $current_cat = $_GET['cat'] ?? '';

            echo "<div class='wzq-filters'>";

            // ✅ All button
            $active_class = empty($current_cat) ? 'active' : '';
            echo "<a class='$active_class' href='" . get_post_type_archive_link('wz_quiz') . "'>All</a>";

            // ✅ Category buttons
            foreach ($terms as $term) {

                $active_class = ($current_cat === $term->slug) ? 'active' : '';

                echo "<a class='$active_class' href='" . add_query_arg('cat', $term->slug) . "'>{$term->name}</a>";
            }

            echo "</div>";
        }
    ?>

    <div class="wzq-quiz-list">

        <?php

        // 👇 ADD HERE (before loop)
        $tax_query = [];

        if (!empty($_GET['cat'])) {
            $tax_query[] = [
                'taxonomy' => 'wz_quiz_category',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['cat'])
            ];
        }

        $query = new WP_Query([
            'post_type' => 'wz_quiz',
            'posts_per_page' => -1,
            'tax_query' => $tax_query
        ]);

        // 👇 LOOP STARTS
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
        ?>
        
        <?php
            global $wpdb;

            $quiz = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}wz_quizzes WHERE post_id = %d",
                    get_the_ID()
                )
            );

            $question_count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}wz_questions WHERE quiz_id = %d",
                    $quiz->id ?? 0
                )
            );

            // Format time
            $time_label = "Unlimited";

            if (!empty($quiz->time_limit)) {
                $m = floor($quiz->time_limit / 60);
                $s = $quiz->time_limit % 60;

                if ($m > 0) $time_label = $m . " min";
                if ($s > 0 && $m == 0) $time_label = $s . " sec";
            }
            ?>

            <div class="wzq-card">
                <h2><?php the_title(); ?></h2>

                <div class="wzq-meta">
                    <span>📊 <?php echo $question_count; ?> Questions</span>
                    <span>⏱ <?php echo $time_label; ?></span>
                </div>

                <a href="<?php the_permalink(); ?>" class="wzq-btn">▶ Start Quiz</a>
            </div>

        <?php endwhile;
        else :
            echo "<p>No quizzes found</p>";
        endif;

        wp_reset_postdata();
        ?>

    </div>

</div>

<?php get_footer(); ?>