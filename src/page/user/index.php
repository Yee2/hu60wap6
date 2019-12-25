<?php
$tpl = $PAGE->start();
$USER->start($tpl);
if (!$USER->islogin) {
    $USER->gotoLogin(true);
}

if (isset($_GET['floorReverse'])) {
    $USER->setinfo('bbs.floorReverse', (bool)$_GET['floorReverse']);
}
/*隐私设置 */
// 隐藏我的帖子
if(isset($_GET['privacy:hidePost'])){
    $USER->setinfo('privacy:hidePost', (bool)$_GET['privacy:hidePost']);
}

// 禁止搜索引擎收录我的帖子
if(isset($_GET['privacy:noindex'])){
    $USER->setinfo('privacy:noindex', (bool)$_GET['privacy:noindex']);
}

$tpl->assign('floorReverse', $USER->getinfo('bbs.floorReverse'));

if (isset($_POST['newChatNum'])) {
    $newChatNum = (int)$_POST['newChatNum'];
    if ($newChatNum < 1) {
        $newChatNum = 1;
    } elseif ($newChatNum > 10) {
        $newChatNum = 10;
    }
    $USER->setinfo('chat.newchat_num', $newChatNum);
}

$tpl->assign('newChatNum', $USER->getinfo('chat.newchat_num') > 1 ? $USER->getinfo('chat.newchat_num') : 1);

if ($USER->uid == '1') {
    $tpl->assign('mmbt', "admin");
}

$hasRegPhone = null != $USER->getRegPhone();
$tpl->assign('hasRegPhone', $hasRegPhone);

$tpl->display('tpl:index');
