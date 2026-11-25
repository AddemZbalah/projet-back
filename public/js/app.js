// Comportements front globaux.

/** Petit message temporaire en bas de l'écran (remplace le flash quand la page ne se recharge pas). */
function showToast(message, type = 'success') {
    let zone = document.querySelector('.toasts');
    if (!zone) {
        zone = document.createElement('div');
        zone.className = 'toasts';
        zone.setAttribute('role', 'status');
        zone.setAttribute('aria-live', 'polite');
        document.body.appendChild(zone);
    }
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    zone.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('is-leaving');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }, 3200);
}

/**
 * Favoris sans rechargement : on intercepte le formulaire et on l'envoie avec fetch().
 * Si JavaScript est désactivé, le formulaire fonctionne normalement (POST + redirection).
 */
document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-favorite-form]');
    if (!form) return;
    event.preventDefault();

    const button = form.querySelector('button');
    if (button.disabled) return;
    button.disabled = true;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) throw new Error(String(response.status));
        const data = await response.json();

        button.classList.toggle('is-fav', data.favorite);
        button.setAttribute('aria-pressed', String(data.favorite));
        const label = button.querySelector('[data-fav-label]');
        if (label) label.textContent = data.favorite ? button.dataset.labelOn : button.dataset.labelOff;

        // Page « Mes favoris » : la carte disparaît quand on retire le commerce.
        const container = !data.favorite && form.dataset.removeOnUnfav && form.closest(form.dataset.removeOnUnfav);
        if (container) {
            container.classList.add('is-removing');
            container.addEventListener('transitionend', () => {
                container.remove();
                if (!document.querySelector('.fav-list .fav')) location.reload(); // affiche l'état vide
            }, { once: true });
        }

        showToast(data.message);
    } catch (e) {
        showToast('Impossible de mettre à jour vos favoris. Réessayez.', 'error');
    } finally {
        button.disabled = false;
    }
});

/* ==========================================================================
   Photos : glisser-déposer
   Les images sont redimensionnées dans le navigateur (1600 px, JPEG) avant l'envoi :
   upload rapide et sous la limite PHP de 2 Mo par fichier.
   ========================================================================== */
const MAX_SIDE = 1600;
const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];

async function resizeImage(file) {
    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    const ratio = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * ratio);
    canvas.height = Math.round(bitmap.height * ratio);
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff'; // fond blanc pour les PNG transparents
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close?.();
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
    return new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
}

document.querySelectorAll('[data-dropzone]').forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    const previews = zone.querySelector('.dropzone__previews');
    const status = zone.querySelector('.dropzone__status');
    const form = zone.closest('form');
    const max = parseInt(zone.dataset.max || '8', 10);
    let files = [];
    let pending = 0;

    const setStatus = (text) => { status.hidden = !text; status.textContent = text || ''; };

    // Synchronise la liste interne avec le vrai <input type="file"> envoyé par le formulaire.
    const sync = () => {
        const dt = new DataTransfer();
        files.forEach((f) => dt.items.add(f));
        input.files = dt.files;
        zone.classList.toggle('is-full', files.length >= max);
        render();
    };

    const render = () => {
        previews.innerHTML = '';
        files.forEach((file, i) => {
            const li = document.createElement('li');
            li.className = 'photo-tile is-new';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = `Nouvelle photo ${i + 1}`;
            img.onload = () => URL.revokeObjectURL(img.src);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'photo-tile__remove';
            remove.setAttribute('aria-label', `Retirer la photo ${i + 1}`);
            remove.innerHTML = '&times;';
            remove.addEventListener('click', () => { files.splice(i, 1); sync(); });
            li.append(img, remove);
            previews.appendChild(li);
        });
    };

    const addFiles = async (list) => {
        const incoming = [...list].filter((f) => ACCEPTED.includes(f.type));
        const rejected = list.length - incoming.length;
        const room = max - files.length;
        if (room <= 0) { showToast(`Maximum ${max} photo(s) atteint.`, 'error'); return; }
        if (incoming.length > room) showToast(`Seules ${room} photo(s) ont été ajoutées (maximum ${max}).`, 'error');
        if (rejected) showToast('Formats acceptés : JPG, PNG ou WebP.', 'error');

        const batch = incoming.slice(0, room);
        pending += batch.length;
        setStatus('Préparation des photos…');
        for (const file of batch) {
            try {
                files.push(await resizeImage(file));
            } catch {
                files.push(file); // navigateur trop ancien : on envoie l'original, le serveur redimensionnera
            }
            pending--;
            sync();
        }
        if (!pending) setStatus('');
    };

    // Sélection via la boîte de dialogue : on ajoute à la liste au lieu de remplacer.
    input.addEventListener('change', () => {
        const picked = [...input.files];
        sync(); // remet les fichiers déjà choisis dans l'input
        addFiles(picked);
    });

    ['dragenter', 'dragover'].forEach((type) => zone.addEventListener(type, (e) => {
        e.preventDefault();
        zone.classList.add('is-dragover');
    }));
    ['dragleave', 'drop'].forEach((type) => zone.addEventListener(type, (e) => {
        e.preventDefault();
        if (type === 'dragleave' && zone.contains(e.relatedTarget)) return;
        zone.classList.remove('is-dragover');
    }));
    zone.addEventListener('drop', (e) => addFiles(e.dataTransfer.files));

    // Pas d'envoi tant que les photos sont en cours de préparation.
    form?.addEventListener('submit', (e) => {
        if (pending > 0) {
            e.preventDefault();
            showToast('Patientez, les photos sont en cours de préparation…', 'error');
        }
    });
});

