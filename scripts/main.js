/*Main Javascript File*/
$(document).ready(function(){
    // Clear any entered question text on modal hide
    var addModal = $("#addOrEditQuestion");
    addModal.on('hidden.bs.modal', function() {
        $("#questionLanguage").val('');
        $("#questionText").val('');
        $("#questionInputTest").val('');
        $("#questionInputGrade").val('');
        $("#questionSolution").val('');
    });
    addModal.on('shown.bs.modal', function() {
        $("#questionText").focus();
    });

    $("#editTitleLink").on("click", function() {
        closeNav();
        $("#toolTitle").focus();
    });

    $("#toolTitle").on("blur", function() {
        saveTitle();
    }).on("keypress", function(e) {
        if(e.which === 13) {
            this.blur();
        }
    });
});
function saveTitle() {
    var sessionId = $("#sess").val();
    var titleText = $("#toolTitle").text();
    $.ajax({
        type: "post",
        url: "actions/UpdateMainTitle.php?PHPSESSID="+sessionId,
        data: {
            "toolTitle" : titleText,
            "nonav" : true
        }
    });
}
function confirmDeleteQuestion() {
    return confirm("Are you sure you want to delete this question? This action cannot be undone.");
}
function confirmResetTool() {
    return confirm("Are you sure you want to remove all questions and answers from this tool? This cannot be undone.");
}
function openSideNav() {
    document.getElementById("sideNav").style.left = "0";
}
function closeNav() {
    document.getElementById("sideNav").style.left = "-200px";
}
function editQuestionText(questionId) {
    $("#questionText"+questionId).hide();
    var theForm = $("#questionTextForm"+questionId);
    theForm.parent().find('.question-actions').hide();
    theForm.parent().find('.question-answers').hide();
    theForm.addClass("fadeInFast");
    theForm.show();
    theForm.find('textarea[name="questionText"]').focus();
}
function cancelEditQuestionText(questionId) {
    $("#questionText"+questionId).fadeIn(400);
    var theForm = $("#questionTextForm"+questionId);
    theForm.parent().find('.question-actions').show();
    theForm.parent().find('.question-answers').show();
    theForm.removeClass("fadeInFast");
    theForm.hide();
}

