jQuery(document).ready(function ($) {
    var markInput;
	var dateInput;
    $(".licey_edit_marks_table input").bind({    
        focus: function () {
            markInput = $(this).val();
        },
        keyup: function () {    
            var newInput = $(this).val();
            if (newInput.length > 0) {
                if (newInput != 'н' && newInput != '*') {
                    newInput = 1*newInput;
                    if (newInput > 5 || newInput < 1 || isNaN(newInput)) $(this).val(markInput);
					else markInput = newInput;
                } else markInput = newInput;
            }
        }
    });
	
	$(".licey-dates-edit").bind({    
        focus: function () {
            dateInput = $(this).val();
        },
        keyup: function () {    
            var month = $(this).next("select").val();
			month = month*1 - 1;
			var newInput = $(this).val();
			var d = new Date(2005, month, newInput);
			if (newInput.length > 0) {
                newInput = 1*newInput;
                if (isNaN(newInput) || d.getDate() != newInput) $(this).val(dateInput);
				else dateInput = newInput;
            }
        }
    });
	
	$(".licey-student-list-item").bind({
		mouseenter: function () {
			$(this).css('background-color', '#dde3e8');
			$('input', this).css('display', 'inline');
		},
		mouseleave: function () {
			$(this).css('background-color', '#fff');
			$('input', this).css('display', 'none');
		}
	});

    $(".licey_edit_sched_table li").add(".licey_edit_sched_table div").hover( function() {
        $(this).toggleClass('hover');
        var inputs = $(this).children('input').add( $(this).children('form') );
        inputs.toggle();
        if( inputs.first().is(':checked') ) {
            inputs.first().show();
            $(this).addClass('hover');
        }
    });

    $("<span>показать</span>").appendTo('.licey_student_edit h2.edit_marks').toggle(function() {
        $(this).text('скрыть');
        $(this).parent().next().show();
    }, function() {
        $(this).text('показать');
        $(this).parent().next().hide();
    })
});