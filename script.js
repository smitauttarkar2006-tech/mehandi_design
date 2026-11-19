document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.main-nav');

    if (menuButton && navigation) {
        menuButton.addEventListener('click', () => {
            const isOpen = navigation.classList.toggle('is-open');
            menuButton.setAttribute('aria-expanded', String(isOpen));
            menuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
            menuButton.textContent = isOpen ? '×' : '☰';
        });
    }

    document.querySelectorAll('form[data-validate]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const password = form.querySelector('[name="password"]');
            const confirmation = form.querySelector('[name="confirm_password"]');
            if (password && confirmation) {
                confirmation.setCustomValidity(password.value === confirmation.value ? '' : 'The passwords do not match.');
            }
            if (!form.reportValidity()) event.preventDefault();
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    const imageInput = document.querySelector('[data-image-input]');
    const imagePreview = document.querySelector('[data-image-preview]');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                imagePreview.removeAttribute('src');
                imagePreview.hidden = true;
                return;
            }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                imageInput.setCustomValidity('Choose a JPG, PNG or WebP image.');
                imageInput.reportValidity();
                imageInput.value = '';
                return;
            }
            imageInput.setCustomValidity('');
            imagePreview.src = URL.createObjectURL(file);
            imagePreview.hidden = false;
        });
    }

    const gallerySearch = document.querySelector('#search');
    const gallery = document.querySelector('.gallery-section');
    if (gallerySearch && gallery) {
        const cards = Array.from(gallery.querySelectorAll('.design-card[data-search]'));
        const resultCount = gallery.querySelector('.result-count');
        gallerySearch.addEventListener('input', () => {
            const query = gallerySearch.value.trim().toLocaleLowerCase();
            let visible = 0;
            cards.forEach((card) => {
                const matches = card.dataset.search.includes(query);
                card.hidden = !matches;
                if (matches) visible += 1;
            });
            if (resultCount) resultCount.textContent = `${visible} design${visible === 1 ? '' : 's'} shown`;
        });
    }

    const categorySelect = document.querySelector('#category');
    if (categorySelect) {
        categorySelect.addEventListener('change', () => categorySelect.form.requestSubmit());
    }

    const analyzeButton = document.querySelector('#analyze-style');
    if (analyzeButton) {
        const fileInput = document.querySelector('#analyzer-image');
        const preview = document.querySelector('#analyzer-preview');
        const resultBox = document.querySelector('#analyzer-result');
        fileInput.addEventListener('change', () => {
            const file = fileInput.files && fileInput.files[0];
            if (!file) {
                preview.hidden = true;
                preview.removeAttribute('src');
                return;
            }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                fileInput.setCustomValidity('Choose a JPG, PNG or WebP image.');
                fileInput.reportValidity();
                fileInput.value = '';
                return;
            }
            fileInput.setCustomValidity('');
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });

        analyzeButton.addEventListener('click', () => {
            const selected = Array.from(document.querySelectorAll('input[name="clue"]:checked')).map((item) => item.value);
            if (selected.length === 0) {
                resultBox.innerHTML = '<h3>Select a few clues first</h3><p>Choose at least one visual clue to get a rule-based suggestion.</p>';
            } else {
                const scores = {
                    Bridal: selected.filter((item) => item === 'dense').length * 2 + selected.filter((item) => item === 'symmetry').length,
                    Arabic: selected.filter((item) => item === 'flowing').length * 2 + selected.filter((item) => item === 'simple').length,
                    Traditional: selected.filter((item) => item === 'symmetry').length * 2 + selected.filter((item) => item === 'dense').length,
                    Simple: selected.filter((item) => item === 'simple').length * 2,
                    Floral: selected.filter((item) => item === 'flower').length * 2 + selected.filter((item) => item === 'flowing').length,
                    Geometric: selected.filter((item) => item === 'geometry').length * 2 + selected.filter((item) => item === 'symmetry').length
                };
                const topScore = Math.max(...Object.values(scores));
                const styles = Object.keys(scores).filter((style) => scores[style] === topScore);
                const scoreMessage = styles.length > 1 ? 'A few styles share the top score.' : 'This style best matches the visual clues you selected.';
                resultBox.innerHTML = `<h3>Suggested style: ${styles.join(' / ')}</h3><p>${scoreMessage} This is a simple checklist-based suggestion, not an automatic image prediction.</p>`;
            }
            resultBox.hidden = false;
        });
    }
});
