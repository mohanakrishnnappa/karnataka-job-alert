document.addEventListener("DOMContentLoaded", function () {

    const wrapper = document.querySelector(".wzq-wrapper");
    if (!wrapper) return;

    const quizId = wrapper.dataset.quiz;
    const key = "quiz_cert_" + quizId;

    // 🔥 CLEAR on refresh
    sessionStorage.removeItem(key);

});

document.addEventListener("click", async function (e) {

    const wrapper = document.querySelector(".wzq-wrapper");
    if (!wrapper) return;

    const popup = document.getElementById("wzq-cert-popup");
    const nameInput = document.getElementById("wzq-cert-name");

    // 🎓 DOWNLOAD CLICK
    const downloadBtn = e.target.closest("#wzq-download-cert");

    if (downloadBtn) {

        const quizId = wrapper.dataset.quiz;
        const key = "quiz_cert_" + quizId;

        const data = JSON.parse(sessionStorage.getItem(key));

        // ✅ Already generated → download directly
        if (data && data.generated && data.image) {

            const link = document.createElement("a");
            link.download = "certificate.png";
            link.href = data.image;

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            return; // 🔥 IMPORTANT (stop popup)
        }

        // ❗ First time → show popup
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

        const image = generateCertificate(name, score, total, quizTitle);

        // ✅ store in session (not permanent)
        sessionStorage.setItem(key, JSON.stringify({
            generated: true,
            name: name,
            image: image
        }));

        if (popup) popup.style.display = "none";

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
        ctx.fillStyle = "#fff";
        ctx.fillRect(0, 0, canvas.width, canvas.height); // ✅ ADD THIS

        // Border
        ctx.strokeStyle = "#d4af37";
        ctx.lineWidth = 10;
        ctx.strokeRect(20, 20, 1160, 810);

        ctx.textAlign = "center";

        // Title
        ctx.font = "bold 48px Arial";
        ctx.fillStyle = "#000";
        ctx.fillText("Certificate of Achievement", 600, 140);

        // Name
        ctx.font = "bold 42px Arial";
        ctx.fillStyle = "#000";
        ctx.fillText(name, 600, 320);

        // Text
        ctx.font = "24px Arial";
        ctx.fillStyle = "#000"; // white text
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
        link.href = canvas.toDataURL();

        document.body.appendChild(link); // 🔥 REQUIRED
        setTimeout(() => {
            link.click();
        }, 100);
        document.body.removeChild(link);

        window.generatedCert = canvas.toDataURL();

        const image = canvas.toDataURL("image/png");

        // 🔥 VERY IMPORTANT
        window.generatedCert = image;

        return image;
    }

});