/* cntnd_booking */
$(document).ready(function(){
  $('.cntnd_booking-date').click(function(){
    $('.cntnd_booking .table tbody tr').addClass('res_hide');
    $(this).parents('tr').toggleClass('res_hide');
  });

  $('.res_checkbox').click(function(){
    var res = $(this).children('input');
    if (res.is(':checked')){
      $(this).parents('td').addClass('reserved');
    }
    else {
      $(this).parents('td').removeClass('reserved');
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

  $('#cntnd_booking-reservation').submit(function() {
    $('.cntnd_booking-validation').addClass('hide');
    var dates = $('.cntnd_booking-checkbox:checkbox:checked');
    var required = $('#cntnd_booking-reservation .required').filter(function(){
      return ($(this).val()==='');
    });
    if (dates.length===0 || required.length>0){
      $('.cntnd_booking-validation').removeClass('hide');
      return false;
    }
    return true;
  });
});
