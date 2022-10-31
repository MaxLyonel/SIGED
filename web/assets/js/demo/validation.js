$(function() {
    /* jQuery Validation */
    var validate2 = $("#validate2").validate({
        rules: {                                            
                login: {
                        required: true,
                        minlength: 2,
                        maxlength: 8
                },
                password: {
                        required: true,
                        minlength: 5,
                        maxlength: 10
                },
                're-password': {
                        required: true,
                        minlength: 5,
                        maxlength: 10,
                        equalTo: "#password2"
                },
                age: {
                        required: true,
                        min: 18,
                        max: 100
                },
                email: {
                        required: true,
                        email: true
                },
                date: {
                        required: true,
                        date: true
                },
                credit: {
                        required: true,
                        creditcard: true
                },
                site: {
                        required: true,
                        url: true
                },
                gender: "required",
                rules: "required"
            },                                        
            errorPlacement: function(error, element) {
                if (element.attr("name") == "gender" || element.attr("name") == "rules" ) {                                                
                    element.parents(".form-group").append(error);                                                
                }else
                    error.insertAfter(element);                                            
            }
    });
    /* // jQuery Validation */

    /* jQuery MaskedInputs */
    $.extend($.inputmask.defaults, {
        'autounmask': true
    });

	$("#inputmask-date").inputmask("d/m/y", {autoUnmask: true});
    $("#inputmask-date-1").inputmask("d/m/y",{ "placeholder": "*"});
    $("#inputmask-date-2").inputmask("d/m/y",{ "placeholder": "dd/mm/yyyy" });
    $("#inputmask-phone").inputmask("mask", {"mask": "(999) 999-9999"});
    $("#inputmask-tax").inputmask({"mask": "99-9999999"});
    $("#inputmask-decimal").inputmask('decimal', { rightAlignNumerics: false });
    $("#inputmask-currency").inputmask('\u20AC 999,999,999.99', { numericInput: true, rightAlignNumerics: false, greedy: false});
    $("#inputmask-ssn").inputmask("999-99-9999", {clearMaskOnLostFocus: true });
    /* // jQuery MaskedInputs */
});
