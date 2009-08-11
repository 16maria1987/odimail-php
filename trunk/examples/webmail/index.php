<?php
include_once 'config.php';

$connection = new Odimail_Connection();

$pageSize = 5;
$currentMailbox = isset($_GET['mbox']) ? $_GET['mbox'] : 'INBOX';
$currentPage    = isset($_GET['page']) ? $_GET['page'] : 1;

$config['mailbox'] = $currentMailbox;
$connection->open($config);

$messagesCount = $connection->countMessages();
$pagesCount    = ceil($messagesCount / $pageSize);

$numStart = (($currentPage - 1) * $pageSize) + 1; 
$numEnd   = $currentPage * $pageSize;

if ($numEnd > $messagesCount) {
    $numEnd = $messagesCount;
}

$mailboxes = $connection->getMailboxes();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="style.css" type="text/css" rel="stylesheet" />
	
	<title>Webmail</title>
</head>

<body>
<div id="page">
	<div class="margin">
		<div id="hd">
			<h1>Webmail</h1>
		</div>
	
		<div id="messages-panel">
			<h2><?php echo $currentMailbox ?></h2>
			
			<div class="paginator">
				<?php for ($i = 1; $i <= $pagesCount; $i++){ ?>
					<a href="index.php?mailbox=<?php echo $currentMailbox?>&amp;page=<?php echo $i ?>"><?php echo $i ?></a>
				<?php } ?>
			</div>
			
			<table class="messages-list">
			<tr>
				<th>From</th>
				<th>Subject</th>
				<th>Date</th>
			</tr>
			<?php 
			$odd = false;
			for ($msgInd = $numStart; $msgInd <= $numEnd; $msgInd++) {
			    $message = $connection->getMessage($msgInd);
			    $rowClass = ($odd) ? 'odd' : 'even'; 
			?>
			<tr class="<?php echo $rowClass ?>">
				<td><?php echo $message->getFrom()->getEmail() ?></td>
				<td><?php echo $message->getSubject() ?></td>
				<td><?php echo $message->getDate('Y-m-d') ?></td>
			</tr>
			<?php
			    $odd = !$odd; 
			} 
			?>
			</table>
			
		</div>

		<div id="folders-panel">
			<h3>Mail Folders</h3>
			
			<ul>
			<?php foreach ($mailboxes as $mailbox) { ?>
				<li><a href="index.php?mailbox=<?php echo $mailbox ?>"><?php echo $mailbox ?></a></li>
			<?php } ?>
			</ul>
		
		</div>	

		<div id="ft">
			Odimail - Juan Odicio Arrieta - Lima, Peru 2009
		</div>
		
	</div>
</div>
</body>
</html>
