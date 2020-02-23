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

    function checkValidation(){
        let checkboxesAmount = $("[type='checkbox']:checked").length;
        if (checkboxesAmount > 1) {
            alert('There can only be one correct answer');
            return false;
        } else if (checkboxesAmount === 0) {
            alert('Choose the correct answer');
            return false;
        }

        return true;
    }

    $("#send").click(function () {

        if(!checkValidation())
            return false;

        let form = $("#question_form");
        let data = form.serializeObject();
        let gameId = $("#game_id").val();
        let btnNext = $("#next");

        $(this).prop("disabled", true);
        $(this).val("Wait...");

        $.ajax({
            url: '/games/play/' + gameId,
            type: 'POST',
            dataType: 'json',
            data: data,
            success:function(data){

                if (data === "CORRECT")
                {
                    $(".result").addClass("correct-answer");
                    $("#result").text("CORRECT");
                }else
                {
                    $(".result").addClass("wrong-answer");
                    $("#result").text("INCORRECT");
                }

                btnNext.removeClass("disabled");
                $("#send").val('Send answer');
            }
        });
    });
});

