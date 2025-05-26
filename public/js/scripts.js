document.addEventListener("DOMContentLoaded", function () {

    // === COOKIE BANNER ===
    const cookieBanner = document.getElementById("cookieConsentBanner");
    const acceptBtn = document.getElementById("acceptCookiesBtn");

    if (cookieBanner && acceptBtn && !localStorage.getItem("cookiesAccepted")) {
        cookieBanner.style.display = "block";

        acceptBtn.addEventListener("click", function () {
            localStorage.setItem("cookiesAccepted", "true");
            cookieBanner.style.display = "none";
        });
    }

    // === PROMO NABÍDKA ===
    const promoModal = document.getElementById("promoOfferModal");
    const closePromoBtn = document.getElementById("closePromoOfferBtn");

    if (promoModal && closePromoBtn) {
        if (!localStorage.getItem("promoOfferShown")) {
            promoModal.style.display = "flex";
            localStorage.setItem("promoOfferShown", "true");
        }

        closePromoBtn.addEventListener("click", function () {
            promoModal.style.display = "none";
        });
    }

    // === LAZY LOAD GALERIE ===
    const galleryContainer = document.getElementById("gallery-container");

    if (galleryContainer) {
        fetch("/galerie/load-images")
            .then(response => response.text())
            .then(html => {
                galleryContainer.innerHTML = html;

                if (window.lightbox) {
                    lightbox.option({
                        resizeDuration: 200,
                        wrapAround: true
                    });
                }
            })
            .catch(error => {
                galleryContainer.innerHTML = "<p>Obrázky se nepodařilo načíst.</p>";
                console.error("Chyba při načítání galerie:", error);
            });
    }

    // === PARTNEŘI POPOVER ===
    const partnersTrigger = document.getElementById('partnersPopover');
    const partnersPopoverElement = document.getElementById('customPartnersPopover');

    if (partnersTrigger && partnersPopoverElement) {
        new bootstrap.Popover(partnersTrigger, {
            trigger: 'focus',
            html: true,
            content: partnersPopoverElement.innerHTML,
            placement: 'bottom',
            customClass: 'partners-popover'
        });
    }

    // === KONTAKT POPOVER ===
    const contactTrigger = document.getElementById('contactPopover');

    if (contactTrigger) {
        const contactContent = `
            <ul class="list-unstyled mb-3">
                <li><strong>Email:</strong> <a href="mailto:alukol@post.cz">alukol@post.cz</a></li>
                <li><strong>Telefon:</strong> +420 778 013 813</li>
                <li><strong>Adresa:</strong> Ludéřov 42, 783 44 Drahanovice</li>
                <li><strong>IČO:</strong> 09925066</li>
            </ul>
            <a href="${window.location.origin}/kontakt" class="btn btn-danger">Kontaktujte nás</a>
        `;

        new bootstrap.Popover(contactTrigger, {
            trigger: 'focus',
            html: true,
            content: contactContent,
            placement: 'bottom',
            customClass: 'contact-popover'
        });
    }

    // === TINYMCE EDITOR ===
    const contentField = document.querySelector('#content');
    if (contentField) {
        tinymce.init({
            selector: '#content',
            height: 400,
            menubar: false,
            plugins: 'link lists code',
            toolbar: 'undo redo | styleselect | bold italic | bullist numlist | link | code',
            content_css: 'default'
        });
    }
});

