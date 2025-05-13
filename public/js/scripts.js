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