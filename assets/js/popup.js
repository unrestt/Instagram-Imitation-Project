function showPopup(id) {
    const popup = document.getElementById(id);
    const message = popup.getAttribute("data-message"); // Pobierz wiadomość z atrybutu
    if (message) {
        popup.querySelector('p').textContent = message; // Ustaw treść pop-up
        popup.classList.remove("hidden");
        setTimeout(() => {
            popup.classList.add("visible");
        }, 10);

        setTimeout(() => {
            popup.classList.add("fade-out");
            setTimeout(() => {
                popup.classList.remove("visible", "fade-out");
                popup.classList.add("hidden");
            }, 500);
        }, 3000);
    }
}

window.onload = function() {
    showPopup("popup_2"); // Wywołaj funkcję dla drugiego pop-upu (hasło)
};
