function readURL(input,elForDisplay) {
    // input -> where original image
    // elForDisplay -> where display
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            $(elForDisplay).attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}