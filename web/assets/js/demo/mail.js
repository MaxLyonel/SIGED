$(function() {

    // Initialize Bootstrap Checbox
    $('.bscheckbox').checkbox({
        checkedClass: 'fa fa-check-square-o',
        uncheckedClass: 'fa fa-square-o'
    });

    /* Select2 */
    if($("#select2").length > 0) 
        $("#select2").select2();
});