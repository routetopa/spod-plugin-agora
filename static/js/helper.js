$(document).ready(function () {
    $('#sroll_content').perfectScrollbar();
});


showHelper =  function() {
    var dialog = document.getElementById("helpDialog");
    if (dialog) {
        dialog.open();
    }
};
