<?php
$bdd = new PDO('mysql:host=localhost;dbname=assoesaip_dev;charset=utf8', 'assoesaip_dev', 'ecompecomp');
$stmt = $bdd->prepare("
    INSERT INTO message (id, author_id, content, message_date, is_prevention) 
    VALUES (NULL, :id, :content,'" . date('Y-m-d H:i:s') . "', :is_prevention);
");

$stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
$stmt->bindParam(':content', $_POST['content'], PDO::PARAM_STR);
$stmt->bindParam(':is_prevention', $_POST['is_prevention'], PDO::PARAM_INT);
$stmt->execute();

$messages = $bdd->query("SELECT * from message where author_id = " . $_POST['id'] . " ORDER BY message_date");

while ($message = $messages->fetch()) {
?>
    <div class="message">
        <p><?= $message['content'] ?></p>
        <i><?= date_create($message['message_date'])->format('H:i, d/m/Y') ?></i>
    </div>
<?php
}

$bdd = null;
