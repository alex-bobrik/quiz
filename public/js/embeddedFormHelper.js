$(document).ready(function () {

    const MAX_ELEMENTS = 10;

    function addTagFormDeleteLink(tagFormLi) {
        let removeFormButton = $('<button type="button" class="btn btn-danger">X</button>');
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
        let elementsOnForm = list.children().length + 1;

        if(elementsOnForm > MAX_ELEMENTS){
            return 0;
        }else {

            let newWidget = list.attr('data-prototype');
            newWidget = newWidget.replace(/__name__/g, counter);
            counter++;
            list.data('widget-counter', counter);
            let newElem = $(list.attr('data-widget-tags')).html(newWidget);
            newElem.appendTo(list);
            addTagFormDeleteLink(newElem);
            $('.selectpicker').selectpicker('refresh')
        }
    });

    // validate question_form before submit
    $('#question_form').submit(function () {
        let anwsersAmount = $("#type-fields-list").children().length;
        if (anwsersAmount < 1) {
            alert('Add at least 1 answer');
            return false;
        } else {
            let checkboxesAmount = $("[type='checkbox']:checked").length;
            if (checkboxesAmount > 1) {
                alert('There can only be 1 correct answer');
                return false;
            } else if (checkboxesAmount === 0) {
                alert('Choose correct answer');
                return false;
            } else {
                return true;
            }
        }
    });

    // validate quiz_form before submit
    $('#quiz_form').submit(function () {
        let questionsAmount = $("#type-fields-list").children().length;
        if (questionsAmount === 0) {
            alert('Add at least 1 question for quiz');
            return false;
        } else {
            return true;
        }
    });
});