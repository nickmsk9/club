<?php
// Srinivas Tamada http://9lessons.info
// Loading Comments link with load_updates.php 

if (!isset($Wall) || !isset($msg_id)) {
    die('Ошибка: переменные не определены');
}

$commentsarray = $Wall->Comments($msg_id, 0);

if (!empty($x)) {
    $comment_count = count($commentsarray);
    $second_count = $comment_count - 2;

    if ($comment_count > 2) {
?>
<div class="comment_ui" id="view<?php echo $msg_id; ?>">
<a href="#" class="view_comments" id="<?php echo $msg_id; ?>">View all <?php echo $comment_count; ?> comments</a>
</div>
<?php
        $commentsarray = $Wall->Comments($msg_id, $second_count);
    }
}

if (!empty($commentsarray)) {
    foreach ($commentsarray as $cdata) {
        $com_id = $cdata['com_id'];
        $comment = tolink($cdata['comment']);
        $time = $cdata['created'];
        $username = $cdata['username'];
        $uid = $cdata['uid_fk'];
        // User Avatar
        if (!empty($gravatar))
            $cface = $Wall->Gravatar($uid);
        else
            $cface = $Wall->Profile_Pic($uid);
        // End Avatar
?>
<div class="stcommentbody" id="stcommentbody<?php echo $com_id; ?>">
<div class="stcommentimg">
<img src="<?php echo $cface; ?>" class='small_face' alt='<?php echo $username; ?>'/>
</div> 
<div class="stcommenttext">
<a class="stcommentdelete" href="#" id='<?php echo $com_id; ?>' title='Delete Comment'></a>
<b><a href="<?php echo $base_url . $username; ?>"><?php echo $username; ?></a></b> <?php echo clear($comment); ?>
<div class="stcommenttime"><?php echo $time; ?></div> 
</div>
</div>
<?php 
    }
}
?>