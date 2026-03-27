let current = 0;
let score = 0;

document.addEventListener("click", function (e) {

    let questions = document.querySelectorAll(".wzq-question");
    let prevBtn = document.querySelector(".wzq-prev");
    let nextBtn = document.querySelector(".wzq-next");

    if (!questions.length) return;

    // =====================
    // UPDATE UI FUNCTION (INSIDE)
    // =====================
    function updateUI() {

        let currentEl = document.getElementById("wzq-current");
        if (currentEl) currentEl.innerText = current + 1;

        let percent = ((current + 1) / questions.length) * 100;
        let bar = document.querySelector(".wzq-bar-fill");
        if (bar) bar.style.width = percent + "%";

        // Previous button disable
        if (prevBtn) {
            prevBtn.disabled = current === 0;
        }

        // Next button text change
        if (nextBtn) {
            if (current === questions.length - 1) {
                nextBtn.innerText = "Submit";
            } else {
                nextBtn.innerText = "Next";
            }
        }
    }

    // =====================
    // ANSWER CLICK
    // =====================
    if (e.target.classList.contains("wzq-option")) {

        let parent = e.target.closest(".wzq-question");

        if (parent.classList.contains("answered")) return;

        parent.classList.add("answered");

        let correct = e.target.dataset.correct;
        let chosen = e.target.dataset.opt;

        parent.querySelectorAll(".wzq-option").forEach(btn => {
            if (btn.dataset.opt === correct) {
                btn.classList.add("correct");
            } else if (btn === e.target) {
                btn.classList.add("wrong");
            }
        });

        if (correct === chosen) score++;

        parent.querySelector(".wzq-explanation").style.display = "block";
    }

    // =====================
    // NEXT BUTTON
    // =====================
    if (e.target.classList.contains("wzq-next")) {

        if (current < questions.length - 1) {

            questions[current].classList.remove("active");
            current++;
            questions[current].classList.add("active");

        } else {

            document.querySelector(".wzq-card").style.display = "none";
            document.querySelector(".wzq-result").style.display = "block";
            document.querySelector(".wzq-result").innerHTML =
                "<h2>Your Score: " + score + " / " + questions.length + "</h2>";
        }

        updateUI();
    }

    // =====================
    // PREVIOUS BUTTON
    // =====================
    if (e.target.classList.contains("wzq-prev")) {

        if (current > 0) {

            questions[current].classList.remove("active");
            current--;
            questions[current].classList.add("active");
        }

        updateUI();
    }

});