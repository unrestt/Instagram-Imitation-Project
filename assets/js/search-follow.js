function searchUsers() {
    const query = document.getElementById('search-input').value;
    
    if (query.length > 0) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `search_users.php?q=${query}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('search-results').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    } else {
        document.getElementById('search-results').innerHTML = '';
    }
}

function followUser (event, userId) {
    event.preventDefault(); // Zapobiega domyślnej akcji (np. przewijaniu na górę)

    const followLink = document.querySelector(`a[data-userid='${userId}']`);
    
    // Sprawdzenie, czy użytkownik jest już obserwowany
    if (followLink.classList.contains('followed')) {
        console.log("unfollowanie");
        // Użytkownik jest już obserwowany, więc odobserwuj
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `unfollow_user.php?unfollow_user_id=${userId}`, true);
        xhr.onload = function() {
            console.log(xhr.responseText); // Log the response
            if (xhr.status === 200) {
                if (xhr.responseText === 'unfollowed') {
                    // Change text and class
                    followLink.classList.remove('followed');
                    followLink.textContent = 'Obserwuj';
                } else {
                    alert('Wystąpił błąd podczas odobserwowania użytkownika.');
                }
            }
        };
        xhr.send();
    } else {
        console.log("followanie");
        // Użytkownik nie jest obserwowany, więc zaobserwuj
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `follow_user.php?follow_user_id=${userId}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText === 'followed') {
                    // Zmień tekst i klasę
                    followLink.classList.add('followed');
                    followLink.textContent = 'Obserwujesz';
                } else if (xhr.responseText === 'already_followed') {
                    alert('Już obserwujesz tego użytkownika.');
                } else {
                    alert('Wystąpił błąd.');
                }
            }
        };
        xhr.send();
    }
}