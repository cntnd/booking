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

  $('.cntnd_booking-checkbox').click(function(){
    if ($(this).is(':checked')){
      $(this).parents('div').attr('data-booking','blocked');
    }
    else {
      $(this).parents('div').removeAttr('data-booking');
    }
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


  function gatherElements(formId,elementClass){
    var elements=[];
    $('#'+formId+' .'+elementClass).each(function(){
      elements.push($(this).attr('name'));
    });
    return window.btoa(JSON.stringify(elements));
  }

  function validateBookings(){
    return true;
  }

  $('#cntnd_booking-reservation').submit(function() {
    $('.cntnd_booking-validation').addClass('hide');
    $('.cntnd_booking-validation-required').hide();
    $('.cntnd_booking-validation-dates').hide();
    $('.cntnd_booking-validation-days').hide();
    var required = $('#cntnd_booking-reservation .required').filter(function(){
      return ($(this).val()==='');
    });
    if (!validateBookings() || required.length>0){
      $('.cntnd_booking-validation').removeClass('hide');
      if (required.length>0){
        $('.cntnd_booking-validation-required').show();
      }
      return false;
    }
    $('#cntnd_booking-fields').val(gatherElements('cntnd_booking-reservation','form-control'));
    $('#cntnd_booking-required').val(gatherElements('cntnd_booking-reservation','required'));
    return true;
  });
});
