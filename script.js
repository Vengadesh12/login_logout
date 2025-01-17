document.getElementById('profile-pic-form').addEventListener('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(this);

    fetch('upload.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.status === 'success') {
              document.getElementById('user-pic').src = data.filename;
          } else {
              alert(data.message);
          }
      });
});
