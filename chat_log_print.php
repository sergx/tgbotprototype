<?php require_once '../sae/auth.php'; ?>
<html>
<head>
  <title>Bot log</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.1/css/bulma.css" />
</head>
<body>
<style>
  .msgfrom_u > .is-9{
    margin-left:10% !important;
    text-align: right;
  }
  .msgfrom_u .msg_block{
    display: flex;
    flex-direction: row-reverse;
  }
  .msg_block .sender{
    text-align: center;
  }
  .chat_time {
    position: absolute;
    right: 0;
    top: 50%;
    margin-top: -12px;
  }
  .chat_wr > .columns{
    position: relative;
  }
  .chat_wr{
    padding: 50px 0;
  }
</style>

<div class="container">
  <h1 class="title">Chat Log</h1>
<?php
$table_log = json_decode("[".file_get_contents('buben-db/chat_log.json')."]", true);


$last_chat_id = false;

echo "<div class='chat_wr'>";
foreach($table_log as $row){
  if(!in_array($last_chat_id, array(false, $row['chat']))){
    echo "<hr>";
  }
  echo '<div class="columns is-gapless msgfrom_'.$row['s'].'">';
    echo '<div class="chat_time"><span class="tag is-white">'.$row['t'].'</span></div>';
    echo '<div class="column is-9">';
      echo '<div class="chat_item">';
        echo '<div class="columns msg_block">';
          echo '<div class="column is-2 sender">';
            echo '<div class="box">';
              echo  $row['s'] == "u" ? "<span class='tag is-medium is-dark'>User</span>" : "<span class='tag is-medium is-primary'>Bot</span>";
              echo '<br><span class="tag is-white">'.$row['chat'].'</span>';
            echo '</div>';
          echo '</div>';
          echo '<div class="column is-8"><pre class="chat_text">'.$row['data']['text']. (!empty($row['rm']) ? '<br>[Кнопки]': '' ).'</pre></div>';
        echo "</div>";
        
        //echo '<div>'.  .'</div>';
        echo '';
        //echo "<td>". ( $row['s'] == "u" ? "User" : "Bot") . (!empty($row['rm']) ? ' [btns]': '' ) ."</td>";
      echo "</div>";
    echo "</div>";
  echo "</div>";
  $last_chat_id = $row['chat'];
}
echo "</div>";
?>
</div>

</body>
</html>