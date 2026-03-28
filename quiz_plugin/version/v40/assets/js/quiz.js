// 🔀 SHUFFLE FUNCTION (Fisher-Yates)
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        let j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

// 🔊 SOUND SYSTEM
let correctSound = new Audio(wzq_ajax.sounds.correct);
let wrongSound = new Audio(wzq_ajax.sounds.wrong);

// optional: faster playback
correctSound.preload = "auto";
wrongSound.preload = "auto";

let current = 0;
let score = 0;
let timerInterval = null;

let startTime = Date.now();
let endTime = null;

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

function submitQuiz(forceSubmit = false) {

    const remaining = getUnansweredCount();

    // ❌ DO NOT STOP TIMER YET

    // ❌ SHOW WARNING (if not forced)
    if (!forceSubmit && remaining > 0) {

        warningText.innerText =
            `You have ${remaining} unanswered question${remaining > 1 ? 's' : ''}`;

        warningBox.classList.add("show");
        warningBox.style.display = "flex";

        const firstUnanswered = questions.findIndex(q => !q.classList.contains("answered"));
        if (firstUnanswered !== -1) {
            current = firstUnanswered;
            showQuestion(current);
        }

        return; // 🚨 STOP HERE (timer continues)
    }

    if (timerInterval) clearInterval(timerInterval);

    if (!endTime) endTime = Date.now();

    // ❌ ONLY SHOW WARNING IF NOT FORCE
    if (!forceSubmit && remaining > 0) {

        warningText.innerText =
            `You have ${remaining} unanswered question${remaining > 1 ? 's' : ''}`;

        warningBox.classList.add("show");
        warningBox.style.display = "flex";

        const firstUnanswered = questions.findIndex(q => !q.classList.contains("answered"));
        if (firstUnanswered !== -1) {
            current = firstUnanswered;
            showQuestion(current);
        }

        return;
    }

    // ✅ ENTER REVIEW MODE
    wrapper.classList.add("wzq-review-mode");

    warningBox.classList.remove("show");
    warningBox.style.display = "none";

    // 🚩 ADD REPORT BUTTON IN REVIEW MODE ONLY
    questions.forEach(q => {

        // avoid duplicate buttons
        if (q.querySelector(".wzq-report")) return;

        let actionBox = document.createElement("div");
        actionBox.className = "wzq-actions";

        let btn = document.createElement("button");
        btn.className = "wzq-report";
        btn.innerText = "🚩 Report This Question";

        actionBox.appendChild(btn);

        // 👉 place near question title (right side)
        let title = q.querySelector(".wzq-question-text");

        if (title) {

            // 🔥 create wrapper only in review mode
            let header = document.createElement("div");
            header.className = "wzq-q-header";

            // 👉 META ROW (label + report)
            let meta = document.createElement("div");
            meta.className = "wzq-q-meta";

            // Report button
            meta.appendChild(actionBox);

            // insert BEFORE title
            title.parentNode.insertBefore(header, title);

            header.appendChild(meta);
            header.appendChild(title);
        }
    });

    // 🎯 HANDLE ANSWERED / UNANSWERED QUESTIONS
    questions.forEach(q => {

        const exp = q.querySelector(".wzq-explanation");
        if (exp) exp.style.display = "block";

        let meta = q.querySelector(".wzq-q-meta");
        if (!meta) return;

        let tag = document.createElement("span");
        tag.classList.add("wzq-status-label");

        const correct = q.querySelector(".wzq-option").dataset.correct;
        const selected = q.dataset.selected;

        if (q.classList.contains("answered")) {

            if (selected && selected === correct) {
                tag.innerText = "Points: 1 / 1";
                tag.classList.add("wzq-correct-point");
            } else {
                tag.innerText = "Points: 0 / 1";
                tag.classList.add("wzq-wrong-point");
            }

        } else {
            tag.innerText = "Not Answered";
            tag.classList.add("unanswered");

            q.querySelectorAll(".wzq-option").forEach(opt => {
                opt.disabled = true;

                if (opt.dataset.opt === correct) {
                    opt.classList.add("correct");
                }
            });
        }

        meta.prepend(tag); // always left side
    });

    // 🎯 PAGINATION
    if (total > 5) {
        reviewPage = 0;
        totalPages = Math.ceil(total / perPage);
        showReviewPage(reviewPage);
    } else {
        questions.forEach(q => q.style.display = "block");
    }

    // 🎯 SCORE + TIME
    let reviewScore = document.querySelector(".wzq-review-score");

    if (!reviewScore) {
        reviewScore = document.createElement("div");
        reviewScore.className = "wzq-review-score";
        container.prepend(reviewScore);
    }

    const percent = Math.round((score / total) * 100);

    // ⏱ TIME CALCULATION
    let totalSeconds = Math.floor((endTime - startTime) / 1000);
    let timeTaken = formatTime(totalSeconds);

    reviewScore.innerHTML = `
        <div class="wzq-score-top">
            <div class="wzq-score-text">${score} / ${total}</div>
            <div class="wzq-score-percent">${percent}%</div>
        </div>

        <div class="wzq-score-bar">
            <div class="wzq-score-fill" style="width:${percent}%"></div>
        </div>

        <div class="wzq-score-time">⏱ Time Taken: ${timeTaken}</div>
    `;

    let restartBtn = document.querySelector(".wzq-review-restart");

    if (!restartBtn) {
        restartBtn = document.createElement("button");
        restartBtn.className = "wzq-restart wzq-review-restart";
        restartBtn.innerText = "Restart Quiz";

        container.appendChild(restartBtn);
    }
}

function formatTime(sec) {
    let h = Math.floor(sec / 3600);
    let m = Math.floor((sec % 3600) / 60);
    let s = sec % 60;

    if (h > 0) {
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    } else {
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
}

function startQuiz() {

    // reset tracking
    startTime = Date.now();
    endTime = null;

    showQuestion(0);

    let timerValue = parseInt(wrapper?.dataset?.timer || 0);

    const timerBox = document.querySelector(".wzq-timer");
    const timerText = document.getElementById("wzq-time");

    if (timerValue > 0 && timerBox && timerText) {

        timerBox.style.display = "block";

        let remaining = timerValue;

        function updateTimer() {

            let m = Math.floor(remaining / 60);
            let s = remaining % 60;

            timerText.innerText =
                `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

            if (remaining <= 0) {
                clearInterval(timerInterval);
                submitQuiz(true); // force submit
            }

            remaining--;
        }

        updateTimer();

        timerInterval = setInterval(updateTimer, 1000);
    }
}

// 👉 NEXT BUTTON
nextBtn.addEventListener("click", () => {

    // 👉 LAST QUESTION = SUBMIT
    if (current === total - 1) {
        submitQuiz(false);
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

        // ✅ SAVE USER ANSWER
        parent.dataset.selected = this.dataset.opt;

        if (this.dataset.opt === correct) {
            this.classList.add("correct");
            score++;

            // 🔊 play correct sound
            correctSound.currentTime = 0;
            correctSound.play();

        } else {
            this.classList.add("wrong");

            // 🔊 play wrong sound
            wrongSound.currentTime = 0;
            wrongSound.play();

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

document.addEventListener("DOMContentLoaded", function () {

    const startBtn = document.querySelector(".wzq-start-btn");
    const startScreen = document.querySelector(".wzq-start-screen");
    const quizCard = document.querySelector(".wzq-card");

    if (!startBtn) return;

    startBtn.addEventListener("click", function () {

        // hide start screen
        startScreen.style.display = "none";

        // show quiz
        quizCard.style.display = "block";

        // 👉 START QUIZ HERE
        startQuiz();
    });

});