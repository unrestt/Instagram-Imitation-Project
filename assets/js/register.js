const chooseImgDiv = document.getElementById('choose-img');
const profileImgInput = document.getElementById('profile_img');
const chooseFileText = document.getElementById('choose-file');
const imgprofile = document.getElementById("img-profile-change");

chooseImgDiv.addEventListener('dragover', (event) => {
    event.preventDefault();
    chooseImgDiv.classList.add('drag-over');
});

chooseImgDiv.addEventListener('dragleave', () => {
    chooseImgDiv.classList.remove('drag-over');
});

chooseImgDiv.addEventListener('drop', (event) => {
    event.preventDefault();
    chooseImgDiv.classList.remove('drag-over');
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        profileImgInput.files = files;
        chooseFileText.innerText = shortenFileName(files[0].name);

        // Dodanie funkcjonalności wyświetlania obrazu
        const file = files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgprofile.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});


// Funkcja do obsługi wyboru pliku
profileImgInput.addEventListener('change', (event) => {
    const files = event.target.files;
    if (files.length > 0) {
        chooseFileText.innerText = shortenFileName(files[0].name);
    }
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        imgprofile.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
});


document.getElementById('register_form').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('register_step1.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes("success")) {
            document.getElementById('register-container-1').style.display = 'none';
            document.getElementById('register-container-2').style.display = 'block';
        } else {
            document.getElementById('register-message').textContent = data;
        }
    })
    .catch(error => console.error('Error:', error));
});

function shortenFileName(fileName, maxLength = 15) {
    if (fileName.length > maxLength) {
        return fileName.substring(0, maxLength) + '...';
    }
    return fileName;
}
