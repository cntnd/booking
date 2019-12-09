<script>
$(document).ready(function(){
  $('.cntnd_booking-admin-choose').click(function(){
    $('.card.cntnd_booking').removeClass('focus');
    var res = $(this).parents('.card.cntnd_booking');
    var admin = $('.cntnd_booking-admin-action');
    var offset = $('.cntnd_booking-admin-action h5').outerHeight(true);
    admin.width(admin.width());
    admin.css('position','absolute').css('top',(res.position().top-offset));
    res.addClass('focus');
    $('#cntnd_booking-admin input[name=resid]').val(res.data('resid'));
    showTimeslot(res.data('timeslot'));
  });

  $('.cntnd_booking-admin-cancel').click(function(){
    $('.card.cntnd_booking').removeClass('focus');
    $('.cntnd_booking-admin-action').css('position','static');
    $('#cntnd_booking-admin input[name=resid]').val('');
    hideTimeslot();
  });

  $('.cntnd_booking-admin-delete').click(function(){
    $('#cntnd_booking-admin input[name=action]').val('delete');
    $('#cntnd_booking-admin').submit();
  });

  $('#cntnd_booking-admin').submit(function() {
    var resid = $('#cntnd_booking-admin input[name=resid]').val();
    if (resid==''){
      $('.cntnd_booking-admin-error').removeClass('hide');
      return false;
    }
    hideTimeslot();
    return true;
  });

  function showTimeslot(timeslot){
    $('.cntnd_booking-admin-timeslot > .timeslot').text(timeslot);
    $('.cntnd_booking-admin-timeslot').removeClass('hide');
  }

  function hideTimeslot(){
    $('.cntnd_booking-admin-timeslot > .timeslot').text('');
    $('.cntnd_booking-admin-timeslot').addClass('hide');
  }
});
</script>
