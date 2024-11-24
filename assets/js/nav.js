document.getElementById('search-button').addEventListener('click', function() {
    document.querySelector('.navigation-bar').classList.toggle('search-active');
    document.querySelector("main").classList.toggle('search-active');
    const searchSidePanel = document.querySelector(".search-side-panel");
    
    if (searchSidePanel.classList.contains("show")) {
        searchSidePanel.classList.remove("show");
    } else {
        searchSidePanel.classList.add("show");
    }
});

