<script>
$(document).ready(function(){
  // config
  $('.cntnd_booking-config-add').click(function (){
     var date = $(this).attr('data-date');

     var table = $("table.order-list.date__"+date);

     var lastRow = table.find("tbody > tr").last();
     var counter = lastRow.attr("data-row");
     counter++;

     var newRow = $('<tr data-row="'+counter+'">');
     var cols = "";

     cols += '<td><input type="time" class="form-control" placeholder="Zeit (HH:mm)" name="config['+date+']['+counter+'][time]" required/></td>';
     cols += '<td><input type="number" class="form-control" placeholder="Anzahl Slots" name="config['+date+']['+counter+'][slots]" required/></td>';
     cols += '<td><input type="text" class="form-control" placeholder="Bemerkung" name="config['+date+']['+counter+'][comment]"/></td>';
     cols += '<td><button type="button" class="btn btn-sm cntnd_booking-config-delete">Löschen</button></td>';

     newRow.append(cols);
     table.append(newRow);
  });

  $("table.order-list").on("click", ".cntnd_booking-config-delete", function (event) {
      $(this).closest("tr").remove();
  });

  $('.cntnd_booking-config-save').click(function (){
     $('#cntnd_booking-config').submit();
  });

  // admin
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
