
let userId;


function setUserId(id) {
    userId = id;
}
document.addEventListener('DOMContentLoaded', function(){

document.getElementById('publish-comment').addEventListener('click', function() {
    const commentText = document.getElementById('comment-input').value;
    const postId = document.getElementById('popup-single-post-view').getAttribute('data-post-id');

    if (commentText) {
        fetch('add_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId, post_id: postId, comment: commentText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentsSection = document.querySelector('.comments-section');
                const newComment = document.createElement('div');
                newComment.classList.add('comment');
                newComment.innerHTML = `
                    <div class="img-temp"><img src="${data.profile_img}" alt="Zdjęcie profilowe"></div>
                    <div class="comment-text-container">
                        <p class="author-comment">${data.username}</p>
                        <p class="comment-text">${commentText}</p>
                    </div>
                `;
                commentsSection.appendChild(newComment);
                document.getElementById('comment-input').value = '';
            } else {
                alert('Wystąpił błąd podczas dodawania komentarza.');
            }
        });
    } else {
        alert('Proszę wpisać komentarz.');
    }
});









document.getElementById('popupclose').addEventListener('click', function() {
    document.getElementById('popup-single-post-view').style.display = 'none';
    location.reload();
});

document.querySelectorAll('.fa-heart').forEach(heartIcon => {
    heartIcon.addEventListener('click', function() {
        console.log(this.parentNode.parentNode);
        const postId = this.getAttribute('data-post-id');
        const isLiked = this.classList.contains('liked');
        const likeAmountText = this.parentNode.parentNode.querySelector('.likes-amount-text');

        if (isLiked) {
            this.classList.remove('liked'); 
            this.classList.remove('fa-solid');
            this.classList.add('fa-regular');
            updateLikes(postId, -1);
            likeAmountText.textContent = parseInt(likeAmountText.textContent) - 1;
        }else{
            this.classList.add('liked');
            this.classList.remove('fa-regular');
            this.classList.add('fa-solid');
            updateLikes(postId, 1);
            likeAmountText.textContent = parseInt(likeAmountText.textContent) + 1;
        }
    });
});

function updateLikes(postId, change) {
    fetch('updateLikes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `post_id=${postId}&change=${change}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Zaktualizowano licznik polubień:', data);
        console.log(postId);
    })
    .catch(error => console.error('Błąd:', error));
}