/* Case « supprimer » des photos existantes : retour visuel immédiat. */
document.addEventListener('change', (e) => {
    if (e.target.matches('.photo-tile__delete input')) {
        e.target.closest('.photo-tile').classList.toggle('is-deleted', e.target.checked);
    }
});

/* ==========================================================================
   Galerie photos (page d'une offre) + visionneuse plein écran
   ========================================================================== */
document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const items = [...gallery.querySelectorAll('.mosaic__item img')];
    const photos = items.map((img) => ({ src: img.src, alt: img.alt }));
    const dialog = gallery.querySelector('.lightbox');
    const big = dialog.querySelector('.lightbox__img');
    const counter = dialog.querySelector('[data-gallery-current]');
    const thumbs = [...dialog.querySelectorAll('.lightbox__thumb')];
    const mosaic = gallery.querySelector('.mosaic');
    const mosaicCounter = gallery.querySelector('[data-mosaic-current]');
    let index = 0;

    const show = (i) => {
        index = (i + photos.length) % photos.length;
        big.src = photos[index].src;
        big.alt = photos[index].alt;
        if (counter) counter.textContent = String(index + 1);
        thumbs.forEach((t, n) => t.classList.toggle('is-active', n === index));
        thumbs[index]?.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
    };

    gallery.addEventListener('click', (e) => {
        const open = e.target.closest('[data-gallery-open]');
        if (open) {
            // Sur mobile, la mosaïque est un carrousel : on ouvre la photo réellement visible.
            show(open.classList.contains('mosaic__all') ? 0 : +open.dataset.galleryOpen);
            dialog.showModal();
        } else if (e.target.closest('[data-gallery-prev]')) show(index - 1);
        else if (e.target.closest('[data-gallery-next]')) show(index + 1);
        else if (e.target.closest('[data-gallery-goto]')) show(+e.target.closest('[data-gallery-goto]').dataset.galleryGoto);
        else if (e.target.closest('[data-lightbox-close]') || e.target === dialog) dialog.close();
    });

    dialog.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });

    // Balayage tactile dans la visionneuse.
    let startX = null;
    dialog.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
    dialog.addEventListener('touchend', (e) => {
        if (startX === null || photos.length < 2) return;
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) show(index + (dx < 0 ? 1 : -1));
        startX = null;
    });

    // Carrousel mobile : le compteur suit la photo visible.
    if (mosaicCounter && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) mosaicCounter.textContent = String(+entry.target.dataset.galleryOpen + 1);
            });
        }, { root: mosaic, threshold: 0.6 });
        gallery.querySelectorAll('.mosaic__item').forEach((item) => observer.observe(item));
    }
});

/** Sidebar en tiroir sur mobile : ouverture depuis « Menu », fermeture par le fond, la croix ou Échap. */
(() => {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    const backdrop = document.querySelector('.sidebar-backdrop');
    const openers = document.querySelectorAll('[data-sidebar-open]');
    const mobile = window.matchMedia('(max-width: 760px)');
    let lastFocus = null;

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        document.body.classList.toggle('sidebar-open', open);
        if (backdrop) backdrop.hidden = !open;
        openers.forEach((btn) => btn.setAttribute('aria-expanded', String(open)));

        if (open) {
            lastFocus = document.activeElement;
            sidebar.querySelector('a, button')?.focus();
        } else if (lastFocus) {
            lastFocus.focus();
            lastFocus = null;
        }
    };

    openers.forEach((btn) => btn.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open'))));
    document.querySelectorAll('[data-sidebar-close]').forEach((el) => el.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) setOpen(false);
    });
    // Repasse en mode bureau/tablette : on referme le tiroir.
    mobile.addEventListener('change', (e) => { if (!e.matches) setOpen(false); });
})();
