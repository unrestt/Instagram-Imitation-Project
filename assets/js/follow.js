document.addEventListener('DOMContentLoaded', function() {
    const followButton = document.querySelector('.follow');
    const unfollowButton = document.querySelector('.unfollow');

    if (followButton) {
        followButton.addEventListener('click', function(event) {
            event.preventDefault();
            const form = new FormData();
            form.append('follow', true);
            sendFollowRequest(form);
        });
    }

    if (unfollowButton) {
        unfollowButton.addEventListener('click', function(event) {
            event.preventDefault();
            const form = new FormData();
            form.append('unfollow', true);
            sendFollowRequest(form);
        });
    }

    function sendFollowRequest(formData) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true); // Wysyłanie do tego samego pliku PHP
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = xhr.responseText;
                if (response === 'followed') {
                    // Po udanym obserwowaniu, zmiana przycisków i odświeżenie strony
                    followButton.style.display = 'none';
                    unfollowButton.style.display = 'inline-block';
                    location.reload(); // Odświeżenie strony
                } else if (response === 'unfollowed') {
                    // Po udanym odobserwowaniu, zmiana przycisków i odświeżenie strony
                    unfollowButton.style.display = 'none';
                    followButton.style.display = 'inline-block';
                    location.reload(); // Odświeżenie strony
                }   
            }
        };
        xhr.send(formData);
    }
});