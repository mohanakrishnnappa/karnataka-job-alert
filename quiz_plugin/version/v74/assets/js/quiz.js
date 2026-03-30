if (!document.querySelector(".wzq-wrapper")) {
    console.log("Quiz JS skipped (not quiz page)");
} else {// 🔀 SHUFFLE FUNCTION (Fisher-Yates)

    console.log("Quiz JS Loaded");

    let order = [];
    let pointer = 0;
    const chunk = 5;
    let questions = [];

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            let j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }

    // 🔊 SOUND SYSTEM
    let correctSound = new Audio(wzq_ajax.sounds.correct);
    let wrongSound = new Audio(wzq_ajax.sounds.wrong);
    let finishSound = new Audio(wzq_ajax.sounds.finish);

    // optional: faster playback
    correctSound.preload = "auto";
    wrongSound.preload = "auto";
    finishSound.preload = "auto";

    let current = 0;
    let score = 0;
    let timerInterval = null;

    let startTime = Date.now();
    let endTime = null;

    // wrapper
    const wrapper = document.querySelector(".wzq-wrapper");

    // check random setting (from PHP)
    const isRandom = wrapper?.dataset?.random == "1";

    // container
    const container = document.querySelector(".wzq-card");

    // 🔥 UPDATE INDEX + QUESTION NUMBER
    questions.forEach((q, index) => {
        q.dataset.index = index;

        let num = q.querySelector(".wzq-q-number");
        if (num) {
            num.innerText = "Q" + (index + 1) + ".";
        }
    });

    let total = parseInt(wrapper.dataset.total) || 0;
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

    function loadNextBatch() {

        if (pointer >= order.length) return;

        const ids = order.slice(pointer, pointer + chunk);
        pointer += chunk;

        // 🔥 SHOW LOADING
        container.classList.add("loading");

        fetch(wzq_ajax.url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "wzq_load_questions",
                ids: ids
            })
        })
            .then(res => res.json())
            .then(data => {

                if (!Array.isArray(data)) {
                    console.error("Invalid response:", data);
                    return;
                }

                renderQuestions(data);

                // 🔥 REMOVE LOADING AFTER RENDER
                container.classList.remove("loading");
            })
            .catch(err => {
                console.error(err);

                // ❗ ALWAYS REMOVE LOADING ON ERROR TOO
                container.classList.remove("loading");
            });
    }

    // 🎯 SHOW QUESTION FUNCTION
    function showQuestion(index) {

        if (!questions[index]) return;

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

        // ✅ ENTER REVIEW MODE
        wrapper.classList.add("wzq-review-mode");
        // 🔊 FINISH SOUND
        finishSound.currentTime = 0;
        finishSound.play();

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
        window.wzqPercent = percent;

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

        // 🔥 ACTION WRAPPER
        let actionWrap = container.querySelector(".wzq-review-actions");

        if (!actionWrap) {
            actionWrap = document.createElement("div");
            actionWrap.className = "wzq-review-actions";
            container.appendChild(actionWrap);
        }

        // ✅ RESTART BUTTON
        let restartBtn = actionWrap.querySelector(".wzq-review-restart");

        if (!restartBtn) {
            restartBtn = document.createElement("button");
            restartBtn.className = "wzq-restart wzq-review-restart";
            restartBtn.innerText = "Restart Quiz";
        }

        // ✅ CUSTOM BUTTON
        const btnText = wrapper?.dataset?.btnText;
        const btnLink = wrapper?.dataset?.btnLink;

        // remove old custom button
        let customBtn = actionWrap.querySelector(".wzq-custom-btn");
        if (customBtn) customBtn.remove();

        if (btnText && btnLink) {
            customBtn = document.createElement("a");
            customBtn.className = "wzq-custom-btn";
            customBtn.innerText = btnText;
            customBtn.href = btnLink;
            customBtn.target = "_blank";

            actionWrap.appendChild(customBtn);
        }

        // always append restart last
        actionWrap.appendChild(restartBtn);

        // 🎓 CERTIFICATE BUTTON (ADD HERE)
        let certBtn = actionWrap.querySelector("#wzq-download-cert");

        if (!certBtn) {
            certBtn = document.createElement("button");
            certBtn.id = "wzq-download-cert";
            certBtn.className = "wzq-custom-btn";
            certBtn.innerText = "🎓 Download Certificate";
            certBtn.style.display = "none";
            actionWrap.appendChild(certBtn);
        }

        // 📲 SHARE BUTTON
        let shareBtn = actionWrap.querySelector("#wzq-share-cert");

        if (!shareBtn) {
            shareBtn = document.createElement("button");
            shareBtn.id = "wzq-share-cert";
            shareBtn.className = "wzq-custom-btn";
            shareBtn.innerText = "📲 Share";
            shareBtn.style.display = "none";
            actionWrap.appendChild(shareBtn);
        }

        // 🎯 SHOW ONLY IF >= 35%
        if (percent >= 35) {
            certBtn.style.display = "inline-block";
            certBtn.disabled = false;
        } else {
            certBtn.style.display = "inline-block";
            certBtn.disabled = false;
            certBtn.innerText = "🎓 Download Certificate";
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

    function renderQuestions(data) {

        if (!Array.isArray(data)) {
            console.error("Data is not array:", data);
            return;
        }

        const container = document.getElementById("wzq-question-container");

        data.forEach((q, index) => {

            const qIndex = questions.length;

            const div = document.createElement("div");
            div.className = "wzq-question";
            div.dataset.index = qIndex;

            div.innerHTML = `
            <h3 class="wzq-question-text">
                <span class="wzq-q-number">Q${qIndex + 1}.</span>
                ${q.question}
            </h3>

            <div class="wzq-options">
                ${["a", "b", "c", "d"].map(opt => `
                    <button class="wzq-option"
                        data-correct="${q.correct}"
                        data-opt="${opt}">
                        <span>${opt.toUpperCase()})</span>
                        ${q["option_" + opt]}
                    </button>
                `).join("")}
            </div>

            <div class="wzq-explanation">
                <span class="wzq-expl-title">Explanation:</span>
                ${q.explanation}
            </div>`;

            container.appendChild(div);

            questions.push(div);
        });

        // show first if needed
        if (questions.length === data.length) {
            current = 0;
        }

        // ALWAYS ensure first question visible
        showQuestion(current);
    }

    function startQuiz() {

        // reset tracking
        startTime = Date.now();
        endTime = null;

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

        // 🔥 Load more when user reaches last loaded question
        if (current >= questions.length - 2) {
            loadNextBatch();
        }

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
    document.addEventListener("click", function (e) {

        const btn = e.target.closest(".wzq-option");
        if (!btn) return;

        const parent = btn.closest(".wzq-question");

        // ❌ already answered → stop
        if (parent.classList.contains("answered")) return;

        parent.classList.add("answered");

        const correct = btn.dataset.correct;

        // disable all options
        parent.querySelectorAll(".wzq-option").forEach(opt => {
            opt.disabled = true;
        });

        // ✅ SAVE USER ANSWER
        parent.dataset.selected = btn.dataset.opt;

        if (btn.dataset.opt === correct) {
            btn.classList.add("correct");
            score++;

            // 🔊 ✅ PLAY CORRECT SOUND
            if (correctSound) {
                correctSound.currentTime = 0;
                correctSound.play().catch(() => { });
            }

        } else {
            btn.classList.add("wrong");

            // 🔊 ✅ PLAY WRONG SOUND
            if (wrongSound) {
                wrongSound.currentTime = 0;
                wrongSound.play().catch(() => { });
            }

            // highlight correct answer
            parent.querySelectorAll(".wzq-option").forEach(opt => {
                if (opt.dataset.opt === correct) {
                    opt.classList.add("correct");
                }
            });
        }

        // show explanation
        const exp = parent.querySelector(".wzq-explanation");
        if (exp) exp.style.display = "block";
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

            startScreen.style.display = "none";
            quizCard.style.display = "block";

            // 🔥 FETCH QUESTION IDS
            fetch(wzq_ajax.url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    action: "wzq_get_question_ids",
                    quiz_id: wrapper.dataset.quiz
                })
            })
                .then(res => res.json())
                .then(ids => {

                    order = ids;

                    if (isRandom) shuffleArray(order);

                    total = order.length;
                    wrapper.dataset.total = total;

                    // ✅ update UI
                    document.querySelector(".wzq-progress-text").innerHTML =
                        `Question <span id="wzq-current">1</span> / ${total}`;

                    // 🚀 LOAD FIRST BATCH
                    loadNextBatch();

                    // 🎯 start timer
                    startQuiz();
                });
        });

    });

    // Template filter buttons (AJAX)
    document.addEventListener("click", function (e) {

        if (!e.target.classList.contains("wzq-filter-btn")) return;

        const btn = e.target;
        const cat = btn.dataset.cat;

        // active class switch
        document.querySelectorAll(".wzq-filter-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");

        const container = document.getElementById("wzq-quiz-container");

        container.innerHTML = "Loading...";

        fetch(wzq_ajax.url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "wzq_filter_quiz",
                cat: cat
            })
        })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);

                    if (!Array.isArray(data)) {
                        console.error("Invalid response:", data);
                        return;
                    }

                    renderQuestions(data);
                } catch (e) {
                    console.error("RAW RESPONSE:", text); // 🔥 THIS WILL SHOW PHP ERROR
                }
            });

    });
}

