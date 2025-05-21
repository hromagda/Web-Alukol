document.addEventListener("DOMContentLoaded", function () {
    // COOKIE BANNER
    const cookieBanner = document.getElementById("cookieConsentBanner");
    const acceptBtn = document.getElementById("acceptCookiesBtn");

    if (cookieBanner && acceptBtn && !localStorage.getItem("cookiesAccepted")) {
        cookieBanner.style.display = "block";

        acceptBtn.addEventListener("click", function () {
            localStorage.setItem("cookiesAccepted", "true");
            cookieBanner.style.display = "none";
        });
    }

    // AKČNÍ NABÍDKA
    const promoModal = document.getElementById("promoOfferModal");
    const closePromoBtn = document.getElementById("closePromoOfferBtn");

    if (promoModal && closePromoBtn) {
        // Zobraz promo okno jen pokud ještě nebylo zobrazeno
        if (!localStorage.getItem("promoOfferShown")) {
            promoModal.style.display = "flex";
            localStorage.setItem("promoOfferShown", "true");
        }

        // Zavře promo okno po kliknutí na křížek
        closePromoBtn.addEventListener("click", function () {
            promoModal.style.display = "none";
        });
    }
});

// JS pro nacitani obrazku v galerii

document.addEventListener("DOMContentLoaded", function () {
    // Načti obrázky galerie AJAXem
    const galleryContainer = document.getElementById("gallery-container");

    fetch("/galerie/load-images")
        .then(response => response.text())
        .then(html => {
            galleryContainer.innerHTML = html;

            // Znovu nastav lightbox (to stačí, není nutné .init())
            if (window.lightbox) {
                lightbox.option({
                    resizeDuration: 200,
                    wrapAround: true
                });
            }
        }) // konec .then()
        .catch(error => {
            galleryContainer.innerHTML = "<p>Obrázky se nepodařilo načíst.</p>";
            console.error("Chyba při načítání galerie:", error);
        });
});

const partnersContent = `
    <div class="d-flex flex-column align-items-center">
        <img src="/images/logo/isotra.jpg" alt="Isotra" class="mb-2">
        <img src="/images/logo/neva.jpg" alt="NEVA" class="mb-2">
        <img src="/images/logo/veka_logo.png" alt="VEKA">
    </div>
`;

const partnersTrigger = document.getElementById('partnersPopover');

const popover = new bootstrap.Popover(partnersTrigger, {
    content: partnersContent,
    placement: 'bottom',
    trigger: 'focus', // zavře se kliknutím mimo
    html: true
});

// Aktivace kontakt popoveru
const contactContent = `
    <ul class="list-unstyled mb-3">
        <li><strong>Email:</strong> <a href="mailto:alukol@post.cz">alukol@post.cz</a></li>
        <li><strong>Telefon:</strong> +420 778 013 813</li>
        <li><strong>Adresa:</strong> Ludéřov 42, 783 44 Drahanovice</li>
        <li><strong>IČO:</strong> 09925066</li>
    </ul>
    <a href="${window.location.origin}/kontakt" class="btn btn-danger">Kontaktujte nás</a>
`;

const contactTrigger = document.getElementById('contactPopover');
if (contactTrigger) {
    new bootstrap.Popover(contactTrigger, {
        trigger: 'focus',
        html: true,
        content: contactContent,
        placement: 'bottom',
        customClass: 'contact-popover'
    });
}