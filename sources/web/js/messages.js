// envoyer le message si on appuie sur ENTREE
$("#messageForm textarea").keypress(function(event) {
    if (event.which == 13) {
        event.preventDefault();
        submitMessage();
    }
});

// envoyer le message si on clique sur le bouton VALIDER
$("#messageForm button").click(function () {
    submitMessage();
});

// redimensionner la liste des messages lorsque la fenêtre est redimensionnée
$( window ).resize(resizeMessagesList);

// calcule la nouvelle hauteur de la liste des messages et l'applique
function resizeMessagesList() {
    var size = $(window).height() 
                - $("nav.navbar").height() 
                - $('#messenger > .row:first-child').height()
                - $('#messageForm').height()
                - $('footer').height()
                - 70;
    $("#messagesList").height(size);
    $("#messagesList").scrollTop($("#messagesList")[0].scrollHeight);
}

// envoie le message au serveur via une requête AJAX et applique
// les modifications
function submitMessage (event){
    var message = $('#messageForm textarea').val();
    if (message == "") return false;
    var thread = $('#messagesList').data("thread");
    $('#messageForm textarea').val("");
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_message_post'),
        data: { 'message': message, 'thread' : thread},
        cache: false,
        success: function(data){
            $("#messagesList").append(data.message);
            $("#messagesList").scrollTop($("#messagesList")[0].scrollHeight);
        },
        error : function(){
            $('#messageForm textarea').val(message);
        }
    });
    return false;
};

function setNewValues(event, ui) {
    event.preventDefault();
    $("#autocompNewThread").val(ui.item.label);
    $("#userId").val(ui.item.value);
};

// on document ready :
$(function() { 
    // redimensionner la liste des messages
    resizeMessagesList(); 


    $( "#autocompNewThread" ).autocomplete({
        source: Routing.generate('pilote_message_newThread_searchUser'),
        minLength: 2,
        select: setNewValues,
        focus: setNewValues,
        change: function (e, ui) {
            if(ui.item==null) {
                $("#userId").val(null);
            }
        },
        create: function (e, ui) {
            $(".ui-menu").addClass("dropdown-menu");
        }
    });


    $( "#autocompAddParticipant" ).autocomplete({
        source: Routing.generate('pilote_message_addParticipant_searchUser', {
            'threadId' : $('#messagesList').data('thread')
        }),
        minLength: 2,
        select: function (event, ui) {
            event.preventDefault();
            $("#autocompAddParticipant").val(ui.item.label);
            $("#addParticipantUserId").val(ui.item.value);
            $("#addParticipant .btn-primary").prop('disabled', false);
        },
        focus: function (event, ui) {
            event.preventDefault();
            $("#autocompAddParticipant").val(ui.item.label);
            $("#addParticipantUserId").val(ui.item.value);
            $("#addParticipant .btn-primary").prop('disabled', false);
        },
        change: function (e, ui) {
            if(ui.item==null) {
                $("#addParticipantUserId").val(null);
                $("#addParticipant .btn-primary").prop('disabled', true);
            }
        },
        create: function (e, ui) {
            $(".ui-menu").addClass("dropdown-menu");
        },
        appendTo: "#addParticipant" 
    });
});