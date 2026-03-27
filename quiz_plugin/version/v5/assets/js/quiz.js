let current = 0;
let score = 0;

const questions = document.querySelectorAll(".wzq-question");
const total = questions.length;

const nextBtn = document.querySelector(".wzq-next");
const prevBtn = document.querySelector(".wzq-prev");
const resultBox = document.querySelector(".wzq-result");
const scoreText = document.querySelector(".wzq-score");

// 🎯 SHOW QUESTION FUNCTION
function showQuestion(index) {

    questions.forEach(q => q.classList.remove("active"));
    questions[index].classList.add("active");

    // update question number
    document.getElementById("wzq-current").innerText = index + 1;

    // update progress bar
    let percent = ((index + 1) / total) * 100;
    document.querySelector(".wzq-bar-fill").style.width = percent + "%";

    // prev button state
    prevBtn.disabled = index === 0;
}

// 👉 NEXT BUTTON
nextBtn.addEventListener("click", () => {
    if (current < total - 1) {
        current++;
        showQuestion(current);
    } else {
        // show result
        document.querySelector(".wzq-card").style.display = "none";
        resultBox.style.display = "block";
        scoreText.innerText = "Your Score: " + score + " / " + total;
    }
});

// 👉 PREVIOUS BUTTON
prevBtn.addEventListener("click", () => {
    if (current > 0) {
        current--;
        showQuestion(current);
    }
});

// 👉 OPTION CLICK
document.querySelectorAll(".wzq-option").forEach(btn => {
    btn.addEventListener("click", function () {

        const parent = this.closest(".wzq-question");

        // ❌ already answered → stop everything
        if (parent.classList.contains("answered")) return;

        parent.classList.add("answered");

        const correct = this.dataset.correct;

        // 👉 disable ALL buttons immediately
        parent.querySelectorAll(".wzq-option").forEach(opt => {
            opt.disabled = true;
        });

        if (this.dataset.opt === correct) {
            this.classList.add("correct");
            score++;
        } else {
            this.classList.add("wrong");

            // highlight correct answer
            parent.querySelectorAll(".wzq-option").forEach(opt => {
                if (opt.dataset.opt === correct) {
                    opt.classList.add("correct");
                }
            });
        }

        // show explanation
        parent.querySelector(".wzq-explanation").style.display = "block";
    });
});


// 🔥 RESET FUNCTION (CLEAN WAY)
function resetQuiz() {

    current = 0;
    score = 0;

    questions.forEach(q => {
        q.classList.remove("active", "answered");

        q.querySelectorAll(".wzq-option").forEach(opt => {
            opt.classList.remove("correct", "wrong");
            opt.disabled = false;
        });

        q.querySelector(".wzq-explanation").style.display = "none";
    });

    prevBtn.disabled = true;

    // 🔥 IMPORTANT: re-init UI properly
    showQuestion(0);

    // show quiz again
    document.querySelector(".wzq-card").style.display = "block";
    resultBox.style.display = "none";
}


// 🔁 RESTART BUTTON
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("wzq-restart")) {
        resetQuiz();
    }
});


// 🚀 INITIAL LOAD FIX
document.addEventListener("DOMContentLoaded", function () {
    showQuestion(0);
});