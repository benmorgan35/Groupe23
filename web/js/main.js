

//script for modals
$(function () {
    $(".modal-trigger").click(function (e) {
        e.preventDefault();


        $('.modal').modal({
                closeIcon: true,
                dismissible: true, // Modal can be dismissed by clicking outside of the modal
                opacity: .5, // Opacity of modal background
                inDuration: 300, // Transition in duration
                outDuration: 200, // Transition out duration
                startingTop: '4%', // Starting top style attribute
                endingTop: '4', // Ending top style attribute
                ready: function() { // Callback for Modal open. Modal and trigger parameters available.
                    $('.collapsible').collapsible();
                },
                complete: function() { $('.collapsible').collapsible(); } // Callback for Modal close
            }
        );

        $(".modal .modal-content").load(this.href, function () {

        })
        $('select').material_select();
    })
});

$('#modal2').focus(function() {
    $('.collapsible').collapsible();
});

// script for Alerts
$('#alert_close').click(function(){
    $( "#alert_box" ).fadeOut( "slow", function() {
    });
});
//script document ready
$(document).ready(function () {
    //Menu de navigation
    $(".button-collapse").sideNav();
    $('.tooltipped').tooltip({delay: 50});

    $('.collapsible').collapsible();

    $('.dropdown-content').addClass('dropdown-style-change');

    //select
    $('select').material_select();

});

//FONCTION PUR LE FORMULAIRE D'EXPORT
$('#modal').on('show.bs.modal', function (e) {
    $('select').material_select();
    if
    (!navigator.userAgent.match(/(android|iphone|windows phone|ipad|edge|Chrome|CrOS|CriOS|Edge|Opera)/gi))
    {
        //On utilise Datepicker Jquery
        $(".datepicker").datepicker(
            {
                maxDate: new Date()
            }).attr("readonly", "readonly");;

    }
    else {

        $(".datepicker").attr("type", "date");
    }
    $('#app_bundle_observation_filter_type_taxref').hide();
    $("label[for='app_bundle_observation_filter_type_taxref']").hide();

    $('#app_bundle_observation_filter_type_especeFilter').click(function(){
        if($('#app_bundle_observation_filter_type_especeFilter').prop('checked')){
            $('#app_bundle_observation_filter_type_taxref').show();
            $("label[for='app_bundle_observation_filter_type_taxref']").show();
        }else{
            $('#app_bundle_observation_filter_type_taxref').hide();
            $("label[for='app_bundle_observation_filter_type_taxref']").hide();
        }
    });
});



jQuery.datetimepicker.setLocale('fr');
jQuery('.datetimepicker').datetimepicker({
    format: 'd/m/Y H:i'
});

