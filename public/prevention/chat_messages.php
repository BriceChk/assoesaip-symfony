<?php
$bdd = new PDO('mysql:host=localhost;dbname=assoesaip_dev;charset=utf8', 'assoesaip_dev', 'ecompecomp');
$messages = $bdd->query("SELECT * FROM message WHERE author_id = " . $_POST['id'] . " ORDER BY message_date");

while ($message = $messages->fetch()) {
?>
    <div class="message card <?= $_POST['is_prevention'] && $message['is_prevention'] ? 'ml-auto' : '' ?>" style="width:fit-content">
        <div class="card-body" data-toggle="tooltip" title="<?= date_create($message['message_date'])->format('H:i, d/m/Y') ?>">
            <?= $message['content'] ?>
        </div>
    </div>
<?php
}
$bdd = null;
