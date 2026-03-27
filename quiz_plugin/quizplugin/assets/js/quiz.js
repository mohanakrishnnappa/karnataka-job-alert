document.addEventListener("click", function (e) {

    if (e.target.classList.contains("wzq-option")) {
        let correct = e.target.dataset.correct;
        let chosen = e.target.dataset.opt;

        if (correct === chosen) {
            e.target.style.background = "green";
        } else {
            e.target.style.background = "red";
        }

        e.target.closest(".wzq-question").querySelector(".wzq-explanation").style.display = "block";
    }

});