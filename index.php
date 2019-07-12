<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Amazon Inorganic</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 text-center">
            <h1>Amazon Inorganic </h1>
        </div>
        <div class="col-md-12 text-center" style="margin: 12px 0 12px 0;">
            <div class="col-md-2 offset-5">
                <select id="locale" class="form-control">

                </select>
            </div>

        </div>
        <div class="col-md-12 text-center">
            <a class="btn btn-success btn-lg" href="export.php?action=products" id="exportProducts">Export Data</a>
            <a class="btn btn-success btn-lg" href="export.php?action=inputs" id="exportInputs">Export Inputs</a>
            <button class="btn btn-lg btn-success" data-toggle="modal" data-target="#localeModal">Add Locale</button>
            <a class="btn btn-outline-secondary btn-lg" href="../proxy.php">Edit proxies</a>
        </div>
        <div class="col-md-12 text-center">
            <hr>
            <form id="form">
                <input type="file" name="importFile" id="import-file-holder">
                <button type="submit" class="btn btn-success btn-md" id="submitBtn">Import</button>
            </form>
            <hr>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="localeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Locale</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" id="inputLocale" placeholder="Locale">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success btnAddLocale">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="assets/js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
      $('#form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $.ajax({
          url: 'Controller/api.php?action=import&locale='+$('#locale').val(),
          type: 'POST',
          xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            $('#submitBtn').attr('disabled', true).html('Importing....');
            return myXhr;
          },
          success: function (data) {
            if(data == 1){
              alert('Keywords successfully imported.');
            }else{
              alert('Oops somehting went wrong, please try again.')
            }
            $('#submitBtn').removeAttr('disabled').html('Import');
          },
          data: formData,
          cache: false,
          contentType: false,
          processData: false
        });
        return false;
      });


      getLocale();


      $('.btnAddLocale').click(function(){
         $locale = $('#inputLocale').val();
         if($locale != ''){
             $.ajax({

                 url: 'Controller/api.php?action=addLocale',
                 type: 'POST',
                 data: {locale: $locale},
             dataType: 'json',

         }).done(function (result) {
                if(result.success == true){
                    alert('Locale successfully added');
                    $('#inputLocale').val('');
                    $('#localeModal').modal('hide');
                    getLocale();
                }else{
                    alert('Locale already exists');
                }
             });
         }else{
             alert('Please input locale');
         }
      });


      $('#locale').on('change', function(){
          $loc = $(this).val();
          $('#exportProducts').attr('href', 'export.php?action=products&locale='+$loc);
          $('#exportInputs').attr('href', 'export.php?action=inputs&locale='+$loc);
      })
    });


    function getLocale(){
        $.ajax({
            url: 'Controller/api.php?action=getLocale',
            type: 'POST',
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                return myXhr;
            },
            success: function (data) {
                $json = JSON.parse(data);
                $html = '';
                $.each($json, function(index, key){
                    if(index == 0){
                        $('#exportProducts').attr('href', 'export.php?action=products&locale='+key.symbol);
                        $('#exportInputs').attr('href', 'export.php?action=inputs&locale='+key.symbol);
                    }
                    $html += '<option value="'+key.symbol+'">'+key.symbol+'</option>';
                });

                $('#locale').html($html);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }
</script>
</body>

</html>