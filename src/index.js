$(document).ready(function () {
  $("form").submit(function (event) {
    var formData = {
      name: $("#fullname").val(),
      email: $("#email").val(),
      password: $("#password").val()
    };
  
    $.ajax({
      type: "POST",
      url: "index.php",
      data: { 'data' : JSON.stringify(formData) },
      dataType: "json",
      encode: true,
      success : function(d){
        console.log(d)
      },
      error : function(e){
        
        console.log('error', typeof this.data)
      }
    })
      event.preventDefault();
    });
  });

  $(function() {
    $('input[name="datetimes"]').daterangepicker({
      linkedCalendars: false,
      timePicker: true,
      startDate: moment().startOf('hour'),
      endDate: moment().startOf('hour').add(32, 'hour'),
      locale: {
        format: 'M/DD hh:mm A'
      }
    }, function(start, end) {
      $.ajax({
        type: "GET",
        url: "index.php",
        data: { 'data' :  JSON.stringify({"start": start._d, "end" : end._d })},
        dataType : 'json',
        encode : true,
        success : (d) => {
          console.log(d)
        },
        error : (e) => {
          console.log("Service unAvailable", e)
          alert ("Service unAvailable")
        }})}
    )})

        