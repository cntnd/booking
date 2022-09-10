/* cntnd_booking */
$(document).ready(function(){
  $('.cntnd_booking-date').click(function(){
    $('.cntnd_booking-checkbox:checked').prop( "checked", false);
    $('.cntnd_booking__slot').removeAttr('data-booking');

    if (!$(this).closest('tr').hasClass('highlight')) {
      $('table.cntnd_booking-table tbody tr').removeClass('highlight');
    }
    $(this).closest('tr').toggleClass('highlight');
  });

  $('.cntnd_booking-date_vertical').click(function(){
    var slot = $(this).attr('data-slot');
    toggleInterval(slot, "cntnd_booking_slots--vertical");
  });

  $('.cntnd_booking-date_interval').click(function(){
    var slot = $(this).attr('data-slot');
    toggleInterval(slot,"cntnd_booking_slots--interval");
  });


  $('.cntnd_booking--action').click(function(){
    var slot = $(this).attr('data-slot');
    toggleInterval(slot,"cntnd_booking_slots--interval");

    var type = $(this).attr('data-action-type');
    toggleType(type);
  });

  function toggleInterval(slot, className) {
    $('.cntnd_booking-checkbox:checked').prop("checked", false);
    $('.cntnd_booking__slot').removeAttr('data-booking');

    $('.'+className).removeClass('highlight');
    $("."+className+"[data-slot='"+slot+"']").addClass('highlight');
  }

  function toggleType(type) {
    if (type!=="all"){
      $('.highlight > .'+type+' .cntnd_booking-checkbox').prop("checked", true);
      $('.highlight > .'+type+' .cntnd_booking-checkbox').parents('div').attr('data-booking','blocked');
    }
    else {
      $('.highlight .cntnd_booking-checkbox').prop("checked", true);
      $('.highlight .cntnd_booking-checkbox').parents('div').attr('data-booking','blocked');
    }
  }

  $('.cntnd_booking-checkbox').click(function(){
    if ($(this).is(':checked')){
      $(this).parents('div').attr('data-booking','blocked');
    }
    else {
      $(this).parents('div').removeAttr('data-booking');
    }
  });

  $('.cntnd_booking-radio').click(function(){
    $('.cntnd_booking-radio').parents('div').removeAttr('data-booking');

    if ($(this).is(':checked')){
      $(this).parents('div').attr('data-booking','blocked');
    }
  });

  function gatherElements(formId,elementClass){
    var elements=[];
    $('#'+formId+' .'+elementClass).each(function(){
      elements.push($(this).attr('name'));
    });
    return window.btoa(JSON.stringify(elements));
  }

  function validateBookings(){
    if ($('#cntnd_booking-one_click_booking').val()){
      return $(".cntnd_booking-radio[name='booking']").is(":checked");
    }
    return $('.cntnd_booking-checkbox:checked').length>0;
  }

  $('#cntnd_booking-reservation').submit(function() {
    $('.cntnd_booking-validation').addClass('hide');
    $('.cntnd_booking-validation-required').hide();
    $('.cntnd_booking-validation-dates').hide();
    var required = $('#cntnd_booking-reservation .required').filter(function(){
      return ($(this).val()==='');
    });
    var bookings=validateBookings();
    if (!bookings || required.length>0){
      $('.cntnd_booking-validation').removeClass('hide');
      if (required.length>0){
        $('.cntnd_booking-validation-required').show();
      }
      if (!bookings){
        $('.cntnd_booking-validation-dates').show();
      }
      return false;
    }
    $('#cntnd_booking-fields').val(gatherElements('cntnd_booking-reservation','form-control'));
    $('#cntnd_booking-required').val(gatherElements('cntnd_booking-reservation','required'));
    return true;
  });

  $('.cntnd_booking-more').click(function(){
    $(this).addClass('hide');
    $('.not-in-range').removeClass('hide');
    $('.cntnd_booking-less').removeClass('hide');
  });

  $('.cntnd_booking-less').click(function(){
    $(this).addClass('hide');
    $('.not-in-range').addClass('hide');
    $('.cntnd_booking-more').removeClass('hide');
  });

  if ($('#cntnd_booking-form').length > 0) {
    document.querySelector('#cntnd_booking-form').scrollIntoView({
      behavior: 'smooth'
    });
  }
});
