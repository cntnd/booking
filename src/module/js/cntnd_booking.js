/* cntnd_booking */
$(document).ready(function(){
  $("#res").validate();

  $('.more-less').click(function(){
    $('.hide-dat').toggleClass('hide');
    $('.more-less').toggleClass('hide');
  });
});

var last_check=0;
function show(id,count){
      // checkboxen anzeigen/ausblenden
      var i=0;
      for (i=0;i<=count;i++) {
         if (last_check>0){
             // letzte zeile ausblenden
             document.getElementById(last_check+"-"+i).style.display="none";
         }
         document.getElementById(id+"-"+i).style.display="block";
      }
      last_check = id;
}
