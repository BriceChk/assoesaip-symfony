<?php
$bdd = new PDO('mysql:host=localhost;dbname=assoesaip_dev;charset=utf8', 'assoesaip_dev', 'ecompecomp');
$messages = $bdd->query("SELECT * FROM message WHERE author_id = " . $_POST['id'] . " ORDER BY message_date");

while ($message = $messages->fetch()) {
?>
    <div class="message">
        <p><?= $message['content'] ?></p>
        <i><?= date_create($message['message_date'])->format('H:i, d/m/Y') ?></i>
    </div>
<?php
}
$bdd = null;
