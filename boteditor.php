<?php
require_once '../sae/auth.php';
if($_SERVER['REQUEST_METHOD'] == 'POST' AND !empty($_POST['answersdata'])){
  
  $answersdata = $_POST['answersdata'];
  
  $fwrite_info = array('error' => array());
  
  $folder = 'buben-db/';
  $filename = "answersdata.json";
  $backup_folder = $folder."backup/";
  $backup_filename = $backup_folder.date('ymd-His')."_".$filename;
  $filename = $folder.$filename;
  
  if(!is_dir($backup_folder)){
    mkdir($backup_folder, 0777, true);
  }  
  
  if(!file_exists($filename)){
    $file = fopen($filename, "w");
    fclose($file);
  }
  
  $file = fopen($filename, "r");
  $file_backup = fopen($backup_filename, "w");
  if(filesize($filename)){
    fwrite($file_backup, fread($file, filesize($filename)));
  }
  fclose($file);
  $file = fopen($filename, "w");
  
  
  $fwriten = fwrite($file, $answersdata);
  
  if(empty($answersdata)){
    $fwrite_info['error'][] = "Input data is empty";
  }
  
  
  $fwrite_info = array_merge($fwrite_info,array(
    'fwriten' => $fwriten,
    'strlen' => strlen($answersdata),
    'status' => strlen($answersdata) === $fwriten ? true : false,
    ));
  
  fclose($file);
  fclose($file_backup);
    
    
  echo json_encode($fwrite_info);
  
  // Сохранить файл в .json
  // Сохранить бэкап перезаписываемого файла
  // Удалить лишние бэкапы
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>BotEditor JSON</title>
    <!--<link rel="stylesheet" id="theme_stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">-->
    <link rel="stylesheet" id="theme_stylesheet" href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css">
    
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/css/selectize.bootstrap3.css">
    <script src="be-js/jsoneditor.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/js/standalone/selectize.js"></script>
  </head>
  <body>
    <style>
      #editor_holder > div > .well{
        background-color: transparent;
        border:none;
        padding: 0;
        -webkit-box-shadow: none;
        -moz-box-shadow: none;
        box-shadow: none;
      }
    </style>
    <div class="container">
      <h1>BotEditor JSON</h1>
      
      <button id='submit'>Save</button>
      <span id='valid_indicator'></span>
      
      <div id='editor_holder'></div>
    </div>
    <script>
      JSONEditor.plugins.selectize.enable = true;
      JSONEditor.defaults.theme = 'bootstrap2';
      
      // This is the starting value for the editor
      // We will use this to seed the initial editor 
      // and to provide a "Restore to Default" button.
      var starting_value = <?php echo file_get_contents('buben-db/answersdata.json'); ?>;
      
      // Initialize the editor
      var editor = new JSONEditor(document.getElementById('editor_holder'),{
        // Enable fetching schemas via ajax
        ajax: true,
        
        // The schema for the editor
        schema: {
          type: "array",
          title: "Answers",
          //format: "tabs",
          items: {
            title: "Answer",
            headerTemplate: "{{self.question}}{{self.command}} ({{i}})",
            $ref: "buben-db/basic_person.json",
          }
        },
        
        // Seed the form with a starting value
        startval: starting_value,
        
        // Disable additional properties
        no_additional_properties: true,
        
        // Require all properties by default
        required_by_default: true,
        
        disable_array_delete_last_row:true,
        disable_collapse:true,
        disable_edit_json:true,
        disable_properties:true
      });
      
      // Hook up the submit button to log to the console
      document.getElementById('submit').addEventListener('click',function() {
        // Get the value from the editor
        console.log(editor.getValue());
        output = JSON.stringify(editor.getValue());
    		$.ajax({
    			type: "POST",
    			data: {answersdata: output},
    			url:'<?php echo $_SERVER['PHP_SELF']; ?>',
    			dataType:'json',
    			success: function(data){
    			  console.log(data);
    			},
    			error: function (xhr, ajaxOptions, thrownError){
    				console.log(xhr.responseText);
    			}
    		});
      });
      /*
      // Hook up the Restore to Default button
      document.getElementById('restore').addEventListener('click',function() {
        editor.setValue(starting_value);
      });
      
      // Hook up the enable/disable button
      document.getElementById('enable_disable').addEventListener('click',function() {
        // Enable form
        if(!editor.isEnabled()) {
          editor.enable();
        }
        // Disable form
        else {
          editor.disable();
        }
      });
      */
      
      // Hook up the validation indicator to update its 
      // status whenever the editor changes
      editor.on('change',function() {
        // Get an array of errors from the validator
        var errors = editor.validate();
        
        var indicator = document.getElementById('valid_indicator');
        
        // Not valid
        if(errors.length) {
          indicator.style.color = 'red';
          indicator.textContent = "not valid";
        }
        // Valid
        else {
          indicator.style.color = 'green';
          indicator.textContent = "valid";
        }
      });
    </script>
  </body>
</html>
