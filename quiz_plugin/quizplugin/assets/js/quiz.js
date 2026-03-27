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

// container
const container = document.querySelector(".wzq-card");

// 🔀 APPLY RANDOM ORDER
if (isRandom) {
    shuffleArray(questions);

    questions.forEach(q => {
        container.insertBefore(q, container.querySelector(".wzq-nav"));
    });
}

// 🔥 UPDATE INDEX + QUESTION NUMBER
questions.forEach((q, index) => {
    q.dataset.index = index;

    let num = q.querySelector(".wzq-q-number");
    if (num) {
        num.innerText = "Q" + (index + 1) + ".";
    }
});

const total = questions.length;
// 🎯 REVIEW PAGINATION
let reviewPage = 0;
const perPage = 5;
let totalPages = Math.ceil(total / perPage);

const nextBtn = document.querySelector(".wzq-next");
const prevBtn = document.querySelector(".wzq-prev");
const resultBox = document.querySelector(".wzq-result");
const scoreText = document.querySelector(".wzq-score");

// ✅ cache warning elements (optimized)
const warningBox = document.querySelector(".wzq-warning");
const warningText = document.querySelector(".wzq-warning-text");

// ✅ CHECK ALL ANSWERED
function allAnswered() {
    return questions.every(q => q.classList.contains("answered"));
}

function getUnansweredCount() {
    return questions.filter(q => !q.classList.contains("answered")).length;
}

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

    // 👉 LAST QUESTION LOGIC
    if (index === total - 1) {
        nextBtn.innerText = "Submit";

        if (!allAnswered()) {
            nextBtn.classList.add("disabled");
        } else {
            nextBtn.classList.remove("disabled");
        }
    } else {
        nextBtn.innerText = "Next";
        nextBtn.classList.remove("disabled");
    }
}

function renderPagination() {
    let pagination = document.querySelector(".wzq-pagination");
    if (!pagination) {
        pagination = document.createElement("div");
        pagination.className = "wzq-pagination";
        container.appendChild(pagination);
    }
    pagination.innerHTML = "";
    for (let i = 0; i < totalPages; i++) {
        const btn = document.createElement("button");
        btn.innerText = i + 1;
        btn.className = "wzq-page-btn";
        if (i === reviewPage) {
            btn.classList.add("active");
        }
        btn.addEventListener("click", () => {
            reviewPage = i;
            showReviewPage(reviewPage);
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
        pagination.appendChild(btn);
    }
}

// 🎯 SHOW REVIEW PAGE (NUMBERED)
function showReviewPage(page) {
    const start = page * perPage;
    const end = start + perPage;
    questions.forEach((q, index) => {
        if (index >= start && index < end) {
            q.style.display = "block";
        } else {
            q.style.display = "none";
        }
    });
    renderPagination();
}

// 👉 NEXT BUTTON
nextBtn.addEventListener("click", () => {

    const remaining = getUnansweredCount();

    // 👉 LAST QUESTION = SUBMIT
    if (current === total - 1) {

        if (remaining > 0) {

            // set text
            warningText.innerText =
                `You have ${remaining} unanswered question${remaining > 1 ? 's' : ''}`;

            // show
            warningBox.classList.add("show");
            warningBox.style.display = "flex";

            // scroll to warning
            warningBox.scrollIntoView({ behavior: "smooth", block: "start" });

            const firstUnanswered = questions.findIndex(q => !q.classList.contains("answered"));
            if (firstUnanswered !== -1) {
                current = firstUnanswered;
                showQuestion(current);
            }

            return;
        }

        // ✅ submit
        // ✅ DIRECT REVIEW MODE (skip result screen)

        wrapper.classList.add("wzq-review-mode");

        // hide warning just in case
        warningBox.classList.remove("show");
        warningBox.style.display = "none";

        // show explanations for all
        questions.forEach(q => {
            const exp = q.querySelector(".wzq-explanation");
            if (exp) exp.style.display = "block";
        });

        // 🎯 PAGINATION LOGIC
        if (total > 5) {

            reviewPage = 0;
            totalPages = Math.ceil(total / perPage);

            showReviewPage(reviewPage);

        } else {
            // show all if <=5
            questions.forEach(q => {
                q.style.display = "block";
            });
        }

        // ✅ CREATE SCORE BAR
        let reviewScore = document.querySelector(".wzq-review-score");

        if (!reviewScore) {
            reviewScore = document.createElement("div");
            reviewScore.className = "wzq-review-score";
            container.prepend(reviewScore);
        }

        const percent = Math.round((score / total) * 100);

        let remark = "Needs Improvement ❗";
        if (percent >= 80) remark = "Excellent 🎉";
        else if (percent >= 60) remark = "Good Job 👍";
        else if (percent >= 40) remark = "Keep Practicing 💪";

        reviewScore.innerHTML = `
            <div class="wzq-score-top">
                <div class="wzq-score-text">${score} / ${total}</div>
                <div class="wzq-score-percent">${percent}%</div>
            </div>

            <div class="wzq-score-bar">
                <div class="wzq-score-fill" style="width:${percent}%"></div>
            </div>

            <div class="wzq-score-remark">${remark}</div>
        `;

        // ✅ ADD RESTART BUTTON AT BOTTOM
        let restartBtn = document.querySelector(".wzq-review-restart");

        if (!restartBtn) {
            restartBtn = document.createElement("button");
            restartBtn.className = "wzq-restart wzq-review-restart";
            restartBtn.innerText = "Restart Quiz";

            container.appendChild(restartBtn);
        }

    } else {
        current++;
        showQuestion(current);
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

        // disable all options
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

// 👉 GLOBAL CLICK HANDLERS
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("wzq-warning-close")) {
        warningBox.classList.remove("show");
        warningBox.style.display = "none";
    }
    if (e.target.classList.contains("wzq-restart")) {
        location.reload();
    }
});

// 🚀 INITIAL LOAD
document.addEventListener("DOMContentLoaded", function () {
    showQuestion(0);
});