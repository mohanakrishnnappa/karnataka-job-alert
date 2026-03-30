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
/* =========================================
   FILTER BUTTONS (AJAX)
========================================= */

.wzq-filters {
    text-align: center;
    margin-bottom: 25px;
}

/* Base button */
.wzq-filter-btn {
    margin: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid #e3e6ea;
    background: #f5f7fa;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
}

/* Hover */
.wzq-filter-btn:hover {
    background: #007cba;
    color: #fff;
    border-color: #007cba;
    transform: translateY(-1px);
}

/* Active */
.wzq-filter-btn.active {
    background: linear-gradient(135deg, #007cba, #005a87);
    color: #fff;
    border-color: #005a87;
    box-shadow: 0 3px 10px rgba(0, 124, 186, 0.3);
}

/* Click effect */
.wzq-filter-btn:active {
    transform: scale(0.96);
}

/* Optional: loading state */
.wzq-filter-btn.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* =========================================
   SKELETON LOADING (QUIZ CARDS)
========================================= */

.wzq-skeleton-card {
    border-radius: 12px;
    padding: 20px;
    background: #fff;
    border: 1px solid #e5e5e5;
    overflow: hidden;
    position: relative;
}

/* shimmer effect */
.wzq-skeleton {
    background: linear-gradient(
        90deg,
        #f0f0f0 25%,
        #e0e0e0 37%,
        #f0f0f0 63%
    );
    background-size: 400% 100%;
    animation: wzq-shimmer 1.4s ease infinite;
    border-radius: 6px;
}

@keyframes wzq-shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

/* skeleton parts */
.wzq-skeleton-title {
    height: 18px;
    width: 80%;
    margin-bottom: 12px;
}

.wzq-skeleton-meta {
    height: 12px;
    width: 60%;
    margin-bottom: 10px;
}

.wzq-skeleton-btn {
    height: 32px;
    width: 120px;
    margin-top: 15px;
}

#wzq-quiz-container {
    transition: opacity 0.2s ease;
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
            echo "<button class='wzq-filter-btn active' data-cat=''>All</button>";

            // ✅ Category buttons
            foreach ($terms as $term) {

                $active_class = ($current_cat === $term->slug) ? 'active' : '';

                echo "<button class='wzq-filter-btn $active_class' data-cat='{$term->slug}'>{$term->name}</button>";
            }

            echo "</div>";
        }
    ?>

    <div class="wzq-quiz-list" id="wzq-quiz-container">

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
            $quiz = wzq_get_quiz_by_post(get_the_ID());

            $question_count = wzq_get_question_count($quiz->id ?? 0);

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