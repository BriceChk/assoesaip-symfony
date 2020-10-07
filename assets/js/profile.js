$("#user_avatarFile").change(function() {
    if (this.files && this.files[0]) {
        let reader = new FileReader();

        reader.onload = function(e) {
            let logoProjet = $('#photo');
            logoProjet.attr('src', e.target.result);
            logoProjet.show();
        };

        reader.readAsDataURL(this.files[0]);
    }
});