document.querySelectorAll('.faq-q').forEach(q => {
    q.addEventListener('click', () => {
        const item = q.closest('.faq-item');
        const isOpen = item.classList.contains('open');

        document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));

        if (!isOpen) {
            item.classList.add('open');

            setTimeout(() => {
                const yOffset = -80; // 🔥 adjust this value
                const y = item.getBoundingClientRect().top + window.pageYOffset + yOffset;

                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });
            }, 200);
        }
    });
});