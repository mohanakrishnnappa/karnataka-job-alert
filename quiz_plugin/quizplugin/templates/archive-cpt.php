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

/* EXTRA */
div.wzq-fullwidth {
    background-color: #ffffff;
    margin: 20px auto;
}
</style>

<div class="wzq-fullwidth">

    <h1 class="wzq-heading">All Quizzes</h1>

    <div class="wzq-quiz-list">

        <?php
        $query = new WP_Query([
            'post_type' => 'wz_quiz',
            'posts_per_page' => -1
        ]);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
        ?>

            <div class="wzq-card">
                <h2><?php the_title(); ?></h2>
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