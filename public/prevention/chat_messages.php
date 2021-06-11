<?php
$bdd = new PDO('mysql:host=localhost;dbname=assoesaip_dev;charset=utf8', 'assoesaip_dev', 'ecompecomp');
$messages = $bdd->query("SELECT * FROM message WHERE author_id = " . $_POST['id'] . " ORDER BY message_date");


while ($message = $messages->fetch()) {
    $bool = $_POST['is_prevention'] ? $message['is_prevention'] : !$message['is_prevention'];
?>
    <div class="message card <?= $bool ? 'ml-auto' : '' ?>" style="width:fit-content">
        <div class="card-body" data-toggle="tooltip" title="<?= date_create($message['message_date'])->format('H:i, d/m/Y') ?>">
            <?= $message['content'] ?>
        </div>
    </div>
<?php
}
$bdd = null;
