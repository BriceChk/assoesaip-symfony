<?php
$bdd = new PDO('mysql:host=localhost;dbname=assoesaip_dev;charset=utf8', 'assoesaip_dev', 'ecompecomp');
$users = $bdd->query("
    SELECT user.id, user.is_anonymous, 
        CONCAT(user.first_name, ' ', user.last_name) as 'full_name', 
        CONCAT('Anonyme',SUBSTRING(ms_id,15,4)) as 'anonymous_id'
    FROM message, user
    WHERE user.id = message.author_id
    GROUP BY user.id
    ORDER BY MAX(message.message_date) DESC
");


while ($user = $users->fetch()) {
?>
    <li class="py-1" role="button" onclick="load(<?= $user['id'] ?>, this)"><?= $user['is_anonymous'] ? $user['anonymous_id'] : $user['full_name'] ?></li>
<?php
}
$bdd = null;
