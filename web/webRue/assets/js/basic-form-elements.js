$(function () {
    //Textare auto growth
    autosize($('textarea.auto-growth'));

    //Datetimepicker plugin
    $('.datetimepicker').bootstrapMaterialDatePicker({
        format: 'DD-MM-YYYY - HH:mm',
        clearButton: true,
        weekStart: 1,
        lang : 'es',
        cancelText : 'Cancelar',
        clearText : 'Limpiar',
        okText : 'Aceptar'
    });

    $('.datepicker').bootstrapMaterialDatePicker({
        format: 'DD-MM-YYYY',
        clearButton: true,
        weekStart: 1,
        lang : 'es',
        cancelText : 'Cancelar',
        clearText : 'Limpiar',
        okText : 'Aceptar',
        time: false
    });

    $('.timepicker').bootstrapMaterialDatePicker({
        format: 'HH:mm',
        clearButton: true,
        lang : 'es',
        cancelText : 'Cancelar',
        clearText : 'Limpiar',
        okText : 'Aceptar',
        date: false
    });
});