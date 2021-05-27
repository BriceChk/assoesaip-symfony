<?php
$userId = $_POST['userId'];
$bdd = new PDO('mysql:host=localhost;dbname=assoesaip_dev;charset=utf8', 'assoesaip_dev', 'ecompecomp');
$test = $bdd->query("SELECT * from message where author_id = " . $userId);

while ($message = $test->fetch()) {
?>
    <div class="message">
        <p><?= $message['content'] ?></p>
        <i><?= date_create($message['message_date'])->format('H:i, d/m/Y') ?></i>
    </div>
<?php
}
$bdd = null;
