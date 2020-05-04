$(document).ready(function () {

    $.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
});

    function checkValidation(){
        let checkboxesAmount = $("[type='checkbox']:checked").length;
        if (checkboxesAmount === 0) {
            alert('Выберите хотя бы 1 правильный ответ');
            return false;
        }

        return true;
    }