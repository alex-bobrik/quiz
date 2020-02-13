$(document).ready(function () {

    function addTagFormDeleteLink(tagFormLi) {
        let removeFormButton = $('<button type="button">X</button>');
        $(tagFormLi).append($(removeFormButton));

        $(removeFormButton).on('click', function (e) {
            $(tagFormLi).remove();
        });
    }

    let collectionHolder = $('ul#type-fields-list');

    $(collectionHolder).find('li').each(function() {
        addTagFormDeleteLink($(this));
    });

    $('.add-another-collection-widget').click(function (e) {
        let list = $($(this).attr('data-list-selector'));
        let counter = list.data('widget-counter') || list.children().length;
        let answersOnForm = list.children().length + 1;

        if(answersOnForm > 5){
            return 0;
        }else {

            let newWidget = list.attr('data-prototype');
            newWidget = newWidget.replace(/__name__/g, counter);
            counter++;
            list.data('widget-counter', counter);
            let newElem = $(list.attr('data-widget-tags')).html(newWidget);
            newElem.appendTo(list);
            addTagFormDeleteLink(newElem);

            console.log(counter);
        }
    });

    // validate question_form before submit
    $("#question_submit").on('click', function(e){

        let anwsersAmount = $("#type-fields-list").children().length;
        if (anwsersAmount < 1){
            alert('add at least 1 answer');
        } else {
            let checkboxesAmount = $("[type='checkbox']:checked").length;
            if (checkboxesAmount > 1) {
                alert('only one correct answer');
            } else if (checkboxesAmount === 0) {
                alert('select correct answer');
            } else {
                e.preventDefault();
                $('#question_form').submit();
            }
        }

    });

    // validate quiz_form before submit
    $("#quiz_submit").on('click', function(e){

       let questionsAmount = $("#type-fields-list").children().length;
       if (questionsAmount === 0) {
            alert('add at least 1 question for quiz');
        } else {
            e.preventDefault();
            $('#quiz_form').submit();
        }

    });

});