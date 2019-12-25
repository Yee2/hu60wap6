<?php
$tpl = $PAGE->start();
$USER->start($tpl);
$bbs = new bbs($USER);
$search = new search();

//获取页码
$p = (int)$_GET['p'];
if ($p < 1) $p = 1;
$tpl->assign('p', $p);

$size = 20;
$offset = ($p - 1) * $size;

//获取搜索词
$keywords = $_GET['keywords'];
$username = $_GET['username'];
$searchType = $_GET['searchType'];

if ($keywords == '' && $username == '') {
  $tpl->assign('count', 0);
  //显示版块列表
  $tpl->display('tpl:searchtopic');
}

if($searchType != 'reply') {
  $result = [];
  if(empty($username)){
    $result = $search->searchTopic($keywords, $username, $offset, $size, $count);
  }else{
    // 根据用户隐私设置 是否展示指定用户的所有帖子
    $targetUser = new userinfo();
    if($targetUser->name($username) && $targetUser->getinfo("privacy:hidePost")){
    }else{
      $result = $search->searchTopic($keywords, $username, $offset, $size, $count);
    }
  }
  //获取帖子列表
  $maxP = ceil($count / $size);
  $topicList = [];
  foreach ($result as $v) {
    $topic = $bbs->topicMeta($v['tid'], '*');
    // 偶尔会有回复内容存在但是主题帖丢失的情况
    if (empty($topic)) {
        continue;
    }
    $forum = $bbs->forumMeta($topic['forum_id'], 'name');
    $topic['forum_name'] = $forum['name'];
    $topic['reply_count'] = $bbs->topicContentCount($v['tid']) - 1;
    $topic['uinfo'] = new userinfo();
    $topic['uinfo']->uid($topic['uid']);
    $topicList[] = $topic;
  }
  // 列表整个为空时跳转到上一页或最大页
  // 避免搜索结果为空时循环重定向
  if (empty($topicList) && $p > 1) {
    $u = '?keywords='.urlencode($keywords).'&username='.urlencode($username).'&p='.min($p-1, $maxP);
    header('Location: '.$u);
    die;
  }
  $tpl->assign('topicList', $topicList);
  $tpl->assign('count', $count);
  $tpl->assign('maxP', $maxP);
  //显示版块列表
  $tpl->display('tpl:searchtopic');
}
else {
  $result = $search->searchReply($keywords, $username, $offset, $size, $count);
  $uinfo = new UserInfo();
  $uinfo->name($username);

  $maxP = ceil($count / $size);
  foreach ($result as &$v) {
      $topic = $bbs->topicMeta($v['topic_id'], '*');
      
      // 偶尔会有回复内容存在但是主题帖丢失的情况
      if (empty($topic)) {
          continue;
      }
      $v['topic']=$topic;
      $v['uinfo'] = new userinfo();
      $v['uinfo']->uid($topic['uid']);
  }

  //加载 UBB 组件
  $ubb = new ubbdisplay();
  $uinfo->setUbbOpt($ubb);

  $tpl->assign('ubb', $ubb);
  $tpl->assign('replyList', $result);
  $tpl->assign('count', $count);
  $tpl->assign('maxP', $maxP);
  $tpl->display('tpl:searchreply');
}
