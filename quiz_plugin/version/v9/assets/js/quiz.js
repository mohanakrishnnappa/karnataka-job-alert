// 🔀 SHUFFLE FUNCTION (Fisher-Yates)
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        let j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

let current = 0;
let score = 0;

// wrapper
const wrapper = document.querySelector(".wzq-wrapper");

// check random setting (from PHP)
const isRandom = wrapper?.dataset?.random == "1";

// get questions as array
let questions = Array.from(document.querySelectorAll(".wzq-question"));

// 🔀 APPLY RANDOM ORDER
if (isRandom) {
    shuffleArray(questions);
}

// re-append shuffled questions into DOM
const container = document.querySelector(".wzq-card");

questions.forEach(q => {
    container.insertBefore(q, container.querySelector(".wzq-nav"));
});

// 🔥 UPDATE INDEX + QUESTION NUMBER
questions.forEach((q, index) => {
    q.dataset.index = index;

    let num = q.querySelector(".wzq-q-number");
    if (num) {
        num.innerText = "Q" + (index + 1) + ".";
    }
});

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

        // ❌ already answered → stop
        if (parent.classList.contains("answered")) return;

        parent.classList.add("answered");

        const correct = this.dataset.correct;

        // 👉 disable ALL buttons
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

// 🔁 RESTART BUTTON
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("wzq-restart")) {
        location.reload();
    }
});


// 🚀 INITIAL LOAD
document.addEventListener("DOMContentLoaded", function () {
    showQuestion(0);
});