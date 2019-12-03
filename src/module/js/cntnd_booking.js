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
});