document.querySelectorAll('.image-container img').forEach(img => {
    img.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        const popup = document.getElementById('popup-single-post-view');
        const popupImage = popup.querySelector('.popup-post-left');
        const likeButton = popup.querySelector("#post-like-button");
        popup.setAttribute('data-post-id', postId);
        likeButton.setAttribute('data-post-id', postId);
        popupImage.src = this.src;

        fetch(`checkLikeStatus.php?post_id=${postId}&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.isLiked) {
                    likeButton.classList.add('liked');
                    likeButton.classList.remove("fa-regular");
                    likeButton.classList.add("fa-solid");
                } else {
                    likeButton.classList.remove('liked');
                    likeButton.classList.add("fa-regular");
                    likeButton.classList.remove("fa-solid");
                }

                fetch(`getPostData.php?post_id=${postId}`)
                    .then(response => response.json())
                    .then(data => {
                        popup.querySelector('.likes-amount-text').textContent = data.likes;
                        popup.querySelector('.date-post').textContent = data.date;

                    });
            })
            .catch(error => console.error('Błąd:', error));

        popup.style.display = 'flex';
    });
});


    document.querySelectorAll('.fa-heart').forEach(heartIcon => {
        const postId = heartIcon.getAttribute('data-post-id');
        fetch(`checkLikeStatus.php?post_id=${postId}&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.isLiked) {
                heartIcon.classList.add('liked');
                heartIcon.classList.remove("fa-regular");
                heartIcon.classList.add("fa-solid");
            } else {
                heartIcon.classList.remove('liked');
                heartIcon.classList.add("fa-regular");
                heartIcon.classList.remove("fa-solid");
            }

            fetch(`getPostData.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    const popup = document.getElementById('popup-single-post-view');
                    popup.querySelector('.likes-amount-text').textContent = data.likes;
                    popup.querySelector('.date-post').textContent = data.date;
                });
        })
        .catch(error => console.error('Błąd:', error));
    })












document.getElementById("create-post-button").addEventListener("click", function() {
    document.getElementById("popup-create-post-box-first").style.display = "flex";
});

document.getElementById("popup-close").addEventListener("click", function() {
    document.getElementById("popup-create-post-box-first").style.display = "none";
});
document.getElementById("popup-close-final").addEventListener("click", function() {
    document.getElementById("popup-reject-post").style.display = "flex";
});
document.querySelector(".reject-row-2").addEventListener("click", function(){
    document.getElementById("popup-create-post-box-final").style.display = "none";
    location.reload();
})
document.querySelector(".reject-row-3").addEventListener("click", function(){
    document.getElementById("popup-reject-post").style.display = "none";
})
document.getElementById("go-back-button").addEventListener("click", function(){
    document.getElementById("popup-reject-post-2").style.display = "flex";

    document.querySelector(".reject-row-2-2").addEventListener("click", function(){
        document.getElementById("popup-create-post-box-final").style.display = "none";
        document.getElementById("popup-create-post-box-first").style.display = "flex";
        document.getElementById("popup-reject-post-2").style.display = "none";
    })
    document.querySelector(".reject-row-3-3").addEventListener("click", function(){
        document.getElementById("popup-reject-post-2").style.display = "none";
})
})




const fileInput = document.getElementById("create-post-img-input");
const dropArea = document.getElementById("create-post-drop-area");

dropArea.addEventListener("dragover", (event) => {
    event.preventDefault();
    dropArea.classList.add('drag-over');
});

dropArea.addEventListener("dragleave", () => {
    dropArea.classList.remove('drag-over');
});

dropArea.addEventListener("drop", (event) => {
    event.preventDefault();
    dropArea.classList.remove('drag-over');

    const files = event.dataTransfer.files;
    if (files.length > 0) {
        handleFileUpload(files[0]);
    }
});

fileInput.addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
        handleFileUpload(file);
    }
});

function handleFileUpload(file) {
    let formData = new FormData();
    formData.append("file", file);

    fetch("upload.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("final-box-image-view").src = data.filePath;
            document.getElementById("popup-create-post-box-final").style.display = "flex";
            document.getElementById("popup-create-post-box-first").style.display = "none";
        } else {
            alert("Błąd w przesyłaniu zdjęcia.");
        }
    });
}

document.getElementById("button-choose-image-pc").addEventListener("click", function() {
    fileInput.click();
});




document.getElementById("share-post").addEventListener("click", function() {
    let description = document.querySelector("textarea[name='opis-postu']").value;
    let imageUrl = document.getElementById("final-box-image-view").src;

    fetch("save_post.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            user_id: userId,
            image_url: imageUrl,
            opis: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Post został opublikowany!");
            document.getElementById("popup-create-post-box-final").style.display = "none";

            location.reload();
        } else {
            alert("Błąd w publikowaniu postu.");
        }
    })
    .catch(error => {
        console.error("Błąd:", error);
    });
});




const textarea = document.querySelector("textarea[name='opis-postu']");
const counter = document.getElementById("textarea-amount");

textarea.addEventListener("input", function() {
    const length = textarea.value.length;
    counter.textContent = `${length}/350`;
});




document.querySelectorAll('.image-container img').forEach(img => {
    img.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        const popup = document.getElementById('popup-single-post-view');
        popup.setAttribute('data-post-id', postId);

        fetch(`get_comments.php?post_id=${postId}`)
            .then(response => response.json())
            .then(comments => {
                const commentsSection = popup.querySelector('.comments-section');
                commentsSection.innerHTML = '';
                comments.forEach(comment => {
                    const commentDiv = document.createElement('div');
                    commentDiv.classList.add('comment');
                    commentDiv.innerHTML = `
                        <div class="img-temp">
                            <img src="${comment.profile_img}" alt="Zdjęcie profilowe" style="width: 40px; height: 40px; border-radius: 50%;">
                        </div>
                        <div class="comment-text-container">
                            <p class="author-comment">${comment.login}</p>
                            <p class="comment-text">${comment.tresc_komentarza}</p>
                        </div>
                    `;
                    commentsSection.appendChild(commentDiv);
                });
            })
            .catch(error => console.error('Błąd:', error));

        popup.style.display = 'flex';
    });
});






function openCommentsPopup(postId) {
    const popup = document.getElementById('popup-single-post-view');
    popup.setAttribute('data-post-id', postId);


    fetch(`get_comments.php?post_id=${postId}`)
        .then(response => response.json())
        .then(comments => {
            const commentsSection = popup.querySelector('.comments-section');
            commentsSection.innerHTML = '';
            comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.classList.add('comment');
                commentDiv.innerHTML = `
                    <div class="img-temp">
                        <img src="${comment.profile_img}" alt="Zdjęcie profilowe" style="width: 40px; height: 40px; border-radius: 50%;">
                    </div>
                    <div class="comment-text-container">
                        <p class="author-comment">${comment.login}</p>
                        <p class="comment-text">${comment.tresc_komentarza}</p>
                    </div>
                `;
                commentsSection.appendChild(commentDiv);
            });
        })
        .catch(error => console.error('Błąd:', error));

    popup.style.display = 'flex'; 
}

document.querySelectorAll('.fa-comment').forEach(commentIcon => {
    popup = document.getElementById("popup-single-post-view");
    commentIcon.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        popup.querySelectorAll(".fa-heart").forEach(heart =>{
            heart.setAttribute('data-post-id', postId);
        })
        openCommentsPopup(postId);

        fetch(`getPostData.php?post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            popup.querySelector('.likes-amount-text').textContent = data.likes;
            popup.querySelector('.date-post').textContent = data.date;
            popup.querySelector('#popup-author-name').textContent = data.user_name;
            popup.querySelector("#profile-autor").src = data.profile_img;
            popup.querySelector("#post-image-url").src = data.image_url;
        });
    });
});

