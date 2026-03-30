document.addEventListener("click", async function (e) {

    const wrapper = document.querySelector(".wzq-wrapper");
    if (!wrapper) return;

    const popup = document.getElementById("wzq-cert-popup");
    const nameInput = document.getElementById("wzq-cert-name");

    // 🎓 DOWNLOAD CLICK
    const downloadBtn = e.target.closest("#wzq-download-cert");

    if (downloadBtn) {

        console.log("CLICK DETECTED");

        const quizId = wrapper.dataset.quiz;
        const key = "quiz_cert_" + quizId;

        const data = JSON.parse(localStorage.getItem(key));

        if (data && data.downloaded) {
            alert("Already downloaded");
            return;
        }

        if (popup) popup.style.display = "flex";
    }

    // ❌ CANCEL
    const cancelBtn = e.target.closest("#wzq-cert-cancel");
    if (cancelBtn) {
        if (popup) popup.style.display = "none";
    }

    // ✅ SAVE
    const saveBtn = e.target.closest("#wzq-cert-save");
    if (saveBtn) {

        const name = nameInput?.value.trim();
        if (!name) return alert("Enter name");

        const quizId = wrapper.dataset.quiz;
        const key = "quiz_cert_" + quizId;

        const scoreText = document.querySelector(".wzq-score-text")?.innerText || "0/0";
        const parts = scoreText.split("/").map(s => s.trim());
        const score = parts[0];
        const total = parts[1];

        const quizTitle = document.querySelector(".wzq-question-text")?.innerText || "Quiz";

        generateCertificate(name, score, total, quizTitle);

        localStorage.setItem(key, JSON.stringify({
            downloaded: true,
            name: name
        }));

        if (popup) popup.style.display = "none";

        // show share button
        const shareBtn = document.getElementById("wzq-share-cert");
        if (shareBtn) shareBtn.style.display = "inline-block";
    }

    // 📲 SHARE
    const shareBtn = e.target.closest("#wzq-share-cert");
    if (shareBtn) {

        const text = `🎉 I completed a quiz!\nTry here 👉 ${wzq_cert.site_url}`;

        if (navigator.share && window.generatedCert) {
            const blob = await (await fetch(window.generatedCert)).blob();

            const file = new File([blob], "certificate.png", { type: "image/png" });

            navigator.share({
                files: [file],
                title: "My Certificate",
                text: text
            });
        } else {
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`);
        }
    }


    // 🎨 GENERATE CERTIFICATE
    function generateCertificate(name, score, total, quizTitle) {

        const canvas = document.createElement("canvas");
        canvas.width = 1200;
        canvas.height = 850;

        const ctx = canvas.getContext("2d");

        // Background
        ctx.fillStyle = "#faf7f0";
        ctx.fillRect(0, 0, 1200, 850);

        // Border
        ctx.strokeStyle = "#d4af37";
        ctx.lineWidth = 10;
        ctx.strokeRect(20, 20, 1160, 810);

        ctx.textAlign = "center";

        // Title
        ctx.font = "bold 48px Arial";
        ctx.fillText("Certificate of Achievement", 600, 140);

        // Name
        ctx.font = "bold 42px Georgia";
        ctx.fillText(name, 600, 320);

        // Text
        ctx.font = "24px Arial";
        ctx.fillText("has successfully completed", 600, 380);
        ctx.fillText(quizTitle, 600, 430);

        ctx.fillText(`Score: ${score}/${total}`, 600, 480);

        // Date
        ctx.fillText(new Date().toLocaleDateString(), 600, 540);

        // 🟡 Seal
        ctx.beginPath();
        ctx.arc(950, 650, 60, 0, Math.PI * 2);
        ctx.fillStyle = "#d4af37";
        ctx.fill();

        ctx.fillStyle = "#000";
        ctx.font = "bold 14px Arial";
        ctx.fillText("CERTIFIED", 950, 655);

        // ✍️ Signature
        ctx.font = "italic 28px cursive";
        ctx.fillText("Mohana Krishnnappa", 250, 700);

        ctx.font = "16px Arial";
        ctx.fillText("Founder", 250, 740);

        // Download
        const link = document.createElement("a");
        link.download = "certificate.png";
        console.log("GENERATING CERT...");
        link.href = canvas.toDataURL();

        document.body.appendChild(link); // 🔥 REQUIRED
        setTimeout(() => {
            link.click();
        }, 100);
        document.body.removeChild(link);

        window.generatedCert = canvas.toDataURL();
    }

});