document.querySelectorAll('.comments-text-link').forEach(link => {
    link.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        popup.querySelectorAll(".fa-heart").forEach(heart =>{
            heart.setAttribute('data-post-id', postId);
        })
        openCommentsPopup(postId);
        fetch(`getPostData.php?post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            popup.querySelector('.likes-amount-text').textContent = data.likes;
            popup.querySelector('.date-post').textContent = data.date;
            popup.querySelector('#popup-author-name').textContent = data.user_name;
            popup.querySelector("#profile-autor").src = data.profile_img;
            popup.querySelector("#post-image-url").src = data.image_url;
        });
    });
});


const deletepostbutton = document.getElementById("delete-post-popup-button");
const deletepostbox = document.getElementById("popup-delete-post");

if(deletepostbutton){
    deletepostbutton.addEventListener("click", function() {
        const currentDisplay = getComputedStyle(deletepostbox).display;
    
        if (currentDisplay === "flex") {
            deletepostbox.style.display = "none";
        } else {
            deletepostbox.style.display = "flex";
        }
    });
}



const deletePostButton = document.getElementById("delete-post-button");
const anulujPostButton = document.getElementById("anuluj-post-button");
const deletePostPopup = document.getElementById("popup-delete-post");
if(anulujPostButton){
    anulujPostButton.addEventListener("click", function() {
        deletePostPopup.style.display = "none";
    });
}


if(deletePostButton){
    deletePostButton.addEventListener("click", function() {
        const postId = document.getElementById('popup-single-post-view').getAttribute('data-post-id');
        console.log(postId);
        fetch('delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `post_id=${postId}`
        })
        .then(response => response.text())
        .then(data => {
            if (data === "success") {
                document.getElementById('popup-single-post-view').remove();
                deletePostPopup.style.display = "none";
                location.reload();
                alert("Post usuniety!");
            } else {
                alert("Problem z usunieciem posta.");
                console.log(data);
                
            }
            
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });
}


});



